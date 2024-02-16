<?php

/*
	get cafe tg keys
	for each roles:
	['waiter','manager','supervisor']	

*/	

	header('content-type: application/json; charset=utf-8');
	$callback = $_REQUEST['callback'] ?? 'alert';
	if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }	

	define("BASEPATH",__file__);
	
	require_once '../../../config.php';
	require_once '../../../vendor/autoload.php';

	require_once '../../core/common.php';	
	
	require_once '../../core/class.sql.php';
	require_once '../../core/class.tg_keys.php';
	 
	require_once '../../core/class.smart_object.php';
	require_once '../../core/class.smart_collect.php';
	require_once '../../core/class.user.php';


	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	if(!isset($_POST['cafe_uniq_name']) || empty($_POST['cafe_uniq_name'])) __errorjsonp('need to pass cafe_uniq_name');
	
	$cafe_uniq_name = trim((string) $_POST['cafe_uniq_name']);

	$ARR_KEYS = Tg_keys::get($cafe_uniq_name);
	if($ARR_KEYS && count($ARR_KEYS)){
		__answerjsonp($ARR_KEYS);	
	}else{
		__errorjsonp("keys have not found");
	}


?>
