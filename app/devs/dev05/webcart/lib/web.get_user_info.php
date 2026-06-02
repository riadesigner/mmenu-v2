<?php

/*
	get webuser info by his public_id & cafe_unic_name

*/	
define("BASEPATH",__FILE__);

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

$cafe_uniq_name = $_POST['cafe_unic_name']??'';
if(empty($cafe_uniq_name)) __errorjson('its need to cafe_uniq_name');	

$public_id = $_POST['webuser_public_id']??'';
if(empty($public_id)) __errorjson('its need to webuser_public_id');	
	
$all_push_users = new Smart_collect("push_users","where cafe_uniq_name = '{$cafe_uniq_name}' AND public_id = '{$public_id}'", "ORDER BY role");	
if($all_push_users && $all_push_users->full()){
    $user = $all_push_users->get(0);
}else{
    $user= null;
}
    
__answerjson(["user"=>$user?$user->export():null]);







	


?>