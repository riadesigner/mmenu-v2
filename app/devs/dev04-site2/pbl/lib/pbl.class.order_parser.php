<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Order_parser{
    
    protected array $order_data;
    protected array $order_items;
    protected string $time_sent;    
    protected string $time_need;  
    protected string $user_phone; 
    protected string $deliv_address;
    protected string $user_comment;
    protected int $total_price;
    protected string $order_txt;
    protected bool $NEARTIME_MODE;   
    protected bool $PICKUPSELF_MODE;    
    protected bool $READY_FOR_BUILD;

    public function __construct($params){
        $this->READY_FOR_BUILD = $this->verify($params);
        return $this;
    }

    // ----------------------------------------
    //   BUILDING ORDER STRING (FOR TELEGRAM)
    // ----------------------------------------
    public function build_tg_txt(): Order_parser{
        if (!$this->READY_FOR_BUILD) throw new Exception("--not found the order params"); 

        $time_format = 24;
        $str_currency = "₽";
        $str_time = glb_russian_datetime($this->time_need, $time_format);
        
        if($this->time_need==$this->time_sent){
            $order_time_to = "Заказ на ближайшее время";
        }else{
            $order_time_to =  "Приготовить к: {$str_time}";	
        }
        $str_order_mode  = $this->PICKUPSELF_MODE?"Самовывоз":"Доставка";
        
        $order_txt = "";
        $order_txt .= "   ------------\n";
        $order_txt .= "   {$str_order_mode}\n";
        if(!$this->PICKUPSELF_MODE){
        $order_txt .= "   {$this->deliv_address}\n";	
        }
        $order_txt .= "   тел: {$this->user_phone}\n";
        $order_txt .= "   ------------\n";
        $order_txt .= "   Создан: {$str_time}\n";
        $order_txt .= "   Сумма: {$this->total_price} {$str_currency}.\n";
        $order_txt .= "   ------------\n";
        
        if(!empty($this->user_comment)){
            $order_txt .= "  Комментарий: {$this->user_comment}\n";	
            $order_txt .= "  ------------\n";	
        }        
        
        $count = 0;
        foreach ($this->order_items as $row) {		
            $count++;
        
            $item_modifiers = $row['chosen_modifiers'] ?? false;	
            $item_title = $count.". ".$row["item_data"]["title"];	
            $item_size = !empty($row["sizeName"])?$row["sizeName"] : "";
            $item_volume = !empty($row["volume"])?$row["volume"] : "";
            $item_units = !empty($row["units"])?$row["units"] : "";
            $volume_str = !empty($item_volume)?$item_volume." ".$item_units : "";
        
            $item_price = $row["count"]."x".$row["price"]." ".$str_currency;
        
            $order_txt .= "_{$item_title}_\n";
            $order_txt .= "{$item_size} / {$volume_str}\n";	
        
            if($item_modifiers){
                foreach($item_modifiers as $m){
                    $mod_title = $m["name"];
                    $mod_price = "1x".$m["price"]." ".$str_currency;
                    $order_txt .= "+ {$mod_title}, {$mod_price}\n";
                }
            }
            $order_txt .= "= {$item_price}\n";
            $separator = $count < count($this->order_items) ?"---------\n":"--------- //\n";
            $order_txt .= $separator;
        }
        $this->order_txt = $order_txt;
        return $this;
    }

    public function get(): string{
        return $this->order_txt;
    }

    private function verify($params): bool{
        if(!isset($params["order_data"])){ throw new Exception('order_data param required'); }
        $this->order_data = $params["order_data"];
        if(!is_array($this->order_data)||!count($this->order_data)) {throw new Exception('order_data param is wrong');}

        if(!isset($this->order_data["order_time_sent"])){ throw new Exception('order_time_sent param is wrong'); }
        $this->time_sent = post_clean($this->order_data['order_time_sent'],100);
        if(empty($this->time_sent)) { throw new Exception('--wrong order data'); }

        if(!isset($this->order_data["order_time_need"])){ throw new Exception('order_time_need param is wrong'); }
        $this->time_need = post_clean($this->order_data['order_time_need'],100);        
        if(empty($this->time_need)) $this->time_need = $this->time_sent;

        $this->NEARTIME_MODE = $this->time_need===$this->time_sent;        

        if(!isset($this->order_data['order_total_price'])){ throw new Exception('order_total_price param is wrong'); }
        $this->total_price = (int) $this->order_data['order_total_price'];
        
        if(!isset($this->order_data['order_items'])){ throw new Exception('order_items param is wrong'); }
        if(!is_array($this->order_data['order_items'])){ throw new Exception('order_items param is wrong'); }
        $this->order_items = $this->order_data['order_items'];

        if(!isset($params['pickupself_mode'])){
            $this->PICKUPSELF_MODE = true;            
        }else{
            $this->PICKUPSELF_MODE = $params['pickupself_mode'];            
        }
        
        if(!isset($this->order_data['order_user_phone'])){ 
            $this->user_phone = "";
        }else{
            $this->user_phone = post_clean($this->order_data["order_user_phone"], 50);
            $this->user_phone = preg_replace("/[^+0-9 ()\-,.]/", "", $this->user_phone);
            if(empty($this->user_phone)) {throw new Exception('--need to know user phone');};
        }

        if(!$this->PICKUPSELF_MODE){
            $u_address = $this->order_data['order_user_full_address'];
            $u_address_entrance = isset($u_address['entrance'])? $u_address['entrance']: "";
            $u_address_floor = isset($u_address['floor']) ? isset($u_address['floor']) : "";
            if(empty($u_address['u_street'])) throw new Exception('--need to know user street');
            if(empty($u_address['u_house'])) throw new Exception("--need to know user house");		            
            
            $this->deliv_address = "ул. ".$u_address['u_street'].", д. ".$u_address['u_house'].",
            подъезд ({$u_address_entrance}),  этаж ({$u_address_floor})";
        }else{
            $this->deliv_address = "";
        }

        $this->user_comment = post_clean($this->order_data["order_user_comment"], 250);

        return true;
    }



}