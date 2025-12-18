<?php

define("BASEPATH",__file__);

header('content-type: application/json; charset=utf-8');

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

glog('$_POST= '.print_r($_POST,1));

if(!isset($_POST['cafe_uniq_name'])) __errorjson("--not found cafe_uniq_name");
if(!isset($_POST['short_number'])) __errorjson("--not found short_number");

$cafe_uniq_name = $_POST['cafe_uniq_name'];
$short_number = $_POST['short_number'];

$q = implode(" ",[
    "where cafe_uniq_name='$cafe_uniq_name'",
    "AND short_number='$short_number'",
]);

// glog("order check: \$q");

$orders = new Smart_collect("orders", $q);	

if(!$orders->full()) __errorjson("--not found order");
$order = $orders->get(0);

if(!empty($order->manager) ){
    $manager = new Smart_object("tg_users", $order->manager); 
    if($manager && $manager->valid()){
        $order_manager_name = $manager->name;
    }else{
        $order_manager_name = "";
    }    
}else{
    $order_manager_name = "";
}

__answerjson( [
    "order_status"=>$order->state, 
    "order_manager"=>$order->manager,
    "order_manager_name"=>$order_manager_name,
    "id_uniq"=>$order->id_uniq,
    "short_number"=>$order->short_number,
    "date"=>$order->date,
    "order_target"=>$order->order_target,
    "table_number"=>$order->table_number,
    ] );	


?>