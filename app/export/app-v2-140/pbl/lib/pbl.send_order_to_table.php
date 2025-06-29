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
require_once WORK_DIR.APP_DIR.'core/class.iiko_order.php';
require_once WORK_DIR.APP_DIR.'core/class.iiko_params_reader.php';

require_once WORK_DIR.APP_DIR.'pbl/lib/pbl.class.order_parser.php';


session_start();
SQL::connect();

// --------------------------------
// SEND ORDER TO TABLE 
// --------------------------------

// glog("============= SEND ORDER TO TABLE _POST ============ ".print_r($_POST,1));

// $path = $CFG->dirroot.$CFG->log_path.'/post_debug.log';		

// Временная отладка
// file_put_contents(
//     $path,
//     "=== HEADERS ===\n" . 
//     print_r(getallheaders(), true) . 
//     "\n=== POST ===\n" . 
//     print_r($_POST, true) . 
//     "\n=== INPUT ===\n" . 
//     file_get_contents('php://input') . 
//     "\n\n",
//     FILE_APPEND
// );


if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("--it needs to know id_cafe");
if(!isset($_POST['order'])) __errorjsonp("--empty order");
if(!isset($_POST['table_number'])) __errorjsonp("--it needs to table_number");



// CHECK IS REAL CAFE
$id_cafe = (int) $_POST['id_cafe'];
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);

define('DEMO_MODE', (int) $cafe->cafe_status !== 2);
define("ORDER_TARGET", Order_sender::ORDER_TABLE);

$order_data = $_POST['order'];
$table_number = (int) $_POST['table_number'];

if(DEMO_MODE) __errorjsonp("--demo mode");

$params = [
	"order_data"=>$order_data,
	"order_target"=>ORDER_TARGET,	
	"table_number"=>$table_number,
];

try{
	$ORDER_TXT = (new Order_parser($params))
	->build_tg_txt()->get();

}catch( Exception $e){
	glogError($e->getMessage());
	__errorjsonp("--fail parsing the order params");	
}

$IIKO_PARAMS = (new Iiko_params_reader($id_cafe))->get();

define('IIKO_MODE', !empty($cafe->iiko_api_key)); // bool
define('PENDING_MODE', (int) $cafe->order_way ); // int
define('NOMENCLATURE_MODE', (int) $IIKO_PARAMS->nomenclature_mode ); // int)

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

	glog("TEST!!!!");

	try{		
		$ARR_ORDER_FOR_IIKO = $Iiko_order->prepare_order_for_table( $table_number, $order_items );
		glog("================= ARR_ORDER_FOR_IIKO ==================");
		glog(capture_var_dump($ARR_ORDER_FOR_IIKO));
		
	}catch( Exception $e){
		glogError($e->getMessage());
		__errorjsonp("--fail preparing order for table");
	}

} else {
	$ARR_ORDER_FOR_IIKO = "";
}

// ----------------------------------------
//  - SAVE ORDER IN DB
//	- GETTING ORDER_ID_UNIQ
// ----------------------------------------
 
$order_id_uniq = Order_sender::save_order_to_db(
	ORDER_TARGET,
	$cafe, 
	[		
		"ORDER_TEXT"=>addcslashes($ORDER_TXT,"\n"),
		"ORDER_IIKO"=>$ARR_ORDER_FOR_IIKO,
	], 
	$table_number,
	PENDING_MODE,
	DEMO_MODE
);

if(!$order_id_uniq)__errorjsonp("--cant save order");

$short_number = Order_sender::get_short_number($order_id_uniq);

DEMO_MODE && __answerjsonp(["short_number"=>$short_number,"demo_mode"=>DEMO_MODE]);

$ORDER_TXT = "Заказ №: {$short_number}\n".$ORDER_TXT;

// IF HAS NOT ACTIVE WAITERS
define('NOTG_MODE', !Order_sender::total_tg_users_for($cafe->uniq_name, ORDER_TARGET));
NOTG_MODE && __answerjsonp(["short_number"=>$short_number,"demo_mode"=>DEMO_MODE, "notg_mode"=>true]);

// ---------------------------
//  SENDING THE ORDER TO TG
// ---------------------------
try{
	Order_sender::send_tg_order($cafe->uniq_name, ORDER_TARGET, $order_id_uniq, $ORDER_TXT);	

	__answerjsonp( [
		"short_number"=>$short_number, 
		"demo_mode"=>DEMO_MODE,  // if cafe on demo_mode
		"notg_mode"=>NOTG_MODE  // if has not active waiters
		] );	

}catch(Exception $e){
	glogError($e->getMessage().", ".__FILE__.", ".__LINE__);
	__errorjsonp("--fail sending to table tg-order");	
}


?>