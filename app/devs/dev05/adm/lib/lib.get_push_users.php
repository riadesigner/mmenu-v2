<?php

/*
	get cafe push users
	with roles:
	['waiter','manager','supervisor']	

*/	

	define("BASEPATH",__FILE__);
	
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
	if(!$user || !$user->valid())__errorjson("Unknown user");


	if(empty($_POST['cafe_uniq_name'])) __errorjson('need to pass cafe_uniq_name');
	
	$cafe_uniq_name = trim((string) $_POST['cafe_uniq_name']);

	
	$internalApiKey = $_ENV['CHATS_APP_INTERNAL_API_KEY']; 
	$url = "http://chats-app-backend:3001/api-internal/cafe/{$cafe_uniq_name}/users";

		

	$headers = ['x-internal-key' => $internalApiKey];
	$params = []; 

	$curlResult = get_get_info($url, $headers, $params);
	$parsed = parse_curl_response($curlResult);

	glog("answer: ".print_r($curlResult, true)); // Логируем полный результат для отладки

	if (!$parsed['ok']) {
		glog("API error: {$parsed['errorCode']} - {$parsed['message']}");
		__errorjson($parsed['errorCode'] ?? 'external_error', $parsed['httpCode'] ?? 500);			
		return;
	}

	// Успех — работаем с данными
	$pushUsers = $parsed['data'];
	glog("get cafe push users: " . print_r($pushUsers, true));
	__answerjson($pushUsers);	


?>
