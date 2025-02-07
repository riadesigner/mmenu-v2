<?php

define("BASEPATH",__file__);
require_once 'config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';

$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, TRUE);
glogIikoHook(print_r($data, 1),__FILE__);

echo "ok";

// if(!$_SERVER["CURRENT_ENV_LOCAL"]){    
//     // PRODUCTION ENV
//     echo sent_copy_to_dev($jsonData);
// }else{
//     // DEVELOP ENV
//     echo "ok";
// }

// function sent_copy_to_dev($jsonData): string{
//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, "http://31.200.237.194/iiko-hook.php");
//     curl_setopt($ch, CURLOPT_POST, 1);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, [
//         "Content-Type: application/json",
//         "Content-Length: " . strlen($jsonData)
//     ]);    
//     $response = curl_exec($ch);    
//     // проверяем на ошибки
//     if (curl_errno($ch)) {
//         echo "Ошибка: " . curl_error($ch);
//     }
//     // закрываем соединение
//     curl_close ($ch);    
//     return "Ответ dev сервера: " . $response;
// }


?>