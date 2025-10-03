<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 	ПОЛУЧАЕМ ВНЕШНЕЕ МЕНЮ ИЗ IIKO v-1.0.0
 * 
 *  @param <string> $id_organization
 *  @param <string> $iiko_api_key
 * 
*/
class Iiko_extmenu_loader{

	private string $ID_ORG;
	private string $IIKO_API_KEY;
	private string $EXTERNAL_MENU_ID;
	private array $DATA;
	private string $TOKEN;
	private string $INFO;
	
	/**
	 * @param <string> $id_org / required
	 * @param <string> $iiko_api_key / required
	 * @return <Iiko_extmenu_loader>
	 * 
	*/
	function __construct(string $id_org, string $iiko_api_key="", $externalMenuId = ""){
		$this->ID_ORG = $id_org;
		$this->IIKO_API_KEY = $iiko_api_key;	
		$this->EXTERNAL_MENU_ID = $externalMenuId;
		return $this;
	}

	public function reload(): void{		

		if(empty($this->TOKEN)){ $this->TOKEN = $this->reload_token(); }
		$this->DATA = $this->load_extmenu();
		// collecting a meta information about the datamenu
		$this->INFO = $this->calc_meta_info($this->DATA);
	}

	public function get_data(): array{
		return $this->DATA;
	}

	public function get_info(): string{
		return $this->INFO;
	}

	// ---------------
	// PRIVATE METHODS
	// ---------------
	private function reload_token(): string {
		// GETTING TOKEN FROM IIKO 
		$url     = 'api/1/access_token';
		$headers = ["Content-Type"=>"application/json"];
		$params  = ["apiLogin" => $this->IIKO_API_KEY];
		$res = iiko_get_info($url,$headers,$params);		
		return $res['token'];
	}

	private function load_extmenu(): array{
		$url     = 'api/2/menu/by_id';
		$headers = [
			"Content-Type"=>"application/json",
			"Authorization" => 'Bearer '.$this->TOKEN
		]; 
		$params  = [
			'externalMenuId' => $this->EXTERNAL_MENU_ID,
			'organizationIds' => [$this->ID_ORG], 
			'priceCategoryId' => null, 
			'version' => 2
		];
		$res = iiko_get_info($url,$headers,$params);
		return $res;
	}

	private function calc_meta_info(array $iiko_response): string{		
		// размер исходных данных от iiko:
		$size_iiko = strlen(serialize($iiko_response)); 
		$size_iiko = round($size_iiko / 1024 / 1024 ) . " MB";
		$vars_iiko = $this->count_recursive($iiko_response);
		$infoMsg = "Размер исходных данных от iiko: ~" . $size_iiko . ", ";
		$infoMsg .= 'Переменных в исходных данных от iiko: ' . $vars_iiko;
		return $infoMsg;
	}

	// Для глубокого подсчёта переменных в res from iiko:
	private function count_recursive(array $arr) {
		$count = 0;
		array_walk_recursive($arr, function() use (&$count) {
			$count++;
		});
		return $count;
	}

}

?>