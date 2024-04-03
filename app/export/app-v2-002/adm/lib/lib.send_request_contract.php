<?php


/*
	send user question

*/	

	header('content-type: application/json; charset=utf-8');
	$callback = $_REQUEST['callback'] ?? 'alert';
	if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }	
	
	define("BASEPATH",__file__);
	
	require_once '../../../config.php';
	require_once '../../../vendor/autoload.php';

	require_once '../../core/common.php';	
	
	require_once '../../core/class.sql.php';	
	 
	require_once '../../core/class.smart_object.php';
	require_once '../../core/class.smart_collect.php';
	require_once '../../core/class.user.php';
	require_once '../../core/class.email.php';

	
	require_once '../../core/class.contract.php';

	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown cafe");
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe");

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed");
	

	$requested = trim((string) $cafe->requested_contract_date)!==""; 
	if($requested){
		$date = new DateTime($cafe->requested_contract_date);
		$today = new DateTime();
		$diff = $today->diff($date);
		$minutes = ($diff->h*60 + $diff->i);
		if($minutes < 120 ){
			// limit if less than 2 hours
			__errorjsonp("--limit message per day ");
		}	
	}


	if(!isset($_POST['user_email']) && empty(trim((string) $_POST['user_email'])) ) __errorjsonp("Unknown user email");
	$user_email = trim((string) $_POST['user_email']);
	if($user_email!==$user->email) __errorjsonp("Not equal real user-email and posted user-email");
	
	// make new name of contract
	$contract = new Contract($cafe);
	$contract_name = $contract->make_new_name();

	if($user->lang=="ru"){
		send_email_ru($user->email,$cafe,$contract_name);
	}else{
		send_email_ru($user->email,$cafe,$contract_name);
	}

	
	function send_email_ru($email,$cafe,$contract_name){
	
		global $CFG; 
		
		$subject = "Снять все ограничения и подключить годовое облуживание!";
		$features_url = $CFG->site_links['features'];
		$terms_url = $CFG->site_links['terms'];		
		$support_link = "mailto:support@chefsmenu.ru";
		$support_tg_link = "https://t.me/".$CFG->support_telegram;

		$m = new Email("ru");
		
		$m->add_title($subject);

		$m->add_paragraph("Приветствуем вас в Chef’s Menu Service!");
		
		$m->add_paragraph(implode("", [
 			"Прежде всего убедитесь, что вы ознакомились со следующими документами ",
 			"и согласны с описанными в них условиями, возможностями и ограничениями."
 			]));
		$m->add_paragraph(implode("", [
 			"<nmlink>{$CFG->http}{$features_url}|",
 			"1. Технические возможности и ограничения сервиса.</nmlink>"
 			]));
		$m->add_paragraph(implode("", [
 			"<nmlink>{$CFG->http}{$terms_url}|",
 			"2. Условия использования программного обеспечения сервиса Chef’s Menu.</nmlink>"
 			]));
		
		$m->add_title2("Для вас сформирован номер договора:");
		$m->add_short_code("№".$contract_name);

		// COMPANY
		$m->add_title3_bright("Подключение Юридических лиц:");
		
		$m->add_paragraph("1. Отправьте на почту <nmlink>{$support_link}|support@chefsmenu.ru</nmlink> реквизиты вашей компании и контактное лицо.");
		
		$m->add_paragraph("<strong>Укажите в письме номер договора, указанный выше.</strong>");
		
		$m->add_paragraph("2. Мы вышлем вам договор и счет для оплаты.");
				
		$m->add_paragraph(implode("", [
 			"3. Началом действия контракта считается ",
 			"день поступления денежных средств на р/с сервиса Chef’s Menu."
 			]));


		$m->add_paragraph("По дополнительным вопросам, связанным с подключением, обращайтесь в телеграм: <nmlink>{$support_tg_link}|@{$CFG->support_telegram}</nmlink>");

		// PERSONAL
		$m->add_title3_bright("Подключение Физических лиц:");
		$m->add_paragraph("Подключение физических лиц к сервису Chef’s Menu планируется в ближайшее время.");

		$m->add_space();
		$m->add_paragraph("<strong>Спасибо за ваш выбор!</strong>");
		
		$m->add_default_bottom();

		if(!$m->send($email,$subject)){
			__errorjsonp("Что-то пошло не так. Не возможно отправить сообщение");
		}else{

			$cafe->updated_date = 'now()';
			$cafe->requested_contract_date = date('Y-m-d H:i:s');
			$cafe->save();
			
			__answerjsonp("Письмо отправлено!");
		}

	}


?>