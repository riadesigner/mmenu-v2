<?php

/*
	Save Item flags

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

	if(!isset($_POST['id_item']) && empty($_POST['id_item']) ) __errorjsonp("Unknown id item");
	$id_item = (int) $_POST['id_item'];	
	$item = new Smart_object('items',$id_item);
	if(!$item->valid()) __errorjsonp("Unknown item");

	$menu = new Smart_object('menu',$item->id_menu);
	if(!$menu || !$menu->valid()) __errorjsonp("Unknown menu");	

	$cafe = new Smart_object("cafe",$menu->id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe");

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed");	
	
	if(!isset($_POST['new_parent_menu']) || empty($_POST['new_parent_menu'])) __errorjsonp("Undefined new parent menu");	
	$newParentMenu = (int) $_POST['new_parent_menu'];
	$newParent = new Smart_object('menu',$newParentMenu);
	if(!$newParent || !$newParent->valid()) __errorjsonp("Unknown parent");		
		
	// вычисляем позицию (pos) в новом разделе
	$q = 'SELECT COUNT(*) AS total FROM items WHERE id_menu='.$newParentMenu;
	$counter = SQL::first($q);
	$count = (int) $counter['total'];

	$oldMenuId = $item->id_menu;

	$item->id_menu = $newParentMenu;
	$item->pos = $count;
	$item->updated_date = 'now()';

	if($item->save()){

		// обновляем номера позиций (pos) в старом разделе
		$oldItems = new Smart_collect("items","WHERE id_menu={$oldMenuId}", "ORDER BY pos");
		if($oldItems->full()){
			foreach ($oldItems->get() as $index => $item) {
				$item->pos = $index;
				$item->updated_date = 'now()';
				$item->save();
			}
		}

		$cafe->updated_date = 'now()';
		$cafe->rev+=1;		
		$cafe->save();

		$answer= ["cafe-rev"=>$cafe->rev, "item"=>$item->export()];
	
		__answerjsonp($answer);

	}else{
		__errorjsonp("Something wrong. Not saved item");

	}
?>