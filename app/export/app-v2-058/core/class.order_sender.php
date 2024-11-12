<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	ORDERS SENDER
	
	depended on: 
	common.php -> send_telegram()
	core/class.Smart_collect()
	core/class.smart_object()

**/

class Order_sender{	

	public const ORDER_TABLE = "table_order";
	public const ORDER_DELIVERY = "delivery_order";

    /*
    	GET COUNT OF RELEVANT TG-USERS
    ----------------------------------- **/			
	static public function total_tg_users_for($cafe_uniq_name, $order_target):int{	
		$ARR_TG_USERS = self::get_team_tg_users($cafe_uniq_name, $order_target);
		return count($ARR_TG_USERS);
	}

    /*
    	GET CAFE SUPERVISORS
		@return string[]
    ------------------------------------- **/		
	static public function get_cafe_supervisors(string $cafe_uniq_name, int|null $except_id_user=null ): array{		
		$COND = "WHERE cafe_uniq_name='{$cafe_uniq_name}' AND role='supervisor'";
		$EXCEPT_USER = $except_id_user!==null?" AND id!={$except_id_user}":"";
		$SUPERVISORS = new Smart_collect("tg_users",$COND.$EXCEPT_USER);
		if($SUPERVISORS&&$SUPERVISORS->full()){
			return self::extract_tg_user_ids($SUPERVISORS);
		}else{			
			return [];
		}		
	}

    /*
    	SAVE ORDER TO DB
		CREATE & RETURN SHORT NUMBER

		@param string $order_target // self::ORDER_DELIVERY | self::IIKO-TABLE | self::IIKO-DELIVERY 
		@param Smart_object $cafe
		@param array $order_data // order params
		@param int|null $table_number
		@param bool $demo_mode // !==2

		@return string $short_number
    ----------------------------------------------------- **/	
	static public function save_order_to_db(
		string $order_target,
		Smart_object $cafe, 
		array $order_data, 
		int|null $table_number=null, 
		bool $demo_mode=false): string{		

		$cafe_uniq_name = $cafe->uniq_name;
		$id_cafe = $cafe->id;

		$demo_mode_str = $demo_mode?"yes":"no";
		glog("Saving order to db, demo_mode: $demo_mode_str, $demo_mode");

		if(!$demo_mode){

			$q = "SELECT COUNT(*) AS total FROM orders WHERE cafe_uniq_name='{$cafe_uniq_name}' AND date >= CURDATE()";
			
			$counter = SQL::first($q);
			$count = (int) $counter["total"];
			$count++;			

			$pre = date("y").date("m").date("d");
			$num = sprintf("%03d", $count);
			$short_number = $pre."-".$num;			

			// SAVE ORDER TO DB
			$ORDER = new Smart_object("orders");
			$ORDER->cafe_uniq_name = $cafe_uniq_name;		
			$ORDER->order_target = $order_target;
			$ORDER->table_number = $table_number;
			$ORDER->short_number = $short_number;			
			$ORDER->date = "now()";
			$ORDER->updated = "now()";
			$ORDER->description = json_encode($order_data, JSON_UNESCAPED_UNICODE);
			
			$just_created_id = $ORDER->save();			

			if(!$just_created_id) {
				// cant saving order to db
				return false;
			}else{			
				$order_uniq_id = $just_created_id."-".$id_cafe."-".$short_number;
				$ORDER->id_uniq = $order_uniq_id;			
				$ORDER->save();
				return $short_number;
			}

		}else{

			$count = random_int(1,100); 
			$pre = date("y").substr(date("F"),0,1).date("d");
			$num = sprintf("%03d", $count);
			$short_number = $pre."-".$num."-DEMO";		
			return $short_number;
		}
	}

    /*
    	SEND TG-ORDER TO ALL 
		TEAM-TG-USERS AND SUPERVISORS

		@param string $cafe_uniq_name
		@param string $order_target // self::ORDER_DELIVERY | self::IIKO-TABLE | self::IIKO-DELIVERY 
		@param string $order_short_number
		@param string $tg_order_text				
    ---------------------------------------------------------------------------------- **/	
	static public function send_tg_order(
		$cafe_uniq_name, 
		$order_target, 
		$order_short_number, 
		$tg_order_text
		): void {
		
		$waiter_mode = $order_target===self::ORDER_TABLE;

		// $button_take_title = $waiter_mode?"Я беру":"Взять заказ";
		$button_take_title = "Взять заказ";
		$button_take_the_order = "take_the_order:{$cafe_uniq_name}:{$order_short_number}";
		$keyboard = json_encode([
			"inline_keyboard" => [
				[
					[
						"text" => $button_take_title,
						"callback_data" => $button_take_the_order
					]
				]
			]
		], JSON_UNESCAPED_UNICODE);	

		// send message to team
		$ARR_USERS_IDS = self::get_team_tg_users($cafe_uniq_name, $order_target);
		count($ARR_USERS_IDS) && self::send_message_to_tg_users($ARR_USERS_IDS, $tg_order_text, $keyboard);		
		
		// send message to supervisors
		$ARR_SUPERVISORS_IDS = self::get_cafe_supervisors($cafe_uniq_name); 		
		count($ARR_SUPERVISORS_IDS) && self::send_message_to_tg_users($ARR_SUPERVISORS_IDS, $tg_order_text);

		if(!$ARR_USERS_IDS || !count($ARR_USERS_IDS) ){
			// сообщить администратору (если есть администратор), 
			// что заказ отправлен с сайте, но некому получать –
			// нет активных менеджеров или официантов.			
		}		
	}

    /*
    	TAKING THE ORDER

		@param string $cafe_uniq_name
		@param Smart_object $ORDER
		@param Smart_object $TG_USER				
    --------------------------------------- **/			
	static public function do_take_the_order(string $cafe_uniq_name, Smart_object $ORDER, Smart_object $TG_USER ):void{
		if($TG_USER->role !== 'waiter' && $TG_USER->role !== 'manager'){
			$cancel_message = "Вы не можете взять заказ. Для этого нужно иметь доступ Менеджера или Официанта";
			self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);							
			return;			
		}
		if($TG_USER->state !== 'active'){
			$cancel_message = "Вы не можете взять заказ. Сначала откройте смену.";
			self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);							
			return;
		}

		$order_short_number = $ORDER->short_number;
		$USER_NAME = !empty($TG_USER->nickname)?$TG_USER->nickname:$TG_USER->name;		
		
		$orders = new Smart_collect("orders","WHERE cafe_uniq_name='{$cafe_uniq_name}' AND short_number='{$order_short_number}'");

		if($ORDER->state!=='created'){
			if($ORDER->manager===$TG_USER->id){
				$cancel_message = "О нет, {$USER_NAME}! Вы уже взяли заказ {$order_short_number}.";
				self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);				
			}else{
				$ORDER_MANAGER = self::get_tg_user_name_by_id($ORDER->manager, true);
				$cancel_message = "О нет, {$USER_NAME}! Заказ {$order_short_number} уже взял {$ORDER_MANAGER}";
				self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);				
			}			
		}else{
			// --------------------
			//  TAKING THE ORDER
			// --------------------		
			$ORDER->state = 'taken';			
			$ORDER->manager = $TG_USER->id;
			$ORDER->updated = 'now()';
						
			if($ORDER->save()){													
			
				$keyboard = "";

				// send answer to user
				$personal_message = "Ок, ".$USER_NAME."! Вы взяли заказ {$order_short_number}.";				
				self::send_message_to_tg_users($TG_USER->tg_user_id, $personal_message, $keyboard);
				
				// send message to team (except current user)
				$NEW_ORDER_MANAGER = self::get_tg_user_name_by_id($TG_USER->id, true);
				$message = $NEW_ORDER_MANAGER." взял заказ {$order_short_number}.";
				$ARR_USERS_IDS = self::get_team_tg_users($cafe_uniq_name, $ORDER->order_target, $TG_USER->id);					
				count($ARR_USERS_IDS) && self::send_message_to_tg_users($ARR_USERS_IDS, $message);
				
				// send message to supervisors (except current user)
				$su_message = $NEW_ORDER_MANAGER." взял заказ {$order_short_number}.";
				$ARR_SUPERVISORS_IDS = self::get_cafe_supervisors($cafe_uniq_name, $TG_USER->id);
				count($ARR_SUPERVISORS_IDS) && self::send_message_to_tg_users($ARR_SUPERVISORS_IDS, $message);

			}else{
				throw new Exception('Cant updating order status.'.__FILE__.", ".__LINE__);				
			}
		}		
	}

    /*
    	GET TEAM (the relevant MANAGERS or WAITERS for the order)

		@param string $cafe_uniq_name
		@param string $order_target // self::ORDER_DELIVERY|self::ORDER_TABLE|self::ORDER_DELIVERY
		@param int|null $except_user_id

		@return array // string[]
    ------------------------------------------------------------------------------- **/			
	static public function get_team_tg_users($cafe_uniq_name, $order_target, int|null $except_user_id=null): array{
		$ACTIVE_ONLY=" AND state='active'";
		$EXCEPT_USER = $except_user_id!==null? " AND id != $except_user_id" : "";
		$COND = match ($order_target) {
			self::ORDER_DELIVERY => " AND role='manager'",
			self::ORDER_TABLE => " AND role='waiter'",
			self::ORDER_DELIVERY => " AND role='manager'",
			default => "",
		};
		$q = "WHERE cafe_uniq_name='{$cafe_uniq_name}' ".$COND.$ACTIVE_ONLY.$EXCEPT_USER;		
		$TG_USERS = new Smart_collect("tg_users",$q);		
		return self::extract_tg_user_ids($TG_USERS);
	}

    /*
    	SEND MESSAGE TO TG-USERS	 	

		@param string|string[] $tg_user_ids
	 	@param string $message
	 	@param string|undefined $keyboard		
    --------------------------------------------------- **/		
	static public function send_message_to_tg_users(string|array $tg_user_ids, $message, $keyboard=""): void{
		global $CFG;
		$tg_token = $CFG->tg_cart_token;
		if(gettype($tg_user_ids)==='string'){ $tg_user_ids = [$tg_user_ids]; }		
		foreach( $tg_user_ids as $tg_user_id){
			$method = 'sendMessage';
			$send_data = [
				"text" => $message,
				"parse_mode" => "Markdown",
				"chat_id" => $tg_user_id,
				"disable_web_page_preview" => true
			];
			if(!empty($keyboard)){ $send_data["reply_markup"] = $keyboard; }
			$res = send_telegram($method, $send_data, $tg_token);			
			glog("tg response: ".print_r($res, 1).__FILE__.", ".__LINE__);
		}			
	}	
	
	static public function get_order_by_params(string $cafe_uniq_name, string $order_short_number):Smart_object|null{
		$query = "WHERE cafe_uniq_name='{$cafe_uniq_name}' AND short_number='{$order_short_number}'";
		$ORDERS = new Smart_collect("orders", $query);			
		if(!$ORDERS || !$ORDERS->full()){
			return null;	
		}else{
			return $ORDERS->get(0);
		}
	}		

	static public function get_cafe_by_uniq_name(string $cafe_uniq_name):Smart_object|null{
		$CAFES = new Smart_collect("cafe","WHERE uniq_name='{$cafe_uniq_name}'");
		if(!$CAFES || !$CAFES->full()){
			return null;	
		}else{
			return $CAFES->get(0);
		}
	}	

	static public function extract_tg_user_ids(Smart_collect $TG_USERS): array{
		if($TG_USERS && $TG_USERS->full()){
			$ARR = []; 
			foreach($TG_USERS->get() as $TG_USER){
				array_push($ARR, $TG_USER->tg_user_id);
			}
			return $ARR;
		}else{
			return [];
		}
	}

    /*
    	GET TG-USER NAME	 	

		// return NICKNAME if exist
		// otherwise return NAME 
		// if $full is true, return PUBLIC NAME

		@param int $id
		@param bool $full
		
		@return string 
    ------------------------------------------------------------------------- **/		
	static public function get_tg_user_name_by_id(int $id, $full=false ):string {
		$TG_USER = new Smart_object("tg_users", $id);
		if($TG_USER && $TG_USER->valid()){
			if($full){
				return self::get_tg_user_public_name($TG_USER);
			}else{
				return !empty($TG_USER->nickname) ? $TG_USER->nickname:$TG_USER->name;
			}			
		}else{
			return "Неизвестный";
		}
	}
		
    static public function get_tg_user_public_name(Smart_object $TG_USER):string{
		if(empty($TG_USER->name)) return "Неизвестный";
        $public_name = !empty($TG_USER->nickname)?$TG_USER->nickname." (".$TG_USER->name.")":$TG_USER->name;
        return $public_name;
    }	


}
		
?>