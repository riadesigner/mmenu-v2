<?php

/*
	Enter to RDSAdmin

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

	require_once WORK_DIR.APP_DIR.'core/class.rdsadmin.php';
	
	session_start();
	

	if(!isset($_REQUEST['login']) || empty($_REQUEST['login']) ) __errorjsonp("unknown login");
	$login = post_clean($_REQUEST['login'],50);

	if(!isset($_REQUEST['md5pass']) || empty($_REQUEST['md5pass']) ) __errorjsonp("unknown pass");
	$md5pass = post_clean($_REQUEST['md5pass'],32);
	

	if(RDSAdmin::authorised($login,$md5pass)){		
		__answerjsonp("signin ok");
	}else{		
		__errorjsonp("wrong login or pass");
	}


?>