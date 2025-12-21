<?php

/*
	Remove menu 

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
	require_once WORK_DIR.APP_DIR.'core/class.app.php';


	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	if(!isset($_POST['id_menu']) && empty($_POST['id_menu']) ) __errorjsonp("Unknown menu id");
	$id_menu = (int) $_POST['id_menu'];
	$menu = new Smart_object("menu",$id_menu);
	if(!$menu->valid())__errorjsonp("Unknown menu");

	$cafe = new Smart_object("cafe",$menu->id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe");

	if($cafe->id_user!==$user->id)__errorjsonp("Not allowed");
	
	App::delete_menu_with_items($menu);

	$cafe->updated_date = 'now()';
	$cafe->rev+=1;
	$cafe->save();

	$answer= ["cafe-rev"=>$cafe->rev, "message"=>"ok, deleted menu {$id_menu}"];

	__answerjsonp($answer);

?>