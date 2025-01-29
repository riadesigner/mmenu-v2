<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	ORDERS CHECKING
	
	checking if some orders not taken.
	if true, then send reminder to waiters (or managers)
	if time is over, then mark the order as forgotten
	and send message about this

	depended on: 
	common.php -> send_telegram()
	class Smart_collect()
	class Smart_object()	
	class Order_sender()
	
**/

class Order_checker{	

	// max time (minutes) for take an order
	// after that its need to sending a reminder to TG
	public static $ORDER_WAITING_DELAY = 5; 

	// end time (minutes) for take an order
	// after that its need to mark the order as forgotten
	public static $ORDER_FORGOTTEN_DELAY = 10;	
  
	// ---------------------- PART I ---------------------- 
	// находим все заказы, созданные более N+ минут назад
	// никем не взятые (не подтвержлденные), 
	// и отмечаем их брошенными (forgotten)

	static public function init(int $reminder_delay, int $forgotten_delay): void{
		self::$ORDER_WAITING_DELAY = $reminder_delay;
		self::$ORDER_FORGOTTEN_DELAY = $forgotten_delay;
		glog('vars: $ORDER_WAITING_DELAY, $ORDER_FORGOTTEN_DELAY = '.self::$ORDER_WAITING_DELAY.", ".self::$ORDER_FORGOTTEN_DELAY);
	}

	static public function find_forgotten(string $order_target): void{

		$time = date('Y-m-d H:i:s');

		$sql = implode("",[
			" WHERE date < NOW() - INTERVAL ".self::$ORDER_FORGOTTEN_DELAY." MINUTE",
			" AND state = 'created'",
			" AND order_target = '".$order_target."'",
		]);

		$cond = "ORDER BY 'cafe_uniq_name'";
		$forgotten_orders = new Smart_collect("orders", $sql, $cond);
		if($forgotten_orders && $forgotten_orders->full()){
			glog("$time forgotten orders found: ".$forgotten_orders->found());
		
			$ORDERS_BY_CAFE = self::sort_orders_by_cafe($forgotten_orders->get());
		
			foreach($ORDERS_BY_CAFE as $cafe_uniq_name => $orders){
				foreach($orders as $order){
					$order->state = 'forgotten';
					$order->save();
				}        
				$MSG = self::send_message("FORGOTTEN_ORDERS", $cafe_uniq_name, $order_target, $orders);         
				glog("-> TG:".$MSG);
			}
			
		}		
	}

	// ---------------------- PART II ----------------------
	// находим все заказы, создагнные более N минут назад,
	// которые никто не взял в работу (и не подтвердил),
	// и рассылаем напоминание об этих заказах

	static public function find_not_taken(string $order_target):void{

		$time = date('Y-m-d H:i:s');

		$sql = implode("",[
			" WHERE date < NOW() - INTERVAL ".self::$ORDER_WAITING_DELAY." MINUTE",
			" AND state = 'created'",
			" AND order_target = '".$order_target."'",
		]);
		$cond = "ORDER BY 'cafe_uniq_name'";
		$not_taken_orders = new Smart_collect("orders", $sql,  $cond);

		if($not_taken_orders && $not_taken_orders->full()){
			glog("$time not taken orders found: ".$not_taken_orders->found());
			
			$ORDERS_BY_CAFE = self::sort_orders_by_cafe($not_taken_orders->get());
			
			foreach($ORDERS_BY_CAFE as $cafe_uniq_name => $orders){                        
				$MSG = self::send_message("NOT_TAKEN_ORDERS", $cafe_uniq_name, $order_target, $orders);         
				glog("-> TG:".$MSG);
			}

		}		
	}

	/**
	 * @param $orders // Array<Smart_object>
	 * @return array // [ "123rf" => Array<Smart_object>, "456ab" => Array<Smart_object>, ... ];
	 */
	static private function sort_orders_by_cafe(array $orders): array{
		$ORDERS_BY_CAFE = [];
		foreach($orders as $order){
			$cafe_uniq_name = $order->cafe_uniq_name;
			if(!isset($ORDERS_BY_CAFE[$cafe_uniq_name]))$ORDERS_BY_CAFE[$cafe_uniq_name] = [];
			$ORDERS_BY_CAFE[$cafe_uniq_name] = [...$ORDERS_BY_CAFE[$cafe_uniq_name], $order];
		}
		return $ORDERS_BY_CAFE;
	}
		
	/**
	 * sending MESSAGE ABOUT FORGOTTEN ORDERS 
	 * or REMINDER ABOUT NOT-TAKEN ORDERS 
	 * to all relevant tg users
	 */
	static public function send_message(string $subject, string $cafe_uniq_name, string $order_target, array $orders): string{

		$str_short_names = self::get_orders_formated_short_names($orders);

		if($subject==="FORGOTTEN_ORDERS"){
			if(count($orders) > 1){
				$msg = "Внимание! Т.к заказы ($str_short_names), и их никто не взял";
				$msg .= " – они отправлены в архив. \n";
			}else{
				$msg = "Внимание! Т.к заказ ($str_short_names), и его никто не взял";
				$msg .= " – он отправлен в архив. \n";
			}
		}else{
			// NOT TAKEN ORDERS
			if(count($orders) > 1){				
				$msg = "Внимание! Эти заказы ждут подтверждения! $str_short_names. \n";
			}else{
				$msg = "Внимание! Этот заказ ждет подтверждения! $str_short_names. \n";
			}			
		}
		Order_sender::send_to_all_relevant_users($cafe_uniq_name, $order_target, $msg);
		return $msg;
	}

   /*
    	BUILD A STRING FROM ORDERS ARRAY
		
		@param array $orders // Array<Smart_object>
		@return string
    ---------------------------------------------------------------------- **/	
	static private function get_orders_formated_short_names(array $orders): string{

		$str_all_short_names = "";
	
		if(count($orders)){
			$short_names = [];
			foreach($orders as $order){
				
				$currentDatetime = new DateTime();
				$orderDatetime = new DateTime($order->date);
				$interval = $orderDatetime->diff($currentDatetime);
				$minutesPassed = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
				$str_waiting = ": ждет $minutesPassed " . getMinutesWord($minutesPassed);

				$short_names = [...$short_names, "\n".$order->short_number.$str_waiting];
			}
			$str_all_short_names = implode(", ", $short_names);
		}
	
		return $str_all_short_names;
	
	}	

}
		
?>