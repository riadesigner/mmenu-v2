<?php

/*
	Add menu to cafe

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
	require_once WORK_DIR.APP_DIR.'core/class.tg_keys.php';	
	require_once WORK_DIR.APP_DIR.'core/class.email.php';	
	require_once WORK_DIR.APP_DIR.'core/class.qr_tables.php';	
	

	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user, ".__LINE__);
	
	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown id cafe, ".__LINE__);
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);	

	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed, ".__LINE__);

	if(!isset($_POST['arr_tables']) || 
		!is_array($_POST['arr_tables']) ||
		!count($_POST['arr_tables'])) __errorjsonp("Empty list of tables");

	$arr_tables = $_POST['arr_tables'];	

	if(!isset($_POST['user_email']) || !checkemail( post_clean($_POST['user_email'])) ) {
		__errorjsonp("Unknown email to send");
	}	
		
	$user_email = post_clean($_POST['user_email']);
	
	try{
		// получаем сслыки и qr-коды для столиков		
		$qrcodes = Qr_tables::make_qrcodes($cafe,$arr_tables);
	}catch( Exception $e){
		glogError($e->getMessage());
		__errorjsonp("--cant create table qr-codes");
	}

	// send qrcodes
	if($qrcodes && count($qrcodes)>0){
		iiko_send_qrcodes_to_email($cafe, $user_email, $qrcodes);	
	}else{
		__errorjsonp("empty list to send");
	}
	
	function iiko_send_qrcodes_to_email($cafe, $user_email, $qrcodes){
	
		global $CFG; 
		
		$subject = "QR-коды для Меню с привязкой к столикам. / ChefsMenu: {$cafe->cafe_title}.";
		
		$m = new Email("ru");
		
		$m->add_title("Меню для заказа на стол");
		$m->add_paragraph("Распечатайте QR-коды для соответствующих столов.");

		foreach($qrcodes as $table){						
			$m->add_title2("Столик номер ".$table['table_number']." (".$table['table_name']."):");
			$m->add_paragraph("<maxwidth 272px><img>".$table['table_qr_image']."</img></maxwidth>",true);	
			$m->add_paragraph(implode("", [
			"В qr-код зашифрована ссылка: <br>",
			"<nmlink>".$table['table_link_url']."|".$table['table_link_name']."</nmlink>",
			]));
			$m->add_space();
		}		 

		$m->add_space();
		$m->add_space();
		$m->add_paragraph("<strong>Спасибо за выбор нашего сервиса!</strong>");
		
		$m->add_default_bottom();

		if(!$m->send($user_email,$subject)){
			
			__errorjsonp("Что-то пошло не так. Невозможно отправить сообщение");

		}else{

			// $cafe->requested_qrcode_date = date('Y-m-d h:i:s');
			// $cafe->updated_date = 'now()';
			// $cafe->save();
			__answerjsonp("Письмо отправлено!"); ;

		}

	}


?>