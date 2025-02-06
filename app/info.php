<?php
// phpinfo();

define("BASEPATH",__file__);
require_once 'config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';


glogIikoHook("test iikoHook message");
echo "ok";


// $str = "123123";

// if(gettype($str)==='string'){
//     echo "\nyes";
// }else{
//     echo "\nyes";
// }

// $tg_user_ids = '123';
// $tg_user_ids = [$tg_user_ids];
// $s = print_r($tg_user_ids,1);
// $s = var_export($tg_user_ids,1);


// echo "\n\n\$s=$s";
// var_dump($tg_user_ids);


// $str = '/start 319mhg-zz76';

// if(str_starts_with($str, '/start ')){
//     $key = explode(" ",$str)[1];
//     echo $key;
// }else{
//     echo "no";
// }

// if (str_contains($string, 'lazy')) {
//     echo "The string 'lazy' was found in the string\n";
// }

?>
