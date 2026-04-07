<?php

/*
	get webuser info by his public_id & cafe_unic_name

*/	
define("BASEPATH",__file__);

// Разрешаем CORS
// header("Access-Control-Allow-Origin: https://your-frontend-domain.com"); // Укажите конкретный домен
// header("Access-Control-Allow-Credentials: true"); // Разрешаем отправку cookies

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Обрабатываем preflight запросы (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once getenv('WORKDIR').'/config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';	
require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';	

if(!isset($_REQUEST['token']) || empty($_REQUEST['token']) ) __errorjson("неправильная ссылка");
$token = post_clean($_REQUEST['token'],50);

// проверем токен
$tokens = new Smart_collect("push_keys","where push_key='$token'");
if(!$tokens || !$tokens->full()) __errorjsonp("неправильная ссылка (токен)");			
$valid_token = ($tokens->get(0));

__answerjson($valid_token->export());	



	


?>