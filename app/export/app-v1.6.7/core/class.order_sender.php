<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	ORDERS SENDER
	
	depended on: 
	common.php -> send_telegram()
	class Smart_collect()
	class Smart_object()

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
    	SEND IIKO-ORDER FOR DELIVERY
    ----------------------------------- **/
	static public function send_iiko_order_for_delivery($token, $organization_id, $terminal_group_id, $order): array{	
		$url     = 'api/1/deliveries/create';
		$headers = [
		    "Content-Type"=>"application/json",
		    "Authorization" => 'Bearer '.$token
		]; 	 	
		$params  = ['organizationId' => $organization_id, 'terminalGroupId' => $terminal_group_id, 'order' => $order]; 	

		// glog("order = ".print_r($order,1));

		$res = iiko_get_info($url,$headers,$params);
		return $res;
	}

    /*
    	SEND IIKO-ORDER TO TABLE
    -------------------------------- **/
	static public function send_iiko_order_to_table($token, $organization_id, $terminal_group_id, $order): array{	

		$url     = 'api/1/order/create';
		$headers = [
		    "Content-Type"=>"application/json",
		    "Authorization" => 'Bearer '.$token
		]; 	 	
		$params  = [
			'organizationId' => $organization_id, 
			'terminalGroupId' => $terminal_group_id, 
			'order' => $order
		];

		$res = iiko_get_info($url, $headers, $params);		
		glog("iiko answer: ".print_r($res,1));
		return $res;
	}

	/*
		GET SHORT NUMBER FROM ORDER_ID_UNIQ
	------------------------------------- **/
	static public function get_short_number(string $order_id_uniq): string{
		glog('------------- SENDING THE ORDER / order_id_uniq ------------- '.$order_id_uniq);
		$n = explode("-", $order_id_uniq);
		if(!isset($n[3]) || str_contains($order_id_uniq, "DEMO")){
			return "DEMO-001";
		}else{
			$order_id_uniq = $n[2]."-".$n[3];
			return $order_id_uniq;
		}
	}

    /*
    	SAVE ORDER TO DB
		CREATE & RETURN SHORT NUMBER

		@param string $order_target // self::ORDER_DELIVERY | self::IIKO-TABLE | self::IIKO-DELIVERY 
		@param Smart_object $cafe
		@param array $order_data // order params
		@param int|null $table_number
		@param int $pending_mode=0 // 0|1
		@param bool $demo_mode // !==2

		@return string $order_id_uniq
    ----------------------------------------------------- **/	
	static public function save_order_to_db(
		string $order_target,
		Smart_object $cafe, 
		array $order_data, 
		int|null $table_number=null, 
		int $pending_mode=0,
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
			$ORDER->pending_mode = $pending_mode;
			$ORDER->date = "now()";
			$ORDER->updated = "now()";			
			$ORDER->description = json_encode($order_data, JSON_UNESCAPED_UNICODE);
			$ORDER->total_price = $order_data["TOTAL_PRICE"];
			$just_created_id = $ORDER->save();			

			if(!$just_created_id) {
				// cant saving order to db
				return false;
			}else{			
				$order_id_uniq = $just_created_id."-".$id_cafe."-".$short_number;
				$ORDER->id_uniq = $order_id_uniq;			
				$ORDER->save();

				return $order_id_uniq;
			}

		}else{

			$count = random_int(1,100); 
			$pre = date("y").substr(date("F"),0,1).date("d");
			$num = sprintf("%03d", $count);
			$order_id_uniq = $pre."-".$num."-DEMO";		
			return  $order_id_uniq;
		}
	}

    /*
    	SEND TG-ORDER TO ALL 
		TEAM-TG-USERS AND SUPERVISORS

		@param string $cafe_uniq_name
		@param string $order_target // self::ORDER_DELIVERY | self::IIKO-TABLE | self::IIKO-DELIVERY 
		@param string $order_id_uniq
		@param string $tg_order_text				
    ---------------------------------------------------------------------------------- **/	
	static public function send_tg_order(
		$cafe_uniq_name, 
		$order_target, 
		$order_id_uniq, 
		$tg_order_text
		): void {
		
		$waiter_mode = $order_target===self::ORDER_TABLE;

		// $button_take_title = $waiter_mode?"Я беру":"Взять заказ";
		$button_take_title = "Взять заказ";
		$button_take_the_order = "take_the_order:{$cafe_uniq_name}:{$order_id_uniq}";
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
			// что заказ отправлен с сайта, но некому получать –
			// нет активных менеджеров или официантов.			
		}		
	}


	static public function send_to_all_relevant_users(string $cafe_uniq_name, string $order_target, $msg): void{
		$team_users = self::get_team_tg_users($cafe_uniq_name, $order_target);		
		$supervisors = self::get_cafe_supervisors($cafe_uniq_name);
		$all_tg_users = [...$team_users, ...$supervisors];
		glog("try to send tg message, ".__FILE__.", ".__LINE__);
		self::send_message_to_tg_users($all_tg_users, $msg);		
	}

    /*
    	TAKING THE ORDER

		@param string $cafe_uniq_name
		@param Smart_object $ORDER
		@param Smart_object $TG_USER				
    --------------------------------------------------------------- **/			
	static public function do_take_the_order(string $cafe_uniq_name, Smart_object $ORDER, Smart_object $TG_USER ):void{

		// FOR ORDERS WITCH NOT IN ARCHIVE ONLY
		if($ORDER->state==='forgotten'){										
			$cancel_message = "Заказ ".$ORDER->short_number." невозможно взять, он в архиве!";
			self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);							
			return;
		}

		// ALLOW FOR WAITERS AND MANAGERS ONLY
		if($TG_USER->role !== 'waiter' 
			&& $TG_USER->role !== 'manager'){
			$cancel_message = "Вы не можете взять заказ. Для этого нужно иметь доступ Менеджера или Официанта";
			self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);							
			return;			
		}
		// ALLOW FOR ACTIVE USERS ONLY
		if($TG_USER->state !== 'active'){
			$cancel_message = "Вы не можете взять заказ. Сначала откройте смену.";
			self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);							
			return;
		}

		define('PENDING_MODE', (int) $ORDER->pending_mode===1);
		
		$USER_NAME = !empty($TG_USER->nickname)?$TG_USER->nickname:$TG_USER->name;		
		
		$order_short_number = $ORDER->short_number; 
		$order_id_uniq = $ORDER->id_uniq;
		$orders = new Smart_collect("orders", "WHERE id_uniq='{$order_id_uniq}'");

		// ---------------------------
		// IF ORDERS IS TAKEN ALREADY
		// ---------------------------
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
		// ------------------------
		// IF ORDERS NOT TAKEN YET
		// ------------------------

			$ORDER->state = 'taken';			
			$ORDER->manager = $TG_USER->id;
			$ORDER->updated = 'now()';
						
			if($ORDER->save()){													
			
				$keyboard = "";

				if(PENDING_MODE){
					$button_send_to_iiko_title = "Отправить в iiko";
					$button_send_to_iiko = "send_to_iiko:{$cafe_uniq_name}:{$order_id_uniq}";
					$button_add_comment = "add_comment:{$cafe_uniq_name}:{$order_id_uniq}";
					$keyboard = json_encode([
						"inline_keyboard" => [
							[
								[
									"text" => $button_send_to_iiko_title,
									"callback_data" => $button_send_to_iiko
								],
								[
									"text" => "Добавить комментарий к заказу",
									"callback_data" => $button_add_comment									
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
    	ADD COMMENT TO ORDER
		(WAITER CAN ADD NOTES FOR THE ORDER )

		@param string $cafe_uniq_name
		@param string $order_id_uniq
		@param Smart_object $TG_USER
    ----------------------------------- **/			
	static public function add_comment_to_order(string $cafe_uniq_name, string $order_id_uniq, Smart_object $TG_USER ):void {

		$order_short_number = self::get_short_number($order_id_uniq);

		if(!$ORDER = self::get_order_by_params($cafe_uniq_name, $order_short_number)){
			$warn_message = "О нет! Заказ {$order_short_number} не найден.";
			self::send_message_to_tg_users($TG_USER->tg_user_id, $warn_message);
			return;
		}else{
			if($ORDER->manager!==$TG_USER->id){
				$cancel_message = "Вы не можете добавить комментарий к этому заказу. Его взял другой менеджер (или официант).";
				self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);
				return;				
			}

			// ДАЕМ ВОЗМОЖНОСТЬ ДАННОМУ ОФИЦИАНТУ ОСТАВИТЬ КОММЕНТ ДЛЯ ЗАКАЗА
			self::send_message_to_tg_users($TG_USER->tg_user_id, "Комментарий к заказу {$order_short_number}:");
			$TG_USER->typing_comment_to_order = $order_id_uniq; 
			$TG_USER->save();

		}

	}	
    /*
    	SEND ORDER TO IIKO
		(FOR PENDING MODE)

		@param string $cafe_uniq_name
		@param string $order_id_uniq
		@param Smart_object $TG_USER
    ----------------------------------- **/		
	static public function send_order_to_iiko(string $cafe_uniq_name, string $order_id_uniq, Smart_object $TG_USER ):void {

		$order_short_number = self::get_short_number($order_id_uniq);		

		if($TG_USER->role !== 'waiter' && $TG_USER->role !== 'manager'){
			$cancel_message = "Вы не можете отпралять заказы. Для этого нужно иметь доступ Менеджера или Официанта";
			self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);							
			return;			
		}
		if($TG_USER->state !== 'active'){
			$cancel_message = "Вы не можете отправлять заказы. Сначала откройте смену.";
			self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);							
			return;
		}

		if(!$ORDER = self::get_order_by_params($cafe_uniq_name, $order_short_number)){
			$warn_message = "О нет! Заказ {$order_short_number} не найден.";
			self::send_message_to_tg_users($TG_USER->tg_user_id, $warn_message);
			return;
		}else{

			if($ORDER->manager!==$TG_USER->id){
				$cancel_message = "Вы не можете отправить этот заказ. Его взял другой менеджер (или официант).";
				self::send_message_to_tg_users($TG_USER->tg_user_id, $cancel_message);							
				return;				
			}

			if($ORDER->state==='sentout'){
				$warn_message = "Вы уже отправили заказ {$order_short_number} в iiko.";
				self::send_message_to_tg_users($TG_USER->tg_user_id, $warn_message);
				return;				
			}else{
				// -------------------------
				//   SENDING ORDER TO IIKO
				// -------------------------
				if(!$CAFE = self::get_cafe_by_uniq_name($cafe_uniq_name)){
					glogError("Ошибка настроек для кафе $cafe_uniq_name. ");
					$err_message = "О нет! Заказ не найден или устарел.";
					self::send_message_to_tg_users($TG_USER->tg_user_id, $err_message);
					return;					
				}
				$k = $CAFE->iiko_api_key;	

				if(empty($k)){
					glogError("не найден iiko_api_key для кафе $cafe_uniq_name, ".__FILE__.", ".__LINE__);
					$err_message = "О нет! Заказ не найден или устарел.";
					self::send_message_to_tg_users($TG_USER->tg_user_id, $err_message);
					return;
				}					

				// getting token 
				$url     = 'api/1/access_token';
				$headers = ["Content-Type"=>"application/json"];
				$params  = ["apiLogin" => $k];
				$res = iiko_get_info($url,$headers,$params);
				if(!isset($res["token"]) || empty($res["token"])){
					glogError("Не походит token (iiko) для кафе $cafe_uniq_name, ".__FILE__.", ".__LINE__);
					$err_message = "О нет! Ошибка настройки. Если она повторится, обратитесь к разработчику ChefsMenu";
					self::send_message_to_tg_users($TG_USER->tg_user_id, $err_message);
				}				
				$token = $res["token"];

				$iiko_params_collect = new Smart_collect("iiko_params", "where id_cafe='".$CAFE->id."'");
				if(!$iiko_params_collect || !$iiko_params_collect->full()) $err_message = "О нет! Ошибка настроек кафе. Если ошибка повторяется, обратитесь в техническую поддержку.";
				$iiko_param = $iiko_params_collect->get(0);

				$organization_id = $iiko_param->current_organization_id;
				$terminal_group_id = $iiko_param->current_terminal_group_id;		
				
				$MANAGER_NAME = self::get_tg_user_public_name($TG_USER);

				$comment_addition = $TG_USER->role === "waiter"? "Официант: ".$MANAGER_NAME : "Менеджер: ".$MANAGER_NAME;
				$comment = "Отправлен через телеграм. ".$comment_addition;
				$order_description = json_decode($ORDER->description, 1);

				$order_data = $order_description["ORDER_IIKO"];				
				$order_data["externalNumber"] = $ORDER->id_uniq;
				$order_data["comment"] = $comment;

				// glog("=========== ORDER_DATA =========== ".capture_var_dump($order_data));

				try{
					// ---------------------------------------------
					//      SEND PENDING IIKO-ORDER TO DELIVERY
					// ---------------------------------------------							
					if($ORDER->order_target===self::ORDER_DELIVERY){
						$answer = self::send_iiko_order_for_delivery($token, $organization_id, $terminal_group_id, $order_data);
						self::check_answer_from_iiko($answer);
					// ---------------------------------------------
					//      SEND PENDING IIKO-ORDER TO TABLE
					// ---------------------------------------------					
					}else if($ORDER->order_target===self::ORDER_TABLE){
						$answer = self::send_iiko_order_to_table($token, $organization_id, $terminal_group_id, $order_data);
						self::check_answer_from_iiko($answer);						
					}else{
						throw new Exception("Не найден ORDER_TARGET для кафе $cafe_uniq_name");
					}					
				}catch( Exception $e){
					glogError($e->getMessage());
					$err_message = "О нет! Не удалось отправить заказ в iiko. Если ошибка повторится, обратитесь в техническую поддержку";
					$err_message.="\n_({$e->getMessage()})_";
					self::send_message_to_tg_users($TG_USER->tg_user_id, $err_message);
					return;					
				}

				$ORDER->state = 'sentout';				
				$ORDER->updated = 'now()';

				if(!$ORDER->save()){
					glogError("Cant save the order ".$ORDER->id_uniq." for cafe {$cafe_uniq_name}, ".__FILE__.", ".__LINE__);
					$err_message = "Заказ отправлен, но его статус не обновлен. Если ошибка повторится, обратитесь к разработчику ChefsMenu";
					self::send_message_to_tg_users($TG_USER->tg_user_id, $err_message);					
					return;					
				}

				// ----------------------
				//      OK MESSAGE
				// ----------------------
				$ok_message = "Заказ {$order_short_number} отправлен в iiko";
				self::send_message_to_tg_users($TG_USER->tg_user_id, $ok_message);
				return;			

			}
		}

	}

	static private function check_answer_from_iiko(array $answer): void{
		glog("===== answer iiko after sending order ====== \n".print_r($answer,1));
		if(isset($answer["error"]) && !empty($answer["error"]) ){
			glogError("IIKO ERR: ".$answer["error"]."\n ".$answer["errorDescription"] );
			throw new Exception($answer["errorDescription"]);
		}		
		if(mb_strtolower($answer["orderInfo"]["creationStatus"])==="error"){
			throw new Exception($answer["orderInfo"]["errorInfo"]["message"]);
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
			// glog("tg response: ".print_r($res, 1).__FILE__.", ".__LINE__);
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