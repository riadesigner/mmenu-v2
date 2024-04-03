<?php

/*
	get cafe info

*/	

	header('content-type: application/json; charset=utf-8');
	$callback = $_REQUEST['callback'] ?? 'alert';
	if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }	

	define("BASEPATH",__file__);
	
	require_once '../../../config.php';
	require_once '../../../vendor/autoload.php';

	require_once '../../core/common.php';	
	
	require_once '../../core/class.sql.php';
	 
	require_once '../../core/class.smart_object.php';
	require_once '../../core/class.smart_collect.php';
	require_once '../../core/class.user.php';


	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	$all_cafe = new Smart_collect("cafe","where id_user = ".$user->id);
	
	if($all_cafe && $all_cafe->full()){
		
		$cafe = $all_cafe->get(0);		
		__answerjsonp(["cafe"=>$cafe->export()]);

	}else{
		__errorjsonp("Unknown cafe");
	}


?>
