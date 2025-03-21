<?php
// phpinfo();

define("BASEPATH",__file__);
require_once 'config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';

$key = "some_key";

// GETTING TOKEN FROM IIKO 

$url     = 'api/1/access_token';
$headers = ["Content-Type"=>"application/json"];
$params  = ["apiLogin" => $key];

$res = iiko_get_info($url,$headers,$params);

// echo "token:";
// echo "<pre>";
// echo json_encode($res);
// echo "</pre>";

$token = $res['token'];




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

$orgId = "0c6f6201-c526-4096-a096-d7602e3f2cfd";
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
$url     = 'api/1/nomenclature';
$headers = [
    "Content-Type"=>"application/json",
    "Authorization" => 'Bearer '.$token
]; 
$params  = [
    "organizationId"=> $orgId,
    "startRevision"=> "0",    
];

$res = iiko_get_info($url,$headers,$params);

echo  "nomenclature:";
echo  "<pre>";
echo json_encode($res);
echo  "</pre>";   
// 9da77ff8-862d-45e4-a7f2-a5117910fa66

?>
