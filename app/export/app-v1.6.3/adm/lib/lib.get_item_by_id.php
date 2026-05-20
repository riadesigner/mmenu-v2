<?php

/*
	get all items from menu 

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
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	if(!isset($_POST['id_item']) && empty($_POST['id_item']) ) __errorjsonp("Unknown item id");
	$id_item = (int) $_POST['id_item'];
	$item = new Smart_object("items",$id_item);
	if(!$item->valid())__errorjsonp("Unknown item");

	$except_fields = $_POST['except_fields'] ?? [];

	$export_item = $item->export();
	if(count($except_fields)){
		foreach ($except_fields as $field) {
			unset($export_item[$field]);
		}
	}	
	
	$answer = ["item"=>$export_item];
	__answerjsonp($answer);	


?>