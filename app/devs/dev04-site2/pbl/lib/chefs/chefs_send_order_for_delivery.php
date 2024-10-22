<?php
	
define("BASEPATH",__file__);


require_once getenv('WORKDIR').'/config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';


header('content-type: application/json; charset=utf-8');
$callback = $_REQUEST['callback'] ?? 'alert';
if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }		

require_once WORK_DIR.APP_DIR.'core/class.sql.php';	
	
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.user.php';	
require_once WORK_DIR.APP_DIR.'core/class.email_simple.php';

require_once WORK_DIR.APP_DIR.'core/class.order_sender.php';

session_start();
SQL::connect();

// SEND CHEFS ORDER FOR DELIVERY

if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("--it needs to know id_cafe");
if(!isset($_POST['order'])) __errorjsonp("--empty order");
if(!isset($_POST['pickupself'])) __errorjsonp("--need to set pickupself option");

$order_data = $_POST['order'];

$time_sent = post_clean($order_data['order_time_sent'],100);
if(empty($time_sent)) __errorjsonp("--wrong order data, ".__LINE__);

$time_need = post_clean($order_data['order_time_need'],100);
if(empty($time_need)) $time_need = $time_sent;

define('NEARTIME_MODE',$time_need==$time_sent);

$total_price = (float) $order_data['order_total_price'];
$order_items = $order_data['order_items'];	

glog('ORDERS (FROM CHEFSMENU MODE) ================== \n'.print_r($order_items, 1));

define('PICKUPSELF_MODE',filter_var($_POST['pickupself'], FILTER_VALIDATE_BOOLEAN));

$user_phone  = post_clean($order_data["order_user_phone"], 50);
$user_phone  = preg_replace("/[^+0-9 ()\-,.]/", "", (string) $user_phone);
if(empty($user_phone)) __errorjsonp("--wrong user phone, #4");

if(!PICKUPSELF_MODE){
	$user_address = post_clean($order_data["order_user_address"], 250);
	if(empty($user_address)) __errorjsonp("--wrong order data, #5");
}else{
	$user_address="Самовывоз";	
}

$user_comment = post_clean($order_data["order_user_comment"], 250);

$time_need = post_clean($order_data["order_time_need"],100);
if(empty($time_need)) __errorjsonp("--wrong order data, #6");

$time_sent = post_clean($order_data["order_time_sent"],100);
if(empty($time_sent)) __errorjsonp("--wrong order data, #7");


// CHECK IS REAL CAFE
$id_cafe = (int) $_POST['id_cafe'];	
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe || !$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);

$DEMO_MODE = (int) $cafe->cafe_status !== 2 ;

// --------------------------------
//   BUILDING ORDER FOR TELEGRAM
// --------------------------------

// preparing time info

$cafe_title = $cafe->cafe_title;
$time_format = 24;
$str_currency = "₽";
$order_total_price = (float) $order_data["order_total_price"];

if($time_need==$time_sent){
	$order_time_to = "Ближайшее время";
}else{
	$order_time_to = glb_russian_datetime($time_need,$time_format);	
}

// building order string

$ORDER_TXT = "";
$ORDER_TXT .= "  ------------\n";
$ORDER_TXT .= "  Создан: ".glb_russian_datetime($time_sent,$time_format)."\n";
$ORDER_TXT .= "  Заказ на: {$order_time_to}\n";
$ORDER_TXT .= "  Сумма: {$order_total_price} {$str_currency}.\n";
$ORDER_TXT .= "  Тел.: [{$user_phone}](tel:{$user_phone})\n";
$ORDER_TXT .= "  Адрес: {$user_address}\n";
if(!empty($user_comment)){
	$ORDER_TXT .= "  Комментарий: {$user_comment}\n";	
} 	
$ORDER_TXT .= "  ------------\n";

$count = 0;
foreach ($order_items as $row) {		
	$count++;

	$item_volume = !empty($row["volume"])?$row["volume"] : "";
	$item_units = !empty($row["units"])?$row["units"] : "";
	$volume_str = !empty($item_volume)?$item_volume." ".$item_units : "";	

	$item_title = $count.". ".$row["item_data"]["title"];
	$item_price = $row["count"]."x".$row["price"]." ".$str_currency;
	$ORDER_TXT .= "_{$item_title}_\n";
	$ORDER_TXT .= "/ {$volume_str}\n";		
	$ORDER_TXT .= "{$item_price}\n";
	$order_items_separate = count($order_items)>$count-1?"---------\n":"--------- //\n";
	$ORDER_TXT .= $order_items_separate;
}	


// ----------------------------------------
//  SAVE COPY ORDER IN DB BEFORE SENDING,
//	GETTING ORDER_SHORT_NUMBER
// ----------------------------------------
define("ORDER_TARGET",Order_sender::CHEFSMENU_ORDER);
$pending_mode = false;
$short_number = Order_sender::save_order_to_db(ORDER_TARGET, $pending_mode, $cafe, ["ORDER_TEXT"=>$ORDER_TXT], $DEMO_MODE);
if(!$short_number)__errorjsonp("--cant save order");

$DEMO_MODE && __answerjsonp(["short_number"=>$short_number,"demo_mode"=>$DEMO_MODE]);

$ORDER_TXT = "Заказ ".$short_number."\n".$ORDER_TXT;

// -------------------------------
//  CHECKING IF MANAGERS IS EXIST
// -------------------------------

// TODO: Показать сообщение, что в данный момент (временно) заказы не принимаются. (Нет менеджеров)
if(empty(Order_sender::total_tg_users_for($cafe->uniq_name,ORDER_TARGET))){	
	__answerjsonp(["short_number"=>$short_number,"demo_mode"=>$DEMO_MODE, "notg_mode"=>true]);
}

// ---------------------------
//  SENDING THE ORDER TO TG
// ---------------------------
$results = Order_sender::send_tg_order($cafe->uniq_name, ORDER_TARGET, $short_number, $ORDER_TXT);
if(	!$results || !count($results)){
	__errorjsonp("--fail sending tg-order");
}

__answerjsonp(["short_number"=>$short_number,"demo_mode"=>$DEMO_MODE]);


?>