<?php


/*
	Order to update password
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
	require_once WORK_DIR.APP_DIR.'core/class.subdomain.php';


	session_start();
	SQL::connect();

	
	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	$new_subdomain = post_clean($_POST['new_subdomain'], $CFG->inputs_length['new-subdomain'] );
	
	if(!Subdomain::is_valid_name($new_subdomain)){
		__errorjsonp(Subdomain::get_err_message());
	}

	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown cafe id");
	$id_cafe = (int) $_POST['id_cafe'];
	
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe || !$cafe->valid())__errorjsonp("Unknown cafe");
	if($cafe->id_user!==$user->id)__errorjsonp("Not allowed");	

	$q = "SELECT COUNT(*) AS total FROM cafe WHERE subdomain='{$new_subdomain}'";
	$counter = SQL::first($q);
	$count = (int) $counter['total'];

	if($count>0){
		__errorjsonp("--already exist");
	}

	if((int) $cafe->cafe_status!==2){
		__errorjsonp("--limited mode");
	}

	mail_to_confirm_ru($user->email, $cafe, $new_subdomain);

	function mail_to_confirm_ru($email, $cafe, $subdomain){
		
		global $CFG; 
		
		$key = Subdomain::calc_key($cafe->uniq_name, $subdomain);

		$confirm_link = $CFG->http.$CFG->wwwroot."/confirm-subdomain/{$cafe->uniq_name}/{$subdomain}/{$key}";
		$subject = "Подтвердите выбранный адрес для вашего меню";
		$new_address = "{$subdomain}.{$CFG->wwwroot}";
		$allways_address = $CFG->wwwroot."/cafe/".$cafe->uniq_name;

		$m = new Email('ru');
		$m->add_title($subject);
		$m->add_paragraph("Здравствуйте, вы выбрали новый адрес для меню. Теперь ссылка на ваше меню будет выглядеть так:");
		
		$m->add_title3($new_address);

		$m->add_paragraph("Нажмите кнопку подтвердить, чтобы сохранить и открыть новый адрес вашего меню.");
		$m->add_button("Подтвердить новый адрес", $confirm_link);

		$m->add_space();
		$m->add_strong("На заметку:");
		$m->add_paragraph(
			implode("", [
				"Независимо от выбранного имени, у вашего меню всегда будет второй неизменный адрес:  ",
				"<nmlink>{$CFG->http}{$allways_address}|{$allways_address}</nmlink>"
				])	
			);
		 
		$m->add_space();
		$m->add_strong("Спасибо за ваш выбор!");
		$m->add_default_bottom();

		if(!$m->send($email,$subject)){
			__errorjsonp("Что-то пошло не так. Не возможно отправить сообщение");
		}else{
			__answerjsonp("ok. mail sent. you need confirm subdomain address");
		}
	}




?>