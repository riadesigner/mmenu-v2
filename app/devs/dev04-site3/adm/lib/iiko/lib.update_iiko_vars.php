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

	// --------------------------
	// GETTING ORGANIZATION INFO
	// --------------------------	

	$url     = 'api/1/organizations';
	$headers = [
	    "Content-Type"=>"application/json",
	    "Authorization" => 'Bearer '.$token
	]; 
	$params  = ['organizationIds'      => null, 'returnAdditionalInfo' => true, 'includeDisabled'      => true];

	$res = iiko_get_info($url,$headers,$params);

	if(!isset($res["organizations"])){
		__errorjsonp("--unknown organization");	
	}

	
	$currentOrganizationId = $res["organizations"][0]["id"];
	$currentOrganizationName = post_clean($res["organizations"][0]["name"]);
	$currentOrganizationAddress = post_clean($res["organizations"][0]["restaurantAddress"]);		

	$iiko_organizations = [
		"current_organization_id"=>$currentOrganizationId,
		"items"=>[]
	];

	foreach($res["organizations"] as $org){
		array_push($iiko_organizations['items'],
			[
				"id"=>$org["id"],
				"name"=>$org["name"],
				"address"=>$org["restaurantAddress"]
			]
		);
	}

	// --------------------------
	// GETTING EXTMENUS INFO
	// --------------------------	
	$url     = 'api/2/menu';
	$headers = [
	    "Content-Type"=>"application/json",
	    "Authorization" => 'Bearer '.$token
	]; 
	$params  = [];
	$res = iiko_get_info($url,$headers,$params);
	if(!isset($res["externalMenus"]) || !count($res["externalMenus"])){
		__errorjsonp("--has not menus");	
	}	
	
	
	$iiko_extmenus = [];	
	foreach($res["externalMenus"] as $menu){
		array_push($iiko_extmenus,[
			"id"=>$menu["id"],			
			"name"=>$menu["name"]			
		]);
	}

	// ----------------------------
	//  SETTING CURRENT EXTMENU ID
	// ----------------------------
	if(count($iiko_extmenus)>0){
		
		if(!empty($iiko_params->current_extmenu_id)){			
			$filteredArray = array_filter($iiko_extmenus, function ($m) {
				global $iiko_params;
				return $m['id'] === $iiko_params->current_extmenu_id;
			});
			if(count($filteredArray)>0){
				// оставляем как есть 
				$iiko_current_extmenu_id = $iiko_params->current_extmenu_id;
			}else{
				// берем первое меню из списка
				$iiko_current_extmenu_id = $res["externalMenus"][0]["id"];
			}			
		}else{
			// берем первое меню из списка
			$iiko_current_extmenu_id = $res["externalMenus"][0]["id"];
		}
	}else{
		$iiko_current_extmenu_id = "";
	}
	

	// --------------------------
	// GETTING TERMINAL GROUPS 
	// --------------------------		

	$url     = 'api/1/terminal_groups';
	$headers = [
	    "Content-Type"=>"application/json",
	    "Authorization" => 'Bearer '.$token
	]; 
	$params  = ['organizationIds'      => [$currentOrganizationId], 'includeDisabled'      => true];
	$res = iiko_get_info($url,$headers,$params);

	if(!isset($res['terminalGroups'])){
		__errorjsonp("--has not terminal groups");		
	}	

	
	$currentTerminalGroups = $res['terminalGroups'][0]['items'];
	if(!count($currentTerminalGroups)) __errorjsonp("--has not actual terminals");

	$currentTerminalGroupId = $res['terminalGroups'][0]['items'][0]['id'];
	
	$terminalGroups = [
		'current_terminal_group_id'=>$currentTerminalGroupId,
		'items'=>$currentTerminalGroups
	];	

	// --------------------------
	// GETTING TABLES FOR THE 
	// TERMINAL GROUPS
	// --------------------------	

	$url     = 'api/1/reserve/available_restaurant_sections';
	$headers = [
	    "Content-Type"=>"application/json",
	    "Authorization" => 'Bearer '.$token
	]; 

	$params  = ['terminalGroupIds' => [$currentTerminalGroupId], 'returnSchema'      => true];

	$res = iiko_get_info($url,$headers,$params);

	if(!isset($res['restaurantSections'])){
		__errorjsonp('--cant getting tables info');	
	}	
	
	glog("SECTIONS = \n".print_r($res,1));

	$arr_tables = iiko_tables_res_parse($res);

	// --------------------------
	// GETTING ORDER_TYPES
	// --------------------------

	$url     = 'api/1/deliveries/order_types';
	$headers = [
	    "Content-Type"=>"application/json",
	    "Authorization" => 'Bearer '.$token
	]; 
	$params  = ['organizationIds' => [$currentOrganizationId]];

	$res = iiko_get_info($url,$headers,$params);

	if(!isset($res['orderTypes'])){
		__errorjsonp("--cant receive order types");		
	}

	$order_types = $res['orderTypes'][0]['items'];	

	// --------------------------
	// UPDATING SOME! CAFE INFO
	// --------------------------	
	
	$iiko_params->organizations = json_encode($iiko_organizations, JSON_UNESCAPED_UNICODE);	
	$iiko_params->extmenus = json_encode($iiko_extmenus, JSON_UNESCAPED_UNICODE);	;	
	$iiko_params->terminal_groups = json_encode($terminalGroups, JSON_UNESCAPED_UNICODE);	
	$iiko_params->tables = json_encode($arr_tables, JSON_UNESCAPED_UNICODE);	
	$iiko_params->order_types = json_encode($order_types, JSON_UNESCAPED_UNICODE);	
	$iiko_params->current_extmenu_id = $iiko_current_extmenu_id;
	$iiko_params->updated_date = 'now()';	

	if($iiko_params->save()){	
		__answerjsonp($iiko_params->export());
	}else{
		glogError("Can't update iiko info for cafe, ".__FILE__.", ".__LINE__);
		__errorjsonp("Can't save iiko_params for ".$iiko_params->id_cafe);
	}

?>