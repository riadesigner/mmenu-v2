<?php

/*
	Save Item Pos

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

	if(!isset($_POST['id_menu']) || empty($_POST['id_menu']) ) __errorjsonp("Unknown id menu");
	$id_menu = (int) $_POST['id_menu'];
	$menu = new Smart_object('menu',$id_menu);
	if(!$menu || !$menu->valid()) __errorjsonp("Unknown menu");

	$cafe = new Smart_object("cafe",$menu->id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe");

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed");

	if(!isset($_REQUEST['arrpos']) || empty($_REQUEST['arrpos'])) __errorjsonp("Unknown pos info");
	if(!count($_REQUEST['arrpos'])) __errorjsonp("Empty pos info");

	$arr_pos_id = $_REQUEST['arrpos'];

	$arr_items = [];

	foreach ($arr_pos_id as $index => $id_item) {
		$item = new Smart_object('items',$id_item);
		if(!$item || !$item->valid())__errorjsonp("Unknown id item ".$id_item);
		if($item->pos!=$index && $item->id_menu == $id_menu){
			$item->pos = $index;
			if(!$item->save())__error("Can not to save the positions now");
			array_push($arr_items, $item->export());
		}
	};

	$cafe->updated_date = 'now()';
	$cafe->rev+=1;
	$cafe->save();
	
	$answer= ["cafe-rev"=>$cafe->rev, "arr-items"=>$arr_items];

	__answerjsonp($answer);

?>