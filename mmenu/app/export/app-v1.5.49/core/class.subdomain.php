<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Subdomain{	

	static private $ERR_MESSAGE = "";
	static private $OK_MESSAGE = "";
	static private $CONFIRMED = false;
	static private $CONFIRMED_SUBDOMAIN = "";

	static public function confirm($cafe_uniq_name, $new_subdomain, $key){
		
		$lang = SITE::get_lang();

		if(!self::is_valid_name($new_subdomain)){
			return false;
		}

		$cafe_uniq_name = post_clean($cafe_uniq_name,100);
		
		$co_cafe = new Smart_collect("cafe","WHERE uniq_name='{$cafe_uniq_name}'");

		if($co_cafe && $co_cafe->full()){
			$cafe = $co_cafe->get(0);
		}else{
			self::$ERR_MESSAGE = "--wrong link";
			return false;	
		}
		
		$new_subdomain = post_clean($new_subdomain,30);

		if($cafe->subdomain==$new_subdomain){
			self::_confirm_true($new_subdomain,"--already confirmed");
			return true;
		}
	
		$key = post_clean($key,100);

		$true_key = self::calc_key($cafe_uniq_name, $new_subdomain);
		if($true_key!==$key){
			self::$ERR_MESSAGE = "--wrong link";
			return false;
		}

		$cafe->subdomain = $new_subdomain;
		$cafe->updated_date = 'now()';
		if(!$cafe->save()){
			self::$ERR_MESSAGE = "--something wrong";
			return false;			
		}

		$user = new Smart_object('users',$cafe->id_user);
		if($user && $user->valid()){
			$user->updated_date = 'now()';
			$user->save();
		}

		self::_confirm_true($new_subdomain,"--ok");
		return true;
	
	}

	static public function is_valid_name($subdomain){
		

		$msk = "|^[0-9a-zA-Z].[\-0-9a-zA-Z]*$|i";
		if(!preg_match($msk,(string) $subdomain)) {
			self::$ERR_MESSAGE = "--illegal name";
			return false;
		}

		if(strlen((string) $subdomain)<3) {
			self::$ERR_MESSAGE = "--too short name";
			return false;			
		}

		$msk = "|^cafe$|i";
		if(preg_match($msk,(string) $subdomain)) {
			self::$ERR_MESSAGE = "--illegal name";
			return false;			
		}


		$msk = "|^menu$|i";
		if(preg_match($msk,(string) $subdomain)) {
			self::$ERR_MESSAGE = "--illegal name";
			return false;
		}

		if(str_contains((string) $subdomain, 'admin') || 
			str_contains((string) $subdomain, 'adm') ||
			str_contains((string) $subdomain, 'admn') ||
			str_contains((string) $subdomain, 'control') ||
			str_contains((string) $subdomain, 'yourname') ||
			str_contains((string) $subdomain, 'www') ||
			str_contains((string) $subdomain, 'ftp') ||
			str_contains((string) $subdomain, 'dev') ||
			str_contains((string) $subdomain, 'reg') ||
			str_contains((string) $subdomain, 'xxx') ||
			str_contains((string) $subdomain, 'sex') ||
			str_contains((string) $subdomain, 'fuck') ||
			str_contains((string) $subdomain, 'porn')){
			
			self::$ERR_MESSAGE = "--illegal name";
			return false;

		}

		return true;

	}

	static public function is_confirmed(){
		return self::$CONFIRMED;
	}
	
	static public function get(){
		return self::$CONFIRMED_SUBDOMAIN;
	}

	static public function get_err_message(){
		return self::$ERR_MESSAGE;
	}	
	static public function get_ok_message(){
		return self::$OK_MESSAGE;
	}		

	static public function calc_key($cafe_uniq_name, $subdomain){		
		return md5("new-subdomain-is:".$cafe_uniq_name.$subdomain);
	}

	//     ____  ____  _____    _____  ____________
	//    / __ \/ __ \/  _/ |  / /   |/_  __/ ____/
	//   / /_/ / /_/ // / | | / / /| | / / / __/
	//  / ____/ _, _// /  | |/ / ___ |/ / / /___
	// /_/   /_/ |_/___/  |___/_/  |_/_/ /_____/


	static private function _confirm_true($subdomain, $msg="--ok"): void{
		self::$OK_MESSAGE = $msg;
		self::$CONFIRMED_SUBDOMAIN = $subdomain;
		self::$CONFIRMED = true;
	}


}
	
?>