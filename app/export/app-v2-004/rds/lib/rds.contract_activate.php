<?php

/*
	activating contract 

*/	
	
	header('content-type: application/json; charset=utf-8');
	$callback = $_REQUEST['callback'] ?? 'alert';
	if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }	

	define("BASEPATH",__file__);
	
	require_once '../../../config.php';
	require_once '../../../vendor/autoload.php';
	
	require_once '../../core/common.php';
	
	require_once '../../core/class.sql.php';	

	require_once '../../core/class.email.php';

	require_once '../../core/class.smart_object.php';
	require_once '../../core/class.smart_collect.php';


	require_once '../../core/class.rdsadmin.php';
	
	session_start();
	SQL::connect();	

	if(RDSAdmin::authorised()){


		if(!isset($_REQUEST["id_user"]) || empty($_REQUEST["id_user"])){
			__errorjsonp("wrong id_user".$id_user);
		}else{
			$id_user = intval(post_clean($_REQUEST["id_user"],50));
		}

		if(!isset($_REQUEST["id_cafe"]) || empty($_REQUEST["id_cafe"])){
			__errorjsonp("wrong id_cafe".$id_cafe);
		}else{
			$id_cafe = intval(post_clean($_REQUEST["id_cafe"],50));
		}		

		if(!isset($_REQUEST["contract_name"]) || empty($_REQUEST["contract_name"])){
			__errorjsonp("wrong contract_name".$contract_name);
		}else{
			$contract_name = post_clean($_REQUEST["contract_name"],50);
		}


		$cafe = new Smart_object('cafe',$id_cafe);
		if(!$cafe || !$cafe->valid()){
			__errorjsonp("Unknown cafe, {$id_cafe}");
		}else{
			
			// change cafe status to contract
			if(  (int) $cafe->cafe_status !== 2){
				$cafe->cafe_status = 2;
				!$cafe->save() && __errorjsonp("Cant update cafe, {$id_cafe}");	
			}
			
			$user = new Smart_object('users',$cafe->id_user);
			if(!$user || !$user->valid()) __errorjsonp("Unknown user");
		

			$contract = new Smart_object("contracts");			
			$contract->contract_name = $contract_name;
			$contract->regdate = 'now()';
			$contract->expire_on = date('Y-m-d H:i:s', strtotime('+366 day'));
			$contract->id_user = $id_user;
			$contract->id_cafe = $id_cafe;
			$contract->cafe_uniq_name = $cafe->uniq_name;
			!$contract->save() && __errorjsonp("Cant add contract $contract_name");
	
			// mail to user what contract activated
			// if ($user->lang=="ru") ...
			
			send_email_ru($user->email,$cafe,$contract);
			
		}

		
	}else{		
		__errorjsonp("Must authorisation");
	}



	function send_email_ru($email,$cafe,$contract){
	
		global $CFG; 
		
		$subject = "Ваше меню «{$cafe->cafe_title}» подключено к годовому обслуживанию";
		$link_to_cafe = $CFG->wwwroot."/cafe/".mb_strtolower((string) $cafe->uniq_name);
		
		$admin_url = $CFG->site_links['admin'];		
		$features_url = $CFG->site_links['features'];

		$contract_name = $contract->contract_name;
		$from_date = CHEFS__beautiful_date($contract->regdate);
		$to_date = CHEFS__beautiful_date($contract->expire_on);

		$m = new Email("ru");
		
		$m->add_title($subject);
		$m->add_paragraph("Активирован годовой контракт:");
		$m->add_short_code("№".$contract_name);

 		
		$m->add_paragraph(implode("", [
 			"Срок действия контракта:<br>",
 			"<strong>с {$from_date} по {$to_date}</strong>"
 			]));

		$m->add_paragraph(implode("", [
 			"Постоянный адрес меню <nmlink>{$CFG->http}{$link_to_cafe}|{$link_to_cafe}</nmlink> "			
 			]));		


		if(!empty($sub = $cafe->subdomain)){
			$link_with_subdomain = mb_strtolower((string) $sub).".".$CFG->wwwroot;			
			$m->add_paragraph("Ваш короткий адрес: <nmlink>{$CFG->http}{$link_with_subdomain}|{$link_with_subdomain}</nmlink> ");
		}else{
			$m->add_paragraph("Самое время выбрать более запоминающийся адрес.");	
		}
		

		$m->add_paragraph("Войдите в Панель Управления вашим меню и воспользуйтесь новыми возможностями."); 
		
		$m->add_button("Панель Управления",$CFG->http.$admin_url);

		$m->add_paragraph(implode("", [
 			"Со всеми актуальными возможностями сервиса вы можете ознакомится ",
 			"на странице: <nmlink>{$CFG->http}{$features_url}|{$features_url}</nmlink>"
 			]));

		$m->add_space();
		$m->add_paragraph("<strong>Спасибо за ваш выбор!</strong>");
		
		$m->add_default_bottom();

		if(!$m->send($email,$subject)){
			__errorjsonp("Что-то пошло не так. Не возможно отправить сообщение");
		}else{

			$cafe->updated_date = 'now()';
			$cafe->save();
			
			__answerjsonp("Письмо отправлено!"); ;
		}

	}





?>