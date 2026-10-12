<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 	ЧИТАЕМ IIKO_PARAMS ДЛЯ КАФЕ ИЗ БД CHEFSMENU
 * 
 *  данный класс специально для pbl –
 *  он только читает переменные, 
 *  но не пишет в БД и не обновляет
 * 
*/
class Iiko_params_reader{
	private int $id_cafe;
	private Smart_object $iiko_params;		

	function __construct(int $id_cafe){
		$this->id_cafe = $id_cafe;			
		$this->iiko_params = $this->read_params_for_cafe($id_cafe);
		return $this;
	}

	public function get(): Smart_object{
		return $this->iiko_params;
	}

	// -------------
	// PRIVATE FUNCS
	// -------------
	private function read_params_for_cafe(int $id_cafe): Smart_object{		
		$iiko_params_collect = new Smart_collect("iiko_params", "where id_cafe='".$id_cafe."'");
		if($iiko_params_collect->full()){
			return $iiko_params_collect->get(0);			
		}else{
			return null;
		}		
	}

}

?>