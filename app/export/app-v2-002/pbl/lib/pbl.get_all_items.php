<?php

/*
	PUBLIC SITE: get all items of menu
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

	SQL::connect();


	if(!isset($_REQUEST['menu']) || empty(trim((string) $_REQUEST['menu'])))__errorjsonp("0. unknown menu");

	$id_menu = (int) trim((string) $_REQUEST['menu']);

	$all_items = new Smart_collect("items","WHERE id_menu={$id_menu}","ORDER BY pos");
	if(!$all_items) __answerjsonp([]); // error

	glog(json_encode($all_items->export(), JSON_UNESCAPED_UNICODE));

	__answerjsonp($all_items->export());


?>