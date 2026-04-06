<?php


/*
	send cafe code

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
	require_once WORK_DIR.APP_DIR.'core/class.email.php';
	

	session_start();
	SQL::connect();

	

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown cafe");
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe || !$cafe->valid())__errorjsonp("Unknown cafe");

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed");
	

	$requested = trim((string) $cafe->requested_qrcode_date)!==""; 
	if($requested){
		$date = new DateTime($cafe->requested_qrcode_date);
		$today = new DateTime(date("Y-m-d h:i:s"));
		$diff = $today->diff($date);

		$minutes = ($diff->h*60 + $diff->i);
		$seconds = $diff->s;
		
		if($minutes < 5 ){
			// limit if less than 5 minutes
			__errorjsonp("--limit message per day. Last request was $minutes min. $seconds sec. ago");
		}			
	}

	
	// if($user->lang=="ru"){
	// 	send_email_ru($user->email,$cafe);
	// }else{
	// 	send_email_ru($user->email,$cafe);
	// }

	send_email_ru($user->email,$cafe);


	function send_email_ru($email,$cafe){
	
		global $CFG; 
		
		
		$subject = "QR-код для быстрого открытия меню {$cafe->cafe_title}";
		$link_to_cafe = $CFG->wwwroot."/cafe/".mb_strtolower((string) $cafe->uniq_name);
		

		$m = new Email("ru");
		
		$m->add_title("QR-код для быстрого открытия меню «{$cafe->cafe_title}» вашими посетителями");

		$m->add_paragraph("Распечатейте этот QR-код. Ваши посетители за секунды смогут открывать меню!");
		$m->add_paragraph("<maxwidth 300px><img>{$cafe->qrcode}</img></maxwidth>");		
		$m->add_paragraph("Вы всегда можете скачать этот QR-код <nmlink>{$cafe->qrcode}|по этому адресу</nmlink>");
		
		$m->add_paragraph(implode("", [
 			"Для открытия меню у себя на телефоне, вашим посетителям не нужно ничего устанавливать. ",
 			"Меню открывается сразу при наличии интернета."
 			]));

		$m->add_paragraph(implode("", [
 			"Для пользователей <strong>iPhone</strong> достаточно просто навести камеру на этот код ",
 			"и телефон сразу предложит открыть ваше в меню."
 			]));		

		$m->add_paragraph(implode("", [
 			"Для пользователей <strong>Android</strong> телефонов достаточно сфотографировать этот код через любое ",
 			"бесплатное приложение для чтения QR-кодов, которое у них имеется."
 			]));
		
		$m->add_title2("Адрес меню:");

		$m->add_paragraph(implode("", [
 			"В QR-код зашифрована ссылка на ваше меню: ",
 			"<nobr><nmlink>{$CFG->http}{$link_to_cafe}|{$link_to_cafe}</nmlink></nobr>"
 			]));

		if((int) $cafe->cafe_status !== 2){		
			$m->add_paragraph(implode("", [
 				"После снятия ограничений тестового периода, ",
 				"вы сможете поменять вид ссылки на более запоминающийся: ",
 				"<strong>bestcafe.</strong>{$CFG->wwwroot}, где (bestcafe) – название вашего кафе или ресторана."
 				]));
		}

		$m->add_space();
		$m->add_paragraph("<strong>Спасибо за выбор нашего сервиса!</strong>");
		
		$m->add_default_bottom();

		if(!$m->send($email,$subject)){
			__errorjsonp("Что-то пошло не так. Не возможно отправить сообщение");
		}else{

			$cafe->requested_qrcode_date = date('Y-m-d h:i:s');
			$cafe->updated_date = 'now()';
			$cafe->save();
			
			__answerjsonp("Письмо отправлено!"); ;
		}

	}



?>