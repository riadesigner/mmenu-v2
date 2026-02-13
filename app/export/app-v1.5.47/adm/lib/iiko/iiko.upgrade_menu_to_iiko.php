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
	
	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user, ".__LINE__);
	
	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown id cafe, ".__LINE__);
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);	
	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed, ".__LINE__);

	if(!isset($_POST['id_menu_saved']) || empty($_POST['id_menu_saved'])) __errorjsonp("id_menu_saved is empty, ".__LINE__);
	$id_menu_saved = $_POST['id_menu_saved'];

	if(!isset($_POST['new_menu_hash'])) __errorjsonp("require a new_menu_hash to update menu");
	$new_menu_hash = post_clean($_POST['new_menu_hash']);
	
	// ================================================
	// LOADING FROM DB THE JUST IMPORTED MENU FROM IIKO
	// ================================================		
	$sm = new Smart_object('menu_imported',$id_menu_saved);
	if(!$sm->valid())__errorjsonp("Unknown menu, ".__LINE__);	
	$menu_data = base64_decode($sm->data);
	$chefs_menu_data = json_decode($menu_data, true);
	$new_menu = $chefs_menu_data["Menu"];

	// ============================================
	// COLLECCTING ALL MENUS AND ITEMS FOR THE CAFE
	// ============================================
	$all_menu = new Smart_collect("menu","where id_cafe = {$id_cafe}", "ORDER BY pos");
	$all_items = new Smart_collect("items","where id_cafe = {$id_cafe}");
	
	$all_menu_prepared = [];
	if($all_menu->full()){
		foreach($all_menu->export() as $menu){			
			if(!empty($menu['id_external'])){
				$all_menu_prepared[$menu['id_external']] = $menu;
			}			
		}
	}

	$all_items_prepared = [];
	if($all_items->full()){
		foreach($all_items->export() as $item){
			if(!empty($item['id_external'])){
				$all_items_prepared[$item['id_external']] = $item;
			}
		}
	}

	// =======================
	// ADDING ARCHIVE CATEGORY
	// =======================
	$arch_menu = new Smart_object('menu');
	$arch_menu->id_cafe = $id_cafe;
	$arch_menu->title = "Архив";	
	if(!$arch_menu->save())__errorjsonp("Something wrong. Cant add archive menu");

	$id_archive	= $arch_menu->id;
	$items_replaced = 0;
	
	// ==============================
	// REPLACING ALL ITEMS TO ARCHIVE
	// ==============================
	$q = "SELECT count(id) as total FROM items WHERE id_cafe={$id_cafe}";              
    $res = SQL::query($q);
    if($res && $res->num_rows){
        $counter = $res->fetch_object();
        $total = $counter->total;
    }else{
    	$total = 0;
    }

    if($total>0){
    	$q = "UPDATE items SET id_menu={$id_archive} WHERE id_cafe={$id_cafe}";	
    	$res = SQL::update($q);			
    	$items_replaced = $res||0;
    }

	// =======================
    // DELETING OLD CATEGORIES	
	// =======================
    $q = "DELETE FROM menu WHERE id_cafe={$id_cafe} AND id!={$id_archive}";  
    $del = SQL::delete($q);


    // COLLECT ITEMS BY ID_EXTERNAL 
    // TO REMOVE FROM ARCHIVE AFTER UPDATING  
    $arr_items_to_remove = [];

	// =====================
    // ADDING NEW CATEGORIES
	// =====================
	$q = "SELECT MAX(pos) AS pos FROM menu WHERE id_cafe={$id_cafe}";
	$pos = SQL::first($q);
	$pos = (int) $pos['pos'];

    $categories = $new_menu['categories'];

    if(count($categories)){
	    foreach ($categories as $cat) {
	    	
	    	$id_menu_external = $cat['id'];

			$menu = new Smart_object('menu');
			$menu->id_cafe = $id_cafe;
			$menu->id_external = $id_menu_external;
			$menu->title = $cat['name'];
			$menu->pos = $pos+1;			

			// ====================================
			// COPY SOME PROPERTIES TO NEW CATEGORY
			// ====================================
	    	if(count($all_menu_prepared)){
	    		if(isset($all_menu_prepared[$id_menu_external])){
	    			$old_menu = $all_menu_prepared[$id_menu_external];	
	    			$menu->pos = $old_menu['pos'];
	    			$menu->title = $old_menu['title'];
	    			$menu->id_icon = $old_menu['id_icon'];   			
	    		}	    		
	    	}			
			if(!$menu->save()) break;

			// ================
			// ADDING NEW ITEMS
			// ================		
			$items = $cat['items'];
			if(count($items)){
				foreach($items as $iiko_item){					
					
					$id_item_external = $iiko_item['id'];

					$item_pos = 0;
					$new_item = new Smart_object('items');
					$new_item->id_cafe = $id_cafe;					
					$new_item->sku = $iiko_item["sku"];
					$new_item->iiko_order_item_type = $iiko_item["orderItemType"];
					$new_item->id_external = $id_item_external;
					$new_item->description = $iiko_item['description'];
					$new_item->id_menu = $menu->id;
					$new_item->title = $iiko_item['name'];
					$new_item->title_original = $iiko_item['name'];
					$new_item->pos = $item_pos+1;
					$new_item->image_url = $iiko_item["imageUrl"];
					$new_item->updated_date = "now()";

					if(isset($iiko_item["modifiers"])){
						$iiko_modifiers = json_encode($iiko_item["modifiers"], JSON_UNESCAPED_UNICODE);	
					}else{
						$iiko_modifiers = "";
					}
					
					if(isset($iiko_item["sizes"])){
						$iiko_sizes = json_encode($iiko_item["sizes"], JSON_UNESCAPED_UNICODE);	
					}else{
						$iiko_sizes = "";
					}
										
					$new_item->iiko_modifiers = $iiko_modifiers;
					$new_item->iiko_sizes = $iiko_sizes;

					// ================================
					// COPY SOME PROPERTIES TO NEW ITEM
					// ================================					
			    	if(count($all_items_prepared)){
			    		if(isset($all_items_prepared[$id_item_external])){
			    			$old_item = $all_items_prepared[$id_item_external];	
			    			$new_item->pos = $old_item['pos'];
			    			$new_item->title = $old_item['title'];
			    			$new_item->description = $old_item['description'];
			    			
			    			$new_item->mode_spicy = $old_item['mode_spicy'];	
			    			$new_item->mode_vege = $old_item['mode_vege'];	
			    			$new_item->mode_hit = $old_item['mode_hit'];	
							$new_item->extra_data = $old_item['extra_data'];
			    				
							if(!empty($old_item['image_url'])) $new_item->image_url = $old_item['image_url'];	

			    			array_push($arr_items_to_remove,$old_item['id_external']);
			    		}	    		
			    	}
			    	$new_item->created_by="iiko";
					$new_item->save();

				}
			}			
			
	    }    	
    }

	// ===========================================
	// DELETE ITEMS FROM ARCHIVE IF IT'S DUPLICATE
	// ===========================================
    if(count($arr_items_to_remove)){
    	$str = implode("','", $arr_items_to_remove);
    	$q = "DELETE FROM items WHERE id_cafe={$id_cafe} AND id_menu={$id_archive} AND id_external IN ('".$str."')";
 		$del = SQL::delete($q);
    }

	// =================================
    // DELETE ARCHIVE MENU IF IT'S EMPTY
	// =================================
    $remaining_items = new Smart_collect("items","where id_menu = {$id_archive}");
    if($remaining_items){
    	if(!$remaining_items->full()){
    		$arch_menu->delete();
    	}
    }

	$iiko_params_collect = new Smart_collect("iiko_params","where id_cafe='".$cafe->id."'");
	if(!$iiko_params_collect || !$iiko_params_collect->full()) __errorjsonp("Not found iiko_params for cafe {$cafe->id}.");
	$iiko_params = $iiko_params_collect->get(0);

	$iiko_params->current_extmenu_hash = $new_menu_hash;	
	$iiko_params->updated_date = 'now()';	

	if($iiko_params->save()){
		__answerjsonp(["re-created"=>$categories,"items-updated"=>$arr_items_to_remove]);
	}else{
		__errorjsonp("Can not save new rough_menu_hash, ".__LINE__);
	}
    


?>