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

	if(!isset($_POST['id_menu']) && empty($_POST['id_menu']) ) __errorjsonp("Unknown menu id");
	$id_menu = (int) $_POST['id_menu'];
	$menu = new Smart_object("menu",$id_menu);
	if(!$menu->valid())__errorjsonp("Unknown menu");

	$cafe = new Smart_object("cafe",$menu->id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe");
	
	if($cafe->id_user!==$user->id)__errorjsonp("Not allowed");	

	$except_fields = $_POST['except_fields'] ?? [];	

	$all_items = new Smart_collect("items","where id_menu = {$id_menu}","ORDER BY pos");

	$export_items = $all_items->export();
	if(count($except_fields)){
		foreach($export_items as $key => $item){
			foreach($except_fields as $field){
				unset($item[$field]);
			}
			$export_items[$key] = $item;
		}
	}

	$answer = ["app-version"=>$CFG->version, "all-items"=>$export_items];	

	if($all_items){		
		__answerjsonp($answer);	
	}else{
		__errorjsonp("Something wrong");
	} 


?>