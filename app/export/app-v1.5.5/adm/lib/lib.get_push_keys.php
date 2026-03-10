<?php

/*
	get cafe PUSH keys
	for each roles:
	['waiter','manager','supervisor']	

*/	

	header('content-type: application/json; charset=utf-8');
	define("BASEPATH",__file__);
	
	require_once getenv('WORKDIR').'/config.php';
	require_once WORK_DIR.APP_DIR.'core/common.php';	
	require_once WORK_DIR.APP_DIR.'core/class.sql.php';	
	require_once WORK_DIR.APP_DIR.'core/class.push_keys.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
	require_once WORK_DIR.APP_DIR.'core/class.user.php';

	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjson("Unknown user");

	if(!isset($_POST['cafe_uniq_name']) || empty($_POST['cafe_uniq_name'])) __errorjson('need to pass cafe_uniq_name');
	
	$cafe_uniq_name = trim((string) $_POST['cafe_uniq_name']);

	$ARR_KEYS = Push_keys::get($cafe_uniq_name);
	if($ARR_KEYS && count($ARR_KEYS)){
		__answerjson($ARR_KEYS);	
	}else{
		__errorjson("keys have not found");
	}
