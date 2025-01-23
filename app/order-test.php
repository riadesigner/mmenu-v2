<?php

define("BASEPATH",__file__);
require_once 'config.php';
// require_once getenv('WORKDIR').'/config.php';
require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/common.php';
require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.user.php';

SQL::connect();

$orders = new Smart_collect("orders", "WHERE state = 'created'",  "ORDER BY 'cafe_uniq_name'");

$time = date('Y-m-d H:i:s');

if($orders && $orders->full()){
    glog("$time check orders: ".$orders->found());
}else{
    glog("$time check orders: ok");
}

echo "$time check orders\n";











?>