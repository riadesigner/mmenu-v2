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
require_once WORK_DIR.APP_DIR.'pbl/lib/pbl.class.order_parser.php';
require_once WORK_DIR.APP_DIR.'core/lib.api.php'; // Подключаем хелпер	

session_start();
SQL::connect();

glog('$_POST= '.print_r($_POST,1));

if(!isset($_POST['cafe_uniq_name'])) __errorjson("--not found cafe_uniq_name");

$cafe_uniq_name = $_POST['cafe_uniq_name'];
$short_number = $_POST['short_number']??'TEST-001';
$order_public_id = $_POST['public_order_id']??'';
$cafe_order_way = (int) $_POST['cafe_order_way'];

if($cafe_order_way!==2){
    // ----------------------------------
    // old_way - check order status in DB
    // ----------------------------------

    $q = implode(" ",[
        "where cafe_uniq_name='$cafe_uniq_name'",
        "AND short_number='$short_number'",
    ]);

    $orders = new Smart_collect("orders", $q);	

    if(!$orders->full()) __errorjson("--not found order");
    $order = $orders->get(0);

    if(!empty($order->manager) ){
        $manager = new Smart_object("tg_users", $order->manager); 
        if($manager && $manager->valid()){
            $order_manager_name = $manager->name;
        }else{
            $order_manager_name = "";
        }    
    }else{
        $order_manager_name = "";
    }

    __answerjson( [
        "order_status"=>$order->state, 
        "order_manager"=>$order->manager,
        "order_manager_name"=>$order_manager_name,
        "id_uniq"=>$order->id_uniq,
        "short_number"=>$order->short_number,
        "date"=>$order->date,
        "order_target"=>$order->order_target,
        "table_number"=>$order->table_number,
        ] );

}else{
    // -------------------------------------------
    // new_way - check order inf CHATS APP BACKEND
    // -------------------------------------------
    
	$internalApiKey = $_ENV['CHATS_APP_INTERNAL_API_KEY']; 
	$base = 'http://chats-app-backend:3001';	
    $url = $base . '/api-internal/orders/' . $order_public_id. '/status';	
	$headers = ['x-internal-key' => $internalApiKey];
	$params = [];

	// Отправляем запрос
	$curlResult = get_get_info($url, $headers, $params);
	$parsed = parse_curl_response($curlResult);

	glog("answer: ".print_r($curlResult, true)); // Логируем полный результат для отладки

	if (!$parsed['ok']) {
		glog("API error: {$parsed['errorCode']} - {$parsed['message']}");

		if(!empty($parsed['errorCode'])) {
			__errorjson($parsed['errorCode'] ?? 'external_error', $parsed['httpCode'] ?? 500);
		}
	}    

    $data = $parsed['data'];
    
    __answerjson( [
        "order_status"=>$data["status"], 
        "order_manager"=>$data["takenByUserId"],
        "order_manager_name"=>'',
        "id_uniq"=>$data["publicId"],
        "short_number"=>$data["shortCode"],
        "date"=>$data["createdAt"],
        "order_target"=>'',
        "table_number"=>'',
        ] );    

}

	


?>