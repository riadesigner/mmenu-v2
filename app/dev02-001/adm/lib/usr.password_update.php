<?php


/*
	Order to update password
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


	session_start();
	SQL::connect();
	
	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	$new_password = post_clean($_POST['newpass'], $CFG->inputs_length['new-password']);
	
	$msk = "|^[\-\_0-9a-zA-Z]*$|i";
	if(!preg_match($msk,(string) $new_password)) __errorjsonp("Недопустимые символы в пароле");

	if(strlen((string) $new_password)<6)	__errorjsonp("Too short password");

	$user->new_password = md5((string) $new_password);
	$user->updated_date = "now()";
	if(!$user->save()) __errorjsonp("Can not save new password");

	if($user->lang=="ru"){
		mail_to_confirm_ru($user->email,$new_password);	
	}else{
		mail_to_confirm_ru($user->email,$new_password);
	}

	function mail_to_confirm_ru($email,$password){
		
		global $CFG; 
		
		$key = md5("newpass:".md5((string) $password));
		$link = $CFG->http.$CFG->wwwroot."/confirmpass/$email/$key";
		$subject = "Ваш новый пароль";

		$m = new Email('ru');
		$m->add_title("Новый пароль!");
		$m->add_paragraph("Здравствуйте, кто-то заказал новый пароль для входа в Панель Управления ChefsMenu.");		
		$m->add_paragraph("Если это вы, подтвердите пожалуйста.");
		$m->add_paragraph("Ваш новый пароль: <strong>$password</strong>");
		$m->add_button("Подтвердить пароль", $link);
		$m->add_space();
		$m->add_paragraph("Если вы не заказывали новый пароль, просто проигнорируйте это письмо.");
		$m->add_default_bottom();

		if(!$m->send($email,$subject)){
			__errorjsonp("Что-то пошло не так. Не возможно отправить сообщение");
		}else{
			__answerjsonp("Письмо отправлено!"); ;
		}
	}




?>