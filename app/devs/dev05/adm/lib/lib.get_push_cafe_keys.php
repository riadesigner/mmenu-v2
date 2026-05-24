<?php

/*
	get all items from menu 

*/	

	header('content-type: application/json; charset=utf-8');
	define("BASEPATH",__file__);
	
	require_once getenv('WORKDIR').'/config.php';
	require_once WORK_DIR.APP_DIR.'core/common.php';	
	require_once WORK_DIR.APP_DIR.'core/class.sql.php';			 
	require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
	require_once WORK_DIR.APP_DIR.'core/class.user.php';
	require_once WORK_DIR.APP_DIR.'core/lib.api.php'; // Подключаем хелпер	

	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	
	$internalApiKey = $_ENV['CHATS_APP_INTERNAL_API_KEY']; 
	$url = 'http://chats-app-backend:3001/api-internal/cafe/uriw76';

	$headers = ['x-internal-key' => $internalApiKey];
	$params = []; // Для вашего случая параметры в URL не нужны

	$curlResult = get_get_info($url, $headers, $params);
	$parsed = parse_curl_response($curlResult);

	glog("answer: ".print_r($curlResult, true)); // Логируем полный результат для отладки

	if (!$parsed['ok']) {
		glog("API error: {$parsed['errorCode']} - {$parsed['message']}");
		__errorjson($parsed['errorCode'] ?? 'external_error', $parsed['httpCode'] ?? 500);
		return;
	}

	// Успех — работаем с данными
	$cafeKeys = $parsed['data'];
	glog("get cafe keys: " . print_r($cafeKeys, true));
	__answerjson($cafeKeys);

?>