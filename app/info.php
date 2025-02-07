<?php
// phpinfo();

define("BASEPATH",__file__);
require_once 'config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';

// $url = "http://31.200.237.194/iiko-hook.php";

$url = "https://chefsmenu.ru/iikohook-dev/";

$data = ["name"=>"petya"];
$jsonData = json_encode($data);

$res =  sent_json_to_url($jsonData, $url);

echo "\$res=$res\n"; 
echo "--ok!--";

function sent_json_to_url($jsonData, $url): string{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Content-Length: " . strlen($jsonData)
    ]);    
    $response = curl_exec($ch);    
    // проверяем на ошибки
    if (curl_errno($ch)) {
        echo "Ошибка: " . curl_error($ch);
    }
    // закрываем соединение
    curl_close ($ch);    
    return "Ответ dev сервера: " . $response;
}


?>
