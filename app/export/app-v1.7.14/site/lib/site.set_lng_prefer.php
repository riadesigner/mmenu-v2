<?php

/*
	Save language prefer to cookie
*/	
	
	header('content-type: application/json; charset=utf-8');
	$callback = $_REQUEST['callback'] ?? 'alert';
	if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }	

	define("BASEPATH",__file__);
		
	require_once getenv('WORKDIR').'/config.php';

	require_once WORK_DIR.APP_DIR.'core/common.php';

	require_once WORK_DIR.APP_DIR.'core/class.lng_prefer.php';

	session_start();

	if(!isset($_POST['lng']) && empty($_POST['lng']) ){ __error("unknown lng"); }
	$lng = $_POST['lng'];

	Lng_prefer::set($lng);

	__answerjsonp(["lng"=>$lng]);


?>