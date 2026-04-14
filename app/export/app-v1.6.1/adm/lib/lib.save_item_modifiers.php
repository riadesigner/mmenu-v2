<?php

/*
	Save Item flags

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

	if(!isset($_POST['id_item']) && empty($_POST['id_item']) ) __errorjsonp("Unknown id item");
	$id_item = (int) $_POST['id_item'];	
	$item = new Smart_object('items',$id_item);
	if(!$item->valid()) __errorjsonp("Unknown item");

	$menu = new Smart_object('menu',$item->id_menu);
	if(!$menu || !$menu->valid()) __errorjsonp("Unknown menu");	

	$cafe = new Smart_object("cafe",$menu->id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe");

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed");	
	
	if(!isset($_POST['new_item_modifiers']) || empty($_POST['new_item_modifiers'])) {
		$ARR_MODIFIERS	 = [];
	}else{
		$ARR_MODIFIERS = $_POST['new_item_modifiers'];
	}

	if(count($ARR_MODIFIERS)){
		$JSON_MODIFIERS = json_encode($ARR_MODIFIERS, JSON_UNESCAPED_UNICODE);
	}else{
		$JSON_MODIFIERS = "";
	}	

	$item->modifiers = $JSON_MODIFIERS;	
	$item->updated_date = 'now()';

	if($item->save()){

		__answerjsonp($item->modifiers);

	}else{
		__errorjsonp("Something wrong. Not saved item");

	}
?>