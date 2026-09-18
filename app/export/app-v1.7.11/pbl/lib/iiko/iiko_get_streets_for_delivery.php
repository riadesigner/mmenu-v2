<?php

header('content-type: application/json; charset=utf-8');

define("BASEPATH",__file__);

require_once getenv('WORKDIR').'/config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';	
require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.user.php';
require_once WORK_DIR.APP_DIR.'core/class.iiko_params.php';
require_once WORK_DIR.APP_DIR.'pbl/lib/pbl.load_cafe.php';


session_start();
SQL::connect();

// GETTING IIKO STREETS FOR DELIVERY 

if(!isset($_POST['token'])) __errorjson("--it needs to token");

$cafe = pbl_load_cafe_by_uniq_name();
$id_cafe = (int) $cafe->id;

$api_key = $cafe->iiko_api_key;
if(empty($api_key)) __errorjson("Cant find iiko api for the cafe, ".__LINE__);

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
	__answerjson(["streets"=>$streets]);
}else{
	glog('нет улиц в городе!'.print_r($res,1));
	__answerjson(["streets"=>[]]);
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