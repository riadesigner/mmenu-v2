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

	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user, ".__LINE__);

	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown cafe, ".__LINE__);


	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);

	if($cafe->id_user!==$user->id)__errorjsonp("Not allowed, ".__LINE__);
	
	// GET IIKO PARAMS OBJECT FOR THE CAFE 	
	$iiko_params_collect = new Smart_collect("iiko_params", "where id_cafe='".$id_cafe."'");
	if($iiko_params_collect->full()){
		$iiko_params = $iiko_params_collect->get(0);
	}else{
		$iiko_params = new Smart_object("iiko_params");
		$iiko_params->id_cafe = $id_cafe;		
		if(!$iiko_params->save())__errorjsonp("Something wrong. Can`t saving iiko params");		
	}

	// GET IIKO API KEY	
	$key = $cafe->iiko_api_key;

	$ROUGH_DATA = [];
	
	// --------------------------
	// CHECK IF IT IS REAL KEY
	// GETTING TOKEN FROM IIKO
	// --------------------------
	$url     = 'api/1/access_token';
	$headers = ["Content-Type"=>"application/json"];
	$params  = ["apiLogin" => $key];
	$res = iiko_get_info($url,$headers,$params);
	if( isset($res["errorDescription"]) ) {
		if(str_contains((string) $res["errorDescription"], "is not authorized")){
			__errorjsonp("--unknown login");	
		}else{
			__errorjsonp($res);
		}		
	}

	if(!isset($res["token"])){
		__errorjsonp($res);		
	}else{
		$token = $res["token"];	
	}

	// ---------------------------
	// GETTING ORGANIZATIONS INFO
	// ---------------------------	
	$arr_organizations = iiko_get_organizations_info($token);
	if(!count($arr_organizations))__errorjsonp("--unknown organization");
	$currentOrganizationId = $arr_organizations[0]["id"];	
	$ROUGH_DATA["ORGANIZATIONS"] = $arr_organizations;	
	
	// --------------------------
	// GETTING EXTMENUS INFO
	// --------------------------		
	$arr_extmenus = iiko_get_extmenus_info($token);	
	if(!count($arr_extmenus))__errorjsonp("--has not menus");
	$ROUGH_DATA["EXTERNALMENUS"] = $arr_extmenus;	

	$iiko_current_extmenu_id = iiko_get_new_current_extmenu_id($arr_extmenus, $iiko_params->current_extmenu_id);

	// --------------------------
	// GETTING ALL TERMINAL GROUPS 
	// FOR CURRENT ORGANIZATION
	// --------------------------		
	$all_terminal_groups = iiko_get_terminal_groups_info($token, $currentOrganizationId);	
	if(!count($all_terminal_groups))__errorjsonp("--has not terminal groups");
	$ROUGH_DATA["TERMINALS"] = $all_terminal_groups;	

	// GET TERMINAL GROUPS FOR CURRENT ORGANIZATION
	$arr_terminals = $all_terminal_groups[0]['items'];
	if(!count($arr_terminals)) __errorjsonp("--has not actual terminals");
	$currentTerminalGroupId = $arr_terminals[0]['id'];
	
	// ----------------------------------------------
	// GETTING TABLES (RESTORAUNT SECTIONS) 
	// FOR ALL TERMINAL GROUPS IN CURRENT RESTAURANT
	// ----------------------------------------------	
	$arrTerminalGroupsIds = [];
	foreach($arr_terminals as $terminalGroup){
		array_push($arrTerminalGroupsIds, $terminalGroup["id"]);
	}
	$restaurantSections = iiko_get_table_sections_info($token, $arrTerminalGroupsIds);
	if(!count($restaurantSections)) __errorjsonp("--cant getting tables info");
	
	$ROUGH_DATA["TABLES"] = $restaurantSections;	

	$arr_tables = iiko_tables_res_parse($restaurantSections);
		
	// --------------------
	// GETTING ORDER_TYPES
	// --------------------
	$arr_order_types = iiko_get_order_types_info($token, $currentOrganizationId);
	if(!count($arr_order_types)) __errorjsonp("--cant receive order types");		
	$order_types = $arr_order_types[0]['items'];	

	$ROUGH_DATA["ORDER_TYPES"] = $arr_order_types;
	
	// --------------------------------
	// UPDATING IIKO PARAMS SAVED INFO
	// --------------------------------		
	$iiko_params->organizations = json_encode($arr_organizations, JSON_UNESCAPED_UNICODE);			
	$iiko_params->terminal_groups = json_encode($arr_terminals, JSON_UNESCAPED_UNICODE);	
	$iiko_params->tables = json_encode($arr_tables, JSON_UNESCAPED_UNICODE);	
	$iiko_params->order_types = json_encode($order_types, JSON_UNESCAPED_UNICODE);	
	$iiko_params->extmenus = json_encode($arr_extmenus, JSON_UNESCAPED_UNICODE);	;	

	$iiko_params->current_extmenu_id = $iiko_current_extmenu_id;
	$iiko_params->current_organization_id = $currentOrganizationId;
	$iiko_params->current_terminal_id = $currentTerminalGroupId;

	$iiko_params->rough_data = json_encode($ROUGH_DATA, JSON_UNESCAPED_UNICODE);	
	$iiko_params->updated_date = 'now()';

	if($iiko_params->save()){	
		__answerjsonp($iiko_params->export());
	}else{
		glogError("Can't update iiko info for cafe, ".__FILE__.", ".__LINE__);
		__errorjsonp("Can't save iiko_params for ".$iiko_params->id_cafe);
	}

	function iiko_get_organizations_info($token): array {
		$url     = 'api/1/organizations';
		$headers = [
			"Content-Type"=>"application/json",
			"Authorization" => 'Bearer '.$token
		]; 
		$params  = ['organizationIds' => null, 'returnAdditionalInfo' => true, 'includeDisabled' => true];
		$res = iiko_get_info($url,$headers,$params);
		return $res["organizations"] ?? [];
	}

	function iiko_get_extmenus_info($token): array {
		$url     = 'api/2/menu';
		$headers = [
			"Content-Type"=>"application/json",
			"Authorization" => 'Bearer '.$token
		]; 
		$params  = [];
		$res = iiko_get_info($url,$headers,$params);
		return $res["externalMenus"] ?? [];
	}

	function iiko_get_terminal_groups_info($token, $orgId): array {
		$url     = 'api/1/terminal_groups';
		$headers = [
			"Content-Type"=>"application/json",
			"Authorization" => 'Bearer '.$token
		]; 
		$params  = ['organizationIds' => [$orgId], 'includeDisabled' => true];
		$res = iiko_get_info($url,$headers,$params);
		return $res["terminalGroups"] ?? [];
	}

	function iiko_get_table_sections_info(string $token, array $arrTerminalGroupIds): array {		
		$url     = 'api/1/reserve/available_restaurant_sections';
		$headers = [
			"Content-Type"=>"application/json",
			"Authorization" => 'Bearer '.$token
		]; 
		$params  = ['terminalGroupIds' => [...$arrTerminalGroupIds], 'returnSchema' => true];
		$res = iiko_get_info($url,$headers,$params);
		return $res['restaurantSections'] ?? [];
	}

	function iiko_get_order_types_info(string $token, string $orgId): array {		
		$url     = 'api/1/deliveries/order_types';
		$headers = [
			"Content-Type"=>"application/json",
			"Authorization" => 'Bearer '.$token
		]; 
		$params  = ['organizationIds' => [$orgId]];
		$res = iiko_get_info($url,$headers,$params);
		return $res['orderTypes'] ?? [];
	}	

	// ------------------------------------------------------
	//  checking if the current_extmenu_id has correct value
	//  return verivied current_extmenu_id  
	// ------------------------------------------------------
	function iiko_get_new_current_extmenu_id(array $arr_extmenus, string $saved_current_extmenu_id): string{
		
		$new_current_extmenu_id = "";

		if(count($arr_extmenus)>0){		
			if(!empty($saved_current_extmenu_id)){			
				$filteredArray = array_filter($arr_extmenus, function ($m) {	
					global $saved_current_extmenu_id;				
					return $m['id'] === $saved_current_extmenu_id;
				});
				if(count($filteredArray)>0){
					// оставляем как есть 
					$new_current_extmenu_id = $saved_current_extmenu_id;
				}else{
					// берем первое меню из списка
					$new_current_extmenu_id = $arr_extmenus[0]["id"];
				}			
			}else{
				// берем первое меню из списка
				$new_current_extmenu_id = $arr_extmenus[0]["id"];
			}
		}

		return $new_current_extmenu_id;
	}	

?>