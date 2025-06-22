<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use chillerlan\QRCode\{QRCode, QROptions};

class Account{

	static private $email = ""; 
	static private $key = "";
	static private $emailValid = "";
	static private $keyValid = "";
	static private $ERR_MESSAGE="";
	static private $cafeTitle="";

	static public function init($input_email,$input_key,$cafeTitle=""){		
		self::$email = substr((string) $input_email, 0, 255);
		self::$key = substr((string) $input_key, 0, 255);
		self::$cafeTitle = substr((string) $cafeTitle, 0, 100);
		self::$emailValid = preg_match("|^[0-9a-z_\.]+@[0-9a-z_^\.]+\.[a-z]{2,6}$|i", self::$email);
		self::$keyValid = md5("new-email:".self::$email) === self::$key;
		return (!self::$email || !self::$key || !self::$emailValid || !self::$keyValid)?false:true;
	}

	static public function already_activated(){
		if(self::$emailValid && $User = User::byEmail(self::$email)){
			return $User;
		}else{
			return false;
		}
	}	

	static public function activate($lang='ru'){

		$password = generate_password();
		$user_id = self::add_new_user_to_db(self::$email,$password,$lang);
			
		glog("CLASSS_ACCOUNT:activate, $password, $user_id");		

		if($user_id){

			if($cafe = self::create_cafe_for($user_id,$lang)){
				
				$mail_start = microtime(true);

				glog("sending password to email ".self::$email);

				self::send_password_to_email_ru(self::$email,$password,$cafe);
				User::auth_update(self::$email);

				$mail_end = microtime(true);
				$mail_time = $mail_end-$mail_start;
				
				glog("mail send time: {$mail_time}");
				
				return true;

			}else{
				glog('Can not add new cafe');			
				return false;
			}

		}else{
			glog('Can not add new user');			
			return false;
		}
	}	

	/**
	 * @param Smart_object $CAFE
	 * @return array / [string $error, string $QrCodeUrl ]
	 */
	static public function recreate_qrcode_image_for(Smart_object $CAFE):array {
		if(!$CAFE || !$CAFE->valid()) return ["unknown cafe", null];
		
		$newImageQrCodeUrl = self::generate_qrcode($CAFE->id, $CAFE->uniq_name);
		if($newImageQrCodeUrl){
			$CAFE->qrcode = $newImageQrCodeUrl;
			$CAFE->updated_date = 'now()';
			$CAFE->save();
			return [null, $newImageQrCodeUrl];
		}else{
			return ["cant create qrcode image", null];
		}

	}

	/* private */

	static private function create_cafe_for($id_user,$lang){
			
		global $CFG;
		$lng = $lang=='ru'?$lang:'en';

		$time_start = microtime(true);		

		glog("\n-------------- CREATE NEW CAFE FOR {$id_user}, {$lang} --------------\n");

		$bucket = $CFG->S3['bucket'];

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

		$arr = new Smart_collect("cafe","WHERE sample='{$lng}'");

		if($arr&&$arr->full()){
			$cafe = $arr->get(0);
		}else{
			glog('No Sample found');
			return false;
		}		

		// GET USER EMAIL
		$user = new Smart_object("users",$id_user);
		if(!$user || !$user->valid()) {
			glog("User {$id_user} not found");;	
			return false;
		}
		
		// CREATING CAFE
		$newCafe = new Smart_object("cafe");
		$newCafe->id_user = $id_user;
		$newCafe->cafe_title = !empty(self::$cafeTitle) ? self::$cafeTitle : $cafe->cafe_title;
		$newCafe->created_date = "now()";
		$newCafe->updated_date = "now()";
		$newCafe->chief_cook = $cafe->chief_cook;
		$newCafe->expire_on = null;
		$newCafe->cafe_status = 0;
		$newCafe->id_skin = $cafe->id_skin;
		$newCafe->cafe_address = $cafe->cafe_address;
		$newCafe->cafe_phone = $cafe->cafe_phone;
		$newCafe->price_hidden = $cafe->price_hidden;
		$newCafe->cafe_currency = $cafe->cafe_currency;
		$newCafe->lang = $cafe->lang;
		$newCafe->work_hours = $cafe->work_hours;
		$newCafe->cafe_description = $cafe->cafe_description;


		if(!$newCafe->save()){
			glog("Can not save cafe");
			return false;
		}

		glog("Saved new cafe, $newCafe");
		
		// UPDATE CAFE PARAMS		
		$newCafe->uniq_name = self::generate_uniqname($newCafe->id);				
		glog("Generated uniq_name for cafe, ".$newCafe->uniq_name);		

		$newCafe->qrcode = self::generate_qrcode($newCafe->id,$newCafe->uniq_name);
		
		if(!$newCafe->qrcode){
			glog("Cant generated qrcode for cafe, ".$newCafe->id);			
			return false;
		}else{
			glog("Generated qr_code for cafe, ".$newCafe->id.", ".$newCafe->qrcode);
		}

		$newCafe->save();		
		glog("Updated new cafe, $newCafe, ".$newCafe->uniq_name);
		glog("cafe $newCafe = ".var_export($newCafe,1), __FILE__);

		Tg_keys::update_all($newCafe->uniq_name);

		// CLONE SAMPLE MENU FOR NEWCAFE

		$arr = new Smart_collect("menu","WHERE id_cafe={$cafe->id}","ORDER BY pos");

		if($arr&&$arr->full()){
			
			$arrMenu = $arr->get();

			foreach ($arrMenu as $menu) {				

				// add menu 
				$newMenu = new Smart_object('menu');
				$newMenu->id_cafe = $newCafe->id;
				$newMenu->title = $menu->title;				
				$newMenu->pos = $menu->pos;
				$newMenu->id_icon = $menu->id_icon;
				$newMenu->save();

				glog("created new menu, cloned from, ".$menu->id);

				$arr2 = new Smart_collect("items","WHERE id_menu={$menu->id}","ORDER BY pos");
				
				if($arr2&&$arr2->full()){
					$arrItems = $arr2->get();
					
					foreach ($arrItems as $item){

						// add item 
						$newItem = new Smart_object('items');
						$newItem->id_menu = $newMenu->id;
						$newItem->title = $item->title;						
						$newItem->description = $item->description;
						$newItem->sizes = $item->sizes;
						$newItem->updated_date = 'now()';
						$newItem->image_name = "";
						$newItem->image_url = "";
						$newItem->mode_spicy = $item->mode_spicy;
						$newItem->mode_vege = $item->mode_vege;
						$newItem->mode_hit = $item->mode_hit;
						$newItem->pos = $item->pos;
						$newItem->hidden = $item->hidden;
						$newItemId = $newItem->save();

						// copy images
						$imageSrc = $item->image_name;

						if($newItemId && !empty($imageSrc)){							
							$ext = '.jpg';							

							$oldImageName = $imageSrc;
							$oldImageNameSmall = substr((string) $imageSrc, 0,-4).'-s'.$ext;
							
							glog("clone large image from ".$oldImageName);
							glog("clone small image from ".$oldImageNameSmall);

							$uniq = mb_strtolower((string) $newCafe->uniq_name);
							$name = $newItemId.'-'.time().'-'.random_int(0,1000);
							$prefix = "{$uniq}/{$uniq}-";
							$newName = $prefix.md5($name);

							glog("new image name = ".$newName.$ext);							

							try{
								// copy large image
							    $result1 = $s3Client->copyObject([
							        'Bucket'     => $bucket,
							        'Key'        => "{$newName}{$ext}",
							        'CopySource' => "{$bucket}/{$oldImageName}",
							    ]);
								// copy small image
							    $result2 = $s3Client->copyObject([
							        'Bucket'     => $bucket,
							        'Key'        => "{$newName}-s{$ext}",
							        'CopySource' => "{$bucket}/{$oldImageNameSmall}",
							    ]);

							}catch(S3Exception $e){
							    
							    glog("err clone images: ".$e->getMessage());								
							    return false;

							}

							if ($result1["@metadata"]["statusCode"] == '200') {
							    
							    $url = $result1["ObjectURL"];
								$newItem = new Smart_object('items',$newItemId); 
								$newItem->image_name = "{$newName}{$ext}";
								$newItem->image_url = $url;
								$newItem->updated_date = 'now()';
								$newItem->save();

							}else{
								glog("stoped creating items");								
								return false;
							}

						}
						
					}	
				}

			}
		}
		

		$time_end = microtime(true);

		$time = $time_end - $time_start;
		$generated_time_sec = $time;

		$logCafe = new Smart_object("log_cafe_generating");
		$logCafe->id_cafe = $newCafe->id;
		$logCafe->sample = $cafe->uniq_name;
		$logCafe->regdate = "now()";
		$logCafe->generated_time_sec = $generated_time_sec;
		$logCafe->save();

		glog("generated_time_sec={$generated_time_sec}");

		return $newCafe;

	}
	

	static private function generate_uniqname($id_cafe){
		$chars="abcdefghkmnopqrstuvwyz";
		$max=3; 
		$size=StrLen($chars)-1;
		$uname=null;
    	while($max--) 
    	$uname.=$chars[random_int(0,$size)]; 
    	return $id_cafe.$uname;
	}

	static private function generate_qrcode($id_cafe,$uniq_name):string|bool{
		global $CFG;
		
		$prefix = $uniq_name.'/'.$uniq_name;		
		$qrName = mb_strtolower($prefix)."-qrcode.png";
		$url2cafe = $CFG->http.$CFG->wwwroot."/cafe/".$uniq_name;

		// ----- CREATING PNG QR-CODE ----- \
		$options = new QROptions([
			'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
			'returnResource'=> true,
			'scale' => 30,
			'quietzoneSize'=> 1,
		]);
		
		$im = (new QRCode($options))->render($url2cafe);

		ob_start();		
		imagepng($im);
		$imageQrcode = ob_get_contents();
		ob_end_clean();
		// ----- CREATING PNG QR-CODE ----- /

		$bucket = $CFG->S3['bucket'];

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

		$result = $s3Client->putObject([
		 'Bucket' => $bucket,
		 'Key'    => $qrName,
		 'ContentType' => 'image/png',
		 'ACL'=>'public-read',	 
		 'Body' => $imageQrcode
		]);

		try{
			if ($result["@metadata"]["statusCode"] == '200') {
				$urlQrcode = $result["ObjectURL"];
				return $urlQrcode;
			}else{
				self::$ERR_MESSAGE = 'Cant save qrcode';
				glogError(self::$ERR_MESSAGE);
				return false;				
			}
		} catch (S3Exception $e) {
			self::$ERR_MESSAGE = 'cant save qrcode, '.$e->getMessage() . PHP_EOL;
			glogError(self::$ERR_MESSAGE);
			return false;	
		}
	}		

	static private function add_new_user_to_db($email,$password,$lang){
		$user = new Smart_object('users');		
		$user->email = $email;
		$user->password = md5((string) $password);
		$user->regdate = 'now()';
		$user->updated_date = 'now()';
		$user->lang = $lang;
		return $user->save();
	}	

	static private function send_password_to_email_ru($email,$password,$cafe){
		
		global $CFG; 		

		if(!$cafe || !$cafe->valid()) {
			glog('Неизвестное кафе');
			return false;
		}		

		$subject = "Для меню «{$cafe->cafe_title}» активирован тестовый период! Пароль и логин внутри.";

		$admin_link = $CFG->wwwroot."/admin/";			
		$menu_link = $CFG->wwwroot."/cafe/".$cafe->uniq_name;
		$help_qr_link = $CFG->wwwroot."/help/#qr-code";
		$help_link = $CFG->wwwroot."/help/";

		$m = new Email("ru");

		$m->add_title("Отлично! Для вашего меню «{$cafe->cafe_title}» активирован тестовый период!");
		
		$m->add_title2("Ссылка на меню:");
		$m->add_paragraph("На время действия тестового периода:<br><nmlink>".$CFG->http.$menu_link."|".$menu_link."</nmlink>");
		 
		$m->add_title2("Управление:");
		$m->add_button("Панель Управления",$CFG->http.$admin_link);
		
		$m->add_paragraph('Логин: <strong>'.$email.'</strong>');
		$m->add_paragraph('Пароль: <strong>'.$password.'</strong>');

		$m->add_title2("Для ваших посетителей:");
		
		$m->add_paragraph(implode("", [
 			"Распечатейте этот QR-Code. Ваши посетители за секунды смогут открывать меню! ",
 			"Подробнее об этом смотрите на сайте: <nmlink>".$CFG->http.$help_qr_link."|".$help_qr_link."</nmlink>"
 			]));

		$m->add_paragraph('<maxwidth 200px><img>'.$cafe->qrcode.'</img></maxwidth>');
		$m->add_paragraph("Скачать QR-code для печати можно <nmlink>{$cafe->qrcode}|по этой ссылке</nmlink>");

		$m->add_title2("Настройки:");

		$m->add_paragraph(implode("", [
 			"Во время тестового периода действуют некоторые ограничения возможностей меню. ",
 			"Подробности ограничений описаны на нашем сайте <nmlink>".$CFG->http.$help_link."| в разделе help.</nmlink>"
 			]));

		$m->add_paragraph("После окончания тестового периода ваше меню перейдет в архив и вам будет предложено заключить договор.");
		
		$m->add_paragraph(implode("", [
 			"Для того, чтобы заключить договор сейчас и воспользоваться всеми возможностями сервиса ",
 			"зайдите в Панель Управления -> Настройки -> <strong>Снять ограничения.</strong> ",
 			"Вам придет письмо с договором и подробной инструкцией."
 			]));

		$m->add_space();
		$m->add_paragraph("<strong>Приятной работы!</strong>");

		$m->add_default_bottom();

		
		$mail_start = microtime(true);
		if(!$m->send($email,$subject)){
			glog("cant send message, to: ".$email.", subject: ".$subject);
			return false;			
		}else{
			$mail_end = microtime(true);
			$mail_time = $mail_end-$mail_start;
			glog("sent message, to: ".$email.", subject: ".$subject);
			glog("mail send time, point 2: {$mail_time}");
			return true;
		}

	}

	

}


?>