<?php

/*
	Save Items flags

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

	// CHECK FLAGS
	if(!isset($_POST['arrItemsFlags']) && empty($_POST['arrItemsFlags']) ) __errorjsonp("needs arrItemsFlags var");
	$arrItemsFlags = $_POST['arrItemsFlags']; // getting array of items with flags params
	if(!count($arrItemsFlags))__errorjsonp("items amount must be more then 0");

	// CHECK MENU
	if(!isset($_POST['id_menu']) || empty($_POST['id_menu'])) __errorjsonp("Undefined id_menu");	
	$id_menu = (int) $_POST['id_menu'];		
	$menu = new Smart_object('menu',$id_menu);
	if(!$menu || !$menu->valid()) __errorjsonp("Unknown menu");	

	// CHECK CAFE
	$cafe = new Smart_object("cafe",$menu->id_cafe);
	if(!$cafe || !$cafe->valid())__errorjsonp("Unknown cafe");
	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed");	
	
	// CHECK POS PARAM
	if(!isset($_POST['arrItemsPos']) || empty($_POST['arrItemsPos'])){
		$arrItemsPos = []; 
	}else{
		$arrItemsPos = $_POST['arrItemsPos'];
	}

	// rebuild aray to associative array
	$objFlags = [];
	if(count($arrItemsFlags)){
		foreach($arrItemsFlags as $item){
			$objFlags[$item["id_item"]] = $item;
		}
	}

	$objPoses = [];
	if(count($arrItemsPos)){
		foreach($arrItemsPos as $item){
			$objPoses[$item["id_item"]] = $item;
		}
	}
	
	// GET ALL ITEMS IN MENU 	
	$items = new Smart_collect("items","where id_menu = {$id_menu}", "ORDER BY pos");
	if(!$items->full()) __errorjsonp("items amount in db must be more then 0");

	
	$arr_items = $items->get();

	$pos_updated = 0;
	$flags_updated = 0;

	// update items flags and pos params
	foreach($arr_items as $item){
		$item_updated = 0;
		if(isset($objPoses[$item->id])){	
			$itm = $objPoses[$item->id];
			$item->pos = (int) $itm['pos'];
			$item_updated ++;
			$pos_updated ++;
		}							
		if(isset($objFlags[$item->id])){	
			$flags = $objFlags[$item->id];
			$item->mode_spicy = (int) $flags['flag_spicy'];
			$item->mode_vege = (int) $flags['flag_vege'];
			$item->mode_hit = (int) $flags['flag_hit'];			
			$item_updated ++;
			$flags_updated ++;
		}
		if($item_updated>0){
			$item->updated_date = 'now()';
			$item->save();
		}
	}
	
	$cafe->updated_date = 'now()';
	$cafe->rev+=1;
	$cafe->save();	

	$message = "";
	if($pos_updated>0) $message.="pos updated, ";
	if($flags_updated>0) $message.="flags updated, ";	 

	$answer= ["cafe-rev"=>$cafe->rev, "message"=>$message];	

	__answerjsonp($answer);

?>