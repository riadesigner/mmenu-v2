<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Password{	

	static private $ERR_MESSAGE = ""; // string	
	static private $CONFIRMED = false; 

	static public function confirm($email,$key){
		
		$lang = SITE::get_lang();

		if(!preg_match("|^[0-9a-z_\.]+@[0-9a-z_^\.]+\.[a-z]{2,6}$|i", (string) $email)){
			if($lang=="ru"){
				self::$ERR_MESSAGE = "Ошибка. Неправильная ссылка для активации пароля";
			}else{
				self::$ERR_MESSAGE = "Error. Incorrect password activation link";	
			}			
			return false;
		}

		$User = User::byEmail($email);

		if(!$User->valid()) {
			if($lang=="ru"){
				self::$ERR_MESSAGE = "Такого пользователя не существует.";		
			}else{
				self::$ERR_MESSAGE = "The user doesn't exist";		
			}
			
			
		}elseif(!$User->new_password){			

			if($lang=="ru"){
				self::$ERR_MESSAGE = "Ошибка. Возможно пароль уже был активирован.";	
			}else{
				self::$ERR_MESSAGE = "Error. Perhaps the passward already have been activated.";		
			}
			

		}elseif($key===md5("newpass:".$User->new_password)){
		
			$User->password = $User->new_password;
			$User->new_password = "";
			$User->updated_date = 'now()';
			if($User->save()){
				self::$CONFIRMED = true;
			}else{
				if($lang=="ru"){
					self::$ERR_MESSAGE = "Ошибка. Не удается активировать новый пароль.";
				}else{
					self::$ERR_MESSAGE = "Error. Cant activate new password.";
				}
				
			}
	
		}else{
			if($lang=="ru"){
				self::$ERR_MESSAGE = "Ошибка. Неправильная ссылка для активации пароля.";	
			}else{
				self::$ERR_MESSAGE = "Error. Wrong activation link.";	
			}
		}

		return self::is_confirmed();
		
	}

	static public function is_confirmed(){
		return self::$CONFIRMED;
	}

	static public function get_err_message(){
		return self::$ERR_MESSAGE;
	}	

}
	
?>