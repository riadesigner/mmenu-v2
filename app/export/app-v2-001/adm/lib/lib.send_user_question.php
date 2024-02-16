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
	require_once '../../core/class.email_simple.php';	


	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown cafe");
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe");

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed");

	$user_email=$user->email;

	if(!isset($_POST['user_question']) && empty(trim((string) $_POST['user_question'])) ) __errorjsonp("Empty message");
	$user_question = post_clean($_POST['user_question'], $CFG->inputs_length['user_question'] );
	 
	send_email_to_support( $user->email, $user_question, $cafe );

	
	function send_email_to_support($user_email,$user_question,$cafe){
	
		global $CFG;

		$subject = "Вопрос от {$user_email}";
		$link_to_cafe = $CFG->http.$CFG->wwwroot."/cafe/".$cafe->uniq_name;

		$body = "<html><body>";
		$body.= "<p>Пользователь <a href='mailto:{$user_email}?subject=ChefsMenu.ru/Ответ'>{$user_email}</a>.</p>";
		$body.= "<p>IDCAFE/UNIQNAME: ".$cafe->id."/".$cafe->uniq_name."</p>";		
		$body.= "<p>Ссылка на кафе: <a href='{$link_to_cafe}' target='_blank'>".$cafe->cafe_title."</a></p>";
		$body.= "<p><strong>Вопрос:</strong></p>";
		$body.= "<p style='border:1px solid gray;padding:10px;'>{$user_question}</p>";
		$body.= "</body></html>";		

		// WHOM, TO ,SUBJECT, MESSAGE
		$m = new Email_simple();
	
		if(!$m->send("Администратору",$CFG->support_email, $subject, $body)){
			__errorjsonp("Something wrong");
		}else{
			__answerjsonp("ok");
		}


	}

?>