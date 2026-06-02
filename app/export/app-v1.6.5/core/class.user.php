<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class User{	

	static public function from_cookie(){	

		//verify agent
		$cookie_agent = "CHMENU_AGENT";
		if(!isset($_COOKIE[$cookie_agent])) return false;
		if(md5((string) self::get_device_info())!==$_COOKIE[$cookie_agent]) return false; 
		
		$cookie_name = "CHMENU_USER";
		if(!isset($_COOKIE[$cookie_name])) return false;
		$cookie_value = $_COOKIE[$cookie_name];
		if(strlen((string) $cookie_value)!==32)return false;

		$token = new Smart_collect("one_time_auth","WHERE token = '{$cookie_value}'");

		if($token && $token->full()){			
			$email = $token->get(0)->email;
			return USER::byEmail($email);
		}else{
			return false;
		}
	}

	static public function auth_update($email){	
				
		global $CFG;		
		$device = self::get_device_info();

		//user coockie
		$cookie_name = "CHMENU_USER";
		$token = md5($email."/".$device);
		$expire_date = time() + $CFG->user_cookie_time;
		setcookie($cookie_name, $token, ['expires' => $expire_date, 'path' => "/", 'domain' => "", 'secure' => false, 'httponly' => true]); 
		
		//agent coockie
		$cookie_agent = "CHMENU_AGENT";
		setcookie($cookie_agent, md5((string) $device), ['expires' => $expire_date, 'path' => "/", 'domain' => "", 'secure' => false, 'httponly' => true]); 
		
		$auth = new Smart_collect("one_time_auth","WHERE token = '{$token}'");
		if($auth&&$auth->full()){
			$a=$auth->get(0);
			$a->updated_date = 'now()';
			return $a->save();
		}else{		
			$a = new Smart_object("one_time_auth");
			$a->email = $email;
			$a->token = $token;
			$a->agent_info = $_SERVER["HTTP_USER_AGENT"];
			$a->updated_date = 'now()';
			return $a->save();
		}
	}	

	static public function byEmail($email){	
		$U = new Smart_collect("users","WHERE email='{$email}'");
		if(!$U->full()) return false;
		$USER = $U->get(0);
		return $USER->id?$USER:false;
	}

	static public function clear_cookie(){		
		$cookie_name = "CHMENU_USER";
		$token = $_COOKIE[$cookie_name] ?? "";
		$expire_date = time() - 100;
		setcookie($cookie_name, "", ['expires' => $expire_date, 'path' => "/", 'domain' => "", 'secure' => false, 'httponly' => true]); 
		$cookie_agent = "CHMENU_AGENT";
		$expire_date = time() - 100;
		setcookie($cookie_agent, "", ['expires' => $expire_date, 'path' => "/", 'domain' => "", 'secure' => false, 'httponly' => true]);
		if($token!==""){
			$q = "DELETE FROM one_time_auth WHERE token = '$token'";
			SQL::delete($q);
		}
		return true;
	}

/*
	PRIVATE: RETURN DEVICE INFO
*/

	static private function get_device_info(){	
		// get part of agent, except version browser
		$agent = $_SERVER["HTTP_USER_AGENT"];		
		$device = preg_match('/\([A-Z;:\- a-z\d_.\/]+\)/', (string) $agent, $match)?$match[0]:"Unknown";
		$device = preg_replace('/(rv:\d+.\d)/', "", $device);// fix firefox
		return $device;
	}

}
		
?>