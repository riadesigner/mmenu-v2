<?php

/*
	Add item to menu

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

	if(!isset($_POST['id_menu']) && empty($_POST['id_menu']) ) __errorjsonp("Unknown id menu");
	$id_menu = (int) $_POST['id_menu'];	
	$menu = new Smart_object('menu',$id_menu);
	if(!$menu || !$menu->valid()) __errorjsonp("Unknown menu");

	$cafe = new Smart_object("cafe",$menu->id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe");

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed");

	if(!isset($_POST['created_by'])) __errorjsonp("Unknown created_by params");
	$item_created_by = post_clean($_POST['created_by']);


	if(!isset($_POST['text_inputs']) || empty($_POST['text_inputs']))__errorjsonp("--Not found user inputs");
	$text_inputs = $_POST['text_inputs']; 

	// ---------------------------------
	// FOR RUSSIAN LANG (BY DEFAULT)
	// ---------------------------------

	$item_title = post_clean($text_inputs['ru']['title'],$CFG->inputs_length['item-title']);
	$item_description = post_clean($text_inputs['ru']['description'],$CFG->inputs_length['item-description']);	
	$pos = (int) $_POST['pos'];
	
	// ---------------------------------
	// THIS PART ONLY FOR CHEFMENU MODE
	// ---------------------------------
	$sizes = !isset($_POST['sizes']) ? "" : json_encode($_POST['sizes'], JSON_UNESCAPED_UNICODE);

	// ------------------------------------
	// SAVING EXTRA DATA (OTHER LANGUAGES)
	// ------------------------------------
	unset($text_inputs['ru']);
	$extra_data = count($text_inputs)? json_encode($text_inputs, JSON_UNESCAPED_UNICODE):"";
	

	if(isset($_POST['id_item']) && !empty($_POST['id_item'])){
		
		// -----------------------
		//      UPDATING ITEM
		// -----------------------

		$id_item = (int) $_POST['id_item'];	
		$item = new Smart_object('items',$id_item);
		if(!$item->valid()) __errorjsonp("Unknown item");
		
		$item->title = !empty($item_title)?$item_title:"Untitled";
		$item->description = $item_description;
		$item->pos = $pos;
		$item->sizes = $sizes; // FOR CHEFMENUMODE ONLY
		$item->extra_data = $extra_data;
		$item->updated_date = 'now()';

	}else{

		// -----------------------------------------------
		//      ADDING NEW ITEM UNDER CHEFSMENU MAMAGING
		// -----------------------------------------------

		$q = 'SELECT COUNT(*) AS total FROM items WHERE id_cafe='.$cafe->id;
		$counter = SQL::first($q);
		$total_items_in_cafe = (int) $counter['total'];
		$limits = (int) $cafe->cafe_status!==2 ? $CFG->limits['test'] : $CFG->limits['full'];
		if($total_items_in_cafe > $limits['total_items']-1) __errorjsonp("limit total_items reached");

		$item = new Smart_object('items');
		$item->id_menu = $id_menu;
		$item->id_cafe = $cafe->id;
		$item->title = !empty($item_title)?$item_title:"Sample item ".($count);
		$item->description = $item_description;
		$item->sizes = $sizes; // FOR CHEFMENUMODE ONLY
		$item->extra_data = $extra_data;
		$item->updated_date = 'now()';
		$item->pos = $pos;
		$item->created_by = 'chefsmenu';

	}

	if($item->save()){

		$cafe->updated_date = 'now()';
		$cafe->rev+=1;
		$cafe->save();

		$answer= ["cafe-rev"=>$cafe->rev, "item"=>$item->export()];

		__answerjsonp($answer);

	}else{
		__errorjsonp("Something wrong. Not saved item");

	}

?>