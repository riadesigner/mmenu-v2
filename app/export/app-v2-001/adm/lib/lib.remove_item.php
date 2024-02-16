<?php

/*
	Remove item from menu

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
	require_once '../../core/class.app.php';


	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user, ".__LINE__);

	if(!isset($_POST['id_item']) && empty($_POST['id_item']) ) __errorjsonp("Unknown id item, ".__LINE__);
	$id_item = (int) $_POST['id_item'];	
	$item = new Smart_object('items',$id_item);
	if(!$item->valid()) __errorjsonp("Unknown item, ".__LINE__);
	
	$menu = new Smart_object('menu',$item->id_menu);
	if(!$menu || !$menu->valid()) __errorjsonp("Unknown menu, ".__LINE__);

	$cafe = new Smart_object("cafe",$menu->id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed, ".__LINE__);
	
	if(!App::delete_item($item)) __errorjsonp("Cant remove item, ".__LINE__);;
	
	$cafe->updated_date = 'now()';
	$cafe->rev+=1;
	$cafe->save();

	$answer= ["cafe-rev"=>$cafe->rev, "message"=>"Item {$id_item} Deleted"];

	__answerjsonp($answer);	


?>