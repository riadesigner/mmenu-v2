<?php

/*
	Wake up current terminal group by user request
*/	
	
header('content-type: application/json; charset=utf-8');
define("BASEPATH",__file__);

require_once getenv('WORKDIR').'/config.php';

require_once WORK_DIR.APP_DIR.'core/common.php';	

require_once WORK_DIR.APP_DIR.'core/class.sql.php';
	
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.user.php';


session_start();
SQL::connect();

$user = User::from_cookie();
if(!$user || !$user->valid())__errorjson("Unknown user, ".__LINE__);

if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjson("Unknown cafe, ".__LINE__);


$id_cafe = (int) $_POST['id_cafe'];
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe->valid())__errorjson("Unknown cafe, ".__LINE__);

if($cafe->id_user!==$user->id)__errorjson("Not allowed, ".__LINE__);
	
$k = $cafe->iiko_api_key;
if(empty($k)) __errorjson("Cant find iiko api for the cafe, ".__LINE__);

// GETTING IIKO PARAMS FOR THE CAFE
$search_params = new Smart_collect("iiko_params", "where id_cafe={$id_cafe}");
if(!$search_params->found()) __errorjson("iiko params not found");
$iiko_params = $search_params->get(0);

$terminalId = $iiko_params->current_terminal_group_id;
$orgId = $iiko_params->current_organization_id;

if(empty($terminalId)) __errorjson("неизвестный терминал");	
if(empty($orgId)) __errorjson("неизвестная организация");	

// GETTING TOKEN FROM IIKO 
$url     = 'api/1/access_token';
$headers = ["Content-Type"=>"application/json"];
$params  = ["apiLogin" => $k];
$res = iiko_get_info($url,$headers,$params);
$token = $res["token"];

// Awake terminal groups from sleep mode
$url     = 'api/1/terminal_groups/awake';
$headers = [
    "Content-Type"=>"application/json",
    "Authorization" => 'Bearer '.$token
];
$params  = [
    "organizationIds" => [$orgId],
    "terminalGroupIds" => [$terminalId],

];
$res = iiko_get_info($url,$headers,$params);	

if($res && !isset($res['error'])){
    glog("wakeup terminal $terminalId = ".print_r($res,1));
    __answerjson(["success"=>true, "terminalId"=>$terminalId]);	
}else{
    glogError("wakeup terminal $terminalId error = ".print_r($res,1));
    __errorjson("Не удалось разбудить терминал $terminalId");
}


?>