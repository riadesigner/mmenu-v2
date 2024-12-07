<?php

/*
	get cafe info

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
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	$all_cafe = new Smart_collect("cafe","where id_user = ".$user->id);
	
	if($all_cafe && $all_cafe->full()){
		
		$cafe = $all_cafe->get(0);		

		if(!empty($cafe->iiko_api_key)){
			$iiko_params = new iiko_params($cafe);
			if(!$iiko_params->found()){
				$arr_iiko_params = $iiko_params->get_empty();
			}else{
				$arr_iiko_params = $iiko_params->get();
			}						 
		}else{
			$arr_iiko_params = [];
		}

		__answerjsonp( [
			"cafe"=>[...$cafe->export(), ...["iiko_params"=>$arr_iiko_params]]
		]);		

	}else{
		__errorjsonp("Unknown cafe");
	}


?>
