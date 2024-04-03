<?php

/*
	Add menu to cafe

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
	if(!$user || !$user->valid())__errorjsonp("Unknown user, ".__LINE__);
	
	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown id cafe, ".__LINE__);
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);	

	if(!isset($_REQUEST['arrpos']) || empty($_REQUEST['arrpos'])) __errorjsonp("Unknown pos info, ".__LINE__);
	if(!count($_REQUEST['arrpos'])) __errorjsonp("Empty pos info, ".__LINE__);

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed, ".__LINE__);

	$arr_pos_id = $_REQUEST['arrpos'];

	$arr_menu = [];

	foreach ($arr_pos_id as $new_pos => $id_menu) {
		$menu = new Smart_object('menu',$id_menu);
		if(!$menu || !$menu->valid() || (intval($menu->id_cafe) !== $id_cafe) )__errorjsonp("Unknown menu, ".__LINE__);
		if($menu->pos!=$new_pos){
			$menu->pos = $new_pos;
			if(!$menu->save())__errorjsonp("Can not to save the positions now, ".__LINE__);
		}

		array_push($arr_menu, $menu->export());

	};

	$cafe->updated_date = 'now()';
	$cafe->rev+=1;
	$cafe->save();

	$answer= ["cafe-rev"=>$cafe->rev, "arr-menu"=>$arr_menu];

	__answerjsonp($answer);


?>