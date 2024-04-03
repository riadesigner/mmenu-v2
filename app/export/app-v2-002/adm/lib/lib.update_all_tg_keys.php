<?php

/*
	updagte all tg keys for cafe
	creating keys for:
	-> waiter	
	-> manager
	-> supervisor
*/	

	header('content-type: application/json; charset=utf-8');
	$callback = $_REQUEST['callback'] ?? 'alert';
	if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }	

	define("BASEPATH",__file__);
	
	require_once '../../../config.php';
	require_once '../../../vendor/autoload.php';

	require_once '../../core/common.php';	
		
	require_once '../../core/class.tg_keys.php';	

	require_once '../../core/class.sql.php';	
	require_once '../../core/class.smart_object.php';
	require_once '../../core/class.smart_collect.php';
	require_once '../../core/class.user.php';


	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	if(!isset($_POST['cafe_uniq_name']) || empty($_POST['cafe_uniq_name'])){
		__errorjsonp("Unknown or empty cafe uniq_name");
	}
	
	$uniq_name = trim((string) $_POST['cafe_uniq_name']);

	$TG_KEYS = Tg_keys::update_all($uniq_name);		
	if(!empty($TG_KEYS)){
		__answerjsonp($TG_KEYS);
	}else{
		__errorjsonp("cant update the keys");
	}


?>
