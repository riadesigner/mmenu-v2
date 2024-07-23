<?php

$filename = "tg_hook_dev_deta";
$data = json_decode(file_get_contents('php://input'), TRUE);

glog("tg cart dev bot answer: ".print_r($data, 1),$filename);