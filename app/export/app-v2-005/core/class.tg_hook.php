<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/common.php';	 
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.order_sender.php';

class Tg_hook {

    private $TG_TOKEN = "";    
    //
    private $data = ""; // array
    private $message = ""; // string 
    private $tg_user_id= ""; // string
    private $tg_user_name= ""; // string
    //
    private $CALLBACK_MODE = false; // bool
    private $REAL_TG_USER = false; // object
    //
    private $TG_KEY = false; // object

    function __construct($data){

        SQL::connect();	

        global $CFG;

        $this->TG_TOKEN = $CFG->tg_cart_token;

        glog("tg answer: ".print_r($data, 1));

        // IF WAS PRESSED A CALBACK BUTTON        
        $this->CALLBACK_MODE = !empty($data['callback_query']);

        $data = !empty($data['callback_query']) ? $data['callback_query'] : $data['message'];
        $message = $data['text'] ?? $data['data'];
        $message = mb_strtolower(trim((string) $message),'utf-8');
        $tg_user_id = $data['from']['id'];
        $tg_user_name = $data['from']['first_name'];

        $this->data = $data;        
        $this->message = $message;        
        $this->tg_user_id = $tg_user_id;
        $this->tg_user_name = $tg_user_name;

        return $this;

    }

    public function parse(): void{

        if(!empty($this->data['error_code'])){
            $error_message = "О нет! Неизвестная ошибка. Обратитесь к администратору сервиса. (".__LINE__.")";
            $this->send_error_message($error_message);
            return;
        }

        $this->calc_real_tg_user();

        // -------------------------------------------------------------
        //  IF KNOWN TG_USER, REGISTERED FOR RECEIVING ORDERS FROM MENU 
        // -------------------------------------------------------------        
        if($this->REAL_TG_USER){

            // ----------------------------------------
            //  IF REAL TG_USER PRESSED CALBACK BUTTON
            // ----------------------------------------
            if($this->CALLBACK_MODE){
                try{

                    $this->parse_user_callback();                    

                }catch(Throwable $e){
                    glogError($e->getMessage());
                    $error_message = "О нет! Неизвестная ошибка. Обратитесь к администратору сервиса. (".__LINE__.")".__FILE__;
                    $this->send_message($error_message);                    
                }

            }else{

                // --------------------------------
                //  IF REAL TG_USER INPUT «Отмена»
                // --------------------------------
                if($this->message==="отмена"){

                    $this->delete_real_user();

                // --------------------------------
                //  IF REAL TG_USER INPUT «Имя»
                // --------------------------------
                }else if($newname = $this->check_if_need_change_name($this->message)){

                    $this->change_name_real_user($newname);

                }else{

                // -----------------------------------
                //  IF REAL TG_USER INPUT bla-bla-bla
                // -----------------------------------                    
                $this->show_status_real_user();

                }                
            }

        }else{
            // --------------------------------------------
            //  IF UNKNOWN (OR NEW) TG_USER ENTERED TG_KEY
            // --------------------------------------------
            if($this->check_tg_key()){
                if($this->check_cafe_by_tg_key()){
                    // -----------------------
                    //  CREATING NEW TG_USER
                    // -----------------------                    
                    $this->create_new_user();
                }else{
                    // the tg key real, but cafe does not exist,
                    // so its might be the key needs to update
                    $error_message = "О нет! Данный ключ вероятно устарел. Не найдено Меню с id: ".$this->TG_KEY->cafe_uniq_name;
                    $this->send_error_message($error_message);
                }
            }else{
                // --------------------------------------
                //  IF UNKNOWN USER INPUTTED BLA-BLA-BLA
                // --------------------------------------
                $this->send_message_first_time();
            }    
        }
    }

    // -------------------------------------------------
    //
    //                PRIVATE FUNCTIONS
    //
    // -------------------------------------------------

    private function calc_real_tg_user(): void{        

        $tg_users = new Smart_collect('tg_users',"WHERE tg_user_id='".$this->tg_user_id."'");
        if($tg_users&&$tg_users->full()){	
            $this->REAL_TG_USER = $tg_users->get(0);                        
        }        

    }

    private function parse_user_callback(): void{

        $params = explode(":",(string) $this->message);
        $command = $params[0];

        switch($command){
            case 'confirm_order':
                $cafe_uniq_name= $params[1];
                $order_short_number= $params[2];                
                try{

                    $this->confirm_order($cafe_uniq_name,$order_short_number);

                }catch(Throwable $e){
                    glogError($e->getMessage());
                    $error_message = "О нет, не получается подтвердить заказ. Техническая ошибка.";
                    $this->send_error_message($error_message);
                }
            break;
            case 'change_state':

                $this->change_tg_user_state_to($params[1]);

            break;
            default:
                $this->unknown_command();
            break;            
        }
    }

    private function unknown_command(): void{
        glog("--unknown_command--");
    }

    private function confirm_order($cafe_uniq_name, $order_short_number): void{        
        if(!$order = $this->valid_order($cafe_uniq_name, $order_short_number)){
            throw new Exception("Заказ с номером {$order_short_number} не найден, (".__LINE__.")");                    
        }
        Order_sender::order_confirm_and_send_to_iiko($cafe_uniq_name, $order, $this->REAL_TG_USER);          
    }

    private function change_tg_user_state_to($state): void{
        $this->REAL_TG_USER->state = $state;
        $this->REAL_TG_USER->updated = 'now()';
        if(!$this->REAL_TG_USER->save()){
            throw new Exception("Не получилось изменить статус пользователя. ".__LINE__);
        }else{
            $name = $this->get_manager_name();

            if($state==="active"){                
                $personal_message = "Ок, {$name}! Вы открыли смену.";
                // $answer_message = "Ок, {$name} открыл смену.";
            }else{
                $personal_message = "Ок, {$name}! Вы закрыли смену.";
            }
            $this->send_message($personal_message);
            // $this->send_to_relevant($cafe, $cafe->order_target, $answer_message);
        }
    }

    private function valid_order($cafe_uniq_name, $order_short_number){
        // ----------------------
        //  CHECK IS VALID ORDER
        // ----------------------
        $q = "WHERE cafe_uniq_name='".$cafe_uniq_name."' AND short_number='".$order_short_number."'";
        $orders = new Smart_collect('orders',$q);        
        if($orders&&$orders->full()){
            $order = $orders->get(0);
            return $order;
        }else{
            return false;
        }
    }

    private function check_if_need_change_name($message){
        $m = explode(" ",(string) $message);
        if(count($m)>1 && str_contains( trim($m[0]), "имя")){
            $new_name = $m[1];
            return $new_name;   
        }else{
            return false;
        }
    }

    private function change_name_real_user($new_name): void{
        $new_name = strtolower((string) $new_name);
        $new_name =  mb_strtoupper(mb_substr($new_name, 0, 1)) . mb_substr($new_name, 1);        
        $this->REAL_TG_USER->nickname = $new_name;
        $this->REAL_TG_USER->updated = 'now()';        
        if($this->REAL_TG_USER->save()){
            $answer_message = "Ok, $new_name! Вы поменяли ваше имя.";
            $this->send_message($answer_message);
        }else{
            $error_message = "О нет! $new_name! Возникла ошибка при попытке поменять имя. Если она повторится, обратитесь к администратору сервиса.";
            $this->send_error_message($error_message);
        }                
    }

    private function delete_real_user(): void{

        $tg_users = new Smart_collect('tg_users',"WHERE tg_user_id='".$this->tg_user_id."'");
        if($tg_users&&$tg_users->full()){	
            $arr_users->get();
            foreach($arr_users as $usr){
                $usr->delete();
            }            
        }                
        $answer_message = "Все предыдущие регистрации для этого чата сняты. 
        Для регистрации нового кафе и вашей роли в нем (официант, менеджер, администратор) – введите *«Секретный ключ».* 
         _(Вы найдете его в Панели Управления Меню. В разделе «Настройка корзины». Скопируйте его и вставьте сюда.)_" ;        
        $this->send_message($answer_message);
    }

    private function show_status_real_user(): void{

        if(!$this->REAL_TG_USER){
            $error_message = "неизвестная ошибка ".__LINE__;
            $this->send_error_message($error_message);
            return;
        }
        $m = $this->REAL_TG_USER;
        $MANAGER_NAME = !empty($m->nickname)?$m->nickname:$m->name;

        $cafe_uniq_name  = $this->REAL_TG_USER->cafe_uniq_name;
        $arr_cafe = new Smart_collect("cafe","WHERE uniq_name='".$cafe_uniq_name."'");			

        if($arr_cafe&& $arr_cafe->full()){
            $cafe = $arr_cafe->get(0);
            $cafe_title = $cafe->cafe_title;
        }else{
            $cafe_title="Без названия";
        }

        $cafe_url = !empty($cafe->subdomain)?"https://".$cafe->subdomain.".chefsmenu.ru":"https://chefsmenu.ru/cafe/".$cafe_uniq_name;
        $cafe_link = "[{$cafe_title}]({$cafe_url})";

        switch($this->REAL_TG_USER->role){
            case 'waiter':
                $answer_message = "{$MANAGER_NAME}! Этот чат зарегистрирован для получения заказов «В СТОЛ» из Меню {$cafe_link}.  
                Ваша роль определена как «Официант».  Вы можете брать (подтверждать) заказы и отправлять их в iiko. ";
            break;
            case 'manager':
                $answer_message = "{$MANAGER_NAME}! Этот чат зарегистрирован для получения заказов из Меню {$cafe_link} на «ДОСТАВКУ» и «САМОВЫВОЗ».  
                Ваша роль определена как «Менеджер». Вы можете подтверждать заказы, отправляя их в iiko. ";
            break;
            case 'supervisor':
                $answer_message = "{$MANAGER_NAME}! Этот чат зарегистрирован для получения сводной информации
                обо всех заказах (в стол, доставка, самовывоз) из Меню {$cafe_link}.
                Ваша роль определена как «Администратор».  
                Вы можете получать информацию о работе Официантов и Менеджеров за день, но не можете подтверждать заказы. ";
            break;								
        }

        $answer_message.= "\n\n– _Чтобы зарегистрировать этот чат для другого Меню или выбрать другую роль (официант, менеджер, администратор), отмените текущую регистрацию. Для этого введите слово «Отмена»._";
        $answer_message.= "\n\n– _Чтобы поменять свое имя в данном чате, введите слово «имя» и ваше новое имя. Например: имя Егор._";

        if($this->REAL_TG_USER->state==='active'){
            $button_state_title = "Закрыть смену";
            $button_state = "change_state:inactive";
        }else{
            $button_state_title = "Открыть смену";
            $button_state = "change_state:active";            
        }        

        $keyboard = json_encode([
            "inline_keyboard" => [
                [
                    [
                        "text" => $button_state_title,
                        "callback_data" => $button_state
                    ]
                ]
            ]
        ], JSON_UNESCAPED_UNICODE);	

        $this->send_message($answer_message, $keyboard);

    }

    private function check_tg_key(){
        $tg_key = trim((string) $this->message);
        $arr_keys = new Smart_collect('tg_keys',"WHERE tg_key='{$tg_key}'");
        if($arr_keys&&$arr_keys->full()){             
            $this->TG_KEY = $arr_keys->get(0);
            return $this->TG_KEY;
        }else{
            return false;
        }
    }

    private function check_cafe_by_tg_key(){
		if(!$this->TG_KEY) return false;
        $cafe_uniq_name = $this->TG_KEY->cafe_uniq_name;         
		$arr_cafe = new Smart_collect('cafe',"WHERE uniq_name='{$cafe_uniq_name}'");		
		if($arr_cafe && $arr_cafe->full()){
			$cafe = $arr_cafe->get(0);
			return $cafe;
		}else{
			return false;
		}
    }

    private function create_new_user(){

        $error_message = "*Ой, что-то пошло не так!*
        в данный момент невозоможно зарегистрировать нового пользователя.   
        Попробуйте позже или напишите администратору сервиса.";

        if(!$this->TG_KEY || !$this->tg_user_id || !$this->tg_user_name) {
            $this->send_error_message($error_message);
            return false;
        }

        $new_tg_user = new Smart_object('tg_users');
        $new_tg_user->tg_user_id = $this->tg_user_id;
        $new_tg_user->role = $user_role;
        $new_tg_user->name = $this->tg_user_name;		
        $new_tg_user->nickname = "";		
        $new_tg_user->cafe_uniq_name = $this->TG_KEY->cafe_uniq_name;
        $new_tg_user->regdate = 'now()';

        if(!$new_tg_user->save()){            
            $this->send_error_message($error_message);
            return false;
        }else{
            $msg = "*Поздравляем!*  Вы успешно зарегистрировали этот чат для получения заказов из Меню «{$cafe_title}». ";
            switch($user_role){
                case 'waiter':
                $msg.="Ваша роль определена как «Официант». Вы сможете отменять или подтверждать заказы «В стол». ";
                break;
                case 'manager':
                    $msg.="Ваша роль определена как «Менеджер. Вы сможете отменять или подтверждать внешние заказы на «Доставку» и «Самовывоз». ";
                break;
                case 'supervisor':
                    $msg.="Ваша роль определена как «Администратор». Вы сможете получать информацию об всех заказах («В стол», «Доставка», «Самовывоз») и сводную информацию работы Менеджеров и Официантов за день. ";
                break;
            }

            $cafe_url = !empty($cafe->subdomain)?"https://{$cafe->subdomain}.chefsmenu.ru" : "https://chefsmenu.ru/cafe/{$cafe_uniq_name}";
            $msg.=" 
            Откройте Меню [«{$cafe_title}»]($cafe_url) и отправьте пробный заказ.";
            $this->send_message($msg);
            return true;
        }
    }    

    private function send_message_first_time(): void{
        $answer_message = "*".$this->tg_user_name.",*		
        похоже, вы здесь впервые!     
        Чтобы получать заказы из вашего Меню в этот чат, введите, пожалуйста, *«Секретный ключ».*          
        _(Вы найдете его в Панели Управления Меню. В разделе «Настройка корзины». Скопируйте его и вставьте сюда.)_
        ";
        $this->send_message($answer_message);
    }
    private function get_manager_name(){
        $MANAGER_NAME = !empty($this->REAL_TG_USER->nickname)?$this->REAL_TG_USER->nickname:$this->REAL_TG_USER->name;        
        return $MANAGER_NAME;
    }

    private function send_to_relevant($msg, $keyboard=""): void{
        glog("send_to_relevant не реализована");
        // Order_sender::send_message_to_relevant_users($cafe_uniq_name, $order_target, $msg, $keyboard="");        
    }

    private function send_message($msg, $keyboard=""): void{
        // glog($msg);
        Order_sender::send_message_to_tg_user($this->tg_user_id, $msg, $this->REAL_TG_USER, $keyboard="");	
    }

    private function send_error_message($msg, $keyboard=""): void{
        glogError($msg);
        Order_sender::send_message_to_tg_user($this->tg_user_id, $msg, $this->REAL_TG_USER, $keyboard="");	        
    }    


}


// set webhook
// https://api.telegram.org/bot5864349836:AAGi-PI_20yJy8sIrPpU-oOHnIzlYJmjIbA/setwebhook?url=https://riadesign.ru/ext/tg/index.php



?>