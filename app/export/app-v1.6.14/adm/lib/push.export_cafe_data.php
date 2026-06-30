<?php



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
	if(!$user || !$user->valid())__errorjson("Unknown user");

	if(empty($_POST['cafe_id'])) __errorjson('need to pass cafe_id');	
	$id_cafe = trim((int) $_POST['cafe_id']);

	$cafe = new Smart_object("cafe",$id_cafe);
	!$cafe->valid() && __errorjsonp("Unknown cafe with ID ".$id_cafe);

	$cafe_uniq_name = $cafe->uniq_name;
	$displayName = $cafe->cafe_title;
	$menuUrl = !empty($cafe->external_url) ? $cafe->external_url : $CFG->http.$CFG->wwwroot."/cafe/{$cafe_uniq_name}";

	$internalApiKey = $_ENV['CHATS_APP_INTERNAL_API_KEY']; 
	$base = "http://chats-app-backend:3001";
	$url = "{$base}/api-internal/cafe/{$cafe_uniq_name}/update-profile";
	$headers = [
		'x-internal-key' => $internalApiKey,
		'Content-Type' => 'application/json'
		];
	$params = [
		"displayName"=>$displayName,
		"menuUrl"=>$menuUrl,
	];

	$curlResult = post_get_info($url, $headers, $params);
	$parsed = parse_curl_response($curlResult);

	glog("params: ".json_encode($params, JSON_UNESCAPED_UNICODE)."\n");
	glog("======== export cafe params to chats_app ========");
	glog("answer: ".print_r($curlResult, true)); // Логируем полный результат для отладки

	if (!$parsed['ok']) {
		glog("API error: {$parsed['errorCode']} - {$parsed['message']}");
		__errorjson($parsed['errorCode'] ?? 'external_error', $parsed['httpCode'] ?? 500);			
		return;
	}

	// Успех — работаем с данными
	$retCafe = $parsed['data'];	
	__answerjson($retCafe);	


?>
