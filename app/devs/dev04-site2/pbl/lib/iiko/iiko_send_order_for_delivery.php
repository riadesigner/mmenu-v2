<?php

define("BASEPATH",__file__);

header('content-type: application/json; charset=utf-8');
$callback = $_REQUEST['callback'] ?? 'alert';
if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }   

require_once getenv('WORKDIR').'/config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';	
require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.user.php';
require_once WORK_DIR.APP_DIR.'core/class.order_sender.php';

session_start();
SQL::connect();

// --------------------------------
// SEND IIKO ORDER FOR DELIVERY
// --------------------------------

if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("--it needs to know id_cafe");
if(!isset($_POST['order'])) __errorjsonp("--empty order");
if(!isset($_POST['pickupself'])) __errorjsonp("--need to set pickupself option");

// CHECK IS REAL CAFE
$id_cafe = (int) $_POST['id_cafe'];
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);

define('DEMO_MODE', (int) $cafe->cafe_status !== 2);

$order_data = $_POST['order'];

$time_sent = post_clean($order_data['order_time_sent'],100);
if(empty($time_sent)) __errorjsonp("--wrong order data, ".__LINE__);

$time_need = post_clean($order_data['order_time_need'],100);
if(empty($time_need)) $time_need = $time_sent;

define('NEARTIME_MODE',$time_need==$time_sent);

$total_price = (float) $order_data['order_total_price'];
$orders = $order_data['order_items'];

define('PICKUPSELF_MODE',filter_var($_POST['pickupself'], FILTER_VALIDATE_BOOLEAN));

$user_phone = post_clean($order_data["order_user_phone"], 50);
$user_phone = preg_replace("/[^+0-9 ()\-,.]/", "", (string) $user_phone);
if(empty($user_phone))__errorjsonp("--need to know user phone");

$str_order_mode  = PICKUPSELF_MODE?"Самовывоз":"Доставка";

if(!PICKUPSELF_MODE){
	$u_address = $order_data['order_user_iiko_address'];
	if(empty($u_address['u_street']))__errorjsonp("--need to know user street");
	if(empty($u_address['u_house']))__errorjsonp("--need to know user house");		
	// for TG
	$deliv_address = "ул. ".$u_address['u_street'].", д. ".$u_address['u_house'].", 
	подъезд (".$u_address['entrance']."),  этаж (".$u_address['floor'].")";	
}else{
	$deliv_address = "";
}

$user_comment = post_clean($order_data["order_user_comment"], 250);

// ----------------------------------------
//   BUILDING ORDER STRING (FOR TELEGRAM)
// ----------------------------------------

$time_format = 24;
$str_currency = "₽";

if($time_need==$time_sent){
	$order_time_to = "Заказ на ближайшее время";
}else{
	$order_time_to =  "Приготовить к: ".glb_russian_datetime($time_need,$time_format);	
}

$ORDER_TXT = "";
$ORDER_TXT .= "   ------------\n";
$ORDER_TXT .= "   {$str_order_mode}\n";
if(!PICKUPSELF_MODE){
$ORDER_TXT .= "   {$deliv_address}\n";	
}
$ORDER_TXT .= "   тел: {$user_phone}\n";
$ORDER_TXT .= "   ------------\n";
$ORDER_TXT .= "   Создан: ".glb_russian_datetime($time_sent,$time_format)."\n";
$ORDER_TXT .= "   Сумма: {$total_price} {$str_currency}.\n";
$ORDER_TXT .= "   ------------\n";

if(!empty($user_comment)){
	$ORDER_TXT .= "  Комментарий: {$user_comment}\n";	
	$ORDER_TXT .= "  ------------\n";	
}

$count = 0;
foreach ($orders as $row) {		
	$count++;

	$item_modifiers = $row['chosen_modifiers'] ?? false;	
	$item_title = $count.". ".$row["item_data"]["title"];	
	$item_size = !empty($row["sizeName"])?$row["sizeName"] : "";
	$item_volume = !empty($row["volume"])?$row["volume"] : "";
	$item_units = !empty($row["units"])?$row["units"] : "";
	$volume_str = !empty($item_volume)?$item_volume." ".$item_units : "";

	$item_price = $row["count"]."x".$row["price"]." ".$str_currency;

	$ORDER_TXT .= "_{$item_title}_\n";
	$ORDER_TXT .= "{$item_size} / {$volume_str}\n";	

	if($item_modifiers){
		foreach($item_modifiers as $m){
			$mod_title = $m["name"];
			$mod_price = "1x".$m["price"]." ".$str_currency;
			$ORDER_TXT .= "+ {$mod_title}, {$mod_price}\n";
		}
	}
	$ORDER_TXT .= "= {$item_price}\n";
	$separator = $count < count($orders) ?"---------\n":"--------- //\n";
	$ORDER_TXT .= $separator;
}

// ----------------------------------------
//  - SAVE ORDER IN DB
//	- GETTING ORDER_SHORT_NUMBER
// ----------------------------------------

define("ORDER_TARGET",Order_sender::IIKO_DELIVERY);
$pending_mode = false;
$short_number = Order_sender::save_order_to_db(
	ORDER_TARGET, 
	$pending_mode, 
	$cafe, 
	["ORDER_TEXT"=>$ORDER_TXT], 
	null, 
	DEMO_MODE
);
if(!$short_number)__errorjsonp("--cant save order");

DEMO_MODE && __answerjsonp(["short_number"=>$short_number,"demo_mode"=>DEMO_MODE]);

$ORDER_TXT = "Заказ №: {$short_number}\n".$ORDER_TXT;

// IF HAS NOT ACTIVE MANAGERS
define('NOTG_MODE', !Order_sender::total_tg_users_for($cafe->uniq_name, ORDER_TARGET));
NOTG_MODE && __answerjsonp(["short_number"=>$short_number,"demo_mode"=>DEMO_MODE, "notg_mode"=>true]);

// ---------------------------
//  SENDING THE ORDER TO TG
// ---------------------------
try{
	Order_sender::send_tg_order($cafe->uniq_name, ORDER_TARGET, $short_number, $ORDER_TXT);
	__answerjsonp( ["short_number"=>$short_number, "demo_mode"=>DEMO_MODE, "notg_mode"=>NOTG_MODE] );	
}catch(Exception $e){
	glogError($e->getMessage().", ".__FILE__.", ".__LINE__);
	__errorjsonp("--fail sending delivery tg-order");	
}


?>