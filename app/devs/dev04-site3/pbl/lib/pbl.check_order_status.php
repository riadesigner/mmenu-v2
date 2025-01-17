<?php

define("BASEPATH",__file__);

header('content-type: application/json; charset=utf-8');
$callback = $_REQUEST['callback'] ?? 'alert';
if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }   

require_once getenv('WORKDIR').'/config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';	
require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.user.php';
require_once WORK_DIR.APP_DIR.'core/class.order_sender.php';
require_once WORK_DIR.APP_DIR.'core/class.iiko_order.php';

require_once WORK_DIR.APP_DIR.'pbl/lib/pbl.class.order_parser.php';

session_start();
SQL::connect();


if(!isset($_POST['cafe_uniq_name'])) __errorjsonp("--not found cafe_uniq_name");
if(!isset($_POST['short_number'])) __errorjsonp("--not found short_number");

$cafe_uniq_name = $_POST['cafe_uniq_name'];
$short_number = $_POST['short_number'];

$orders = new Smart_collect("orders", "where cafe_uniq_name='$cafe_uniq_name' AND short_number='$short_number'");	
if(!$orders->full()) __errorjsonp("--not found order");
$order = $orders->get(0);

__answerjsonp( ["order_status"=>$order->state, "order_manager"=>$order->manager] );	


?>