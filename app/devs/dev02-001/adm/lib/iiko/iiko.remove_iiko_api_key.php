<?php

/*
	Safe cafe info

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
	if(!$user || !$user->valid())__errorjsonp("Unknown user, ".__LINE__);

	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown cafe, ".__LINE__);
	

	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);

	if($cafe->id_user!==$user->id)__errorjsonp("Not allowed, ".__LINE__);
		
	// REMOVE IIKO API KEY	
	$cafe->iiko_api_key = "";
	$cafe->iiko_organizations = "";
	$cafe->iiko_extmenus = "";
	$cafe->iiko_tables = "";	
	$cafe->iiko_terminal_groups = "";
	$cafe->iiko_current_extmenu_hash = "";	
	$cafe->iiko_order_types = "";		

	$cafe->tables_uniq_names = "";	
	$cafe->updated_date = 'now()';
	$cafe->rev+=1;

	$iiko_items = new Smart_collect('items',"where id_cafe={$cafe->id}");
	if($iiko_items && $iiko_items->full()){
		foreach($iiko_items->get() as $item){				
			$item->iiko_measure_unit="";
			$item->iiko_modifiers="";
			$item->iiko_sizes="";
			$item->iiko_order_item_type="";
			$item->created_by="";
			$item->price=100;
			$item->updated_date="now()";
			$item->save();
		}
	}

	if($cafe->save()){	
		__answerjsonp("ok");
	}else{
		__errorjsonp("Can not update cafe iiko api key, ".__LINE__);

	}


?>