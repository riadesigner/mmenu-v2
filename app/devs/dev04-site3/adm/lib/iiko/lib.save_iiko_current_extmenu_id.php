<?php

/*
	Add menu to cafe

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
	

	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user, ".__LINE__);
	
	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown id cafe, ".__LINE__);
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);	

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed, ".__LINE__);

	$current_extmenu_id = post_clean($_POST['extmenu_id'],100);	

	$iiko_params_collect = new Smart_collect("iiko_params", "where id_cafe='".$id_cafe."'");
	if(!$iiko_params_collect || !$iiko_params_collect->full()) __errorjsonp("Not found iiko params for cafe {$id_cafe}");
	
	$iiko_params = $iiko_params_collect->get(0);
	$iiko_params->current_extmenu_id = $current_extmenu_id;
	$iiko_params->updated_date = 'now()';	
	
	if(!$iiko_params->save()){
		__errorjsonp("can`t save a new iiko_extmenu_id");
	}else{
		__answerjsonp("ok");	
	}

	

?>