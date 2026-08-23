<?php

/*
	get cafe tg link to invitation user with role:	
	'waiter', 'manager' or 'supervisor';
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
	
	$chat_id = "7430189381";
	// $CFG->tg_cart_bot,

	$tg_token = $CFG->tg_cart_token;		
	$method = 'exportChatInviteLink';
	$send_data = [
		"chat_id" => $chat_id,
		"name" => "chefsmenu_waiter",
	];

	$res = send_telegram($method, $send_data, $tg_token);

	__answerjsonp("TEST OK!, ".print_r($res,1));

	// if($ARR_KEYS && count($ARR_KEYS)){
	// 	__answerjsonp($ARR_KEYS);	
	// }else{
	// 	__errorjsonp("keys have not found");
	// }


?>
