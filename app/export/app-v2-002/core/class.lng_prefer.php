<?php

/*
	ПРЕДПОЧИТАЕМЫЙ ЯЗЫК
*/

class Lng_prefer{	
	
	static public $COOKIE_NAME = "CHMENU_LANG_PREFER";

	static public function get(){
		
		// get from browser	
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			$lng = explode(",", (string) $_SERVER['HTTP_ACCEPT_LANGUAGE'])[0];
		}else{
			$lng='ru-RU';
		}
		
		// glog("Lng_prefer=".$lng);

		$lng =  mb_strtolower(explode("-", $lng)[0]);		

		//get from cookie
		$name = self::$COOKIE_NAME;
		if(isset($_COOKIE[$name])){			
			$str = $_COOKIE[$name];
			if(strlen((string) $str)==2){ $lng = $str;	}
		}

		// -------------------------------------\
		// временно оставляем только русский
		// пока не добавим оплату через paypal
		$lng = "ru";
		// -------------------------------------/

		return $lng;

	}

	static public function set($lng){
		
		if(strlen((string) $lng)==2){ 
						
			setcookie(self::$COOKIE_NAME, (string) $lng, ['expires' => strtotime( '+365 days' ), 'path' => '/', 'domain' => "", 'secure' => false, 'httponly' => true]);

			return true;
		}else{
			return false;
		}
	}

}
		
?>