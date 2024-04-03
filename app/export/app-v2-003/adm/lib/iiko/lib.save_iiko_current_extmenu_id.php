<?php

/*
	Add menu to cafe

*/	
	header('content-type: application/json; charset=utf-8');
	$callback = $_REQUEST['callback'] ?? 'alert';
	if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }	

	define("BASEPATH",__file__);
	
	require_once '../../../../config.php';
	require_once '../../../../vendor/autoload.php';

	require_once '../../../core/common.php';	
	
	require_once '../../../core/class.sql.php';
	 
	require_once '../../../core/class.smart_object.php';
	require_once '../../../core/class.smart_collect.php';
	require_once '../../../core/class.user.php';
	

	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user, ".__LINE__);
	
	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown id cafe, ".__LINE__);
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);	

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed, ".__LINE__);

	$iiko_current_extmenu_id = post_clean($_POST['extmenu_id'],100);	

	$cafe->iiko_current_extmenu_id = $iiko_current_extmenu_id;
	$cafe->updated_date = 'now()';
	$cafe->rev+=1;
	
	if(!$cafe->save()){
		__errorjsonp("cant save new iiko extmenu id");
	}else{
		__answerjsonp("ok");	
	}

	

?>