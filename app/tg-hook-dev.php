<?php

define("BASEPATH",__file__);
require_once 'config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';

$data = json_decode(file_get_contents('php://input'), TRUE);

glog("tg cart dev bot answer: ".print_r($data, 1),__FILE__);

?>