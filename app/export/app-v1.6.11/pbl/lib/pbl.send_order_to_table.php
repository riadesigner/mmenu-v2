<?php

define("BASEPATH",__file__);

header('content-type: application/json; charset=utf-8');

session_start();

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
require_once WORK_DIR.APP_DIR.'core/lib.api.php'; // Подключаем хелпер	


SQL::connect();

// --------------------------------
// SEND ORDER TO TABLE 
// --------------------------------

if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjson("--it needs to know id_cafe");
if(!isset($_POST['order'])) __errorjson("--empty order");
if(!isset($_POST['table_number'])) __errorjson("--it needs to table_number");

// CHECK IS REAL CAFE
$id_cafe = (int) $_POST['id_cafe'];
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe->valid())__errorjson("Unknown cafe, ".__LINE__);

$order_data = $_POST['order'];
$table_number = (int) $_POST['table_number'];

$IIKO_PARAMS = (new Iiko_params_reader($id_cafe))->get();

define('DEMO_MODE', (int) $cafe->cafe_status !== 2);
define('PENDING_MODE', (int) $cafe->order_way ); // int
define("ORDER_TARGET", Order_sender::ORDER_TABLE);
define('IIKO_MODE', !empty($cafe->iiko_api_key)); // bool
define('NOMENCLATURE_MODE', (int) $IIKO_PARAMS->nomenclature_mode ); // int)

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
	__errorjson("--fail parsing the order params");	
}


if(IIKO_MODE){

	$Iiko_order = new Iiko_order($cafe);

	// разворачиваем размерный ряд опять 
	// в модификаторы размеров (если originalPrice > 0 || virtualSize == true )
	$order_items = $Iiko_order->remake_for_nomenclature($order_data['order_items']);	


	$ARR_ORDER_FOR_IIKO = "";

	try{		
		$ARR_ORDER_FOR_IIKO = $Iiko_order->prepare_order_for_table( $order_items, $table_number );
		glog("=================================");
		glog("ORDER TO TABLE / PREPARED TO IIKO");
		glog("=================================");
		glog(print_r($ARR_ORDER_FOR_IIKO,1));

	}catch( Exception $e){
		glogError($e->getMessage());
		__errorjson("--fail preparing order for table");
	}

} else {
	$ARR_ORDER_FOR_IIKO = "";
}

$FULL_ORDER = 
[
	"ORDER_TEXT"=>$ORDER_TXT,
	"ORDER_IIKO"=>$ARR_ORDER_FOR_IIKO,
	"TOTAL_PRICE"=>$order_data["order_total_price"],		
];

if(PENDING_MODE !==2){

	// ----------------------------------------
	// Старый способ сохранения заказа, 
	// Для отправки с TG и IIKO
	// ----------------------------------------
	
	//  - SAVE ORDER IN DB
	//	- GETTING ORDER_ID_UNIQ		

	$order_id_uniq = Order_sender::save_order_to_db(
		ORDER_TARGET,
		$cafe, 
  		$FULL_ORDER, 
		$table_number,
		PENDING_MODE,
		DEMO_MODE
	);

	if(!$order_id_uniq)__errorjson("--cant save order");

	$short_number = Order_sender::get_short_number($order_id_uniq);

	DEMO_MODE && __answerjson(["short_number"=>$short_number,"demo_mode"=>DEMO_MODE]);

	$ORDER_TXT = "Заказ №: {$short_number}\n".$ORDER_TXT;

	// IF HAS NOT ACTIVE WAITERS
	define('NOTG_MODE', !Order_sender::total_tg_users_for($cafe->uniq_name, ORDER_TARGET));
	NOTG_MODE && __answerjson(["short_number"=>$short_number,"demo_mode"=>DEMO_MODE, "notg_mode"=>true]);

	// ---------------------------
	//  SENDING THE ORDER TO TG
	// ---------------------------
	try{
		Order_sender::send_tg_order($cafe->uniq_name, ORDER_TARGET, $order_id_uniq, $ORDER_TXT);	

		__answerjson( [
			"short_number"=>$short_number, 
			"public_order_id"=>$order_id_uniq,
			"demo_mode"=>DEMO_MODE,  // if cafe on demo_mode
			"notg_mode"=>NOTG_MODE  // if has not active waiters
			] );	

	}catch(Exception $e){
		glogError($e->getMessage().", ".__FILE__.", ".__LINE__);
		__errorjson("--fail sending to table tg-order");	
	}

}else{

	// ----------------------------------------
	// Новый способ сохранения заказа, 
	// Для отправки с PUSH (CHATS_APP) и IIKO
	// ----------------------------------------

	$data = push__save_order_to_table($cafe->uniq_name, $FULL_ORDER, $table_number);
	__answerjson([
		"short_number"=>$data['shortCode'],
		"public_order_id"=>$data['publicId'],
		]);

}

// COMMON FUNCTIONS

function push__save_order_to_table($cafe_uniq_name, $order_data, $table_number){ 

	$base = "http://chats-app-backend:3001";
	

	$internalApiKey = $_ENV['CHATS_APP_INTERNAL_API_KEY']; 

	// Подготавливаем параметры для заказа
	$params = [
		'cafeUniqId' => $cafe_uniq_name,
		'tableId' => 'a1b2c3d4e5f6-table-token-from-qr',
		'title' => "Заказ на стол №{$table_number}",
		'description' => $order_data,
	];

	glog('$params='.print_r($params, true)); // Логируем параметры для отладки

	// Подготавливаем заголовки
	$headers = [
		'x-internal-key' => $internalApiKey,
		'Content-Type' => 'application/json'
	];

	// Формируем полный URL
	$url = $base . "/api-internal/orders";

	// Отправляем запрос
	$curlResult = post_get_info($url, $headers, $params);
	$parsed = parse_curl_response($curlResult);

	glog("answer: ".print_r($curlResult, true)); // Логируем полный результат для отладки

	if (!$parsed['ok']) {
		glog("API error: {$parsed['errorCode']} - {$parsed['message']}");

		if(!empty($parsed['errorCode'])) {
			__errorjson($parsed['errorCode'] ?? 'external_error', $parsed['httpCode'] ?? 500);
		}
	}

	return $parsed['data'];

}


?>