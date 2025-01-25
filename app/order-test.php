<?php

// Report all PHP errors
error_reporting(E_ALL);

define("BASEPATH",__file__);
require_once 'config.php';

require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/common.php';
require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.user.php';

require_once WORK_DIR.APP_DIR.'core/class.order_sender.php';
require_once WORK_DIR.APP_DIR.'core/class.order_checker.php';

SQL::connect();

$time = date('Y-m-d H:i:s');

// for table_orders only
Order_checker::find_forgotten(Order_sender::ORDER_TABLE);
Order_checker::find_not_taken(Order_sender::ORDER_TABLE);

echo "$time check orders: ok\n"; // to cron's log

// Order_sender::send_tg_order($cafe->uniq_name, ORDER_TARGET, $short_number, $ORDER_TXT);






?>