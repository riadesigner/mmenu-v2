<?php
// php8

$start = microtime(true);

$key = "bc4abe7044f6447eac7deb36d4531f6c"; // ЭТО ПИЦЦАЙОЛО (получаем пустое меню)
// $key = "06515b33726d4ae3a3eb04edda4e5b07"; // ЭТО КАФЕ WALLSTREET (все работает)

// ------------------------
// GETTING TOKEN FROM IIKO 
// ------------------------
$url     = 'api/1/access_token';
$headers = ["Content-Type"=>"application/json"];
$params  = ["apiLogin" => $key];
$res = iiko_get_info($url,$headers,$params);
$token = $res['token'];

echo "TOKEN = $token<br><hr>";

// -------------------------------
// GETTING ORGANIZATIONS FROM IIKO
// ------------------------------- 
$url     = 'api/1/organizations';
$headers = [
    "Content-Type"=>"application/json",
    "Authorization" => 'Bearer '.$token
]; 
$params  = [
    'organizationIds' => null, 
    'returnAdditionalInfo' => true, 
    'includeDisabled' => true
];
$res = iiko_get_info($url,$headers,$params);
$ARR_ORGS = $res["organizations"] ?? [];

echo "ALL ORGANIZATIONS:";
echo "<pre>";
print_r($res);
echo "</pre>";

$orgId = $ARR_ORGS[0]["id"]; // 0c6f6201-c526-4096-a096-d7602e3f2cfd
echo "<br><br>CURRENT ORGANIZATION ID = $orgId<br><br>";

echo "<hr>";

// --------------------------------------------
// получаем все меню с ценовыми категориями
// --------------------------------------------
$url     = 'api/2/menu';
$headers = [
    "Content-Type"=>"application/json",
    "Authorization" => 'Bearer '.$token
]; 
$params  = [];
$res = iiko_get_info($url,$headers,$params);
$priceCategories = $res['priceCategories']??[];
$currentPriceCategory = count($priceCategories)>0 ? $priceCategories[0]['id'] : null;		

echo '<p>получаем все меню с ценовыми категориями</p>';
echo "<pre>";
print_r($res);
echo "</pre>";
echo '<p>currentPriceCategory = '.$currentPriceCategory.'</p>';

$EXTERNAL_MENU_ID = $res["externalMenus"][0]["id"];
echo "<p>EXTERNAL MENU ID = $EXTERNAL_MENU_ID</p>";
echo "<hr>";

$end = microtime(true);
$execution_time = $end - $start;

echo "Часть Скрипта выполнлась за: " . $execution_time . " секунд<br>";

// ------------------------------
// получаем Меню по его Id 
// с Базовой ценовой категорией, 
// если вообще категория выбрана
// ------------------------------
$url     = 'api/2/menu/by_id';
$headers = [
    "Content-Type"=>"application/json",
    "Authorization" => 'Bearer '.$token
]; 
$params  = [
    'externalMenuId' => $EXTERNAL_MENU_ID,
    'organizationIds' => [$orgId], 
];	

if($currentPriceCategory!==null){
    $params['priceCategoryId'] = $currentPriceCategory;
}

$res = iiko_get_info($url,$headers,$params);

$end = microtime(true);
$execution_time = $end - $start;

echo "Скрипт выполнился за: " . $execution_time . " секунд<br>";

echo 'menu = ';
echo "<pre>";
print_r($res);
echo "<pre>";



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