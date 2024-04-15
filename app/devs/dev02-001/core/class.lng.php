<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LNG {
	
    static private $CURRENT_LANG = "en";      
    static private $LNG_LIST = [];

    static public function set($lng): void{
        self::$CURRENT_LANG = $lng;        
        self::$LNG_LIST = self::get_list();
    }
    
    static public function get($word){
        
        if(!count(self::$LNG_LIST))
        self::$LNG_LIST = self::get_list();
        
        if(! isset(self::$LNG_LIST[$word])){
            return "Undefined";
        }else{
            $k = self::$CURRENT_LANG==="ru"?1:0;            
            return self::$LNG_LIST[$word][$k];
        }
    }    

    static private function get_list(){
        return  [
            // public site / main page
            // "lng_answers" => array("Answer and quastions","Вопросы и ответы"),
            "lng_contacts" => ["Contacts", "Контакты"],
            "lng_create" => ["Create", "Создать"],
            "lng_description" => ["The Menu for cafe in 10 second", "Меню для кафе за 10 секунд"],
            "lng_enter" => ["Enter", "Войти"],
            "lng_enter_to_cabinet" => ["Enter <br>to Control<br> Panel", "Войти <br>в&nbsp;Панель<br> управления"],
            "lng_getfree" => ["Create my <br>own&nbsp;menu", "Создать<br>свое меню"],
            "lng_main" => ["Main", "Главная"],
            "lng_price" => ["Price", "Стоимость"],
            "lng_privacy" => ["Privacy Policy", "Конфиденциальность"],
            "lng_slogan" => ["So simple <span>and clearly</span>", "Так просто <span>и&nbsp;понятно</span>"],
            "lng_terms" => ["Terms of Service", "Условия сервиса"],
            "lng_features" => ["Features and limitations", "Возможности и ограничения"],
            "lng_to_developers" => ["To developers", "Разработчикам"],
            "lng_help" => ["Quick start", "С чего начать"],
            "lng_easy_open" => ["Easy open menu", "Легкое открытие меню"],
            "lng_howto_embed_menu" => ["Connect to your site", "Подключение к сайту"],
            "lng_noregistration" => ["14 days for free", "14 дней бесплатно"],
            "lng_forms_name_of_your_cafe" => ["Name of your Cafe", "Название вашего кафе"],
            "lng_forms_your_email" => ["Your email", "Ваш email"],
            "lng_form_description" => ["Get access to the Control Panel of your&nbsp;Menu:", "Введите название вашего кафе и&nbsp;электронную почту:"],
            "lng_link_will_be_sent"=> ["You will receive an email with a link to access the Control Panel.", "На вашу почту придет письмо с&nbsp;ссылкой для входа в&nbsp;Панель Управления."],
            "lng_bnt_next" => ["Get it", "Получить меню"],
            "lng_menu_created" => ["Done!", "Сделано!"],
            // 404 page
            "lng_wrong_page" =>["Wrong url!", "Неизвестная страница!"],
            // Confirm password page
            // public site / sign in panel
            "lng_friends" => ["Dear Friends!", "Друзья!"],
            "lng_singin_touch_only"=>['The Control Panel is designed for easy handling of <strong>touch</strong> devices. Open this site from your phone or tablet. Thank you.', 'Панель Управления спроектирована для <strong>touch</strong> устройств. Откройте ее со вашего телефона или планшета. Спасибо.'],
            "lng_close"=>["Close", "Закрыть"],
            "lng_send"=>["Send", "Отправить"],
            "lng_get"=>["Get", "Получить"],
            "lng_sending"=>["Sending", "Отправка"],
            "lng_get_new_pass"=>["Send new password to e-mail", "Получить на почту новый пароль"],
            "lng_forgot_pass"=>["Forgot my password", "Забыл пароль"],
            "lng_checking"=>["Checking", "Проверка"],
            "lng_your_email"=>["Your email", "Ваш email"],
            "lng_your_pass"=>["Password", "Пароль"],
        ];
    }

}


?>