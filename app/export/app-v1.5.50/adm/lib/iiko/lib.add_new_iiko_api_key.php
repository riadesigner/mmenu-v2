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

	require_once WORK_DIR.APP_DIR.'core/class.qr_tables.php';
	require_once WORK_DIR.APP_DIR.'core/class.iiko_params.php';

	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user, ".__LINE__);

	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown cafe, ".__LINE__);

	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);

	if($cafe->id_user!==$user->id)__errorjsonp("Not allowed, ".__LINE__);
	
	// CHECK IF IIKO API KEY HAS LEGAL NAME	
	$key =  post_clean($_POST['new_iiko_api_key'], $CFG->inputs_length['iiko-api-key'] );
	$msk = "|^[0-9a-zA-Z].[\-0-9a-zA-Z]*$|i";
	if(!preg_match($msk,(string) $key)) {		
		__errorjsonp("--illegal name");		
	}
	
	$cafe->iiko_api_key = $key;

	try{
		$IIKO_PARAMS = new Iiko_params($cafe->id, $cafe->iiko_api_key);
		$IIKO_PARAMS->reload();				
	}catch(Exceprion $e){
		glogError($e->getMessage());
		__errorjsonp("Something wrong. Can`t reload iiko params");		
	}

	$org = $IIKO_PARAMS->get_current_organization();
	if(!$org || !count($org)) __errorjsonp("--wrong iiko params. cant find organization");		
	
	// --------------------------
	// UPDATING CAFE INFO
	// --------------------------	
	$cafe->cafe_title = $org["name"];
	$cafe->cafe_address = $org["restaurantAddress"];
	$cafe->chief_cook="";
	$cafe->cafe_description="Нет описания";
	$cafe->updated_date = 'now()';
	$cafe->rev+=1;

	if($cafe->save()){	
		__answerjsonp($cafe->export());		
	}else{
		glogError("Can't save cafe iiko api key, ".__FILE__.", ".__LINE__);
		__errorjsonp("Can't save cafe ".$cafe->id." iiko api key");
	}

?>