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

// SEND IIKO ORDER TO TABLE 

if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("--it needs to know id_cafe");
if(!isset($_POST['token'])) __errorjsonp("--it needs to token");
if(!isset($_POST['orders'])) __errorjsonp("--empty orders");
if(!isset($_POST['table_number'])) __errorjsonp("--it needs to table_number");
if(!isset($_POST['total_price'])) __errorjsonp("--it needs to get total_price");
if(!isset($_POST['order_time_sent'])) __errorjsonp("--it needs to get order_time_sent");

$time_sent = post_clean($_POST['order_time_sent'],100);
if(empty($time_sent)) __errorjsonp("--wrong order data, ".__LINE__);

$id_cafe = (int) $_POST['id_cafe'];
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);

$DEMO_MODE = (int) $cafe->cafe_status !== 2 ;

$k = $cafe->iiko_api_key;
if(empty($k)) __errorjsonp("Cant find iiko api for the cafe, ".__LINE__);

define('THE_ORDER_WAY',(int) $cafe->order_way);

$total_price = (float) $_POST['total_price'];
$token = $_POST['token'];
$table_number = (int) $_POST['table_number'];
$orders = $_POST['orders'];


$orgs = $cafe->iiko_organizations;
$orgs = !empty($orgs)?json_decode((string) $orgs,true):false;
if(!$orgs) __errorjsonp("--cant find iiko organization_id for the cafe, ".__LINE__);
$organization_id = $orgs['current_organization_id'];

$terminal_groups = $cafe->iiko_terminal_groups;
$terminal_groups = !empty($terminal_groups)?json_decode((string) $terminal_groups,true):false;
if(!$terminal_groups) __errorjsonp("--cant find iiko terminal_groups for the cafe, ".__LINE__);
$terminal_group_id = $terminal_groups['current_terminal_group_id'];

$iiko_tables = $cafe->iiko_tables;
$iiko_tables = !empty($iiko_tables)?json_decode((string) $iiko_tables,true):false;
if(!$iiko_tables) __errorjsonp("--cant find iiko tableIds for the cafe, ".__LINE__);
$table_id = iiko_get_table_id_by_number($iiko_tables,$table_number);
if($table_id===null){__errorjsonp("--cant calculate iiko table_id, ".__LINE__);}


function iiko_get_table_id_by_number($iiko_tables,$table_number){
	$table_id = null;
	$stop_search = false;	
	if(gettype($iiko_tables)=='array' && count($iiko_tables)){
		foreach($iiko_tables as $section){
			if($stop_search) break;			
			$arr_tbls = $section['tables'];
			if(gettype($arr_tbls)=='array' && count($arr_tbls)){
				foreach($arr_tbls as $tbl){
					if((int)$tbl['number']==(int)$table_number){
						$table_id = $tbl['id'];
						$stop_search = true;
						break;
					}
				}
			}
		}
	}
	return $table_id;
}

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
	// regular order
	if(mb_strtolower((string) $o_type['orderServiceType']) === 'common'){
		$order_type_id = $o_type['id'];
		break;
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

// --------------------------------------------
//   BUILDING ORDER ARRAY FOR SENDING TO IIKO
// --------------------------------------------

$order = [
		"id"=>"",
		"externalNumber"=>"",
		"tableIds"=>[$table_id],
		"customer"=>"",
		"phone"=>"",
		"guests"=>"",
	    "tabName"=>"",
	    "menuId"=>"{$menu_id}",
	    "items"=>$order_items,
	    "combos"=>"",
	    "payments"=>"",
	    "tips"=>"",
	    "orderTypeId"=>$order_type_id,
	    "chequeAdditionalInfo"=> ""
	];

// -----------------------------
//     SAVE COPY ORDER IN DB
// -----------------------------
 
define("ORDER_TARGET",Order_sender::IIKO_TABLE);
$pending_mode = THE_ORDER_WAY===1?true:false;
$short_number = Order_sender::save_order_to_db(ORDER_TARGET, $pending_mode, $cafe, $order, $table_number, $DEMO_MODE);
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

#[AllowDynamicProperties]
class Order_object{};
$TG_ORDER = new Order_object();

$TG_ORDER->text = "";
$TG_ORDER->text .= "Заказ №: ".$short_number."\n";
$TG_ORDER->text .= "       ------------\n";
$TG_ORDER->text .= "       в стол №: ".$table_number."\n";
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

if(empty(Order_sender::total_tg_users_for($cafe->uniq_name, ORDER_TARGET))) 
__answerjsonp(["short_number"=>$short_number,"demo_mode"=>$DEMO_MODE, "notg_mode"=>true]);



// -----------------------------
//         SEND ORDER
// -----------------------------


//     --- TG ONLY ---


if(THE_ORDER_WAY===0){

	// TG sending
	$results = Order_sender::send_tg_order($cafe, ORDER_TARGET, $short_number, $TG_ORDER->text);
	if($results && count($results)){		
		__answerjsonp(["short_number"=>$short_number, "results"=>$results]);	
	}else{
		__errorjsonp("--fail sending tg order to table (way 1): ".print_r($results,true));	
	}

//     --- TG, then IIKO ---

  
}else if(THE_ORDER_WAY===1){	

	// TG sending
	$results = Order_sender::send_tg_order_for_confirm($cafe->uniq_name, ORDER_TARGET, $short_number, $TG_ORDER->text);
	if($results && count($results)){
		__answerjsonp( ["short_number"=>$short_number, "results"=>$results] );	
	}else{
		__errorjsonp("--fail sending tg order to table for confirm (way 2): ".print_r($results,true));	
	}

}




?>