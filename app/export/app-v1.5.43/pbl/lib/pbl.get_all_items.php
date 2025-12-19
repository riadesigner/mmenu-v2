<?php

/*
	PUBLIC SITE: get all items of menu
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


	if(!isset($_REQUEST['menu']) || empty(trim((string) $_REQUEST['menu'])))__errorjson("0. unknown menu");

	$id_menu = (int) trim((string) $_REQUEST['menu']);

	$all_items = new Smart_collect("items","WHERE id_menu={$id_menu}","ORDER BY pos");
	if(!$all_items) __answerjson([]); // error	

	$all_items_export = $all_items->export();
	
	// glog("==== all_items =====: ".print_r($all_items_export,1));
	__answerjson($all_items->export());


?>