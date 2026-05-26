<?php

/*
	get cafe push users
	with roles:
	['waiter','manager','supervisor']	

*/	

	define("BASEPATH",__file__);
	
	require_once getenv('WORKDIR').'/config.php';
	require_once WORK_DIR.APP_DIR.'core/common.php';		
	require_once WORK_DIR.APP_DIR.'core/class.sql.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
	require_once WORK_DIR.APP_DIR.'core/class.user.php';


	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjson("Unknown user");


	if(empty($_POST['cafe_uniq_name'])) __errorjson('need to pass cafe_uniq_name');
	
	$cafe_uniq_name = trim((string) $_POST['cafe_uniq_name']);

	
	// $all_push_users = new Smart_collect("push_users","where cafe_uniq_name = '{$cafe_uniq_name}' ", "ORDER BY role");
	
	// $ARR_PUSH_USERS = [];
	// if($all_push_users && $all_push_users->full()){
	// 	glog("total push users for cafe {$cafe_uniq_name}: ".$all_push_users->found());
	// 	foreach($all_push_users->get() as $PUSH_USER){
	// 		array_push($ARR_PUSH_USERS, $PUSH_USER->export());
	// 	}		
	// }

	// __answerjson($ARR_PUSH_USERS);
	__answerjson([]);

?>
