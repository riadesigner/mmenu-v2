<?php

/*
	get all items from menu 

*/	

	header('content-type: application/json; charset=utf-8');
	define("BASEPATH",__FILE__);

	session_start();
	
	require_once getenv('WORKDIR').'/config.php';
	require_once WORK_DIR.APP_DIR.'core/common.php';	
	require_once WORK_DIR.APP_DIR.'core/class.sql.php';			 
	require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
	require_once WORK_DIR.APP_DIR.'core/class.user.php';
	require_once WORK_DIR.APP_DIR.'core/lib.api.php'; // Подключаем хелпер	


	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	if (empty($_POST['cafe_uniq_name'])) {
    	__errorjsonp("Unknown cafe_uniq_name");
	}
	if (empty($_POST['user_public_id'])) {
    	__errorjsonp("Unknown user_public_id");
	}	
	
	$cafe_uniq_name = $_POST['cafe_uniq_name'];
	$user_public_id = $_POST['user_public_id'];

	$internalApiKey = $_ENV['CHATS_APP_INTERNAL_API_KEY']; 
	$url = 'http://chats-app-backend:3001/api-internal/cafe/' . $cafe_uniq_name. '/users/' . $user_public_id . '/archive';
	

	$headers = ['x-internal-key' => $internalApiKey];
	$params = []; // Для вашего случая параметры в URL не нужны

	$curlResult = post_get_info($url, $headers, $params);
	$parsed = parse_curl_response($curlResult);

	glog("answer: ".print_r($curlResult, true)); // Логируем полный результат для отладки

	if (!$parsed['ok']) {
		glog("API error: {$parsed['errorCode']} - {$parsed['message']}");

		if(!empty($parsed['errorCode']) && $parsed['errorCode'] === 'CAFE_NOT_FOUND') {
			// регистрируем новое кафе			
			$url = 'http://chats-app-backend:3001/api-internal/add-cafe/' . $cafe_uniq_name;
			$headers = ['x-internal-key' => $internalApiKey];
			$cafeKeys = register_cafe_for_push($url, $headers, []);
			glog("get keys for new cafe: " . print_r($cafeKeys, true));
			__answerjson($cafeKeys);			
			return;
		}else{
			__errorjson($parsed['errorCode'] ?? 'external_error', $parsed['httpCode'] ?? 500);
			return;
		}
	}

	// Успех — работаем с данными
	$cafeKeys = $parsed['data'];
	glog("get cafe keys: " . print_r($cafeKeys, true));
	__answerjson($cafeKeys);	

	function register_cafe_for_push($url, $headers, $data) {

		$curlResult = post_get_info($url, $headers, $data);
		$parsed = parse_curl_response($curlResult);

		glog("register cafe response: ".print_r($curlResult, true)); // Логируем полный результат для отладки

		if (!$parsed['ok']) {
			glog("API error during registration: {$parsed['errorCode']} - {$parsed['message']}");
			return false;
		}

		return $parsed['data'];
	}

?>