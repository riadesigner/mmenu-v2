<?php

/*

	UPDATING ALL PUSH-KEYS FOR CAFE

	1. creating new push-keys for:
	-> waiter	
	-> manager
	-> supervisor
	
	2. removing all push users (waiters, manages, supervisors) for this cafe

*/	

	header('content-type: application/json; charset=utf-8');
	define("BASEPATH",__file__);
	
	require_once getenv('WORKDIR').'/config.php';	
	require_once WORK_DIR.APP_DIR.'core/common.php';			
	require_once WORK_DIR.APP_DIR.'core/class.push_keys.php';	
	require_once WORK_DIR.APP_DIR.'core/class.sql.php';	
	require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
	require_once WORK_DIR.APP_DIR.'core/class.user.php';

	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjson("Unknown user");

	if(!isset($_POST['cafe_uniq_name']) || empty($_POST['cafe_uniq_name'])){
		__errorjson("Unknown or empty cafe uniq_name");
	}
	
	$cafe_uniq_name = trim((string) $_POST['cafe_uniq_name']);

	$all_push_users = new Smart_collect("push_users","where cafe_uniq_name = '{$cafe_uniq_name}'");
	if($all_push_users && $all_push_users->full()){
		$arr = $all_push_users->get();
		foreach($arr as $user){
			$user->delete();
		}
	}

	$PUSH_KEYS = Push_keys::update_all($cafe_uniq_name);		
	if(!empty($PUSH_KEYS)){
		__answerjson($PUSH_KEYS);
	}else{
		__errorjson("cant update the keys");
	}


?>
