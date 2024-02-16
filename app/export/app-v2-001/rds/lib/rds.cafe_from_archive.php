<?php

/*
	Order to delete User
	sending to rdsadmin email

*/	
	
	header('content-type: application/json; charset=utf-8');
	$callback = $_REQUEST['callback'] ?? 'alert';
	if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }	

	define("BASEPATH",__file__);
	
	require_once '../../../config.php';
	require_once '../../../vendor/autoload.php';
	
	require_once '../../core/common.php';
	
	require_once '../../core/class.sql.php';
	 
	require_once '../../core/class.smart_object.php';
	require_once '../../core/class.smart_collect.php';

	require_once '../../core/class.rdsadmin.php';


	session_start();
	SQL::connect();	

	if(RDSAdmin::authorised()){

		if(!isset($_REQUEST["id_cafe"]) || empty($_REQUEST["id_cafe"])){
			__errorjsonp("wrong id_cafe");
		}else{

			$id_cafe = intval(post_clean($_REQUEST["id_cafe"],100));

			$cafe = new Smart_object('cafe',$id_cafe);

			if(!$cafe || !$cafe->valid()){
				__errorjsonp("Unknown cafe, {$id_cafe}");
			}else{
				(int) $cafe->cafe_status = 0;

				if(!$cafe->save()){
					__errorjsonp("Cant update cafe, {$id_cafe}");
				}else{
					// mail_user_about_status_cafe($cafe);
					__answerjsonp("Cafe status now in archive");
				}
			}
		}
		
	}else{		
		__errorjsonp("Must authorisation");
	}

	function mail_user_about_status_cafe($cafe){
	
	}


?>