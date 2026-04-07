<?php
// php8


$key = "bc4abe7044f6447eac7deb36d4531f6c";

// ------------------------
// GETTING TOKEN FROM IIKO 
// ------------------------
$url     = 'api/1/access_token';
$headers = ["Content-Type"=>"application/json"];
$params  = ["apiLogin" => $key];
$res = iiko_get_info($url,$headers,$params);
$token = $res['token'];

echo "TOKEN = $token<br><br>";

// -------------------------------
// GETTING ORGANIZATIONS FROM IIKO
// ------------------------------- 
$url     = 'api/1/organizations';
$headers = [
    "Content-Type"=>"application/json",
    "Authorization" => 'Bearer '.$token
]; 
$params  = ['organizationIds' => null, 'returnAdditionalInfo' => true, 'includeDisabled' => true];
$res = iiko_get_info($url,$headers,$params);
$ARR_ORGS = $res["organizations"] ?? [];

echo "ALL ORGANIZATIONS:";
echo "<pre>";
print_r($res);
echo "</pre>";

$orgId = $ARR_ORGS[0]["id"]; // 0c6f6201-c526-4096-a096-d7602e3f2cfd
echo "<br><br>CURRENT ORGANIZATION ID = $orgId<br><br>";

exit();


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


$externalMenuId = "9da77ff8-862d-45e4-a7f2-a5117910fa66";

$url     = 'api/2/menu/by_id';
$headers = [
    "Content-Type"=>"application/json",
    "Authorization" => 'Bearer '.$token
]; 

$params  = [
    'externalMenuId' => $externalMenuId,
    'organizationIds' => [$orgId], 
    'priceCategoryId' => null, 
    'version' => 2
];

$res = iiko_get_info($url,$headers,$params);
echo "menu?? ";
echo  "<pre>";
echo json_encode($res);
echo  "</pre>"; 


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



/* --------------------------------------------------

	          IIKO FUNCTIONS 

----------------------------------------------------- */

function iiko_get_info($url,$headers,$params){        
      $header = [];
      if (is_array($headers)) {
          $i_header = $headers;          
          foreach ($i_header as $param => $value) {
              $header[] = "$param: $value";
          }
      }      
      $curl = curl_init();
      curl_setopt_array($curl, [CURLOPT_URL => 'https://api-ru.iiko.services/'.$url, CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 15, CURLOPT_TIMEOUT => 10, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => 'POST', CURLOPT_POSTFIELDS => json_encode($params, JSON_UNESCAPED_UNICODE), CURLOPT_HTTPHEADER => $header]);
      $json = curl_exec($curl);        
      curl_close($curl);

      // glog($json);
            
      return json_decode($json, true);
}



?>