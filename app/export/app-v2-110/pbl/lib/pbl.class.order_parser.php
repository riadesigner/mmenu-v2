<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Order_parser{
    
    protected array $order_data;
    protected array $order_items;
    protected string $ORDER_TARGET;    
    protected string $time_sent;    
    protected string $time_need;  
    protected string $user_phone; 
    protected string $deliv_address;
    protected string $user_comment;    
    protected string $order_text;
    protected string $str_currency;
    protected int $total_price;
    protected $table_number = null;
    protected int $time_format;
    protected bool $NEARTIME_MODE;   
    protected bool $PICKUPSELF_MODE;    
    protected bool $READY_FOR_BUILD;

    public function __construct($params){
        $this->time_format = 24;
        $this->str_currency = "₽";
        $this->READY_FOR_BUILD = $this->verify($params);
        return $this;
    }

    // -------------------------------------
    //  BUILDING ORDER STRING FOR TELEGRAM
    // -------------------------------------
    public function build_tg_txt(): Order_parser{    
        if (!$this->READY_FOR_BUILD) throw new Exception("--not found the order params");        
        
        switch ($this->ORDER_TARGET){ 
            case Order_sender::ORDER_DELIVERY:
                $this->order_text =  $this->build_for_delivery();    
            break; 
            case Order_sender::ORDER_TABLE:
                $this->order_text =  $this->build_for_table();
            break;
            default:
                throw new Exception("--wrong order_target param");
            break;     
        }
        return $this;
    }

    public function get(): string{
        return !empty($this->order_text)?$this->order_text:"";
    }
    
    // PRIVATE

    private function build_for_delivery():string {

        $str_time = glb_russian_datetime($this->time_need, $this->time_format);
        
        if($this->time_need==$this->time_sent){
            $order_time_to = "Заказ на ближайшее время";
        }else{
            $order_time_to =  "Приготовить к: {$str_time}";	
        }
        $str_order_mode  = $this->PICKUPSELF_MODE?"Самовывоз":"Доставка";
        
        $str = "";
        $str .= "   ------------\n";
        $str .= "   {$str_order_mode}\n";
        if(!$this->PICKUPSELF_MODE){
        $str .= "   {$this->deliv_address}\n";	
        }
        $str .= "   тел: {$this->user_phone}\n";
        $str .= "   ------------\n";
        $str .= "   Создан: {$str_time}\n";
        $str .= "   Сумма: {$this->total_price} {$this->str_currency}.\n";
        $str .= "   ------------\n";
        
        if(!empty($this->user_comment)){
            $str .= "  Комментарий: {$this->user_comment}\n";	
            $str .= "  ------------\n";	
        }        
        
        $str .= $this->build_str_items();
        return $str;
    }

    private function build_for_table():string {

        $str_time = glb_russian_datetime($this->time_need, $this->time_format);
                
        $str = "";
        $str .= "   ------------\n";
        $str .= "   на стол №: {$this->table_number}\n";
        $str .= "   ------------\n";
        $str .= "   Создан: {$str_time}\n";
        $str .= "   Сумма: {$this->total_price} {$this->str_currency}.\n";
        $str .= "   ------------\n";
        
        $str .= $this->build_str_items();
        
        return $str;
    }    

    private function build_str_items(): string{        
        $str = "";
        $count = 0;
        foreach ($this->order_items as $row) {		
            $count++;        
            $item_modifiers = $row['chosen_modifiers'] ?? false;	
            $item_title = $count.". ".$row["item_data"]["title"];	
            $item_size = !empty($row["sizeName"])?$row["sizeName"] : "";
            $item_volume = !empty($row["volume"])?$row["volume"] : "";
            $item_units = !empty($row["units"])?$row["units"] : "";
            $volume_str = !empty($item_volume)?$item_volume." ".$item_units : "";
        
            $item_price = $row["count"]."x".$row["price"]." ".$this->str_currency;
        
            $str .= "_{$item_title}_\n";
            $str .= "{$item_size} / {$volume_str}\n";	
        
            if($item_modifiers){
                foreach($item_modifiers as $m){
                    $mod_title = $m["name"];
                    $mod_price = "1x".$m["price"]." ".$this->str_currency;
                    $str .= "+ {$mod_title}, {$mod_price}\n";
                }
            }
            $str .= "= {$item_price}\n";
            $separator = $count < count($this->order_items) ?"---------\n":"--------- //\n";
            $str .= $separator;
        }       
        return  $str;   
    }    

    private function verify($params): bool{

        if(!isset($params["order_target"])){ throw new Exception('order_target param required'); }
        $this->ORDER_TARGET = $params["order_target"];

        if(!isset($params["order_data"])){ throw new Exception('order_data param required'); }
        $this->order_data = $params["order_data"];
        if(!is_array($this->order_data)||!count($this->order_data)) {throw new Exception('order_data param is wrong');}
        
        if(!isset($this->order_data["order_time_sent"])){ throw new Exception('order_time_sent param is wrong'); }
        $this->time_sent = post_clean($this->order_data['order_time_sent'],100);
        if(empty($this->time_sent)) { throw new Exception('--wrong order data'); }

        if(!isset($this->order_data['order_total_price'])){ throw new Exception('order_total_price param is wrong'); }
        $this->total_price = (int) $this->order_data['order_total_price'];
        
        if(!isset($this->order_data['order_items'])){ throw new Exception('order_items param is wrong'); }
        if(!is_array($this->order_data['order_items'])){ throw new Exception('order_items param is wrong'); }
        $this->order_items = $this->order_data['order_items'];

        // time part
        if(!isset($this->order_data["order_time_need"])){ throw new Exception('order_time_need param is wrong'); }
        $this->time_need = post_clean($this->order_data['order_time_need'],100);        
        if(empty($this->time_need)) $this->time_need = $this->time_sent;    
        $this->NEARTIME_MODE = $this->time_need===$this->time_sent;  

        // ORDER TO TABLE MODE
        if($this->ORDER_TARGET === Order_sender::ORDER_TABLE){
            if(!isset($params["table_number"])){ throw new Exception('table_number param required'); }
            $this->table_number = $params["table_number"];

        // ORDER TO DELIVERY OR PICKUPSELF
        }else{

            // pickupself part
            if(!isset($params['pickupself_mode'])){
                $this->PICKUPSELF_MODE = true;            
            }else{
                $this->PICKUPSELF_MODE = $params['pickupself_mode'];            
            }
                        
            // userphone part
            if(!isset($this->order_data['order_user_phone'])){ 
                $this->user_phone = "";
            }else{
                $this->user_phone = post_clean($this->order_data["order_user_phone"], 50);
                $this->user_phone = preg_replace("/[^+0-9 ()\-,.]/", "", $this->user_phone);
                if(empty($this->user_phone)) {throw new Exception('--need to know user phone');};
            }

            // delivery part
            if(!$this->PICKUPSELF_MODE){
                // DELIVERY MODE
                if($this->ORDER_TARGET===Order_sender::ORDER_DELIVERY){
                    $u_address = $this->order_data['order_user_full_address'];
                    if(!empty($u_address['description'])){
                        // CHEFSMENU MODE
                        $this->deliv_address = $u_address['description'];
                    }else{
                        // IIKO MODE
                        $u_address_entrance = isset($u_address['entrance'])? $u_address['entrance']: "";
                        $u_address_floor = isset($u_address['floor']) ? isset($u_address['floor']) : "";
                        if(empty($u_address['u_street'])) throw new Exception('--need to know user street');
                        if(empty($u_address['u_house'])) throw new Exception("--need to know user house");            
                        $this->deliv_address = "ул. ".$u_address['u_street'].", д. ".$u_address['u_house'].",
                        подъезд ({$u_address_entrance}),  этаж ({$u_address_floor})";                
                    }                    
                }
            }else{
                $this->deliv_address = "";
            }

        }
                
        $this->user_comment = post_clean($this->order_data["order_user_comment"], 250);

        return true;
    }

}