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
	require_once WORK_DIR.APP_DIR.'core/class.account.php';
	require_once WORK_DIR.APP_DIR.'core/class.tg_keys.php';	
	require_once WORK_DIR.APP_DIR.'core/class.email.php';
	
	session_start();
		
	// Getting lang from POST, otherwise from BROWSER
	if(!isset($_POST['lang']) || empty($_POST['lang'])){
		$lang = Lng_prefer::get();	
	}else{
		$lang = $_POST['lang'];
	}	
	
	$lang = $lang=='ru'?'ru':'en'; // limit just two languages

	SQL::connect();
	$site = $CFG->wwwroot;
	
	if(!isset($_POST['email']) || empty($_POST['email'])) __errorjsonp("wrong email");
	$email = post_clean($_POST['email'], 100);

	if(!checkemail($email)) 
	__errorjsonp($lang=='ru'?'Ошибка, возможно, ваш адрес электронной почты <b><'.$email.'></b>написан с ошибкой!':'Sorry, perhaps your email address <b><'.$email.'></b> is not correct!');

	if(!isset($_POST['key']) || empty($_POST['key'])) __errorjsonp("wrong key");
	$key = post_clean($_POST['key'], 100);

	if(!isset($_POST['cafe']) || empty($_POST['cafe'])){
		$cafe = "";
	}else{
		$cafe = post_clean($_POST['cafe'], 100);		
	}

	glog("INITIATION NEW ACCOUNT: ".$email.", ".$key.", ".$cafe);	

	if( Account::init($email,$key,$cafe) ){
		glog("Account inited");

		if(Account::activate($lang)){
			glog("Account activated");
			__answerjsonp("activation ok");
		}else{
			glog("ERR: Account activate");
			__errorjsonp('Ошибка. Что-то не пошло не так..');
		}
	}else{
		glog("ERR: Account init");
		__errorjsonp('Ошибка. Неправильная ссылка');
	}
	
?>