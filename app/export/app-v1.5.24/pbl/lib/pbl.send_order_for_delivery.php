<?php

define("BASEPATH",__file__);

header('content-type: application/json; charset=utf-8');

require_once getenv('WORKDIR').'/config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';	
require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.user.php';
require_once WORK_DIR.APP_DIR.'core/class.order_sender.php';
require_once WORK_DIR.APP_DIR.'core/class.iiko_order.php';
require_once WORK_DIR.APP_DIR.'core/class.iiko_params_reader.php';

require_once WORK_DIR.APP_DIR.'pbl/lib/pbl.class.order_parser.php';

session_start();
SQL::connect();

// --------------------------------
// SEND IIKO ORDER FOR DELIVERY
// --------------------------------

if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjson("--it needs to know id_cafe");
if(!isset($_POST['order'])) __errorjson("--empty order");
if(!isset($_POST['pickupself'])) __errorjson("--need to set pickupself option");

// CHECK IS REAL CAFE
$id_cafe = (int) $_POST['id_cafe'];
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe->valid())__errorjson("Unknown cafe, ".__LINE__);

$order_data = $_POST['order'];

$IIKO_PARAMS = (new Iiko_params_reader($id_cafe))->get();

define('DEMO_MODE', (int) $cafe->cafe_status !== 2);
define('PENDING_MODE', (int) $cafe->order_way ); // int
define("ORDER_TARGET", Order_sender::ORDER_DELIVERY);
define('IIKO_MODE', !empty($cafe->iiko_api_key)); // bool
define('PICKUPSELF_MODE',filter_var($_POST['pickupself'], FILTER_VALIDATE_BOOLEAN));

$params = [
	"order_data"=>$order_data,
	"order_target"=>ORDER_TARGET,
	"pickupself_mode"=>PICKUPSELF_MODE,
];

try{
	$ORDER_TXT = (new Order_parser($params))
	->build_tg_txt()->get();	
	
}catch( Exception $e){
	glogError($e->getMessage());
	__errorjson("--fail parsing the order params");	
}

if(IIKO_MODE){

	$Iiko_order = new Iiko_order($cafe);

	if(NOMENCLATURE_MODE){
		// разворачиваем размерный ряд опять 
		// в модификаторы размеров
		$order_items = $Iiko_order->remake_for_nomenclature($order_data['order_items']);		

	}else{
		$order_items = $order_data['order_items'];
	}

	$ARR_ORDER_FOR_IIKO = "";

	try{		
		$ARR_ORDER_FOR_IIKO = $Iiko_order->prepare_order_for_delivery( $order_items, PICKUPSELF_MODE );
		glog("================================");
		glog("ORDER TO DELIVERY / SEND TO IIKO");
		glog("================================");
		glog(print_r($ARR_ORDER_FOR_IIKO,1));

	}catch( Exception $e){
		glogError($e->getMessage());
		__errorjson("--fail preparing order for table");
	}	

}else{

}

// ----------------------------------------
//  - SAVE ORDER IN DB
//	- GETTING ORDER_SHORT_NUMBER
// ----------------------------------------

$order_id_uniq = Order_sender::save_order_to_db(
	ORDER_TARGET,
	$cafe, 
	[
		"ORDER_TEXT"=>$ORDER_TXT
	], 
	null,  
	PENDING_MODE,
	DEMO_MODE	
);
if(!$order_id_uniq)__errorjson("--cant save order");

$short_number = Order_sender::get_short_number($order_id_uniq);

DEMO_MODE && __answerjson(["short_number"=>$short_number,"demo_mode"=>DEMO_MODE, "success"=>true]);

$ORDER_TXT = "Заказ №: {$short_number}\n".$ORDER_TXT;

// IF HAS NOT ACTIVE MANAGERS
define('NOTG_MODE', !Order_sender::total_tg_users_for($cafe->uniq_name, ORDER_TARGET));
NOTG_MODE && __answerjson(["short_number"=>$short_number,"demo_mode"=>DEMO_MODE, "notg_mode"=>true, "success"=>true]);

// ---------------------------
//  SENDING THE ORDER TO TG
// ---------------------------
try{
	Order_sender::send_tg_order($cafe->uniq_name, ORDER_TARGET, $order_id_uniq, $ORDER_TXT);
	__answerjson([
		"short_number"=>$short_number, 
		"demo_mode"=>DEMO_MODE, 
		"notg_mode"=>NOTG_MODE,
		"success"=>true,
		]);	
}catch(Exception $e){
	glogError($e->getMessage().", ".__FILE__.", ".__LINE__);
	__errorjson("--fail sending delivery tg-order");	
}

?>