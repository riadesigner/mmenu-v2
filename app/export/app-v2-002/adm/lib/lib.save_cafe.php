<?php

/*
	Safe cafe info

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
	require_once '../../core/class.user.php';

	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown cafe");
	$id_cafe = (int) $_POST['id_cafe'];
	
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe || !$cafe->valid())__errorjsonp("Unknown cafe");
	if($cafe->id_user!==$user->id)__errorjsonp("Not allowed");	

	$cafe_title = post_clean($_POST['cafe_title'], $CFG->inputs_length['cafe-title'] );		
	$chief_cook = post_clean($_POST['chief_cook'], $CFG->inputs_length['chief-cook'] );
	$cafe_address = post_clean($_POST['cafe_address'], $CFG->inputs_length['cafe-address'] );
	$work_hours = post_clean($_POST['work_hours'], $CFG->inputs_length['work-hours'] );
	$cafe_phone = post_clean($_POST['cafe_phone'], $CFG->inputs_length['cafe-phone'] );

	$cafe->cafe_title = $cafe_title;
	$cafe->cafe_address = $cafe_address;
	$cafe->chief_cook = $chief_cook;
	$cafe->work_hours = $work_hours;
	$cafe->cafe_phone = $cafe_phone;
	$cafe->updated_date = 'now()';
	$cafe->rev+=1;

	if($cafe->save()){	

		__answerjsonp($cafe->export());

	}else{
		__errorjsonp("Error Save cafe info");

	}


?>