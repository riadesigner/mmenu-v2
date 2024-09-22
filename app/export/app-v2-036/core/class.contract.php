<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Contract{	
	
	private $_ID_CAFE = 0;
	private $_ID_USER = 0;
	private $_FULL = false; // cafe has contracts
	private $_TOTAL = 0; 
	private $_ARR = [];

	function __construct($cafe){

		if($cafe && $cafe->id){
			$this->_ID_CAFE = (int) $cafe->id;
			$this->_ID_USER = (int) $cafe->id_user;
			$this->_ARR = $this->_collect_all();
		}
		return $this;
	}

	// PUBLIC
	public function get(){
		return $this->_ARR;
	}
	public function full(){
		return $this->_FULL;
	}
	public function total(){
		return $this->_TOTAL;
	}		
	public function make_new_name(){
		$nextNumber = $this->_TOTAL+1;
		return "{$this->_ID_USER}-{$this->_ID_CAFE}-{$nextNumber}";
	}

	// PRIVATE
	private function _collect_all(){
		if(!empty($this->_ID_CAFE)){
			$contracts = new Smart_collect("contracts", "WHERE id_cafe='{$this->_ID_CAFE}'" );
			if($contracts && $contracts->full()){
				$this->_FULL = true;
				$this->_TOTAL = $contracts->total();
				return $contracts->get();
			}else{
				return [];
			}
		}else{
			return [];
		}
	}

}
		
?>