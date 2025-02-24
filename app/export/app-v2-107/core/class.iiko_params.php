<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 	СОБИРАЕМ И ОБНОВЛЯЕМ ВСЕ ПАРАМЕТРЫ IIKO
 *  @param Smart_object $cafe
 *  
*/
class Iiko_params{
		
	private Smart_object $cafe;
	private Smart_object $iiko_params;	
	private string $API_KEY;
	private string $token; 
	private string $current_organization_id;
	private string $iiko_current_extmenu_id;
	private string $current_terminal_group_id;
	private string $current_terminal_group_status;	
	private array $arr_organizations;
	private array $arr_extmenus;
	private array $all_terminal_groups;
	private array $arr_terminals;
	private array $arr_tables;
	private array $arr_order_types;
	private array $ROUGH_DATA;

	function __construct(Smart_object $cafe){
		$this->cafe = $cafe;			
		$this->API_KEY = $cafe->iiko_api_key;
		if(empty($this->API_KEY)){ throw new Exception("not found iiko api key"); }
		return $this;
	}

	public function reload(): void{
		if(!$this->check_iiko_key()){ throw new Exception("not valid iiko api key"); }
		$this->ROUGH_DATA = [];			
		$this->read_iiko_params_for_cafe();
		$this->read_organizations_info();
		$this->read_extmenus_info();
		$this->read_terminals_info();
		$this->read_iiko_tables_info();
		$this->read_order_types();	
		$this->read_status_current_terminal_group();			
		$this->update_db();
	}

	public function get(): Smart_object{
		return $this->iiko_params;
	}

	public function get_token(): string{
		return $this->token;
	}
	
	public function get_current_organization(): array{
		$currentOrganization = null;
		$id = $this->current_organization_id;
		foreach($this->arr_organizations as $org){
			if($org["id"]===$id){
				$currentOrganization = $org;
				break;
			}
		}
		return $currentOrganization;
	}

	public function export(): array{
		return $this->iiko_params->export();		
	}

	public function read_status_current_terminal_group():void {					

		$url     = 'api/1/terminal_groups/is_alive';
		$headers = [
			"Content-Type"=>"application/json",
			"Authorization" => 'Bearer '.$this->token
		]; 
		$params  = [
			"organizationIds" => [$this->current_organization_id],
			"terminalGroupIds" => [$this->current_terminal_group_id],
		];
		$res = iiko_get_info($url,$headers,$params);
		
		if(!isset($res["isAliveStatus"]) || !count($res["isAliveStatus"])) {
			glogError(print_r($res,1));	
			$this->current_terminal_group_status = "Unknown";
		}else{
			$this->current_terminal_group_status = $res["isAliveStatus"][0]["isAlive"];
		}
		
		$this->ROUGH_DATA["STATUS_CURRENT_TERMINAL_GROUP"] = $res;

	}

	// PRIVATE FUNCS
	private function update_db(): bool{

		$this->iiko_params->organizations = json_encode($this->arr_organizations, JSON_UNESCAPED_UNICODE);			
		$this->iiko_params->terminal_groups = json_encode($this->arr_terminals, JSON_UNESCAPED_UNICODE);	
		$this->iiko_params->tables = json_encode($this->arr_tables, JSON_UNESCAPED_UNICODE);	
		$this->iiko_params->order_types = json_encode($this->arr_order_types, JSON_UNESCAPED_UNICODE);	
		$this->iiko_params->extmenus = json_encode($this->arr_extmenus, JSON_UNESCAPED_UNICODE);	;	
	
		$this->iiko_params->current_extmenu_id = $this->iiko_current_extmenu_id;
		$this->iiko_params->current_organization_id = $this->current_organization_id;
		$this->iiko_params->current_terminal_group_id = $this->current_terminal_group_id;	
		$this->iiko_params->current_terminal_group_status = $this->current_terminal_group_status;	
			
		$this->iiko_params->rough_data = json_encode($this->ROUGH_DATA, JSON_UNESCAPED_UNICODE);	
		$this->iiko_params->updated_date = 'now()';
	
		glog("------------------- IIKO LOADED ROUGH_DATA ---------------- \n".print_r($this->ROUGH_DATA,1));

		if($this->iiko_params->save()){	
			return true;			
		}else{
			throw new Exception("Can't save iiko_params for ".$this->iiko_params->id_cafe);
		}
	
	}

	private function check_iiko_key():bool{
		$url     = 'api/1/access_token';
		$headers = ["Content-Type"=>"application/json"];
		$params  = ["apiLogin" => $this->API_KEY];
		$res = iiko_get_info($url,$headers,$params);
		if( isset($res["errorDescription"]) ) {
			if(str_contains((string) $res["errorDescription"], "is not authorized")){				
				glogError("--unknown login");	
				return false;
			}else{
				glogError(print_r($res,1));
				return false;
			}		
		}
	
		if(!isset($res["token"])){
			glogError(print_r($res,1));
			return false;
		}else{
			$this->token = $res["token"];			
		}
	
		return true;
	}

	private function read_iiko_params_for_cafe(): void{
		$id_cafe = $this->cafe->id;
		$iiko_params_collect = new Smart_collect("iiko_params", "where id_cafe='".$id_cafe."'");
		if($iiko_params_collect->full()){
			$this->iiko_params = $iiko_params_collect->get(0);
		}else{
			$this->iiko_params = new Smart_object("iiko_params");
			$this->iiko_params->id_cafe = $id_cafe;		
			if(!$this->iiko_params->save()) throw new Exception("Something wrong. Can`t saving iiko params");
		}
	}

	private function read_organizations_info(): void{
		$this->arr_organizations = $this->iiko_get_organizations_info($this->token);
		if(!count($this->arr_organizations))throw new Exception("--unknown organization");
		$this->current_organization_id = $this->arr_organizations[0]["id"];	
		$this->ROUGH_DATA["ORGANIZATIONS"] = $this->arr_organizations;	
	}	

	private function read_extmenus_info(): void{
		$this->arr_extmenus = $this->iiko_get_extmenus_info($this->token);	
		if(!count($this->arr_extmenus))throw new Exception("--has not menus");
		$this->ROUGH_DATA["EXTERNALMENUS"] = $this->arr_extmenus;	
		$this->iiko_current_extmenu_id = $this->calc_new_current_extmenu_id($this->arr_extmenus, $this->iiko_params->current_extmenu_id);
	}	

	private function read_iiko_tables_info(): void{
		$arrTerminalGroupsIds = [];
		foreach($this->arr_terminals as $terminalGroup){
			array_push($arrTerminalGroupsIds, $terminalGroup["id"]);
		}
		$restaurantSections = $this->iiko_get_table_sections_info($this->token, $arrTerminalGroupsIds);				
		$this->ROUGH_DATA["TABLES"] = $restaurantSections;	
		$this->arr_tables = $this->iiko_parse_tables_res($restaurantSections);		
	}

	private function read_terminals_info(): void{
		$this->all_terminal_groups = $this->iiko_get_terminal_groups_info($this->token, $this->current_organization_id);	
		if(!count($this->all_terminal_groups))throw new Exception("--has not terminal groups");
		$this->ROUGH_DATA["TERMINALS"] = $this->all_terminal_groups;	
	
		// GET TERMINAL GROUPS FOR CURRENT ORGANIZATION
		$this->arr_terminals = $this->all_terminal_groups[0]['items'];
		if(!count($this->arr_terminals)) throw new Exception("--has not actual terminals");
		$this->current_terminal_group_id = $this->arr_terminals[0]['id'];

	}	

	private function iiko_parse_tables_res(array $restaurantSections): array{
		$arr = [];	
		if(is_array($restaurantSections) && count($restaurantSections)){
			foreach($restaurantSections as $section){
				array_push($arr,[
					'section_name'=>$section['name'],
					'section_id'=>$section['id'],
					'terminalGroupId'=>$section['terminalGroupId'],
					'tables'=>$section['tables']
				]);			
			}
		}
		return $arr;
	}

	private function read_order_types(): void{
		$the_order_types = $this->iiko_get_order_types_info($this->token, $this->current_organization_id);
		if(!count($the_order_types)) throw  new Exception("--cant receive order types");		
		$this->arr_order_types = $the_order_types[0]['items'];	
		$ROUGH_DATA["ORDER_TYPES"] = $the_order_types;		
	}

	private function iiko_get_organizations_info($token): array {
		$url     = 'api/1/organizations';
		$headers = [
			"Content-Type"=>"application/json",
			"Authorization" => 'Bearer '.$token
		]; 
		$params  = ['organizationIds' => null, 'returnAdditionalInfo' => true, 'includeDisabled' => true];
		$res = iiko_get_info($url,$headers,$params);
		return $res["organizations"] ?? [];
	}

	private function iiko_get_order_types_info(string $token, string $orgId): array {		
		$url     = 'api/1/deliveries/order_types';
		$headers = [
			"Content-Type"=>"application/json",
			"Authorization" => 'Bearer '.$token
		]; 
		$params  = ['organizationIds' => [$orgId]];
		$res = iiko_get_info($url,$headers,$params);
		return $res['orderTypes'] ?? [];
	}	

	private function iiko_get_extmenus_info($token): array {
		$url     = 'api/2/menu';
		$headers = [
			"Content-Type"=>"application/json",
			"Authorization" => 'Bearer '.$token
		]; 
		$params  = [];
		$res = iiko_get_info($url,$headers,$params);
		return $res["externalMenus"] ?? [];
	}	

	private function iiko_get_table_sections_info(string $token, array $arrTerminalGroupIds): array {		
		$url     = 'api/1/reserve/available_restaurant_sections';
		$headers = [
			"Content-Type"=>"application/json",
			"Authorization" => 'Bearer '.$token
		]; 
		$params  = ['terminalGroupIds' => [...$arrTerminalGroupIds], 'returnSchema' => true];				
		$res = iiko_get_info($url,$headers,$params);					
		return $res['restaurantSections'] ?? [];
	}

	private function iiko_get_terminal_groups_info($token, $orgId): array {
		$url     = 'api/1/terminal_groups';
		$headers = [
			"Content-Type"=>"application/json",
			"Authorization" => 'Bearer '.$token
		]; 
		$params  = [
			'organizationIds' => [$orgId], 
			'includeDisabled' => true
		];
		$res = iiko_get_info($url,$headers,$params);
		return $res["terminalGroups"] ?? [];
	}

	private function calc_new_current_extmenu_id(array $arr_extmenus, string $saved_current_extmenu_id): string{
		
		$new_current_extmenu_id = "";

		if(count($arr_extmenus)>0){		
			if(!empty($saved_current_extmenu_id)){			
				$filteredArray = array_filter($arr_extmenus, function ($m) {	
					global $saved_current_extmenu_id;				
					return $m['id'] === $saved_current_extmenu_id;
				});
				if(count($filteredArray)>0){
					// оставляем как есть 
					$new_current_extmenu_id = $saved_current_extmenu_id;
				}else{
					// берем первое меню из списка
					$new_current_extmenu_id = $arr_extmenus[0]["id"];
				}			
			}else{
				// берем первое меню из списка
				$new_current_extmenu_id = $arr_extmenus[0]["id"];
			}
		}

		return $new_current_extmenu_id;
	}

}

?>