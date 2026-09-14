<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/common.php';	 
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.order_sender.php';
require_once WORK_DIR.APP_DIR.'core/lib.api.php'; // Подключаем хелпер	

class Iiko_hook {

    private array $data = [];

    function __construct($data){

        if(!$data){
            echo "no data for you!";
            glogIikoHook("no data");
            return $this;
        }

        $decoded = json_decode($data, true);

        SQL::connect();	
        global $CFG;        
        $this->data = $decoded;        
        glogIikoHook(print_r($this->data, 1));
        return $this;
    }

    public function parse(): void{
        if(!$this->data) return;
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
                    $this->send_warning($eventInfo);                    
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

    private function send_warning(array $eventInfo): void{
        // смотрим на externalNumber заказа
        $orderExternalNumber = $eventInfo["externalNumber"];
        // если это номер старого вида, отправляем в TELEGRAM
        // 189-321-260602-001 (старый вид)
        // если это номер нового вида – отправляем в chats_app
        // efd3d965-7469-4ee6-b591-bc05738efb1b (новый вид)
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $orderExternalNumber)) {
            // Новый вид (UUID v4)
            $this->send_warning_to_chats_app($eventInfo);
        } else {
            // Старый вид (189-321-260602-001)
            $this->send_warning_to_waiters($eventInfo);
        }
        
    }

    private function send_warning_to_chats_app(array $eventInfo): void{ 
        try{ 
            $orderExternalNumber = $eventInfo["externalNumber"];
            $errorDescription = $eventInfo["errorInfo"]["description"];
            glogIikoHook("отправляю ошибку в чаты");  
                    
            $internalApiKey = $_ENV['CHATS_APP_INTERNAL_API_KEY']; 
            $base = "http://chats-app-backend:3001";
            $url = "{$base}/api-internal/orders/iiko-cash-rejection";
            $headers = [
                'x-internal-key' => $internalApiKey,
                'Content-Type' => 'application/json'
                ];
            $params = [
                "externalNumber"=>$orderExternalNumber,
                "iikoResponse"=>$errorDescription
            ];

            $curlResult = post_get_info($url, $headers, $params);
            $parsed = parse_curl_response($curlResult);

            if (!$parsed['ok']) {
                glog("API error: {$parsed['errorCode']} - {$parsed['message']}");
                glog($parsed['errorCode'] ?? 'external_error', $parsed['httpCode'] ?? 500);			
                return;
            }

            // Успех — работаем с данными
            glog("chats_app answered: ".print_r($parsed['data'], 1));


        }catch(Exception $e){
            glogIikoHook("Error parsing 4: ".$e->getMessage());
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