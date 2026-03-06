<?php

/*
	Sign in to site	by password and email;
	Open session;

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

	require_once WORK_DIR.APP_DIR.'core/class.lng_prefer.php';
	

	session_start();
	$lang = Lng_prefer::get();

	if(!isset($_REQUEST['pass']) || empty($_REQUEST['pass'])) 
	__errorjsonp($lang=='ru'?'Введите ваш пароль!':'Enter your <b>password</b>, please!');

	if(!isset($_REQUEST['email'])  || empty($_REQUEST['email'])) 
	__errorjsonp($lang=='ru'?'Укажите вашу электронную почту!':'Please, enter your <b>email</b>!');

	$email = post_clean($_REQUEST['email'],200);
	$pass = post_clean($_REQUEST['pass'],200);

	if(!preg_match("|^[0-9a-z_\.]+@[0-9a-z_^\.]+\.[a-z]{2,6}$|i", (string) $email))
	__errorjsonp($lang=='ru'?'Ошибка, возможно, ваш адрес электронной почты <b><'.$email.'></b>написан с ошибкой!':'Sorry, perhaps your email address <b><'.$email.'></b> is not correct!');

	SQL::connect();

	$user = User::byEmail($email);	
	if(!$user)__errorjsonp($lang=='ru'?'Пользователь с почтой <b>'.$email.'</b> не найден!':'Sorry, unknown user <b>'.$email.'</b>!');

	if($user->password!==md5((string) $pass))
	__errorjsonp($lang=='ru'?'Введите правильный <b>пароль!</b>':'Sorry, the <b>password</b> is not correct!');

	if(User::auth_update($email)){
		__answerjsonp(["login"=>"ok"]);
	}else{
		__errorjsonp($lang=='ru'?'Извините, не удалось войти сейчас.':'Sorry, can not sign-in now.');
	}

?>