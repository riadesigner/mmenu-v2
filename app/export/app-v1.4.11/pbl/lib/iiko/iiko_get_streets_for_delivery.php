<?php

header('content-type: application/json; charset=utf-8');
$callback = $_REQUEST['callback'] ?? 'alert';
if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }   

define("BASEPATH",__file__);

require_once getenv('WORKDIR').'/config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';	
require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.user.php';
require_once WORK_DIR.APP_DIR.'core/class.iiko_params.php';


session_start();
SQL::connect();

// GETTING IIKO STREETS FOR DELIVERY 

if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("--it needs to know id_cafe");
if(!isset($_POST['token'])) __errorjsonp("--it needs to token");

$id_cafe = (int) $_POST['id_cafe'];
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);

$api_key = $cafe->iiko_api_key;
if(empty($api_key)) __errorjsonp("Cant find iiko api for the cafe, ".__LINE__);

$token = $_POST['token'];

$IIKO_PARAMS = new Iiko_params($id_cafe, $api_key);
$id_org = ($IIKO_PARAMS->get())->current_organization_id;

$vladivostok_id = "b090de0b-8550-6e17-70b2-bbba152bcbd3";

$res = get_streets_by_city($token, $id_org, $vladivostok_id);

if(isset($res['streets']) && count($res['streets'])){
	$streets = [];
	foreach($res['streets'] as $street){
		$streets[]=$street['name'];
	}
	__answerjsonp(["streets"=>$streets]);
}else{
	glog('нет улиц в городе!'.print_r($res,1));
	__answerjsonp(["streets"=>[]]);
}





// -----------------------------------
//      GET CITIES
// -----------------------------------

// function get_iiko_cities($token, $id_org){	
// 	$url     = 'api/1/cities';
// 	$headers = [
// 	    "Content-Type"=>"application/json",
// 	    "Authorization" => 'Bearer '.$token
// 	]; 	 	
// 	$params  = array(
// 	    'organizationIds' => [$id_org],
// 	    'includeDeleted' => false	    
// 	); 	
// 	$res = iiko_get_info($url,$headers,$params);
// 	return $res;
// }

// -----------------------------------
//      GET STREETS BY CITY
// -----------------------------------

function get_streets_by_city($token, $id_org, $city){	
	$url     = 'api/1/streets/by_city';
	$headers = [
	    "Content-Type"=>"application/json",
	    "Authorization" => 'Bearer '.$token
	]; 	 	
	$params  = ['organizationId' => $id_org, 'cityId' => $city, 'includeDeleted'=>false]; 	
	$res = iiko_get_info($url,$headers,$params);
	return $res;
}








?>