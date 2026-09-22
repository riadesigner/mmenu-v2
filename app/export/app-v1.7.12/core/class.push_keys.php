<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Push_keys{

	static public function get($cafe_uniq_name){
		
		if(!self::check_cafe($cafe_uniq_name)) return false;

		$push_keys = new Smart_collect("push_keys","where cafe_uniq_name = '{$cafe_uniq_name}'");

		if($push_keys && $push_keys->full()){							
			$ARR_KEYS = [];
			foreach($push_keys->get() as $key){
				array_push($ARR_KEYS,$key->export());
			}
			return $ARR_KEYS;
		}else{
			return false;
		}				
	}

	static public function update_all($cafe_uniq_name){		
		
		glog("Push_keys:update_all, creating keys for $cafe_uniq_name, file:".__FILE__);

		if(!self::check_cafe($cafe_uniq_name)) return false;

		$push_keys = new Smart_collect("push_keys","WHERE cafe_uniq_name='{$cafe_uniq_name}'");
		if($push_keys&&$push_keys->full()){			
			foreach($push_keys->get() as $key){ $key->delete();}
		}

		$ARR_NEW_PUSH_KEYS = [
			'waiter'=>self::generate_push_key($cafe_uniq_name),
			'manager'=>self::generate_push_key($cafe_uniq_name),
			'supervisor'=>self::generate_push_key($cafe_uniq_name),
		];		

		$ARR_KEYS = [];

		foreach($ARR_NEW_PUSH_KEYS as $role=>$key){
			$push_key = new Smart_object('push_keys');
			$push_key->cafe_uniq_name = $cafe_uniq_name;
			$push_key->push_key = $key;
			$push_key->role = $role;
			$push_key->regdate = 'now()';
			$push_key->save();
			array_push($ARR_KEYS,$push_key->export());
		}		

		return $ARR_KEYS;
	}		

	/* private */

	static private function check_cafe($cafe_uniq_name){
		$arr = new Smart_collect("cafe","WHERE uniq_name='{$cafe_uniq_name}'");
		if($arr&&$arr->full()){
			$cafe = $arr->get(0);
			return $cafe;
		}else{			
			// no found cafe with such uniq_name 
			return false;
		}	
	}

	static private function generate_push_key($cafe_uniq_name){				
		$token = bin2hex(random_bytes(8));
    	return $cafe_uniq_name."-".$token;
	}

}


?>