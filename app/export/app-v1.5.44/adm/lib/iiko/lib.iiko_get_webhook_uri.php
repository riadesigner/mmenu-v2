<?php

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

$orgId = $iiko_params->current_organization_id;

// GETTING TOKEN FROM IIKO 
$url     = 'api/1/access_token';
$headers = ["Content-Type"=>"application/json"];
$params  = ["apiLogin" => $k];
$res = iiko_get_info($url,$headers,$params);
$token = $res["token"];


// Get webhooks settings for specified organization 
// and authorized API login. 
$url     = 'api/1/webhooks/settings';
$headers = [
    "Content-Type"=>"application/json",
    "Authorization" => 'Bearer '.$token
];
$params  = ["organizationId" => $orgId];
$res = iiko_get_info($url,$headers,$params);

if($res && !isset($res['error'])){
    glog("webHooks = ".print_r($res,1));
    __answerjson(["webHooks"=>$res]);
}else{
    glogError("webHooks error = ".print_r($res,1));
    __errorjson("Не найдена запись webHooks для данной организации");
}



?>