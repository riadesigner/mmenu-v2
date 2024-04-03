<?php

header('content-type: application/json; charset=utf-8');
$callback = $_REQUEST['callback'] ?? 'alert';
if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }   

define("BASEPATH",__file__);


require_once '../../../../config.php';
require_once '../../../../vendor/autoload.php';

require_once '../../../core/common.php';	

require_once '../../../core/class.sql.php';
 
require_once '../../../core/class.smart_object.php';
require_once '../../../core/class.smart_collect.php';
require_once '../../../core/class.user.php';


session_start();
SQL::connect();

// GETTING IIKO STREETS FOR DELIVERY 

if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("--it needs to know id_cafe");
if(!isset($_POST['token'])) __errorjsonp("--it needs to token");

$id_cafe = (int) $_POST['id_cafe'];
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);

$k = $cafe->iiko_api_key;
if(empty($k)) __errorjsonp("Cant find iiko api for the cafe, ".__LINE__);

$token = $_POST['token'];

$orgs = $cafe->iiko_organizations;
$orgs = !empty($orgs)?json_decode((string) $orgs,true):false;
if(!$orgs) __errorjsonp("--cant find iiko organization_id for the cafe, ".__LINE__);
$organization_id = $orgs['current_organization_id'];

$vladivostok_id = "b090de0b-8550-6e17-70b2-bbba152bcbd3"; 


// $res = get_iiko_cities($token, $organization_id);

$res = get_streets_by_city($token, $organization_id, $vladivostok_id);

if(isset($res['streets']) && count($res['streets'])){
	$streets = [];
	foreach($res['streets'] as $street){
		$streets[]=$street['name'];
	}
	__answerjsonp(["streets"=>$streets]);
}else{
	__errorjsonp($res);	
}





// -----------------------------------
//      GET CITIES
// -----------------------------------

// function get_iiko_cities($token, $organization_id){	
// 	$url     = 'api/1/cities';
// 	$headers = [
// 	    "Content-Type"=>"application/json",
// 	    "Authorization" => 'Bearer '.$token
// 	]; 	 	
// 	$params  = array(
// 	    'organizationIds' => [$organization_id],
// 	    'includeDeleted' => false	    
// 	); 	
// 	$res = iiko_get_info($url,$headers,$params);
// 	return $res;
// }

// -----------------------------------
//      GET STREETS BY CITY
// -----------------------------------

function get_streets_by_city($token, $organization_id, $city){	
	$url     = 'api/1/streets/by_city';
	$headers = [
	    "Content-Type"=>"application/json",
	    "Authorization" => 'Bearer '.$token
	]; 	 	
	$params  = ['organizationId' => $organization_id, 'cityId' => $city, 'includeDeleted'=>false]; 	
	$res = iiko_get_info($url,$headers,$params);
	return $res;
}








?>