<?php

/*
	RDSAdmin logout
*/	
	
	header('content-type: application/json; charset=utf-8');
	$callback = $_REQUEST['callback'] ?? 'alert';
	if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }	
	
	define("BASEPATH",__file__);
	
	require_once getenv('WORKDIR').'/config.php';
	 
	
	require_once WORK_DIR.APP_DIR.'core/common.php';
		
	require_once WORK_DIR.APP_DIR.'core/class.sql.php';
	 
	require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
	require_once WORK_DIR.APP_DIR.'core/class.user.php';

	require_once WORK_DIR.APP_DIR.'core/class.rdsadmin.php';

	session_start();
	RDSAdmin::logout();
	
	__answerjsonp(["logout"=>"ok"]);


?>