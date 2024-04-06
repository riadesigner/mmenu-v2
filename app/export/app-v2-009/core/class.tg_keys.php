<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tg_keys{

	static public function get($cafe_uniq_name){
		
		if(!self::check_cafe($cafe_uniq_name)) return false;

		$tg_keys = new Smart_collect("tg_keys","where cafe_uniq_name = '{$cafe_uniq_name}'");

		if($tg_keys && $tg_keys->full()){							
			$ARR_KEYS = [];
			foreach($tg_keys->get() as $key){
				array_push($ARR_KEYS,$key->export());
			}
			return $ARR_KEYS;
		}else{
			return false;
		}				
	}

	static public function update_all($cafe_uniq_name){		

		if(!self::check_cafe($cafe_uniq_name)) return false;

		$tg_keys = new Smart_collect("tg_keys","WHERE cafe_uniq_name='{$cafe_uniq_name}'");
		if($tg_keys&&$tg_keys->full()){			
			foreach($tg_keys->get() as $key){ $key->delete();}
		}

		$ARR_NEW_TG_KEYS = [
			'waiter'=>self::generate_tg_key($cafe_uniq_name),
			'manager'=>self::generate_tg_key($cafe_uniq_name),
			'supervisor'=>self::generate_tg_key($cafe_uniq_name),
		];		
	
		$ARR_KEYS = [];

		foreach($ARR_NEW_TG_KEYS as $role=>$key){
			$tg_key = new Smart_object('tg_keys');
			$tg_key->cafe_uniq_name = $cafe_uniq_name;
			$tg_key->tg_key = $key;
			$tg_key->role = $role;
			$tg_key->regdate = 'now()';
			$tg_key->save();
			array_push($ARR_KEYS,$tg_key->export());
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

	static private function generate_tg_key($cafe_uniq_name){
		$chars="abcdefghkmnopqrstuvwyz0123456789";
		$max=4; 
		$size=StrLen($chars)-1;
		$tgkey=null;
    	while($max--) 
    	$tgkey.=$chars[random_int(0,$size)]; 
    	return $cafe_uniq_name.":".$tgkey;
	}

}


?>