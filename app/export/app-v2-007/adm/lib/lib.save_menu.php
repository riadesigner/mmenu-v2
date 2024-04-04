<?php

/*
	Edit/Add new section (menu for cafe)

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

	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("1. unknown id_cafe");
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe || !$cafe->valid())__errorjsonp("Unknown cafe");

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed");	
	
	$id_icon = (int) $_POST['id_icon'];

	if(!isset($_POST['all_inputs']) || empty($_POST['all_inputs']))__errorjsonp("--Not found user inputs");
	$all_inputs = $_POST['all_inputs']; 

	// ---------------------------------
	// FOR RUSSIAN LANG (BY DEFAULT)
	// ---------------------------------

	$menu_title = post_clean($all_inputs['ru']['title'],$CFG->inputs_length['menu-title']);
	
	// ------------------------------------
	// SAVING EXTRA DATA (OTHER LANGUAGES)
	// ------------------------------------
	unset($all_inputs['ru']);
	$extra_data = count($all_inputs)? json_encode($all_inputs, JSON_UNESCAPED_UNICODE):"";		


	if(isset($_POST['id_menu']) && !empty($_POST['id_menu'])){

	// MENU UPDATE	

		$id_menu = (int) $_POST['id_menu'];
		$menu = new Smart_object('menu',$id_menu);		
		if(!$menu || !$menu->valid())__errorjsonp("Unknown menu with id ".$id_menu);
		$menu->title = $menu_title;
		$menu->id_icon = $id_icon;

	}else{

	// MENU ADD NEW	

		$q = 'SELECT COUNT(*) AS total FROM menu WHERE id_cafe='.$cafe->id;
		$counter = SQL::first($q);
		$total_menu_in_cafe = (int) $counter['total'];
		$limits = (int) $cafe->cafe_status!==2 ? $CFG->limits['test'] : $CFG->limits['full'];
		if($total_menu_in_cafe > $limits['total_sections']-1) __errorjsonp("limit total_sections reached");

		$q = "SELECT MAX(pos) AS pos FROM menu WHERE id_cafe={$id_cafe}";
		$pos = SQL::first($q);
		$pos = (int) $pos['pos'];

		$menu = new Smart_object('menu');
		$menu->id_cafe = $id_cafe;
		$menu->title = $menu_title;
		$menu->pos = $pos+1;
		$menu->id_icon = $id_icon;

	}

	$menu->updated_date = 'now()';
	$menu->extra_data = $extra_data;

	if($menu->save()){
		$id	= $menu->id;
	}else{
		__errorjsonp("--Something wrong. Not saved menu");
	}

	$cafe->updated_date = 'now()';
	$cafe->rev+=1;
	$cafe->save();

	$answer= ["cafe-rev"=>$cafe->rev, "menu"=>$menu->export()];
	
	__answerjsonp($answer);

?>