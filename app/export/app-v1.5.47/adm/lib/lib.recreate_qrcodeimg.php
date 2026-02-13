<?php

/**
 * 	ПЕРЕСОЗДАЕМ ИЗОБРАЖЕНИЕ QR-CODE ДЛЯ МЕНЮ
 *  СОХРАНЯЕМ ЕГО В YANDEX CLOUD
 *  И ВОЗВРАЩАЕМ ССЫЛКУ НА ИЗОБРАЖЕНИЕ
 * 
*/

header('content-type: application/json; charset=utf-8');
$callback = $_REQUEST['callback'] ?? 'alert';
if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }	

define("BASEPATH",__file__);

require_once getenv('WORKDIR').'/config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';
require_once WORK_DIR.APP_DIR.'core/class.sql.php';		
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
require_once WORK_DIR.APP_DIR.'core/class.user.php';
require_once WORK_DIR.APP_DIR.'core/class.account.php';

session_start();
SQL::connect();

$user = User::from_cookie();
if(!$user || !$user->valid())__errorjsonp("Unknown user");

if(!isset($_POST['id_cafe']) && empty($_POST['id_cafe']) ) __errorjsonp("Unknown id_cafe");
$id_cafe = (int) $_POST['id_cafe'];

$cafe = new Smart_object("cafe", $id_cafe);
if(!$cafe->valid())__errorjsonp("Unknown cafe");

if($cafe->id_user!==$user->id)__errorjsonp("Not allowed");	

try{ 			
	[$error, $newImageQrCodeUrl]  = Account::recreate_qrcode_image_for($cafe);
	if($error || !$newImageQrCodeUrl){				
		glogError($error);
		__errorjsonp("1. failed to create qr-code");
	}else{
		__answerjsonp(["qrcode_image_url"=>$newImageQrCodeUrl]);	
	}

}catch(Exception $e){
	glogError($e->getMessage());
	__errorjsonp("2. failed to create qr-code");
}


?>