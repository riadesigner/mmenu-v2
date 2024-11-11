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
	if(!$cafe || !$cafe->valid())__errorjsonp("Unknown cafe");

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed");	

	if(!isset($_POST['itemFlags']) || empty($_POST['itemFlags'])) __errorjsonp("Undefined item flags");

	$fspicy = (int) $_POST['itemFlags']['flag_spicy'];
	$fhit = (int) $_POST['itemFlags']['flag_hit'];
	$fvege = (int) $_POST['itemFlags']['flag_vege'];
	
	$noneedupdate = $item->mode_spicy==$fspicy && $item->mode_hit==$fhit && $item->mode_vege==$fvege;	

	if(!$noneedupdate){
		$item->mode_spicy = $fspicy;
		$item->mode_hit = $fhit;
		$item->mode_vege = $fvege;
		if(!$item->save())__errorjsonp("Not saved flags");		
	}

	$cafe->updated_date = 'now()';
	$cafe->rev+=1;
	$cafe->save();

	$answer= ["cafe-rev"=>$cafe->rev, "message"=>"flags saved"];

	__answerjsonp($answer);


?>