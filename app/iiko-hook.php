<?php

define("BASEPATH",__file__);
require_once 'config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';

$data = json_decode(file_get_contents('php://input'), true);

glogIikoHook(print_r($data, 1));

echo "ok";

// if(!$_SERVER["CURRENT_ENV_LOCAL"]){    
//     // PRODUCTION ENV
//     echo sent_copy_to_dev($jsonData);
// }else{
//     // DEVELOP ENV
//     echo "ok";
// }


?>