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
    ------------------------------------- **/		
	static public function get_cafe_supervisors(string $cafe_uniq_name, int|null $except_id_user=null ): array{		
		$COND = "WHERE cafe_uniq_name='{$cafe_uniq_name}' AND role='supervisor'";
		$EXCEPT_USER = $except_id_user!==null?" AND id!={$except_id_user}":"";
		$supervisors = new Smart_collect("tg_users",$COND.$EXCEPT_USER);
		if($supervisors&&$supervisors->full()){
			return $supervisors->get();
		}else{			
			return [];
		}		
	}

    /*
    	SEND MESSAGE TO TG-USERS		
    --------------------------------- **/	
	static public function send_message_to_tg_users(string|array $tg_user_ids, $message, $keyboard=""){
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

    /*
    	SEND TG-ORDER TO ALL TEAM TG-USERS
    ------------------------------------------- **/	
	static public function send_tg_order($cafe_uniq_name, $order_target, $order_short_number, $tg_order_text, $keyboard=""){	 					
		$results = self::send_message_to_team_users($cafe_uniq_name, $order_target, $tg_order_text, $keyboard);
		self::send_message_to_supervisors($cafe_uniq_name, $tg_order_text);
		return $results;
	}

    /*
    	SENDING TG-ORDER FOR CONFIRM TO ALL TEAM TG_USERS
    --------------------------------------------------------- **/
	static public function send_tg_order_for_confirm($cafe_uniq_name, $order_target, $order_short_number, $tg_order_text){		
	
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
		
		return self::send_tg_order($cafe_uniq_name, $order_target, $order_short_number, $tg_order_text, $keyboard);
	}

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
    ------------------------- **/	
	static public function save_order_to_db($order_target, $pending_mode, $cafe, $order_data, $table_number=0, $demo_mode=false){		

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

			// CREATING ORDER IN DB
			$order = new Smart_object("orders");
			$order->cafe_uniq_name = $cafe_uniq_name;		
			$order->order_target = $order_target;
			$order->table_number = $table_number;
			$order->pending_mode = $pending_mode;
			$order->short_number = $short_number;			
			$order->date = "now()";
			$order->updated = "now()";
			$order->description = json_encode($order_data, JSON_UNESCAPED_UNICODE);
			
			$just_created_id = $order->save();			

			if(!$just_created_id) {
				// cant saving order to db
				return false;
			}else{			
				$order_uniq_id = $just_created_id."-".$id_cafe."-".$short_number;
				$order->id_uniq = $order_uniq_id;			
				$order->save();
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
    	TAKING THE ORDER
    ------------------------- **/			
	static public function do_take_the_order(string $cafe_uniq_name, $order, Smart_object $TG_USER ):void{
		$order_short_number = $order->short_number;
		$USER_NAME = !empty($TG_USER->nickname)?$TG_USER->nickname:$TG_USER->name;		
		
		$orders = new Smart_collect("orders","WHERE cafe_uniq_name='{$cafe_uniq_name}' AND short_number='{$order_short_number}'");
		if($order->state==='taken'){
			if($order->manager===$TG_USER->id){
				$cancel_message = "О нет, {$USER_NAME}! Вы уже взяли заказ {$order_short_number}.";
				self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);				
			}else{
				$ORDER_MANAGER = self::get_tg_user_name_by_id($order->manager, true);
				$cancel_message = "О нет, {$USER_NAME}! Заказ {$order_short_number} уже взял {$ORDER_MANAGER}";
				self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);				
			}			
		}else{
			// --------------------
			//  TAKING THE ORDER
			// --------------------		
			$order->state = 'taken';			
			$order->manager = $TG_USER->id;
			$order->updated = 'now()';

			if($order->save()){													

				// send answer to user
				$personal_message = "Ок, ".$USER_NAME."! Вы взяли заказ {$order_short_number}.";				
				self::send_message_to_tg_users($TG_USER->tg_user_id, $personal_message);			
				
				// send message to team
				$NEW_ORDER_MANAGER = self::get_tg_user_name_by_id($TG_USER->id, true);
				$message = $NEW_ORDER_MANAGER." взял заказ {$order_short_number}.";
				$ARR_USERS = self::get_team_tg_users($cafe_uniq_name, $order_target, $TG_USER->id);					
				count($ARR_USERS) && self::send_message_to_tg_userss($ARR_USERS, $message);
				
				// send message to supervisors
				$su_message = $NEW_ORDER_MANAGER." взял заказ {$order_short_number}.";
				$ARR_SUPERVISORS = self::get_cafe_supervisors($cafe_uniq_name, $order_target, $TG_USER->id);
				count($ARR_SUPERVISORS) && self::send_message_to_tg_userss($ARR_SUPERVISORS, $message);

			}else{
				throw new Exception('Cant updating order status.'.__FILE__.", ".__LINE__);				
			}
		}		
	}

    /*
    	CONFIRMING ORDER
    ------------------------- **/		
	static public function order_confirm_and_send_to_iiko($cafe_uniq_name, $order, $tg_user ){
			
		$order_short_number = $order->short_number;

		$MANAGER_NAME = !empty($tg_user->nickname)?$tg_user->nickname:$tg_user->name;

		$ERROR_IIKO_SENDING_MESSAGE = "О нет, ".$MANAGER_NAME."! Ошибка отправки заказа {$order_short_number} в iiko.";
		$ERROR_MESSAGE = "О нет, ".$MANAGER_NAME."! Неизвестная ошибка. Заказ {$order_short_number} не может быть подтвержден.";	
		
		$orders = new Smart_collect("orders","WHERE cafe_uniq_name='{$cafe_uniq_name}' AND short_number='{$order_short_number}'");
			
		if($order->state==='taken'){
			if($order->manager===$tg_user->id){
				if($tg_user->role==="waiter"){
					$cancel_message = "О нет, ".$MANAGER_NAME."! Вы уже взяли (подтвердили) заказ {$order_short_number} и отправили в iiko.";
				}else{
					$cancel_message = "О нет, ".$MANAGER_NAME."! Заказ {$order_short_number} уже был вами подтвержден и отправлен в iiko.";
				}				
				self::send_message_to_tg_users($tg_user->tg_user_id, $cancel_message);
			}else{
				$manager_name = self::get_tg_user_name_by_id($order->manager);
				if($tg_user->role==="waiter"){
					$cancel_message = "О нет, ".$MANAGER_NAME."! Заказ {$order_short_number} уже взяли (подтвердили) и отправили в iiko. Официант: {$manager_name}";
				}else{
					$cancel_message = "О нет, ".$MANAGER_NAME."! Заказ {$order_short_number} уже был подтвержден и отправлен в iiko. Его менеджер: {$manager_name}";
				}				
				self::send_message_to_tg_users($tg_user->tg_user_id, $cancel_message);
			}
		}else if($order->state==='cancelled'){

			$cancel_message = "О нет, ".$MANAGER_NAME."! Заказ {$order_short_number} был ранее отменен.";
			self::send_message_to_tg_users($tg_user->tg_user_id, $cancel_message);
			
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
				self::send_message_to_team_users($cafe_uniq_name, $order->order_target, $ok_message);
				self::send_message_to_supervisors($cafe_uniq_name, $order->order_target, $ok_message);					

			}else{
				throw new Exception('Неизвестная ошибка.'.__LINE__);
			}
			
		}
				
	}


	static public function get_team_tg_users($cafe_uniq_name, $order_target, int|null $except_user_id=null){
		$ACTIVE_ONLY=" AND state='active'";
		$EXCEPT_USER = $except_user_id!==null? " AND id != $except_user_id" : "";
		$COND = match ($order_target) {
			self::CHEFSMENU_ORDER => " AND role='manager'",
			self::IIKO_TABLE => " AND role='waiter'",
			self::IIKO_DELIVERY => " AND role='manager'",
			default => "",
		};
		$q = "WHERE cafe_uniq_name='{$cafe_uniq_name}' ".$COND.$ACTIVE_ONLY.$EXCEPT_USER;		
		$tg_users = new Smart_collect("tg_users",$q);		
		return $tg_users->get();
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

}
		
?>