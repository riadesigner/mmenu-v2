<?php


/**
 * 	GETTING MENU AND ITEMS 
 *  AS MODIFIERS GROUPS
 * 
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

	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown cafe");
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe");

	if($cafe->id_user!==$user->id)__errorjsonp("Not allowed");

	// TODO: COLLECT ONLY THE MENU 
	// WITH MODE "MODIFIER"

	$all_menu = new Smart_collect("menu","where id_cafe = {$id_cafe}", "ORDER BY pos");
	if($all_menu && $all_menu->full()){
		$modifiers = [];
		$arr_goups = $all_menu->export();		
		foreach($arr_goups as $menu){
			
			$id_menu = $menu["id"];
			$modifierGroupName = $menu["title"];						
			$m_items = [];

			$items = new Smart_collect("items","where id_menu={$id_menu}", "ORDER BY pos");

			if($items && $items->full()){				
				$arr_items = $items->export();
				foreach($arr_items as $item){
					$i = [
						"id"=>$item["id"],
						"name"=>$item["title"],
						"price"=>0,
					];
					array_push($m_items, $i);		
				}
				
				$m_gloup = [
					"modifierGroupId"=>$id_menu,
					"name" => $modifierGroupName,
					"items"=> $m_items,
				];
				array_push($modifiers, $m_gloup);
			}
		}

		__answerjsonp($modifiers);

	}else{
		$answer= ["modifiers"=>[]];
		__answerjsonp($answer);		
	}

?>
