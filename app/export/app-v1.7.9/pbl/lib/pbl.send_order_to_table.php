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
// order_way:
//   0/1 — Telegram (legacy)
//   2   — chats: local FULL_ORDER → POST /api-internal/orders
//   3   — chats: POST /api-internal/orders/from-cart (сборка в chats-app)
// --------------------------------

if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjson("--it needs to know id_cafe");
if(!isset($_POST['order'])) __errorjson("--empty order");
if(!isset($_POST['table_number'])) __errorjson("--it needs to table_number");

$id_cafe = (int) $_POST['id_cafe'];
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe->valid())__errorjson("Unknown cafe, ".__LINE__);

$order_data = $_POST['order'];
$table_number = (int) $_POST['table_number'];
// QR token (tables_uniq_names / tablesUniqNames) — not iiko table UUID
$table_id = isset($_POST['table_id']) ? trim((string) $_POST['table_id']) : '';

define('DEMO_MODE', (int) $cafe->cafe_status !== 2);
define('PENDING_MODE', (int) $cafe->order_way ); // int
define("ORDER_TARGET", Order_sender::ORDER_TABLE);
define('IIKO_MODE', true);

// ----------------------------------------
// order_way === 3: build + create in chats-app
// ----------------------------------------
if(PENDING_MODE === 3){

	if($table_id === ''){
		__errorjson("--it needs to table_id");
	}

	$fromCart = push__save_order_from_cart($cafe->uniq_name, $order_data, $table_number, $table_id);

	if($fromCart['ok']){
		__answerjson([
			"short_number"=>$fromCart['data']['shortCode'],
			"public_order_id"=>$fromCart['data']['publicId'],
		]);
	}

	// Not re-exported yet — fall back to local prep (same as order_way 2)
	if(($fromCart['errorCode'] ?? '') === 'IIKO_ORDER_PARAMS_MISSING'){
		glog("from-cart IIKO_ORDER_PARAMS_MISSING — fallback to local prep + POST /orders");
	}else{
		glog("from-cart API error: ".($fromCart['errorCode'] ?? '')." - ".($fromCart['message'] ?? ''));
		if(!empty($fromCart['errorCode'])){
			__errorjson($fromCart['errorCode'], $fromCart['httpCode'] ?? 500);
		}
		__errorjson("--fail sending order from-cart");
	}
}

// ----------------------------------------
// Build FULL_ORDER locally (TG, order_way 2, or from-cart fallback)
// ----------------------------------------

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

$Iiko_order = new Iiko_order($cafe);
$order_items = $Iiko_order->remake_for_nomenclature($order_data['order_items']);	

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

$FULL_ORDER = 
[
	"ORDER_TEXT"=>$ORDER_TXT,
	"ORDER_IIKO"=>$ARR_ORDER_FOR_IIKO,
	"TOTAL_PRICE"=>$order_data["order_total_price"],		
];

if(PENDING_MODE === 2 || PENDING_MODE === 3){

	// ----------------------------------------
	// chats: local FULL_ORDER → POST /api-internal/orders
	// (order_way 2, or order_way 3 fallback after IIKO_ORDER_PARAMS_MISSING)
	// ----------------------------------------

	if($table_id === ''){
		__errorjson("--it needs to table_id");
	}

	$data = push__save_order_to_table($cafe->uniq_name, $FULL_ORDER, $table_number, $table_id);
	__answerjson([
		"short_number"=>$data['shortCode'],
		"public_order_id"=>$data['publicId'],
		]);

}else{

	// ----------------------------------------
	// TG path (order_way 0/1)
	// ----------------------------------------

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

	define('NOTG_MODE', !Order_sender::total_tg_users_for($cafe->uniq_name, ORDER_TARGET));
	NOTG_MODE && __answerjson(["short_number"=>$short_number,"demo_mode"=>DEMO_MODE, "notg_mode"=>true]);

	try{
		Order_sender::send_tg_order($cafe->uniq_name, ORDER_TARGET, $order_id_uniq, $ORDER_TXT);	

		__answerjson( [
			"short_number"=>$short_number, 
			"public_order_id"=>$order_id_uniq,
			"demo_mode"=>DEMO_MODE,
			"notg_mode"=>NOTG_MODE
			] );	

	}catch(Exception $e){
		glogError($e->getMessage().", ".__FILE__.", ".__LINE__);
		__errorjson("--fail sending to table tg-order");	
	}

}

// COMMON FUNCTIONS

function chats_internal_base(): string {
	return "http://chats-app-backend:3001";
}

function chats_internal_headers(): array {
	return [
		'x-internal-key' => $_ENV['CHATS_APP_INTERNAL_API_KEY'],
		'Content-Type' => 'application/json'
	];
}

/**
 * Build+create in chats-app. Returns { ok, data } or { ok:false, errorCode, message, httpCode }.
 */
function push__save_order_from_cart($cafe_uniq_name, $order_data, $table_number, $table_id){ 

	$params = [
		'cafeUniqId' => $cafe_uniq_name,
		'tableId' => $table_id,
		'tableNumber' => (int) $table_number,
		'title' => "Заказ на стол №{$table_number}",
		'order' => $order_data,
	];

	glog('from-cart $params='.print_r($params, true));

	$url = chats_internal_base() . "/api-internal/orders/from-cart";
	$curlResult = post_get_info($url, chats_internal_headers(), $params);
	$parsed = parse_curl_response($curlResult);

	glog("from-cart answer: ".print_r($curlResult, true));

	if (!$parsed['ok']) {
		return [
			'ok' => false,
			'errorCode' => $parsed['errorCode'] ?? null,
			'message' => $parsed['message'] ?? 'external_error',
			'httpCode' => $parsed['httpCode'] ?? 500,
		];
	}

	return ['ok' => true, 'data' => $parsed['data']];
}

function push__save_order_to_table($cafe_uniq_name, $order_data, $table_number, $table_id){ 

	$params = [
		'cafeUniqId' => $cafe_uniq_name,
		'tableId' => $table_id,
		'title' => "Заказ на стол №{$table_number}",
		'description' => $order_data,
	];

	glog('$params='.print_r($params, true));

	$url = chats_internal_base() . "/api-internal/orders";
	$curlResult = post_get_info($url, chats_internal_headers(), $params);
	$parsed = parse_curl_response($curlResult);

	glog("answer: ".print_r($curlResult, true));

	if (!$parsed['ok']) {
		glog("API error: {$parsed['errorCode']} - {$parsed['message']}");

		if(!empty($parsed['errorCode'])) {
			__errorjson($parsed['errorCode'] ?? 'external_error', $parsed['httpCode'] ?? 500);
		}
		__errorjson("--fail sending order to chats");
	}

	return $parsed['data'];

}


?>
