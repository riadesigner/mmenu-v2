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

require_once WORK_DIR.APP_DIR.'pbl/lib/pbl.class.order_parser.php';

session_start();
SQL::connect();

// --------------------------------
// SEND ORDER TO TABLE 
// --------------------------------

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

$params = [
	"order_data"=>$order_data,
	"order_target"=>ORDER_TARGET,	
	"table_number"=>$table_number,
];

try{
	$ORDER_TXT = (new Order_parser($params))
	->build_tg_txt()->get();	
	
	glog("-------ORDER FOR TABLE------- \n".print_r($ORDER_TXT,1));

}catch( Exception $e){
	glogError($e->getMessage());
	__errorjsonp("--fail parsing the order params");	
}

// ----------------------------------------
//  - SAVE ORDER IN DB
//	- GETTING ORDER_SHORT_NUMBER
// ----------------------------------------
 
$short_number = Order_sender::save_order_to_db(
	ORDER_TARGET,
	$cafe, 
	["ORDER_TEXT"=>$ORDER_TXT], 
	$table_number, 
	DEMO_MODE
);
if(!$short_number)__errorjsonp("--cant save order");

DEMO_MODE && __answerjsonp(["short_number"=>$short_number,"demo_mode"=>DEMO_MODE]);

$ORDER_TXT = "Заказ №: {$short_number}\n".$ORDER_TXT;

// IF HAS NOT ACTIVE WAITERS
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
	__errorjsonp("--fail sending to table tg-order");	
}


?>