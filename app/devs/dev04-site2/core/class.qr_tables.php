<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 	ОБНОВЛЯЕМ В БД ИМЕНА-ССЫЛКИ НА МЕНЮ ДЛЯ СТОЛИКОВ
 * 
*/
class Qr_tables{

	/**
	 *  @param Smart_object|int $CAFE // required
	 *  @param int|null $total_tables // optional (for CHEFSMENU MODE)
	 *  @param array // $tables_uniq_names
	*/
	static public function update(Smart_object|int $CAFE, int|null $total_tables=null): array{
		
		if(gettype($CAFE)==='int'){
			$CAFE = new Smart_object("cafe", $CAFE);			
		}

		if(!$CAFE || !$CAFE->valid()) throw new Exception("unknown cafe, ".__FILE__.", ".__LINE__);

		if($total_tables===null){
			// ----------------
			//  FOR IIKO MODE	
			// ----------------
			$tables_uniq_names = self::generate_by_iiko_info($CAFE);
			return $tables_uniq_names;
		}else{
			// --------------------------
			// TODO (FOR CHEFSMENU MODE)
			// --------------------------
			throw new Exception("unrealized function, ".__FILE__.", ".__LINE__);
		}
	}

	/**
	 *  GENERATING QR-CODES AND LINKS FOR MENU TABLE
	 * 
	 *  @param Smart_object $CAFE
	 * 	@param array $arr_tables // [string id (optional), string name, int number]
	 *  @return array
	*/
	static public function make_qrcodes( Smart_object $CAFE, array $arr_tables): array{
		global $CFG; 
		$arr = [];
		$tables_uniq_names = $CAFE->tables_uniq_names ? json_decode((string) $CAFE->tables_uniq_names, 1) : [];
		foreach($arr_tables as $table){
			$name = "table-".$table['number'];
			if(!isset($tables_uniq_names[$name])) throw new Exception("unknown table uniq_name, ".__FILE__.", ".__LINE__);
			$uniq = $tables_uniq_names[$name];
			$link = $CFG->wwwroot."/cafe/".mb_strtolower($CAFE->uniq_name)."/table/".$uniq;
			$qr_image = rds_qrcode_create_from($link);
			$arr[] = [
				"table_number"=>$table['number'],
				"table_name"=>$table['name'],				
				"table_link"=>$link,
				"table_qr_image"=>$qr_image
			];
		}
		return $arr;
	}

	// PRIVATE
	static private function generate_by_iiko_info($CAFE): array{

		glog("TEST123 generate_by_iiko_info!!!");
		// ----------------------------------
		//  tables info, saved from iiko api	
		// ----------------------------------
		$iiko_tables = $CAFE->iiko_tables ? json_decode((string) $CAFE->iiko_tables, 1) : [];
		if(!count($iiko_tables)) return [];
		
		$arr_tables = [];
		foreach($iiko_tables as $section){ 
			$tables = $section['tables'];
			if(count($tables)){
				foreach($tables as $table){
					array_push($arr_tables, $table);
				}				
			}			
		}		
		if(!count($arr_tables)) return [];
		glog("TEST!!! ".print_r($arr_tables,1));

		$uniqs_was_modified = false;
		// --------------------------------------
		//  getting uniq_keys already generated	
		// --------------------------------------
		$tables_uniq_names = $CAFE->tables_uniq_names ? json_decode((string) $CAFE->tables_uniq_names, 1) : [];
	
		// ------------------------------------------------------
		//  generating table_uniq_name only for new table-number
		// ------------------------------------------------------
		foreach($arr_tables as $table){
			$table_number = "table-".$table["number"];
			// if it's new table-number
			if(!isset($tables_uniq_names[$table_number])){
				$uniq_name = $table["number"]."-".get_random_string(16);
				$tables_uniq_names[$table_number] = $uniq_name;
				// needs update DB
				$uniqs_was_modified = true;
			}
		}	
		
		// -------------------------------
		//  update DB (tables_uniq_names)
		// -------------------------------
		if($uniqs_was_modified){
			$CAFE->tables_uniq_names = json_encode($tables_uniq_names, JSON_UNESCAPED_UNICODE);
			$CAFE->updated_date = 'now()';
			$CAFE->rev+=1;
			if(!$CAFE->save()){
				throw new Exception("can't save information to cafe ".$CAFE->id.", ".__FILE__.", ".__LINE__);
			}
		}

		return $tables_uniq_names;		
	}


}


?>