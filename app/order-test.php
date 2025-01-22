<?php

define("BASEPATH",__file__);
require_once 'config.php';

require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/common.php';
glog("test crontab");

$time = date('Y-m-d H:i:s');
echo "CHECK ORDERS! $time\n";

?>