<?php

/*
	Add menu to cafe

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
	
	require_once WORK_DIR.APP_DIR.'core/class.iiko_params.php';


	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user, ".__LINE__);
	
	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown id cafe, ".__LINE__);
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);	

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed, ".__LINE__);

	$current_organization_id = post_clean($_POST['current_organization_id'],100);	
	$current_terminal_group_id = post_clean($_POST['current_terminal_group_id'],100);	
	$current_extmenu_id = post_clean($_POST['current_extmenu_id'],100);	
	
	$iiko_params_collect = new Smart_collect("iiko_params", "where id_cafe='".$id_cafe."'");
	if(!$iiko_params_collect || !$iiko_params_collect->full()) __errorjsonp("Not found iiko params for cafe {$id_cafe}");	
	$saved_params = $iiko_params_collect->get(0);	
	

	// ---------------------------------
	//  RELOAD ALL PARAMS FROM IIKO 
	//  IF ORGANIZATION_ID IS DIFFERENT
	// ---------------------------------
	if($saved_params->current_organization_id !== $current_organization_id){		
		try{			
			$IIKO_PARAMS = new Iiko_params($id_cafe, $cafe->iiko_api_key);
			$IIKO_PARAMS->reload($current_organization_id);

			// --------------------------
			// UPDATING CAFE INFO
			// --------------------------	
			$org = $IIKO_PARAMS->get_current_organization();
			$cafe->cafe_title = $org["name"];
			$cafe->cafe_address = $org["restaurantAddress"];
			$cafe->chief_cook="";
			$cafe->cafe_description="Нет описания";
			$cafe->updated_date = 'now()';
			$cafe->rev+=1;
			$cafe->save();

			__answerjsonp($IIKO_PARAMS->export());
		}catch(Exception $e){
			glogError($e->getMessage());
			__errorjsonp("failed (1) reload iiko params for cafe ".$cafe->id);
		}

	} else {
		try{
			$IIKO_PARAMS = new Iiko_params($id_cafe, $cafe->iiko_api_key);			
			if(!empty($current_terminal_group_id)) $IIKO_PARAMS->set_current_terminal_group_id($current_terminal_group_id);
			if(!empty($current_extmenu_id)) $IIKO_PARAMS->set_current_extmenu_id($current_extmenu_id);
			if(!empty($current_terminal_group_id) || !empty($current_extmenu_id)) $IIKO_PARAMS->save();			
			__answerjsonp($IIKO_PARAMS->export());
		}catch(Exception $e){
			glogError($e->getMessage());
			__errorjsonp("failed (2) reload iiko params for cafe ".$cafe->id);
		}
	}	
	
?>