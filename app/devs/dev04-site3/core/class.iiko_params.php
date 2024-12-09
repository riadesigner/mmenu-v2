<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 	СОХРАНЯЕМ IIKO ПАРАМЕТРЫ ДЛЯ КАФЕ
 *  @param Smart_object $cafe
 *  
*/
class Iiko_params{
		
	private Smart_object $cafe;
	private array|null $params;
	private bool $found = false;

	function __construct(Smart_object $cafe){
		$this->cafe = $cafe;		
		$this->load_params();
		return $this;
	}

	public function get(string $name=""){
		if(!empty($name) && isset($this->params[$name])){
			return $this->params[$name];
		}else{
			return $this->params;
		}		
	}

	public function get_empty(): array{
		return [
			"organizations"=>"",
			"extmenus"=>"",	
			"tables"=>"",	
			"terminal_groups"=>"",	
			"order_types"=>"",	
			"current_extmenu_id"=>"",
			"current_extmenu_hash"=>"",
		];		
	}

	public function found(): bool{
		return $this->found;
	}
	
	private function load_params(): void{
		$cafe = $this->cafe;
		$iiko_params_collect = new Smart_collect("iiko_params","where id_cafe='".$cafe->id."'");
		if($iiko_params_collect->full()){
			$this->params = ($iiko_params_collect->get(0))->export();
			$this->found = true;
		}		
	}
}


?>