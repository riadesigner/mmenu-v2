<?php

/*
	Safe cafe info

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

	require_once WORK_DIR.APP_DIR.'core/class.qr_tables.php';
	require_once WORK_DIR.APP_DIR.'core/class.iiko_params.php';

	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user, ".__LINE__);

	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown cafe, ".__LINE__);


	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);

	if($cafe->id_user!==$user->id)__errorjsonp("Not allowed, ".__LINE__);
	
	try{

		$IIKO_PARAMS = new Iiko_params($cafe);		
		$IIKO_PARAMS->reload();
		__answerjsonp($IIKO_PARAMS->export());

	}catch(Exceprion $e){
		glogError($e->getMessage());
		__errorjsonp("Something wrong. Can`t saving iiko params");		
	}

?>