<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 	ОБНОВЛЯЕМ В БД ИМЕНА-ССЫЛКИ НА МЕНЮ ДЛЯ СТОЛИКОВ
 * 
*/
class Qr_tables{

	/**
	 *  @param Smart_object|int $CAFE // required
	 *  @param $total int|undefined // maximum tables amount (from config)
	*/	
	static public function recreate_all_for(Smart_object|int $CAFE, int|undefined $total=50): array{
		$arr_names = [];
		for($table_number=0;$table_number<$total;$table_number++){
			$table_uniq_name = self::generate_name_for($table_number+1);
			$arr_names[]=$table_uniq_name;
		}
		self::update_cafe_info($CAFE, $arr_names);
		return $arr_names;
	}

	static private function update_cafe_info(Smart_object|int $CAFE, array $arr_names): bool {
		if(gettype($CAFE)==='int'){ $CAFE = new Smart_object("cafe", $CAFE); }		
		if(!$CAFE || !$CAFE->valid()) throw new Exception("unknown cafe, ".__FILE__.", ".__LINE__);
		$CAFE->tables_uniq_names = json_encode($arr_names, JSON_UNESCAPED_UNICODE);
		$CAFE->updated_date = 'now()';
		$CAFE->rev+=1;
		if(!$CAFE->save()){
			throw new Exception("can't save information to cafe ".$CAFE->id.", ".__FILE__.", ".__LINE__);
		}
		return true;
	}

	static private function generate_name_for($table_number): string {
		return $table_number."-".get_random_string(4);
	}	
	
	/**
	 *  GENERATING QR-CODES AND LINKS FOR MENU TABLE
	 * 
	 *  @param Smart_object $CAFE
	 * 	@param array $arr_tables // [int number]
	 *  @return array
	*/
	static public function make_qrcodes( Smart_object $CAFE, array $arr_tables): array{
		global $CFG; 
		$arr = [];				

		$tables_uniq_names = $CAFE->tables_uniq_names ? json_decode((string) $CAFE->tables_uniq_names, 1) : [];
		if($arr_tables && count($arr_tables)){
			foreach($arr_tables as $table_number){
				$num = (int) $table_number;
				if(isset($tables_uniq_names[$num-1])){
					$table_name = "Стол {$num}";
					$table_uniq_name = $tables_uniq_names[$num-1];
					$link = $CFG->wwwroot."/cafe/".mb_strtolower($CAFE->uniq_name)."/table/".$table_uniq_name;
					$qr_image = rds_qrcode_create_from($link);
					$arr[] = [
						"table_number"=>$num,
						"table_name"=>$table_name,				
						"table_link"=>$link,
						"table_qr_image"=>$qr_image
					];
				}
			}
		}		

		glog("\$arr=============\n ".print_r ($arr,true));

		return $arr;
	}

}


?>