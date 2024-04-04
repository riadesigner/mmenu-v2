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
	
	require_once '../../../core/class.account.php';
	require_once '../../../core/class.tg_keys.php';	
	require_once '../../../core/class.email.php';	
	require_once '../../../ext/qrcode/qrcode.php';
	

	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user, ".__LINE__);
	
	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown id cafe, ".__LINE__);
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);	

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed, ".__LINE__);

	$modif_dict = new Smart_collect("iiko_params", "where id_cafe={$id_cafe}");
	if($modif_dict->found()){
		$arr = json_decode((string) $modif_dict->get(),1); 
	}else{
		$arr = [];
	}
	__answerjsonp($arr);


?>