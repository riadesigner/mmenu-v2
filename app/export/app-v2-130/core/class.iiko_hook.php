<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/common.php';	 
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.order_sender.php';

class Iiko_hook {

    private array $data = [];

    function __construct($data){
        SQL::connect();	
        global $CFG;        
        $this->data = $data;
        // логируем с любом случае
        glogIikoHook(print_r($data, 1));
        return $this;
    }

    public function parse(): void{
        try{
            foreach($this->data as $data_row){
                $this->do_parse($data_row);
            }
        }catch( Exception $e){
            // неизвестная ошибка 
            glogIikoHook("Error parsing: ".$e->getMessage());
        }
    }

    private function do_parse(array $data_row): void{
        try{
            $eventType = $data_row["eventType"];
            $eventInfo = $data_row["eventInfo"];
            if (str_contains(mb_strtolower($eventType), "error")) {
                // события ошибок            
                if($eventType==="TableOrderError"){
                    glogIikoHook("ошибка отправки заказа на стол");
                    $this->send_warning_to_waiters($eventInfo);
                }else{
                    // другие ошибки
                    glogIikoHook("какая-то ошибка");
                }
            } else {
                // другие события            
                glogIikoHook("какое-то событие");
            }
        }catch (Exception $e){
            // неизвестная ошибка 
            glogIikoHook("Error parsing 2: ".$e->getMessage());
        }
    }

    private function send_warning_to_waiters(array $eventInfo): void{
        try{
            $orderExternalNumber = $eventInfo["externalNumber"];
            $errorDescription = $eventInfo["errorInfo"]["description"];
            $arr = explode("-",$orderExternalNumber);
            $order_id = $arr[0];
            $order = new Smart_object("orders",$order_id);
            if($order && $order->valid()){
                $manager = new Smart_object("tg_users",$order->manager);
                if($manager && $manager->valid()){
                    $msg = implode(" ",[
                      "Не получилось доставить заказ ".$order->short_number." в Iiko.",
                      "Наберите его в Iiko вручную.",
                      "_Сообщение об ошибке отправлено разработчикам._"
                    ]);
                    Order_sender::send_message_to_tg_users([$manager->tg_user_id], $msg); 
                }else{
                    glogIikoHook("не найден менеджер ".$manager->name." (".$manager->tg_user_id.") ");
                }
            }else{                
                glogIikoHook("не найден заказ на стол $order_id");
            }            
        }catch(Exception $e){
            glogIikoHook("Error parsing 3: ".$e->getMessage());
        }        
    }
}


?>