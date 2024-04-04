<?php

/*
	RDSAdmin logout
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

	require_once '../../core/class.rdsadmin.php';

	session_start();
	RDSAdmin::logout();
	
	__answerjsonp(["logout"=>"ok"]);


?>