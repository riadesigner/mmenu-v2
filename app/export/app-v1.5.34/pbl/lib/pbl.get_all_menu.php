<?php

/*
	PUBLIC SITE: get all menu of cafe
*/

	header('content-type: application/json; charset=utf-8');	
	define("BASEPATH",__file__);

	require_once getenv('WORKDIR').'/config.php';
	 
	require_once WORK_DIR.APP_DIR.'core/common.php';	
	require_once WORK_DIR.APP_DIR.'core/class.sql.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
	require_once WORK_DIR.APP_DIR.'core/class.user.php';

	SQL::connect();

	if(!isset($_REQUEST['cafe']) || empty(trim((string) $_REQUEST['cafe'])))__errorjson("0. unknown cafe");

	$cafe_uniq_name = post_clean($_REQUEST['cafe']);

	$q = "SELECT * FROM cafe WHERE uniq_name='$cafe_uniq_name'";
	$res = SQL::first($q);
	
	if(!$res) __errorjson("1. unknown cafe");

	$id_cafe = (int) $res['id'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe || !$cafe->valid()) __errorjson("unknown cafe with #{$id_cafe}");	

	$all_menu = new Smart_collect("menu","WHERE id_cafe={$id_cafe}","ORDER BY pos");

	__answerjson(["cafe"=>$cafe->export(), "menu"=>$all_menu->export()]);




?>