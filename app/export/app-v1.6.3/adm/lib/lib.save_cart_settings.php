<?php

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


	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown cafe");
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe");

	
	$cart_mode = (int) $_POST['cart_mode'];		
	$order_way = (int) $_POST['order_way'];	
	$has_delivery = (int) $_POST['has_delivery'];	


	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed");

	$cafe->cart_mode = $cart_mode;	
	$cafe->order_way = $order_way;		
	$cafe->has_delivery = $has_delivery;	
	$cafe->updated_date = 'now()';
	$cafe->rev+=1;

	if($cafe->save()){	

		__answerjsonp($cafe->export());

	}else{
		__errorjsonp("Can not update cafe data");
	}


?>