<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 	ГОТОВИМ ЗАКАЗ ДЛЯ IIKO
 *  @param Smart_object $cafe
 *  
*/
class Iiko_order{
		
	private Smart_object $cafe;
	private Smart_object $iiko_params;

	function __construct(Smart_object $cafe){
		$this->cafe = $cafe;
		$this->load_iiko_params();
		return $this;
	}

	/**
	 * BUILDING ORDER ARRAY FOR SENDING TO IIKO
	 * @param array $order_rows;
	 * @return array;
	 */
	public function prepare_order_for_table(int $table_number, array $order_rows): array{
						
		$menu_id = $this->iiko_params->current_extmenu_id;
		$table_id = $this->get_table_id_by_number($table_number);
		if($table_id===null){throw new Exception("--cant calculate iiko table_id");}		

		$order_items = $this->prepare_items($order_rows);		
		$order_type_id = $this->get_id_for_tables_type_order();

		$order = [
			"id"=>"",
			"externalNumber"=>"",
			"tableIds"=>[$table_id],
			"customer"=>"",
			"phone"=>"",
			"guests"=>"",
			"tabName"=>"",
			"menuId"=>"{$menu_id}",
			"items"=>$order_items,
			"combos"=>"",
			"payments"=>"",
			"tips"=>"",
			"orderTypeId"=>$order_type_id,
			"chequeAdditionalInfo"=> "",
		];

		return $order;
	}

	public function remake_for_nomenclature($order_items): array{
		
		glog("-------ORDER ITEMS BEFORE------- \n".print_r($order_items,1));
		
		$order_items = array_map(function($item){

			$res = [
				"itemId" => $item["itemId"],
				"uniq_name" => $item["uniq_name"],				
				"count" => $item["count"],
				"volume" => $item["volume"],
				"units" => $item["units"],
				"item_data"	=> $item["item_data"],
			];			

			if((int) $item["originalPrice"] > 0){
				// --------------------------------------------------------
				// преобразуем размерный ряд обратно в модификатор размеров
				// --------------------------------------------------------
				// убираем размерный ряд				
				$originalPrice = (int) $item["originalPrice"];
				$price =  (int) $item["price"] - $originalPrice;
				$chosen_modifiers = $item["chosen_modifiers"] ?? [];
				// добавляем модификатор размера				
				$chosen_modifiers[] = 
					[
					"modifierId" => $item["sizeId"],
					"name" => $item["name"],
					"description" => $item["description"],
					"imageUrl" => $item["imageUrl"],
					"portionWeightGrams" => $item["portionWeightGrams"],
					"price" => $originalPrice,															
					];	
				$res["price"] = $price;	
				$res["chosen_modifiers"] = $chosen_modifiers;	

				$res["sizeName"] = "";
				$res["sizeId"] = "";
				$res["sizeCode"] = "";
				$res["originalPrice"] = $item["originalPrice"];

			}else{

				$res["price"] = $item["price"];	
				
				if(isset($item["chosen_modifiers"])){
					$res["chosen_modifiers"] = $item["chosen_modifiers"];
				}
				
				$res["sizeName"] = $item["sizeName"];
				$res["sizeId"] = $item["sizeId"];
				$res["sizeCode"] = $item["sizeCode"];
				$res["originalPrice"] = $item["originalPrice"];				
			}


		}, $order_items);		

		return $order_items;
	} 
	
	private function load_iiko_params(): void{
		$iiko_params_collect = new Smart_collect("iiko_params","where id_cafe='".$this->cafe->id."'"); 
		if(!$iiko_params_collect->full()) throw new Exception("--iiko psrams not found for the cafe ".$cafe->id);
		$this->iiko_params = $iiko_params_collect->get(0);
	}

	/**
	 * PREPAIRING ORDER ITEMS
	 * @param array $order_rows;
	 */
	private function prepare_items($order_rows): array{
		
		$order_items = [];

		foreach($order_rows as $row){
			$amount = $row['count'];
			$productSizeId = $row['sizeId'];
			$productId =  $row['item_data']['id_external'];
			$orderItemType = $row['item_data']['iiko_order_item_type'];
			$chosenModifiers = isset($row['chosen_modifiers'])?$row['chosen_modifiers']:false;
		
			$item = [
					"type"=>"{$orderItemType}",
					"amount"=>"{$amount}",			
				];
		
			if(!empty($productSizeId)) $item["productSizeId"]=$productSizeId;
			///xxx
			$modifiers = [];
			if($chosenModifiers && count($chosenModifiers)){		
				foreach($chosenModifiers as $m){
					$mod = [ 'productId'=>$m['modifierId'], 'amount'=>1 ];
					if(!empty($m['modifierGroupId'])) $mod['productGroupId'] = $m['modifierGroupId'];				
					$modifiers[] = $mod;			
				}		
			}
		
			if(mb_strtolower($orderItemType)==="product"){
				// Order Type Product
				$item["productId"] = $productId;		
				if(count($modifiers)) $item["modifiers"]=$modifiers;
			}else if(mb_strtolower($orderItemType)==="compound"){			
				// Order Type Compound
				$item["primaryComponent"] = [ "productId" => $productId ];
				if(count($modifiers)) $item["commonModifiers"]=$modifiers;
			}
		
			array_push($order_items,$item);
		
		}		
		
		return $order_items;

	}

	private function get_table_id_by_number($table_number): string{		

		$tbls = $this->iiko_params->tables;
		$tbls = !empty($tbls)?json_decode($tbls,true):false;
		if(!$tbls) throw new Exception("--cant find iiko tableIds for the cafe");

		$table_id = null;
		$stop_search = false;	
		if(gettype($tbls)=='array' && count($tbls)){
			foreach($tbls as $section){
				if($stop_search) break;			
				$arr_tbls = $section['tables'];
				if(gettype($arr_tbls)=='array' && count($arr_tbls)){
					foreach($arr_tbls as $tbl){
						if((int)$tbl['number']==(int)$table_number){
							$table_id = $tbl['id'];
							$stop_search = true;
							break;
						}
					}
				}
			}
		}
		return $table_id;
	}	

	private function get_id_for_tables_type_order(): string{
		
		$order_types = $this->iiko_params->order_types;
		$order_types = !empty($order_types)?json_decode($order_types,true):[];
		
		if(!count($order_types)) throw new Exception("--cant find iiko order types");
		
		$order_type_id = "";
		foreach($order_types as $o_type){
			// regular order
			if(mb_strtolower($o_type['orderServiceType']) === 'common'){
				$order_type_id = $o_type['id'];
				break;
			}
		}
		return $order_type_id;
	}
}


?>