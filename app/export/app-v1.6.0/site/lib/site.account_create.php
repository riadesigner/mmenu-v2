<?php

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
	require_once WORK_DIR.APP_DIR.'core/class.lng_prefer.php';

	
	session_start();	
	$lang = Lng_prefer::get();

	if(!isset($_POST['email']) || empty($_POST['email']))
	__errorjsonp($lang=='ru'?'Укажите вашу электронную почту!':'Please, enter your <b>email</b>!');

	$email = post_clean($_POST['email'],200);	
	if(!checkemail($email))
	__errorjsonp($lang=='ru'?'Ошибка, возможно, ваш адрес электронной почты <b><'.$email.'></b>написан с ошибкой!':'Sorry, perhaps your email address <b><'.$email.'></b> is not correct!');

	$defaultCafeTitle = $lang=='ru'?'Мое кафе':'My Sample Cafe';

	if(!isset($_POST['cafe']) || empty($_POST['cafe'])){
		$cafe_title = $defaultCafeTitle;
	}else{
		$cafe_title = post_clean($_POST['cafe'],100);
		if(strlen((string) $cafe_title)<3) $cafe_title = $defaultCafeTitle;
	}

	SQL::connect();	

	$user = User::byEmail($email);
	if($user) __errorjsonp($lang=='ru' ? 'Пользователь с почтой <b>'.$email.'</b> уже зарегистрирован':'A user with this mail <b>'.$email.'</b> is already registered');

	
	mail_to_confirm_ru($email,$cafe_title);
	
	function mail_to_confirm_ru($email,$cafe_title){
		global $CFG; 
		$key = md5("new-email:".$email);
		$siteurl = $CFG->http.$CFG->wwwroot;
		$link = $siteurl."/activate/$email/$key/".urlencode((string) $cafe_title)."/ru";
		$subject = "Ваше меню готово к работе!";

		$m = new Email('ru');
		$m->add_title("Меню «{$cafe_title}» готово к работе!");
		$m->add_paragraph("Откройте это письмо через <strong>телефон</strong> или <strong>планшет</strong> и начните редактировать меню.");		
		$m->add_button("Начать редактировать",$link);

		$m->add_paragraph(implode(" ",[
			"Сразу после начала редактирования мы вышлем для вас постоянный пароль и ссылку ",
			"для входа в Панель Управления. А также справочную информацию о тестовом периоде."]));
		
		$m->add_space();
		$m->add_paragraph("<strong>Спасибо за ваш выбор!</strong>");
		$m->add_default_bottom();

		if(!$m->send($email,$subject)){
			__errorjsonp("cant send message");
		}else{
			__answerjsonp("Письмо отправлено!"); ;
		}
	}	
	
?>