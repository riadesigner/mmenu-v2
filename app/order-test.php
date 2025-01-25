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

SQL::connect();

$time = date('Y-m-d H:i:s');

// max time (minutes) for take an order
// after that its need to sending a reminder to TG
$ORDER_WAITING_DELAY = 3; 

// end time (minutes) for take an order
// after that its need to mark the order as forgotten
$ORDER_FORGOTTEN_DELAY = 10;

// ---------------------- PART I ---------------------- 
// находим все заказы, созданные более 15 минут назад
// и никем не взятые (не подтвержлденные), 
// отмечаем их брошенными (forgotten)
$sql = "WHERE date < NOW() - INTERVAL $ORDER_FORGOTTEN_DELAY MINUTE AND state = 'created'";
$cond = "ORDER BY 'cafe_uniq_name'";
$forgotten_orders = new Smart_collect("orders", $sql, $cond);
if($forgotten_orders && $forgotten_orders->full()){
    glog("$time forgotten orders found: ".$forgotten_orders->found());

    $ORDERS_BY_CAFE = sort_orders_by_cafe($forgotten_orders->get());

    foreach($ORDERS_BY_CAFE as $cafe_uniq_name => $orders){
        foreach($orders as $order){
            $order->state = 'forgotten';
            $order->save();
        }        
        $MSG = Order_sender::send_forgotten_message($cafe_uniq_name, $orders, $ORDER_FORGOTTEN_DELAY);         
        glog("Отправленное в TG сообщение: ".$MSG );
    }
    
}

// ---------------------- PART II ----------------------
// находим все заказы, создагнные более 5 минут назад,
// которые никто не взял в работу (и не подтвердил)
// рассылаем сообщение (напоминание) об этих заказах

$sql = "WHERE date < NOW() - INTERVAL $ORDER_WAITING_DELAY MINUTE AND state = 'created'";
$cond = "ORDER BY 'cafe_uniq_name'";
$not_taken_orders = new Smart_collect("orders", $sql,  $cond);

if($not_taken_orders && $not_taken_orders->full()){
    glog("$time not taken orders found: ".$not_taken_orders->found());
    
    $ORDERS_BY_CAFE = sort_orders_by_cafe($not_taken_orders->get());
    
    foreach($ORDERS_BY_CAFE as $cafe_uniq_name => $orders){                        
        $MSG = Order_sender::send_a_reminder($cafe_uniq_name, $orders, $ORDER_WAITING_DELAY);         
        glog("Отправленное в TG сообщение: ".$MSG );
    }

}

echo "$time check orders: ok\n"; // to cron's log

/**
 * @param $orders // Array<Smart_object>
 * @return array // [ "123rf" => Array<Smart_object>, "456ab" => Array<Smart_object>, ... ];
 */
function sort_orders_by_cafe(array $orders): array{
    $ORDERS_BY_CAFE = [];
    foreach($orders as $order){
        $cafe_uniq_name = $order->cafe_uniq_name;
        if(!isset($ORDERS_BY_CAFE[$cafe_uniq_name]))$ORDERS_BY_CAFE[$cafe_uniq_name] = [];
        $ORDERS_BY_CAFE[$cafe_uniq_name] = [...$ORDERS_BY_CAFE[$cafe_uniq_name], $order];
    }
    return $ORDERS_BY_CAFE;
}

// Order_sender::send_tg_order($cafe->uniq_name, ORDER_TARGET, $short_number, $ORDER_TXT);






?>