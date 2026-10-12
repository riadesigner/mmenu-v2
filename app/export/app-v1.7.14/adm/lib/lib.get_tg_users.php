<?php

/*
	get cafe tg users
	with roles:
	['waiter','manager','supervisor']	

*/	

	header('content-type: application/json; charset=utf-8');
	$callback = $_REQUEST['callback'] ?? 'alert';
	if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }	

	define("BASEPATH",__file__);
	
	require_once getenv('WORKDIR').'/config.php';
	 

	require_once WORK_DIR.APP_DIR.'core/common.php';	
	
	require_once WORK_DIR.APP_DIR.'core/class.sql.php';
	require_once WORK_DIR.APP_DIR.'core/class.tg_keys.php';
	 
	require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
	require_once WORK_DIR.APP_DIR.'core/class.user.php';


	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	if(!isset($_POST['cafe_uniq_name']) || empty($_POST['cafe_uniq_name'])) __errorjsonp('need to pass cafe_uniq_name');
	
	$cafe_uniq_name = trim((string) $_POST['cafe_uniq_name']);
	
	$all_tg_users = new Smart_collect("tg_users","where cafe_uniq_name = '{$cafe_uniq_name}' ", "ORDER BY role");
	
	$ARR_TG_USERS = [];
	if($all_tg_users && $all_tg_users->full()){
		glog("total tg users for cafe {$cafe_uniq_name}: ".$all_tg_users->found());
		foreach($all_tg_users->get() as $TG_USER){
			array_push($ARR_TG_USERS, $TG_USER->export());
		}		
	}

	__answerjsonp($ARR_TG_USERS);

?>
