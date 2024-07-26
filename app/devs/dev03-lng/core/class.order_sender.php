<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	UNIVERSAL ORDERS SENDER

	depended from: 
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
		$tg_users = self::get_relevant_tg_users($cafe_uniq_name, $order_target);	
		glog("total rel users=".$tg_users->found());
		return $tg_users->found();
	}

    /*
    	SEND MESSAGE TO ALL RELEVANT TG-USERS
    ----------------------------------------- **/		
	static public function send_message_to_relevant_users($cafe_uniq_name, $order_target, $message, $keyboard="" ){
		$tg_users = self::get_relevant_tg_users($cafe_uniq_name, $order_target);
		$results = [];
		if($tg_users && $tg_users->full()){
			foreach($tg_users->get() as $tg_user){				
				$res = self::send_message_to_tg_user($tg_user->tg_user_id, $message, $tg_user, $keyboard);
				$results[] = $res;
			}
		}
		return $results;
	}

    /*
    	SEND MESSAGE TO CAFE SUPERVISORS
    ------------------------------------- **/		
	static public function send_message_to_supervisors($cafe_uniq_name, $message, $keyboard="" ){		
		$supervisors = new Smart_collect("tg_users","WHERE cafe_uniq_name='{$cafe_uniq_name}' AND role='supervisor'");
		if($supervisors&&$supervisors->full()){
			$s = $supervisors->get(0);
			$res = self::send_message_to_tg_user($s->tg_user_id, $message, $s);
			return $res;
		}else{
			glog("супервайзер(ы) для кафе (".$cafe_uniq_name.") не обнаружены.");
			return false;
		}		
	}	

    /*
    	SEND MESSAGE TO TG-USER		
    ---------------------------- **/	
	static public function send_message_to_tg_user($tg_user_id, $message, $tg_user=false, $keyboard=""){
		global $CFG;
		$tg_token = $CFG->tg_cart_token;		
		$method = 'sendMessage';
		$send_data = [
			"text" => $message,
			"parse_mode" => "Markdown",
			"chat_id" => $tg_user_id,
			"disable_web_page_preview" => true
		];		
		if(!empty($keyboard)){
			$send_data["reply_markup"] = $keyboard;
		}

		$res = send_telegram($method, $send_data, $tg_token);
		
		if($tg_user){
			glog("Пользователю ".$tg_user->tg_user_id."(".$tg_user->name."/".$tg_user->nickname.") отправлено сообщение: ".$message);
		}else{
			glog("Пользователю {$tg_user_id} (роль неопределена) отправлено сообщение: ".$message);
		}	

		glog("tg response: (".__LINE__.") ".print_r($res, 1));

		return $res;

	}	
	
	static public function get_relevant_tg_users($cafe_uniq_name, $order_target){
		
		if(gettype($cafe_uniq_name)!=="string"){
			glogError("\$cafe_uniq_name=$cafe_uniq_name");
			throw new Exception("неправильный тип сообщения ".__LINE__." ".__FILE__);
		}

		$ACTIVE_ONLY=" AND state='active'";

		$COND = match ($order_target) {
      self::CHEFSMENU_ORDER => " AND role='manager'",
      self::IIKO_TABLE => " AND role='waiter'",
      self::IIKO_DELIVERY => " AND role='manager'",
      default => "",
  };		
		$q = "WHERE cafe_uniq_name='{$cafe_uniq_name}' ".$COND.$ACTIVE_ONLY;
		glog("getting rel users, ".$q);
		$tg_users = new Smart_collect("tg_users",$q);
		return $tg_users;
	}

    /*
    	SEND TG-ORDER TO ALL RELEVANT TG-USERS
    ------------------------------------------- **/	
	static public function send_tg_order($cafe_uniq_name, $order_target, $order_short_number, $tg_order_text, $keyboard=""){	 			
		$results = [];
		if(self::total_tg_users_for($cafe_uniq_name, $order_target)){						
			$results = self::send_message_to_relevant_users($cafe_uniq_name, $order_target, $tg_order_text, $keyboard);
			self::send_message_to_supervisors($cafe_uniq_name, $tg_order_text);
		}		
		return $results;
	}

    /*
    	SENDING TG-ORDER FOR CONFIRM TO ALL RELEVANT TG_USERS
    --------------------------------------------------------- **/
	static public function send_tg_order_for_confirm($cafe_uniq_name, $order_target, $order_short_number, $tg_order_text){		
	
		$waiter_mode = $order_target===self::IIKO_TABLE;
		$button_confirm_title = $waiter_mode?"Я возьму":"Подтвердить";
		$button_confirm = "confirm_order:{$cafe_uniq_name}:{$order_short_number}";
		
		glog('button_confirm='.$button_confirm);

		$keyboard = json_encode([
			"inline_keyboard" => [
				[
					[
						"text" => $button_confirm_title,
						"callback_data" => $button_confirm
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
		$url     = 'api/1/order/create';
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
    	SAVE ORDER TO DB
    ------------------------- **/	
	static public function save_order_to_db($order_target, $pending_mode, $cafe, $order_data, $table_number=0, $demo_mode=true){		

		$cafe_uniq_name = $cafe->uniq_name;
		$id_cafe = $cafe->id;

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
			$order->cafe_uniq_name = $cafe->uniq_name;		
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
    	CANCELING ORDER
    ------------------------- **/		
	static public function order_remove_from_db($cafe_uniq_name, $order, $tg_user): void{		
		
		// эта функциональность пока не используется.
	
		if(!$tg_user || !$tg_user->valid()){
			throw new Exception('Не указан менеджер.');
			return;
		}	
		
		$order_short_number = $order->short_number;

		$MANAGER_NAME = !empty($tg_user->nickname)?$tg_user->nickname:$tg_user->name;			

		if($order->state==='confirmed'){
			if($order->manager===$tg_user->id){
				$cancel_message = "О нет, ".$MANAGER_NAME."! Невозможно отменить. Заказ $order_short_number уже был подтвержден вами ранее.";
				self::send_message_to_tg_user($tg_user->tg_user_id, $cancel_message, $tg_user);
			}else{
				$manager_name = self::get_tg_user_name_by_id($order->manager);
				$cancel_message = "О нет, ".$MANAGER_NAME."! Невозможно отменить. Заказ $order_short_number уже был подтвержден. Его менеджер: {$manager_name}";
				self::send_message_to_tg_user($tg_user->tg_user_id, $cancel_message, $tg_user);					
			}
		}else if($order->state==='cancelled'){
			if($order->manager===$tg_user->id){
				$cancel_message = "О нет, ".$MANAGER_NAME."! Заказ $order_short_number уже был вами отменен ранее.";
				self::send_message_to_tg_user($tg_user->tg_user_id, $cancel_message, $tg_user);
			}else{
				$manager_name = self::get_tg_user_name_by_id($order->manager);
				$cancel_message = "О нет, ".$MANAGER_NAME."! Заказ $order_short_number уже был отменен ранее. Его менеджер: {$manager_name}";
				self::send_message_to_tg_user($tg_user->tg_user_id, $cancel_message, $tg_user);
			}
		}else{			
			$order->state = 'cancelled';
			$order->manager = $tg_user->id;
			$order->updated = 'now()';			
			if($order->save()){

				$ok_message = "Ок, ".$MANAGER_NAME." отменил заказ {$order_short_number}.";										
				self::send_message_to_relevant_users($cafe_uniq_name, $order->order_target, $ok_message);
				self::send_message_to_supervisors($cafe_uniq_name, $order->order_target, $ok_message);					

			}else{
				throw new Exception('Не удалось отменить заказ.');
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
			
		if($order->state==='confirmed'){
			if($order->manager===$tg_user->id){
				if($tg_user->role==="waiter"){
					$cancel_message = "О нет, ".$MANAGER_NAME."! Вы уже взяли (подтвердили) заказ {$order_short_number} и отправили в iiko.";
				}else{
					$cancel_message = "О нет, ".$MANAGER_NAME."! Заказ {$order_short_number} уже был вами подтвержден и отправлен в iiko.";
				}				
				self::send_message_to_tg_user($tg_user->tg_user_id, $cancel_message, $tg_user);
			}else{
				$manager_name = self::get_tg_user_name_by_id($order->manager);
				if($tg_user->role==="waiter"){
					$cancel_message = "О нет, ".$MANAGER_NAME."! Заказ {$order_short_number} уже взяли (подтвердили) и отправили в iiko. Официант: {$manager_name}";
				}else{
					$cancel_message = "О нет, ".$MANAGER_NAME."! Заказ {$order_short_number} уже был подтвержден и отправлен в iiko. Его менеджер: {$manager_name}";
				}				
				self::send_message_to_tg_user($tg_user->tg_user_id, $cancel_message, $tg_user);
			}
		}else if($order->state==='cancelled'){

			$cancel_message = "О нет, ".$MANAGER_NAME."! Заказ {$order_short_number} был ранее отменен.";
			self::send_message_to_tg_user($tg_user->tg_user_id, $cancel_message, $tg_user);
			
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

			$order->state = 'confirmed';			
			$order->manager = $tg_user->id;
			$order->updated = 'now()';

			if($order->save()){					
				
				$action = $tg_user->role==="waiter"?"взял": "подтвердил";

				$ok_message = "Ок, ".$MANAGER_NAME." {$action} заказ {$order_short_number}.";										
				self::send_message_to_relevant_users($cafe_uniq_name, $order->order_target, $ok_message);
				self::send_message_to_supervisors($cafe_uniq_name, $order->order_target, $ok_message);					

			}else{
				throw new Exception('Неизвестная ошибка.'.__LINE__);
			}
			
		}
				
	}

	static public function get_tg_user_name_by_id($id){
		$tg_user = new Smart_object("tg_users",$id);
		if($tg_user && $tg_user->valid()){
			return !empty($tg_user->nickname)?$tg_user->nickname:$tg_user->name;
		}else{
			return "Не определен";
		}
	}

}
		
?>