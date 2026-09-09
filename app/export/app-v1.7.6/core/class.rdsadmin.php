<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class RDSAdmin{	
	
	static private $SSNAME = 'RDS_CHMENU_ADMIN';	
	
	static public function authorised($login="",$md5pass=""){
		global $CFG;
		
		
		$token = self::calculate_token($CFG->admin_login,md5((string) $CFG->admin_pass));

		glog("try autho");

		isset($_SESSION[self::$SSNAME]) && glog("S1 ".$_SESSION[self::$SSNAME]);

		if($login=="" || $md5pass==""){				

			if(isset($_SESSION[self::$SSNAME])){
				glog("sess ".$_SESSION[self::$SSNAME]);	
			}else{
				glog("sess not exist");
			} 
			
			if(isset($_SESSION[self::$SSNAME]) && $_SESSION[self::$SSNAME]===$token){
				return $token;	
			}else{
				return false;
			}			

		}else{			

			glog("NEW ADMIN SESS");
			
			$ok_token = self::calculate_token($login,$md5pass);

			if($ok_token===$token){				
								
				$_SESSION[self::$SSNAME] = $token;					

				return $_SESSION[self::$SSNAME];

			}else{
				glog("TEST FAILED!!");
				return false;
			}
		}
	}

	static public function logout(){
		if(isset($_SESSION[self::$SSNAME])){
			unset($_SESSION[self::$SSNAME]);
		}
		return true;
	}

	static private function calculate_token($login,$md5pass){		
		$token = md5($login."/".$md5pass."/".$_SERVER["HTTP_USER_AGENT"]."/".session_id());
		glog("============== start");
		glog("calc token for:"); 
		glog("login = $login,\n pass = $md5pass,\n server = ".$_SERVER["HTTP_USER_AGENT"].",\n ".$token);
		glog("============== end \n");
		
		return $token; 
	}

}
		
?>