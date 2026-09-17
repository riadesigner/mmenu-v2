<?php


/*
	Updating external_url
*/	
	
	header('content-type: application/json; charset=utf-8');

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

	$new_external_url = post_clean($_POST['new_external_url'], 100 );

	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjson("Unknown cafe id");
	$id_cafe = (int) $_POST['id_cafe'];
	
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe || !$cafe->valid())__errorjson("Unknown cafe");
	if($cafe->id_user!==$user->id)__errorjson("Not allowed");	

	$cafe->external_url = $new_external_url;

	if(!$cafe->save()){
		__errorjson("не удалось сохранить внешний адрес для кафе");	
	}else{
		__answerjson(["success"=>true]);	
	}	

?>