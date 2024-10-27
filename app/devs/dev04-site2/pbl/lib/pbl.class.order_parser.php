<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Order_parser{
    
    protected array $order_data;
    protected array $order_items;
    protected string $time_sent;    
    protected string $time_need;  
    protected string $user_phone; 
    protected string $deliv_address;
    protected int $total_price;           
    protected bool $NEARTIME_MODE;   
    protected bool $PICKUPSELF_MODE;    

    public function __construct($params){
        $this->verify($params);
        return $this;
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

        return true;
    }



}