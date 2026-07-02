<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');


use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class App{	
	
	static public $ERR_MESSAGE='';	

	static public function delete_cafe($cafe): void{
		global $CFG;
		
		glog("\n-------------- START DELETE CAFE, {$cafe->id} --------------\n");
		
		$all_menu = new Smart_collect("menu","where id_cafe={$cafe->id}");
		if($all_menu->full()){
			foreach($all_menu->get() as $menu){
				if($menu->valid()){
					App::delete_menu_with_items($menu);					
				}			
			}
		}	
		self::delete_qrcode($cafe->uniq_name);
		$cafe->delete();
	}

	static public function delete_menu_with_items($menu): void{
		$all_items = new Smart_collect("items","where id_menu={$menu->id}");
		if($all_items->full()){
			foreach($all_items->get() as $item){
				App::delete_item($item);
			}
		}
		$menu->delete();		
	}
	
	static public function delete_qrcode($uniq_name){
		global $CFG;
		
		if(!empty($uniq_name)){			
			
			$qrcode_name = mb_strtolower("{$uniq_name}/{$uniq_name}-qrcode.png");
			
			glog("qrcode name = {$qrcode_name}");

			$s3Client = new S3Client([
			    'version'     => 'latest',
			    'region'      => $CFG->S3['region'],
			    'credentials' => [
			        'key'    => $CFG->S3['key'],
			        'secret' => $CFG->S3['secret'],
			    ],
			    'endpoint' => $CFG->S3['endpoint'],
			    'use_aws_shared_config_files' => false
			]);			
			$arr = [
				['Key' => $qrcode_name],			
			];
			try {
				$result = $s3Client->deleteObjects([
				    'Bucket'  => $CFG->S3['bucket'],
				    'Delete' => ['Objects' => $arr ],
				]);

				if ($result["@metadata"]["statusCode"] == '200') {				    
					glog("{$qrcode_name}, qrcode was deleted");    
					return true;
				}else{
					glogError(json_encode($result,JSON_UNESCAPED_UNICODE));
					return false;						
				}
				
			} catch (S3Exception $e) {
				glogError($e->getMessage());
				return false;			    
			}
		}
	}

	static public function delete_item($item){		
		if(self::delete_item_image($item)) {
			return $item->delete();			 
		}else{
			return false;
		}
	}

	static public function delete_item_image($item){
		global $CFG;

		$imageName = stripcslashes(trim((string) $item->image_name));

		if(!empty($imageName)){

			$s3Client = new S3Client([
			    'version'     => 'latest',
			    'region'      => $CFG->S3['region'],
			    'credentials' => [
			        'key'    => $CFG->S3['key'],
			        'secret' => $CFG->S3['secret'],
			    ],
			    'endpoint' => $CFG->S3['endpoint'],
			    'use_aws_shared_config_files' => false
			]);

			$imageNameSmall = substr($imageName,0,-4)."-s.jpg";

			$arr = [
				['Key' => $imageName],
				['Key' => $imageNameSmall]
			];

			try {
			
				$result = $s3Client->deleteObjects([
				    'Bucket'  => $CFG->S3['bucket'],
				    'Delete' => ['Objects' => $arr ],
				]);
				glog("images was delete: ".json_encode($arr, JSON_UNESCAPED_UNICODE));
				return true;

			} catch (S3Exception $e) {

				glogError($e->getMessage());				
				return false;			    
			}			

		}else{

			return true;

		}
	}

}
		
?>