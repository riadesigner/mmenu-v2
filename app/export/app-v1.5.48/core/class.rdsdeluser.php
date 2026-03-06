<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class RDSDeluser{	

	static private $ERR_MESSAGE = "";
	static private $User = false;

	static public function valid_email_key($email,$key){
		global $CFG;
		if($email=="" || $key==""){			
			return false;
		}else{
			$user = User::byEmail($email);			
			if(!$user || !$user->valid()) {
				self::log_error("unknown user, {$user}");
				return false;	
			}else{
				self::$User = $user;
				$ok_key = self::calculate_key($user);	
				if($ok_key===$key){					
					return self::$User;
				}else{
					self::log_error("unknown key");
					return false;
				}			
			}
		}
	}

	static public function get_err_message(){
		return self::$ERR_MESSAGE;
	}

	static public function get_link_by_user($user){
		global $CFG;
		if(!$user || !$user->valid())return false;
		$key = self::calculate_key($user);
		$link = $CFG->http."{$CFG->admin_sub}.{$CFG->wwwroot}/deluser/$user->email/$key";
		return $link;
	}

	static public function delete_all_about_user($email,$key){
		global $CFG;
		
		$ALLOW_USER_DELETE = true;

		if(!RDSAdmin::authorised()) { 
			self::log_error("Нужна авторизация");			
			return false;
		}

		if($user = self::valid_email_key($email,$key)){	

			$cafes = new Smart_collect("cafe","WHERE id_user={$user->id}");
			if($cafes&&$cafes->full()){

				foreach ($cafes->get() as $cafe) {
					
					$contracts = new Smart_collect("contracts","WHERE id_cafe={$cafe->id}");

					if($contracts && $contracts->full()){
						$ALLOW_USER_DELETE = false;
						// $arr = array();
						// foreach ($contracts->get() as $con) { $arr[] = $con->contract_name; }
						// $contract_names = implode(", ", $arr);
						break;
					}else{
						$contract_names = "";
					}

					// delete telegram chat link
					$tg_users = new Smart_collect('tg_users',"WHERE cafe_uniq_name='{$cafe->uniq_name}'");
					if($tg_users&&$tg_users->full()){
						foreach($tg_users->get() as $tg_user){
							$tg_user->delete();
						}
					}

					// save information about deleted cafes
					$cafeRemoving = new Smart_object("log_cafe_removing");
					$cafeRemoving->cafe_uniq_name = $cafe->uniq_name;
					$cafeRemoving->user_owner_email = $user->email;
					$cafeRemoving->created_date = $cafe->created_date;
					$cafeRemoving->removed_date = "now()";
					$cafeRemoving->contracts = $contract_names;
					$cafeRemoving->save();

					App::delete_cafe($cafe);

				}

			}



			if(!$ALLOW_USER_DELETE){
				self::log_error("cant delete, because cafe has contracts");
				return false; 
			}else{
				return $user->delete();	
			}
			
		}else{
			self::log_error("wrong access link");
			return false;	
		}
	}	

	static private function calculate_key($user){
		return md5("user to delete: $user->id/$user->email/$user->regdate");
	}

    static public function log($msg): void{
        global $CFG;
		glog($msg);
    }

    static public function log_error($msg): void{
        global $CFG;
        self::$ERR_MESSAGE = $msg;
        glogError($msg);
    }   

}
		
?>