<?php

define("BASEPATH",__file__);
require_once 'config.php';


$filename = "iiko_log";
$data = json_decode(file_get_contents('php://input'), TRUE);


glog("iikohook answer: ".print_r($data, 1),$filename);



?>