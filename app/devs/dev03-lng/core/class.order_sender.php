<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	ORDERS SENDER
	
	depended on: 
	common.php -> send_telegram()
	common.php -> iiko_get_info()
	core/class.Smart_collect()
	core/class.smart_object()

**/

class Order_sender{	

	public const CHEFSMENU_ORDER = "chefsmenu_order";
	public const IIKO_TABLE = "iiko_table_order";
	public const IIKO_DELIVERY = "iiko_delivery_order";

    /*
    	GET COUNT OF RELEVANT TG-USERS
    ----------------------------------- **/			
	static public function total_tg_users_for($cafe_uniq_name, $order_target){	
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
    	SENDING TG-ORDER FOR CONFIRM TO ALL TEAM TG_USERS
    --------------------------------------------------------- **/
	// static public function send_tg_order_for_confirm($cafe_uniq_name, $order_target, $order_short_number, $tg_order_text){		
	
	// 	$waiter_mode = $order_target===self::IIKO_TABLE;
	// 	$button_take_title = $waiter_mode?"Я беру":"Взять заказ";
	// 	$button_take_the_order = "take_the_order:{$cafe_uniq_name}:{$order_short_number}";

	// 	$keyboard = json_encode([
	// 		"inline_keyboard" => [
	// 			[
	// 				[
	// 					"text" => $button_take_title,
	// 					"callback_data" => $button_take_the_order
	// 				]
	// 			]
	// 		]
	// 	], JSON_UNESCAPED_UNICODE);	
		
	// 	self::send_tg_order($cafe_uniq_name, $order_target, $order_short_number, $tg_order_text, $keyboard);
	// }

    /*
    	SEND IIKO-ORDER FOR DELIVERY
    ----------------------------------- **/
	static public function send_iiko_order_for_delivery($token, $organization_id, $terminal_group_id, $order){	
		$url     = 'api/1/deliveries/create';
		$headers = [
		    "Content-Type"=>"application/json",
		    "Authorization" => 'Bearer '.$token
		]; 	 	
		$params  = ['organizationId' => $organization_id, 'terminalGroupId' => $terminal_group_id, 'order' => $order]; 	

		glog("order = ".print_r($order,1));

		$res = iiko_get_info($url,$headers,$params);
		return $res;
	}

    /*
    	SEND IIKO-ORDER TO TABLE
    -------------------------------- **/
	static public function send_iiko_order_to_table($token, $organization_id, $terminal_group_id, $order){	

		glog("send iiko order to table, args: ".print_r([
			'token'=>$token, 
			'organization_id'=>$organization_id, 
			'terminal_group_id'=>$terminal_group_id, 
			'order'=>$order
		],1));

		$url     = 'api/1/order/create';
		$headers = [
		    "Content-Type"=>"application/json",
		    "Authorization" => 'Bearer '.$token
		]; 	 	
		$params  = ['organizationId' => $organization_id, 'terminalGroupId' => $terminal_group_id, 'order' => $order]; 			

		$res = iiko_get_info($url,$headers,$params);		
		glog("iiko answer: ".print_r($res,1));

		return $res;
	}

    /*
    	SAVE ORDER TO DB
		CREATE & RETURN SHORT NUMBER

		@param string $order_target // self::CHEFSMENU_ORDER | self::IIKO-TABLE | self::IIKO-DELIVERY 
		@param int $pending_mode // 0|1, IF NEEDS RESENT TO IIKO AFTER TAKING THE ORDER
		@param Smart_object $cafe
		@param array $order_data // order params
		@param int|null $table_number
		@param bool $demo_mode // !==2

		@return string $short_number
    ----------------------------------------------------- **/	
	static public function save_order_to_db(
		string $order_target, 
		int $pending_mode, 
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

			$pre = date("y").substr(date("F"),0,1).date("d");
			$num = sprintf("%03d", $count);
			$short_number = $pre."-".$num;		
			
			$order_data['externalNumber'] = $short_number;

			// SAVE ORDER TO DB
			$ORDER = new Smart_object("orders");
			$ORDER->cafe_uniq_name = $cafe_uniq_name;		
			$ORDER->order_target = $order_target;
			$ORDER->table_number = $table_number;
			$ORDER->pending_mode = $pending_mode;
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
		@param string $order_target // self::CHEFSMENU_ORDER | self::IIKO-TABLE | self::IIKO-DELIVERY 
		@param string $order_short_number
		@param string $tg_order_text		
		@param int $pending_mode // 0|1, IF NEEDS RESENT TO IIKO AFTER TAKING THE ORDER
    ---------------------------------------------------------------------------------- **/	
	static public function send_tg_order($cafe_uniq_name, $order_target, $order_short_number, $tg_order_text, int $pending_mode=0): void {	 		
		
		$waiter_mode = $order_target===self::IIKO_TABLE;

		$button_take_title = $waiter_mode?"Я беру":"Взять заказ";
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

		define('PENDING_MODE', (int) $ORDER->pending_mode===1);

		$order_short_number = $ORDER->short_number;
		$USER_NAME = !empty($TG_USER->nickname)?$TG_USER->nickname:$TG_USER->name;		
		
		$orders = new Smart_collect("orders","WHERE cafe_uniq_name='{$cafe_uniq_name}' AND short_number='{$order_short_number}'");

		if($ORDER->state==='taken'){
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

				if(PENDING_MODE){
					$button_send_to_iiko_title = "Отправить в iiko";
					$button_send_to_iiko = "send_to_iiko:{$cafe_uniq_name}:{$order_short_number}";
					$keyboard = json_encode([
						"inline_keyboard" => [
							[
								[
									"text" => $button_send_to_iiko_title,
									"callback_data" => $button_send_to_iiko
								]
							]
						]
					], JSON_UNESCAPED_UNICODE);						
				}

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
    	SEND ORDER TO IIKO
		(FOR PENDING MODE)

		@param string $cafe_uniq_name
		@param string $order_short_number
		@param Smart_object $TG_USER
    ----------------------------------- **/		
	static public function send_order_to_iiko($cafe_uniq_name, $order_short_number, Smart_object $TG_USER ):void {

		glog("----------- TEST send_order_to_iiko ----------- ".$cafe_uniq_name.", ".$order_short_number);

	}
	
	static public function send_order_to_iiko_old($cafe_uniq_name, $order_short_number, Smart_object $TG_USER ) {
			
		// $order_short_number = $order->short_number;

		$MANAGER_NAME = !empty($TG_USER->nickname)?$TG_USER->nickname:$TG_USER->name;

		$ERROR_IIKO_SENDING_MESSAGE = "О нет, ".$MANAGER_NAME."! Ошибка отправки заказа {$order_short_number} в iiko.";
		$ERROR_MESSAGE = "О нет, ".$MANAGER_NAME."! Неизвестная ошибка. Заказ {$order_short_number} не может быть подтвержден.";	
		
		$orders = new Smart_collect("orders","WHERE cafe_uniq_name='{$cafe_uniq_name}' AND short_number='{$order_short_number}'");
			
		if($order->state==='taken'){
			if($order->manager===$TG_USER->id){
				if($TG_USER->role==="waiter"){
					$cancel_message = "О нет, ".$MANAGER_NAME."! Вы уже взяли (подтвердили) заказ {$order_short_number} и отправили в iiko.";
				}else{
					$cancel_message = "О нет, ".$MANAGER_NAME."! Заказ {$order_short_number} уже был вами подтвержден и отправлен в iiko.";
				}				
				self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);
			}else{
				$manager_name = self::get_tg_user_name_by_id($order->manager);
				if($TG_USER->role==="waiter"){
					$cancel_message = "О нет, ".$MANAGER_NAME."! Заказ {$order_short_number} уже взяли (подтвердили) и отправили в iiko. Официант: {$manager_name}";
				}else{
					$cancel_message = "О нет, ".$MANAGER_NAME."! Заказ {$order_short_number} уже был подтвержден и отправлен в iiko. Его менеджер: {$manager_name}";
				}				
				self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);
			}
		}else if($order->state==='cancelled'){

			$cancel_message = "О нет, ".$MANAGER_NAME."! Заказ {$order_short_number} был ранее отменен.";
			self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);
			
		}else{

			// -----------------------
			//  CONFIRMING THE ORDER
			// -----------------------
			
			$cafes = new Smart_collect("cafe","WHERE uniq_name='{$cafe_uniq_name}'");
			if(!$cafes || !$cafes->full()){
				throw new Exception('не найдено кафе '.$cafe_uniq_name." (".__LINE__.")");
			}else{
				$cafe = $cafes->get(0);
			}

			$k = $cafe->iiko_api_key;
			
			if(empty($k)){
				throw new Exception('не найден iiko_api_key для кафе '.$cafe_uniq_name." (".__LINE__.")");				
			}

			// GETTING TOKEN FROM IIKO 
			$url     = 'api/1/access_token';
			$headers = ["Content-Type"=>"application/json"];
			$params  = ["apiLogin" => $k];
			$res = iiko_get_info($url,$headers,$params);
			if(!isset($res["token"]) || empty($res["token"])) return $ERROR_MESSAGE."(".__LINE__.")";
			$token = $res["token"];

			$orgs = $cafe->iiko_organizations;
			$orgs = !empty($orgs)?json_decode((string) $orgs,true):false;
			if(!$orgs) return $ERROR_MESSAGE."(".__LINE__.")";
			$organization_id = $orgs['current_organization_id'];

			$terminal_groups = $cafe->iiko_terminal_groups;
			$terminal_groups = !empty($terminal_groups)?json_decode((string) $terminal_groups,true):false;
			if(!$terminal_groups) return $ERROR_MESSAGE."(".__LINE__.")";
			$terminal_group_id = $terminal_groups['current_terminal_group_id'];		
			
			$comment_addition = $tg_user->role === "waiter"? "Официант: ".$MANAGER_NAME : "Менеджер: ".$MANAGER_NAME;
			$comment = "Подтвержден через телеграм. ".$comment_addition;

			$order_data = json_decode((string) $order->description,1); 
			$order_data["comment"] = $comment;

			// ---------------------------------------------
			//      SEND PENDING IIKO-ORDER TO DELIVERY
			// ---------------------------------------------							
			if($order->order_target===self::IIKO_DELIVERY){
				$result = self::send_iiko_order_for_delivery($token, $organization_id, $terminal_group_id, $order_data);

			// ---------------------------------------------
			//      SEND PENDING IIKO-ORDER TO TABLE
			// ---------------------------------------------					
			}else if($order->order_target===self::IIKO_TABLE){
				$result = self::send_iiko_order_to_table($token, $organization_id, $terminal_group_id, $order_data);
			
			}else{
				throw new Exception('Неизвестная ошибка.'.__LINE__);
				return;
			}

			if(isset($result['error']) && !empty($result['error']) ){						 
				glogError("Order_sender answer (".__LINE__."): ".print_r($result, 1));						
				throw new Exception('Неизвестная ошибка.'.__LINE__);
				return;
			}

			$order->state = 'taken';			
			$order->manager = $tg_user->id;
			$order->updated = 'now()';

			if($order->save()){					
				
				$action = $tg_user->role==="waiter"?"взял": "подтвердил";

				$ok_message = "Ок, ".$MANAGER_NAME." {$action} заказ {$order_short_number}.";				
				// xxx						
				self::send_message_to_team_users($cafe_uniq_name, $order->order_target, $ok_message);
				self::send_message_to_supervisors($cafe_uniq_name, $order->order_target, $ok_message);					

			}else{
				throw new Exception('Неизвестная ошибка.'.__LINE__);
			}
			
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
    	GET TEAM (the relevant MANAGERS or WAITERS for the order)

		@param string $cafe_uniq_name
		@param string $order_target // self::CHEFSMENU_ORDER|self::IIKO_TABLE|self::IIKO_DELIVERY
		@param int|null $except_user_id

		@return array // string[]
    ------------------------------------------------------------------------------- **/			
	static public function get_team_tg_users($cafe_uniq_name, $order_target, int|null $except_user_id=null): array{
		$ACTIVE_ONLY=" AND state='active'";
		$EXCEPT_USER = $except_user_id!==null? " AND id != $except_user_id" : "";
		$COND = match ($order_target) {
			self::CHEFSMENU_ORDER => " AND role='manager'",
			self::IIKO_TABLE => " AND role='waiter'",
			self::IIKO_DELIVERY => " AND role='manager'",
			default => "",
		};
		$q = "WHERE cafe_uniq_name='{$cafe_uniq_name}' ".$COND.$ACTIVE_ONLY.$EXCEPT_USER;		
		$TG_USERS = new Smart_collect("tg_users",$q);		
		return self::extract_tg_user_ids($TG_USERS);
	}

	static public function get_tg_user_name_by_id(int $id, $full=false){
		$TG_USER = new Smart_object("tg_users", $id);
		if($TG_USER && $TG_USER->valid()){
			if($full){
				return !empty($TG_USER->nickname) ? $TG_USER->nickname."(".$TG_USER->name.")" : $TG_USER->name;
			}else{
				return !empty($TG_USER->nickname) ? $TG_USER->nickname:$TG_USER->name;
			}			
		}else{
			return "Не определен";
		}
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


}
		
?>