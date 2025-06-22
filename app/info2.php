<?php
// phpinfo();

define("BASEPATH",__file__);
require_once 'config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';

require_once WORK_DIR.APP_DIR.'core/class.sql.php';
 
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.user.php';
require_once WORK_DIR.APP_DIR.'core/class.iiko_params.php';
require_once WORK_DIR.APP_DIR.('core/class.iiko_nomenclature.php');
require_once WORK_DIR.APP_DIR.('core/class.iiko_parser_to_unimenu.php');
require_once WORK_DIR.APP_DIR.('core/class.conv_unimenu_to_chefs.php');

$id_menu_saved = 14;

	// ================================================
	// LOADING FROM DB THE JUST IMPORTED MENU FROM IIKO
	// ================================================		
	$sm = new Smart_object('menu_imported',$id_menu_saved);
	if(!$sm->valid())__errorjsonp("Unknown menu, ".__LINE__);	
    // $new_menu = json_decode($sm->data, true);
    
// $saved_menu = json_decode($sm->data, true, 512, JSON_INVALID_UTF8_IGNORE | JSON_OBJECT_AS_ARRAY);
    
	// $new_menu = ;	 


$data = base64_decode($sm->data);
$saved_menu = json_decode($data, true);


    // echo  $saved_menu["id"];
    echo $saved_menu===null  ? "true" : "false";

    

// echo $sm."<br>";
echo "<pre>";
print_r($saved_menu);
echo "</pre>";


// $key = "some_key";

// GETTING TOKEN FROM IIKO 

// $url     = 'api/1/access_token';
// $headers = ["Content-Type"=>"application/json"];
// $params  = ["apiLogin" => $key];

// $res = iiko_get_info($url,$headers,$params);

// echo "token:";
// echo "<pre>";
// echo json_encode($res);
// echo "</pre>";

// $token = $res['token'];




// GETTING ORGANIZATIONS FROM IIKO 

// $url     = 'api/1/organizations';
// $headers = [
//     "Content-Type"=>"application/json",
//     "Authorization" => 'Bearer '.$token
// ]; 
// $params  = ['organizationIds' => null, 'returnAdditionalInfo' => true, 'includeDisabled' => true];
// $res = iiko_get_info($url,$headers,$params);

// $ARR_ORGS = $res["organizations"] ?? [];

// echo "organizations:";
// echo "<pre>";
// echo json_encode($res);
// echo "</pre>";

// $orgId = $ARR_ORGS[1]["id"]; // Светланская 109

// $orgId = "0c6f6201-c526-4096-a096-d7602e3f2cfd";
// $orgId = "dacdf3a7-2249-4f92-b18f-1491bb2b1c21";

// GETTING TERMINAL GROUPS FROM IIKO 
// $url     = 'api/1/terminal_groups';
// $headers = [
//     "Content-Type"=>"application/json",
//     "Authorization" => 'Bearer '.$token
// ]; 
// $params  = [
//     'organizationIds' => [$orgId], 
//     'includeDisabled' => true
// ];
// $res = iiko_get_info($url,$headers,$params);

// $ARR_TERMINALS = $res["terminalGroups"] ?? [];

// echo "terminals:";
// echo "<pre>";
// echo json_encode($res);
// echo "</pre>";

// GETTING MENU FROM IIKO 
// $url     = 'api/2/menu';
// $headers = [
//     "Content-Type"=>"application/json",
//     "Authorization" => 'Bearer '.$token
// ]; 
// $params  = [];
// $res = iiko_get_info($url,$headers,$params);
// $ARR_MENUS = $res["externalMenus"] ?? [];

// echo  "menus:";
// echo  "<pre>";
// echo json_encode($res);
// echo  "</pre>";   

// GETTING NOMENCLATURE FROM IIKO
// $url     = 'api/1/nomenclature';
// $headers = [
//     "Content-Type"=>"application/json",
//     "Authorization" => 'Bearer '.$token
// ]; 
// $params  = [
//     "organizationId"=> $orgId,
//     "startRevision"=> "0",    
// ];

// $res = iiko_get_info($url,$headers,$params);

// echo  "nomenclature:";
// echo  "<pre>";
// echo json_encode($res);
// echo  "</pre>";   

?>
