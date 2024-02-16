<?php

/*
	Add menu to cafe

*/	
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

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user, ".__LINE__);
	
	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown id cafe, ".__LINE__);
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);	

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed, ".__LINE__);


	if(!isset($_POST['api_login'])){
		__errorjsonp("unknown API LOGIN");		
	}
	
	if(!isset($_POST['organizationId'])){
		__errorjsonp("unknown organization id");		
	}
	
	if(!isset($_POST['terminalGroupId'])){
		__errorjsonp("unknown terminal group id");		
	}	

	$api_login = post_clean($_POST['api_login']);
	$organizationId = $_POST['organizationId']; 
	$terminalGroupId = $_POST['terminalGroupId']; 

	// GETTING TOKEN FROM IIKO
	 
	$url     = 'api/1/access_token';
	$headers = ["Content-Type"=>"application/json"];
	$params  = ["apiLogin" => $api_login];

	$res = iiko_get_info($url,$headers,$params);

	if(!isset($res['token'])){
		__errorjsonp($res);	
	}

	$token = $res['token'];

	// --------------------------
	// GETTING TABLES FOR THE 
	// TERMINAL GROUPS
	// --------------------------	

	$url     = 'api/1/reserve/available_restaurant_sections';
	$headers = [
	    "Content-Type"=>"application/json",
	    "Authorization" => 'Bearer '.$token
	]; 

	$params  = ['terminalGroupIds' => [$terminalGroupId], 'returnSchema'      => true];

	$res = iiko_get_info($url,$headers,$params);

	if(!isset($res['restaurantSections'])){
		__errorjsonp($res);	
	}	
	
	$arr_tables = iiko_tables_res_parse($res);	
	
	// save tables	
	$cafe->iiko_tables = json_encode($arr_tables, JSON_UNESCAPED_UNICODE);
	$cafe->updated_date = 'now()';
	$cafe->rev+=1;
	
	if(!$cafe->save()){
		__errorjsonp("--cant update cafe info");
	}else{
		__answerjsonp($arr_tables);	
	}	
	

?>