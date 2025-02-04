<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/common.php';	 
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.order_sender.php';

class Tg_hook {

    private string $TG_TOKEN = "";    
    //
    private array $data = [];
    private string $message = "";
    private string $tg_user_id= "";
    private string $tg_user_name= "";
    //
    private bool $CALLBACK_MODE = false; 
    private null|Smart_object $REAL_TG_USER = null;         
    private null|Smart_object $TG_KEY = null;

    function __construct($data){

        SQL::connect();	

        global $CFG;

        $this->TG_TOKEN = $CFG->tg_cart_token;

        glog("tg answer: ".print_r($data, 1).", ".__FILE__.", ".__LINE__);

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

        glog('$tg_user_name = '.$tg_user_name);

        return $this;

    }

    public function parse(): void{

        if(!empty($this->data['error_code'])){
            $error_message = "О нет! Неизвестная ошибка. Обратитесь к администратору сервиса. (".__LINE__.")";
            $this->send_error_message($error_message);
            return;
        }
        
        $this->REAL_TG_USER = $this->calc_real_tg_user();

        // -----------------------------------------------------------------
        //  IF KNOWN TG_USER, WHO REGISTERED FOR RECEIVING ORDERS FROM MENU 
        // -----------------------------------------------------------------        
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
                    $this->send_error_message($error_message);                    
                }

            }else{

                glog("real user sent message");
                
                // --------------------------------
                //  IF REAL TG_USER INPUT «Отмена»
                // --------------------------------
                if($this->message==="отмена"){

                    $this->delete_real_user();

                // -------------------------------------
                //  IF REAL TG_USER INPUT VALID TG_KEY
                // -------------------------------------
                }else if( $NEW_KEY = $this->check_tg_key_string($this->message)){
                     
                    $this->change_user_role($NEW_KEY);

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

            if($IS_KEY = $this->check_tg_key_string($this->message)){                
                $this->TG_KEY = $IS_KEY;
                if($CAFE = $this->check_cafe_by_tg_key($this->TG_KEY)){
                    // -----------------------
                    //  CREATING NEW TG_USER
                    // -----------------------                    
                    $this->create_new_user();
                }else{
                    // the tg key real, but cafe does not exist,
                    // so its might be the key needs to update
                    $error_message = "О нет! Данная ссылка вероятно устарела. Не найдено Меню с id: ".$this->TG_KEY->cafe_uniq_name;
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

    private function calc_real_tg_user(){        
        $tg_users = new Smart_collect('tg_users',"WHERE tg_user_id='".$this->tg_user_id."'");
        if($tg_users&&$tg_users->full()){	
            return $tg_users->get(0);
        }else{
            return null;
        }
    }

    private function parse_user_callback(): void{

        $params = explode(":",(string) $this->message);
        $command = $params[0];

        switch($command){
            case 'take_the_order':
                $cafe_uniq_name= $params[1];
                $order_id_uniq= $params[2];                
                $this->take_the_order($cafe_uniq_name, $order_id_uniq);
            break;            
            case 'send_to_iiko':
                $cafe_uniq_name = $params[1];
                $order_id_uniq = $params[2];
                Order_sender::send_order_to_iiko($cafe_uniq_name, $order_id_uniq, $this->REAL_TG_USER);
            break;
            case 'change_state':

                $this->change_tg_user_state_to($params[1]);

            break;
            case 'get_info':

                $this->send_info_to_supervisor($params[1]);

            break;                        
            default:
                $this->unknown_command();
            break;            
        }
    }

    private function unknown_command(): void{
        $this->send_message("опция в разработке");
    }

    private function take_the_order($cafe_uniq_name, $order_uniq_id): void{     
        
        $order_short_number = Order_sender::get_short_number($order_uniq_id);

        if(!$ORDER = $this->valid_order($order_uniq_id)){            
            $this->send_error_message("Заказ с номером {$order_short_number} не найден");
            return;
        }        
        try{
            Order_sender::do_take_the_order($cafe_uniq_name, $ORDER, $this->REAL_TG_USER);
        }catch(Exception $e){            
            glogError($e->getMessage());
            $this->send_error_message("Невозможно взять заказ с номером {$order_short_number}. 
            Если ошибка повторится, обратитесь в службу поддержки ChefsMenu.");
        }        
    }

    private function change_tg_user_state_to($state): void{

        $old_user_state = $this->REAL_TG_USER->state;
        if($old_user_state===$state && $state==='active'){
            $this->send_error_message("Ваша смена уже открыта");
            return;
        } else if($old_user_state===$state && $state==='inactive'){
            $this->send_error_message("Ваша смена уже закрыта");
            return;
        }
        $this->REAL_TG_USER->state = $state;
        $this->REAL_TG_USER->updated = 'now()';
        if(!$this->REAL_TG_USER->save()){
            $this->send_error_message("Не получилось изменить статус пользователя.");
            return;            
        }else{
            $name = $this->get_tg_user_name();

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

    /*
    	FIND ORDER IN DB

		@param string $order_id_uniq        
        @return Smart_object|null          
    --------------------------------------- **/	
    private function valid_order($order_id_uniq): Smart_object|null{
        $q = "WHERE id_uniq='".$order_id_uniq."'";
        $orders = new Smart_collect('orders',$q);        
        if($orders&&$orders->full()){
            $ORDER = $orders->get(0);
            return $ORDER;
        }else{
            return null;
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
        glog("отмена регистрации tg пользователя ".$this->tg_user_id);        
        $tg_users = new Smart_collect('tg_users',"WHERE tg_user_id='".$this->tg_user_id."'");
        
        if($tg_users&&$tg_users->full()){	            
            $usr = $tg_users->get(0);
            glog("удаляется tg user ".$usr);
            $usr->delete();            
            $answer_message = "Ваша регистрация для этого чата снята.";                    
        }else{
            $answer_message = "Вы не подписаны ни на одну роль в кафе.";
        }                

        $this->send_message($answer_message);
    }

    private function get_cafe_params($cafe_uniq_name): array{
        global $CFG;

        $arr_cafe = new Smart_collect("cafe","WHERE uniq_name='".$cafe_uniq_name."'");			

        if($arr_cafe&& $arr_cafe->full()){
            $cafe = $arr_cafe->get(0);
            $cafe_title = $cafe->cafe_title;
        }else{
            $cafe_title="Без названия";
        }
        $cafe_url = !empty($cafe->subdomain)?$CFG->http.$cafe->subdomain.".".$CFG->wwwroot : $CFG->http.$CFG->wwwroot."/cafe/".$cafe_uniq_name;
        $cafe_link = "[{$cafe_title}]({$cafe_url})";        
        return [
            'cafe_title'=>$cafe_title,
            'cafe_url'=>$cafe_url,
            'cafe_link'=>$cafe_link,
        ];   
    }

    private function show_status_real_user(): void{
        global $CFG;
        if(!$this->REAL_TG_USER){
            $error_message = "неизвестная ошибка ".__LINE__;
            $this->send_error_message($error_message);
            return;
        }
        $m = $this->REAL_TG_USER;
        $TG_USER_NAME = !empty($m->nickname)?$m->nickname:$m->name;

        $cafe_uniq_name  = $this->REAL_TG_USER->cafe_uniq_name;
        $params = $this->get_cafe_params($cafe_uniq_name);         

        $answer_message = "{$TG_USER_NAME}! Этот чат был зарегистрирован вами ранее для получения заказов из Меню ".$params['cafe_link'].".";        
        $answer_message .= $this->get_message_for($this->REAL_TG_USER->role);
        $answer_message .= $this->get_short_help_message();
                
        $keyboard = $this->get_keyboard($this->REAL_TG_USER);
        $this->send_message($answer_message, $keyboard);

    }

    private function check_tg_key_string($key_string=""){ 
        
        glog("TEST! check_tg_key_string");

        if(!str_starts_with($key_string, '/start ')) return false;        
        $key = explode(" ",$key_string)[1];        
        $arr_keys = new Smart_collect('tg_keys',"WHERE tg_key='{$key}'");
        if($arr_keys&&$arr_keys->full()){            
            return $arr_keys->get(0);
        }else{
            return false;
        }
    }

    private function check_cafe_by_tg_key( Smart_object $TG_KEY): null|Smart_object{		
        $cafe_uniq_name = $TG_KEY->cafe_uniq_name;         
		$arr_cafe = new Smart_collect('cafe',"WHERE uniq_name='{$cafe_uniq_name}'");		
		if($arr_cafe && $arr_cafe->full()){
			$cafe = $arr_cafe->get(0);
			return $cafe;
		}else{
			return null;
		}
    }

    private function change_user_role( Smart_object $NEW_KEY){        

        $arrRoleNames = [
            "waiter"=>"ОФИЦИАНТ",
            "manager"=>"МЕНЕДЖЕР",
            "supervisor"=>"АДМИНИСТРАТОР",
        ];

        $error_message = "*Ой, что-то пошло не так!*
        В данный момент невозможно поменять вашу роль на ".$arrRoleNames[$NEW_KEY->role].". 
        Попробуйте позже или напишите разработчику сервиса.";

        if(!$this->REAL_TG_USER) {
            $this->send_error_message($error_message);
            return false;
        }        

        if( $this->REAL_TG_USER->role===$NEW_KEY->role){
            $this->send_message("Вы пытаетесь сменить роль на такую же. Возможно вы скопировали не ту ссылку.");
            return false;
        }
        
        $this->REAL_TG_USER->role = $NEW_KEY->role;
        
        if(!$this->REAL_TG_USER->save()){
            $this->send_error_message($error_message);
            return false;
        }        

        $params = $this->get_cafe_params($this->REAL_TG_USER->cafe_uniq_name);    
        $cafe_link = $params['cafe_link'];

        $answer_message = "Ок. Вы зарегистрировались в QR-Меню ".$params['cafe_link'].".";
        $answer_message.= $this->get_message_for($NEW_KEY->role);
        $answer_message.= $this->get_short_help_message();
        
        $keyboard = $this->get_keyboard($this->REAL_TG_USER);
        $this->send_message($answer_message, $keyboard);

    }

    private function create_new_user():void{

        $error_message = "*Ой, что-то пошло не так!*
        В данный момент невозможно зарегистрировать нового пользователя.   
        Попробуйте позже или напишите разработчику сервиса.";

        if(!$this->TG_KEY || !$this->tg_user_id || !$this->tg_user_name) {
            $this->send_error_message($error_message);
            return;
        }
        $user_role = $this->TG_KEY->role;
        $cafe_uniq_name = $this->TG_KEY->cafe_uniq_name;

        $user_state = $user_role==='supervisor'?'active':'inactive';

        $new_tg_user = new Smart_object('tg_users');
        $new_tg_user->tg_user_id = $this->tg_user_id;
        $new_tg_user->role = $user_role;
        $new_tg_user->state = $user_state;
        $new_tg_user->name = $this->tg_user_name;		
        $new_tg_user->nickname = "";		
        $new_tg_user->cafe_uniq_name = $cafe_uniq_name;
        $new_tg_user->regdate = 'now()';

        $params = $this->get_cafe_params($cafe_uniq_name);
    
        $cafe_link = $params['cafe_link'];;
        $cafe_url = $params['cafe_url'];
        $cafe_title = $params['cafe_title'];

        if(!$new_tg_user->save()){            
            $this->send_error_message($error_message);
            return;
        }else{
            $answer_message = "*Поздравляем!*  Вы успешно зарегистрировали этот чат для получения заказов из Меню «{$cafe_link}». ";
            $answer_message .= $this->get_message_for($user_role); 
            $answer_message .= $this->get_short_help_message();
            
            $keyboard = $this->get_keyboard($new_tg_user);
            $this->send_message($answer_message, $keyboard);                    
        }
    }    

    private function get_message_for($role){
        $arr_messages = [
            "waiter" => " Ваша роль определена как ОФИЦИАНТ. Вы можете брать (подтверждать) заказы НА СТОЛ (внутри кафе).",
            "manager" => " Ваша роль определена как МЕНЕДЖЕР. Вы можете подтверждать заказы на ДОСТАВКУ и САМОВЫВОЗ.",
            "supervisor" => " Ваша роль определена как АДМИНИСТРАТОР. Вы можете получать информацию о работе ОФИЦИАНТОВ и МЕНЕДЖЕРОВ, но не можете подтверждать и брать заказы.",
        ];        
        return $arr_messages[$role];
    }    

    private function get_short_help_message(){        
        $str = "\n\n– _Чтобы отменить свою регистрацию в данном чате, введите слово «Отмена»._";
        $str.= "\n\n– _Чтобы поменять свое имя в данном чате, введите слово «имя» и, через пробел, ваше новое имя. Например: имя Егор._";
        return $str;
    }    
    
    private function get_keyboard( Smart_object $TG_USER): string{        
        if($TG_USER->role==='waiter' || $TG_USER->role==='manager'){
            $keyboard = $this->get_shifts_button($TG_USER);
        }else if($TG_USER->role==='supervisor'){
            $keyboard = $this->get_info_button($TG_USER);
        }else{
            $keyboard = "";
        }    
        return $keyboard;
    }

    private function get_info_button( Smart_object $TG_USER): string{
        if(!$TG_USER) return "";
        $button_state_title = "Кто в чате сейчас";
        $button_state = "get_info:general";                    
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
        return $keyboard;
    }
    
    private function get_shifts_button( Smart_object $TG_USER): string{        
        if(!$TG_USER) return "";
        if($TG_USER->state==='active'){
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
        return $keyboard;
    }        

    private function get_all_tg_users(): array{
        if(!$this->REAL_TG_USER) return [];        
        $tg_users = new Smart_collect('tg_users',"WHERE cafe_uniq_name='".$this->REAL_TG_USER->cafe_uniq_name."'");
        if($tg_users&&$tg_users->full()){	
            return $tg_users->get();
        }else{
            return [];
        }                
    }

    private function send_message_first_time(): void{
        $answer_message = "*".$this->tg_user_name.",*		
        похоже, вы здесь впервые! Чтобы получать заказы, 
        попросите у администратора кафе специальную *«Ссылку-приглашение».*";
        $this->send_message($answer_message);
    }
    
    private function get_tg_user_name(){
        $MANAGER_NAME = !empty($this->REAL_TG_USER->nickname)?$this->REAL_TG_USER->nickname:$this->REAL_TG_USER->name;        
        return $MANAGER_NAME;
    }

    private function send_info_to_supervisor($keyword): void{            
        if($keyword==='general'){
            
            $ARR_TG_USERS = $this->get_all_tg_users();
            if($ARR_TG_USERS && count($ARR_TG_USERS)){
                $empl=[
                    "waiter"=>['total'=>0,'active'=>0],
                    "manager"=>['total'=>0,'active'=>0],
                    "supervisor"=>['total'=>0,'active'=>0],
                ];
                foreach($ARR_TG_USERS as $TG_USER){
                    $empl[$TG_USER->role]['total']+=1;
                    if($TG_USER->state==='active')
                    $empl[$TG_USER->role]['active']+=1;
                }
                glog("\$empl = ".print_r($empl,1));
                $str = "*Сейчас в чате: *";     
                $str.="\n- официантов: ".$empl['waiter']['active']." из ".$empl['waiter']['total'];
                $str.="\n- менеджеров: ".$empl['manager']['active']." из ".$empl['manager']['total'];
                $str.="\n- администраторов: ".$empl['supervisor']['active']." из ".$empl['supervisor']['total'];
            }
            $this->send_message($str);  
        }else{            
            $this->send_error_message("Неизвестный запрос");
        }        
    }    

    private function send_to_relevant($msg, $keyboard=""): void{
        glog("send_to_relevant не реализована");
        // Order_sender::send_message_to_relevant_users($cafe_uniq_name, $order_target, $msg, $keyboard="");        
    }

    private function send_message($msg, $keyboard=""): void{                        
        Order_sender::send_message_to_tg_users($this->tg_user_id, $msg, $keyboard);	
    }

    private function send_error_message($msg, $keyboard=""): void{
        glogError($msg, __FILE__);
        Order_sender::send_message_to_tg_users($this->tg_user_id, $msg, $keyboard);	        
    }    


}


?>