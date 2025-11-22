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

		if((int) $this->iiko_params->nomenclature_mode === 1){
			$menu_id = null;
		}else{
			$menu_id = (string) $this->iiko_params->current_extmenu_id;
		}		
		
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
			"menuId"=>$menu_id,
			"items"=>$order_items,
			"combos"=>"",
			"payments"=>"",
			"tips"=>"",
			"orderTypeId"=>$order_type_id,
			"chequeAdditionalInfo"=> "",
		];

		return $order;
	}

	// ---------------------------------------------------
	// extract sizes to modifiers of size (like pizzaiolo)
	// ---------------------------------------------------
	public function remake_for_nomenclature($order_items): array{		
		
		$re_order_items = array_map(function($item){

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
					"modifierGroupId" => $item["sizeGroupId"]??"",
					"name" => $item["sizeName"]??"Размер",
					"description" => $item["description"]??"",
					"imageUrl" => $item["imageUrl"]??"",
					"portionWeightGrams" => $item["volume"]??"",
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
			return $res;

		}, $order_items);

		return $re_order_items;
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
		
		// glog("=== prepare_items === ". print_r($order_rows,1));

		$order_items = [];

		foreach($order_rows as $row){
			$amount = (int) $row['count'];
			$productSizeId = $row['sizeId']??false;
			$productId =  $row['item_data']['id_external'];
			$orderItemType = $row['item_data']['iiko_order_item_type'];

			$productPrice = (int) $row["price"] ;
						
			if(empty($orderItemType)) $orderItemType="Product";
			$chosenModifiers = $row['chosen_modifiers']??false;				

			$item = [
					"type"=>"{$orderItemType}",
					"amount"=> (int) $amount,
					"price"=> (int) $productPrice,					
				];
		
			if(!empty($productSizeId)) $item["productSizeId"]=$productSizeId;
			
			$modifiers = [];
			if($chosenModifiers && count($chosenModifiers)){		
				foreach($chosenModifiers as $m){
					$mod = [ 
						'productId'=>$m['modifierId'], 
						'amount' => 1,
						'price' => (int) $m['price'],				
					 ];
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

	private function get_table_id_by_number( int $table_number ): string{		

		$tbls = $this->iiko_params->tables;
		$tbls = !empty($tbls)?json_decode($tbls,true):false;

		$current_terminal_group_id = $this->iiko_params->current_terminal_group_id;		

		if(!$tbls || empty($current_terminal_group_id)) throw new Exception("--cant find iiko tableIds for the cafe, 1");
		if(mb_strtolower(gettype($tbls))!=='array' || !count($tbls)) throw new Exception("--cant find iiko tableIds for the cafe, 2");				

		$section = array_filter($tbls, function($s) use ($current_terminal_group_id) {
			return $s['terminalGroupId'] == $current_terminal_group_id;
		});				


		if(!count($section)) throw new Exception("--cant find iiko tableIds for the cafe, 3");		

		// UNION ALL TABLES FROM ALL FOUND SECTIONS
		$all_tables = [];		
		foreach($section as $s){
			$all_tables = array_merge($all_tables, $s['tables']);
		}

		$current_table = array_filter($all_tables, function($table) use ($table_number) {
			return $table['number'] == $table_number;
		});
		
		// glog("CURRENT_TABLE", print_r($current_table,1));

		if(!count($current_table)) throw new Exception("--cant find iiko tableIds for the cafe, 4");

		$firstKey = array_key_first($current_table);
		$table_id = $current_table[$firstKey]['id'];

		// glog("TABLE_ID = ".$table_id);

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