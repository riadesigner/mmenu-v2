<?php

header('content-type: application/json; charset=utf-8');
$callback = $_REQUEST['callback'] ?? 'alert';
if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }   

define("BASEPATH",__file__);


require_once getenv('WORKDIR').'/config.php';
 

require_once WORK_DIR.APP_DIR.'core/common.php';	

require_once WORK_DIR.APP_DIR.'core/class.sql.php';
 
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.user.php';

require_once WORK_DIR.APP_DIR.'core/class.order_sender.php';

session_start();
SQL::connect();

// SEND IIKO ORDER FOR DELIVERY

if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("--it needs to know id_cafe");
if(!isset($_POST['token'])) __errorjsonp("--it needs to token");
if(!isset($_POST['order'])) __errorjsonp("--empty order");
if(!isset($_POST['pickupself'])) __errorjsonp("--need to set pickupself option");

$order_data = $_POST['order'];

$time_sent = post_clean($order_data['order_time_sent'],100);
if(empty($time_sent)) __errorjsonp("--wrong order data, ".__LINE__);

$time_need = post_clean($order_data['order_time_need'],100);
if(empty($time_need)) $time_need = $time_sent;

define('NEARTIME_MODE',$time_need==$time_sent);

$total_price = (float) $order_data['order_total_price'];
$token = $_POST['token'];
$orders = $order_data['order_items'];

define('PICKUPSELF_MODE',filter_var($_POST['pickupself'], FILTER_VALIDATE_BOOLEAN));

// TODO CHECK USER PHONE (+7 and 8 digits)

$user_phone = $order_data['order_user_phone'];
if(empty($user_phone))__errorjsonp("--need to know user phone");

if(!PICKUPSELF_MODE){
	$u_address = $order_data['order_user_iiko_address'];
	if(empty($u_address['u_street']))__errorjsonp("--need to know user street");
	if(empty($u_address['u_house']))__errorjsonp("--need to know user house");	
	$order_delivery_street = $u_address['u_street'];
	$order_delivery_house = $u_address['u_house'];
	$order_delivery_flat = $u_address['u_flat'];
	$order_delivery_entrance = $u_address['u_entrance'];
	$order_delivery_floor = $u_address['u_floor'];	
}else{
	$order_delivery_street = "";
	$order_delivery_house = "";
	$order_delivery_flat = "";
	$order_delivery_entrance = "";
	$order_delivery_floor = "";		
}

// TODO

// order_time_need,
// order_time_sent,
// order_user_comment

$id_cafe = (int) $_POST['id_cafe'];
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);

$DEMO_MODE = (int) $cafe->cafe_status !== 2 ;

$k = $cafe->iiko_api_key;
if(empty($k)) __errorjsonp("Cant find iiko api for the cafe, ".__LINE__);

define('THE_ORDER_WAY',(int) $cafe->order_way);

$orgs = $cafe->iiko_organizations;
$orgs = !empty($orgs)?json_decode((string) $orgs,true):false;
if(!$orgs) __errorjsonp("--cant find iiko organization_id for the cafe, ".__LINE__);
$organization_id = $orgs['current_organization_id'];

$terminal_groups = $cafe->iiko_terminal_groups;
$terminal_groups = !empty($terminal_groups)?json_decode((string) $terminal_groups,true):false;
if(!$terminal_groups) __errorjsonp("--cant find iiko terminal_groups for the cafe, ".__LINE__);
$terminal_group_id = $terminal_groups['current_terminal_group_id'];

$menus = $cafe->iiko_extmenus;
$menus = !empty($menus)?json_decode((string) $menus,true):false;
if(!$menus) __errorjsonp("--cant find iiko menus for the cafe, ".__LINE__);
$menu_id = $menus['current_extmenu_id'];

// ---------------------------------
//   GETTTING ID FOR REGULAR ORDER
// ---------------------------------

$order_types = $cafe->iiko_order_types;
$order_types = !empty($order_types)?json_decode((string) $order_types,true):[];
if(!count($order_types)) __errorjsonp("--cant find iiko order types, ".__LINE__);

$order_type_id = "";
foreach($order_types as $o_type){
	if(PICKUPSELF_MODE){
		// PICKUPSELF MODE
		if(mb_strtolower((string) $o_type['orderServiceType']) === 'deliverypickup'){
			$order_type_id = $o_type['id'];
			break;
		}
	}else{
		// DELIVERY MODE
		if(mb_strtolower((string) $o_type['orderServiceType']) === 'deliverybycourier'){
			$order_type_id = $o_type['id'];
			break;
		}
	}
}

// -----------------------------
//   PREPAIRING ORDER ITEMS
// -----------------------------

$order_items = [];
foreach($orders as $order_row){
	$amount = $order_row['count'];
	$productSizeId = $order_row['sizeId'];
	$productId =  $order_row['item_data']['id_external'];
	$orderItemType = $order_row['item_data']['iiko_order_item_type'];
	$chosenModifiers = $order_row['chosen_modifiers'] ?? false;

	$item = [
			"type"=>"{$orderItemType}",
			"amount"=>"{$amount}",			
		];

	if(!empty($productSizeId)) $item["productSizeId"]=$productSizeId;
	
	$modifiers = [];
	if($chosenModifiers && count($chosenModifiers)){		
		foreach($chosenModifiers as $m){
			$mod = [ 'productId'=>$m['id'], 'amount'=>1 ];
			if(!empty($m['modifierGroupId'])) $mod['productGroupId'] = $m['modifierGroupId'];				
			$modifiers[] = $mod;			
		}		
	}

	if(mb_strtolower((string) $orderItemType)==="product"){
		// Order Type Product
		$item["productId"] = $productId;		
		if(count($modifiers)) $item["modifiers"]=$modifiers;
	}else if(mb_strtolower((string) $orderItemType)==="compound"){			
		// Order Type Compound
		$item["primaryComponent"] = [ "productId" => $productId ];
		if(count($modifiers)) $item["commonModifiers"]=$modifiers;
	}

	array_push($order_items,$item);

}


if(PICKUPSELF_MODE){
	$order_deliveryPoint = ""; 	
}else{
	$order_deliveryPoint = [
		"address"=>[
			"street"=>[
				"name"=>$order_delivery_street,
				"city"=>"Владивосток"
			],
			"house"=>$order_delivery_house,
			"flat"=>$order_delivery_flat,
			"entrance"=>$order_delivery_entrance,
			"floor"=>$order_delivery_floor,
			"doorphone"=>""
		]
	];
}



// --------------------------------------------
//   BUILDING ORDER ARRAY FOR SENDING TO IIKO
// --------------------------------------------

$order = [
		"menuId"=>"{$menu_id}",
		"id"=>"",
		"externalNumber"=>"",
		// "completeBefore"=>"", // null if now()
		"phone"=>$user_phone,
		"orderTypeId"=>$order_type_id,
		// "orderServiceType"=> "DeliveryByCourier",
		"deliveryPoint"=>$order_deliveryPoint,
		// "comment"=>"",		
		// "customer"=>"",
		// "guests"=>"",
		// "marketingSourceId"=>"",
	    "items"=>$order_items,
	    // "combos"=>"",
	    // "payments"=>"",
	    // "tips"=>"",
	    "sourceKey"=>"ChefsMenu",
	    // "discountsInfo"=>"",
	    // "loyaltyInfo"=>"",
	    // "chequeAdditionalInfo"=> "",
	    // "externalData"=>""
	];

// -----------------------------
//     SAVE COPY ORDER IN DB
// -----------------------------
	
define("ORDER_TARGET",Order_sender::IIKO_DELIVERY);
$pending_mode = THE_ORDER_WAY===1?true:false;
$demo_mode = false;
$short_number = Order_sender::save_order_to_db(ORDER_TARGET, $pending_mode, $cafe, $order, $demo_mode);
if(!$short_number)__errorjsonp("--cant save order");

$order['externalNumber'] = $short_number;


// -----------------------------------
//     BUILDING ORDER FOR TELEGRAM 
// -----------------------------------

// --- preparing order for telegram ---

$time_format = 24;
$order_currency = !empty($cafe->cafe_currency) ? $cafe->cafe_currency : "RUB"; 
$order_arr_currency = ["USD"=>"$", "RUB"=>"₽", "EUR"=>"€", "JPY"=>"¥", "GBP"=>"£", "KRW"=>"₩"];
$str_currency = $order_arr_currency[$order_currency];


$order_items_tg = [];
foreach($orders as $order_row){
	$amount = $order_row['count'];
	$productSizeId = $order_row['sizeId'];
	$productId =  $order_row['item_data']['id_external'];
	$orderItemType = $order_row['item_data']['iiko_order_item_type'];
	$chosenModifiers = $order_row['chosen_modifiers'] ?? false;

	$item = [
			"type"=>"{$orderItemType}",
			"amount"=>"{$amount}",			
		];

	if(!empty($productSizeId)) $item["productSizeId"]=$productSizeId;
	
	$modifiers = [];
	if($chosenModifiers && count($chosenModifiers)){		
		foreach($chosenModifiers as $m){
			$mod = [ 'productId'=>$m['id'], 'amount'=>1 ];
			if(!empty($m['modifierGroupId'])) $mod['productGroupId'] = $m['modifierGroupId'];				
			$modifiers[] = $mod;			
		}		
	}

	if(mb_strtolower((string) $orderItemType)==="product"){
		// Order Type Product
		$item["productId"] = $productId;		
		if(count($modifiers)) $item["modifiers"]=$modifiers;
	}else if(mb_strtolower((string) $orderItemType)==="compound"){			
		// Order Type Compound
		$item["primaryComponent"] = [ "productId" => $productId ];
		if(count($modifiers)) $item["commonModifiers"]=$modifiers;
	}

	array_push($order_items_tg,$item);

}

// --- building order for telegram ---
$tg_order_mode  = PICKUPSELF_MODE?"самовывоз":"доставка";

if(!PICKUPSELF_MODE){
	$deliv_address = "ул. $order_delivery_street, д. $order_delivery_house, 
	подъезд ($order_delivery_entrance),  этаж ($order_delivery_floor)";	
}else{
	$deliv_address = "";
}

#[AllowDynamicProperties]
class Order_object{};
$TG_ORDER = new Order_object();

$TG_ORDER->text = "";
$TG_ORDER->text .= "Заказ №: {$short_number}\n";
$TG_ORDER->text .= "       ------------\n";
$TG_ORDER->text .= "       {$tg_order_mode}\n";
if(!PICKUPSELF_MODE){
$TG_ORDER->text .= "       {$deliv_address}\n";	
}
$TG_ORDER->text .= "       ------------\n";
$TG_ORDER->text .= "       Создан: ".glb_russian_datetime($time_sent,$time_format)."\n";
$TG_ORDER->text .= "       Сумма: {$total_price} {$str_currency}.\n";
$TG_ORDER->text .= "       ------------\n";

$count = 0;
foreach ($orders as $order_row) {		
	$count++;

	$item_modifiers = $order_row['chosen_modifiers'] ?? false;	
	$item_title = $count.". ".$order_row["item_data"]["title"];	
	$item_size = !empty($order_row["sizeName"])?"/ ".$order_row["sizeName"] : "";
	$item_price = $order_row["count"]."x".$order_row["price"]." ".$str_currency;

	$TG_ORDER->text .= "_{$item_title}_ {$item_size}\n";	
	$TG_ORDER->text .= "= {$item_price}\n";

	if($item_modifiers){
		foreach($item_modifiers as $m){
			$mod_title = $m["name"];
			$mod_price = "1x".$m["price"]." ".$str_currency;			
			$TG_ORDER->text .= "+ {$mod_title}, {$mod_price}\n";
		}
	}

	$order_items_separate = $count < count($orders) ?"---------\n":"--------- //\n";
	$TG_ORDER->text .= $order_items_separate;
}

$DEMO_MODE && __answerjsonp(["short_number"=>$short_number,"demo_mode"=>$DEMO_MODE]);

if(empty(Order_sender::total_tg_users_for($cafe->uniq_name,ORDER_TARGET))) 
__answerjsonp(["short_number"=>$short_number,"demo_mode"=>$DEMO_MODE, "notg_mode"=>true]);


// -----------------------------
//         SEND ORDER
// -----------------------------

//     --- TG ONLY ---


if(THE_ORDER_WAY===0){

	// TG sending
	$results = Order_sender::send_tg_order($cafe->uniq_name, ORDER_TARGET, $short_number, $TG_ORDER->text);
	if($results && count($results)){		
		__answerjsonp(["short_number"=>$short_number, "demo_mode"=>$DEMO_MODE, "results"=>$results]);	
	}else{
		__errorjsonp("--fail sending delivery tg-order (way 1): ".print_r($results,true));	
	}

//     --- TG, then IIKO ---

  
}else if(THE_ORDER_WAY===1){	

	// TG sending
	$results = Order_sender::send_tg_order_for_confirm($cafe->uniq_name, ORDER_TARGET, $short_number, $TG_ORDER->text);
	if($results && count($results)){
		__answerjsonp( ["short_number"=>$short_number, "demo_mode"=>$DEMO_MODE, "results"=>$results] );	
	}else{
		__errorjsonp("--fail sending delivery tg-order for confirm (way 2): ".print_r($results,true));	
	}

}else{
	__errorjsonp("--unknown mode for sending".__file__);	
}


?>