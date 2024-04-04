<?php


/*
	ADMIN APP: get_all_menu
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

	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown cafe");
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe");

	if($cafe->id_user!==$user->id)__errorjsonp("Not allowed");

	$all_menu = new Smart_collect("menu","where id_cafe = {$id_cafe}", "ORDER BY pos");

	if($all_menu){

		$answer= ["cafe-rev"=>$cafe->rev, "all-menu"=>$all_menu->export()];

		__answerjsonp($answer);		
		
	}else{
		__errorjsonp("Something wrong");
	} 


?>
