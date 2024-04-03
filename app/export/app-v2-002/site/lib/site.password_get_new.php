<?php

/*
	order new password

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
	
	require_once '../../core/class.lng.php';
	require_once '../../core/class.lng_prefer.php';

	session_start();

	$lang = Lng_prefer::get();

	$email = post_clean($_POST['email'],200);

	if(!preg_match("|^[0-9a-z_\.]+@[0-9a-z_^\.]+\.[a-z]{2,6}$|i", (string) $email))
	__errorjsonp($lang=='ru'?'Ошибка, возможно, ваш адрес электронной почты <b><'.$email.'></b>написан с ошибкой!':'Sorry, perhaps your email address <b><'.$email.'></b> is not correct!');
	

	$user = User::byEmail($email);
	
	if(!$user || !$user->valid())
	__errorjsonp($lang=='ru'?'Пользователь с почтой <b>'.$email.'</b> не найден!':'Sorry, unknown user <b>'.$email.'</b>!');

	$new_password = generate_password();
	$user->new_password = md5((string) $new_password);
	$user->updated_date = "now()";
	if(!$user->save()) __errorjsonp($lang=='ru'?'Ошибка, невозможно обновить информацию':'Sorry, can not update information.');

	$lang=='ru'? mail_to_confirm_ru($email,$new_password) : mail_to_confirm($email,$new_password);

	function mail_to_confirm($email,$password){
		
		global $CFG; 
		
		$key = md5("newpass:".md5((string) $password));
		$link = $CFG->http.$CFG->wwwroot."/confirmpass/$email/$key";
		$subject = "Your new password";

		$m = new Email('en');
		$m->add_title("New password!");
		$m->add_paragraph("Hello, someone ordered a new password to enter the ChefsMenu Control Panel.");
		$m->add_paragraph("If it was you, please confirm.");
		$m->add_paragraph("Your new password: <strong>$password</strong>");
		$m->add_button("Confirm password", $link);
		$m->add_space();
		$m->add_paragraph("If you did not order a new password, just ignore this letter.");
		$m->add_default_bottom();

		if(!$m->send($email,$subject)){
			__errorjsonp("Something wrong. It is not possible to send a message");
		}else{
			__answerjsonp("Your message has been sent!"); ;
		}

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