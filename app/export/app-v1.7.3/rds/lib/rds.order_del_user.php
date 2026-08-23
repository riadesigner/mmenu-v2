<?php

/*
	Order to delete User
	sending to rdsadmin email

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

	require_once WORK_DIR.APP_DIR.'core/class.rdsadmin.php';
	require_once WORK_DIR.APP_DIR.'core/class.rdsdeluser.php';

	require_once WORK_DIR.APP_DIR.'core/class.email_simple.php';

	
	session_start();
	SQL::connect();	

	if(RDSAdmin::authorised()){

		if(!isset($_REQUEST["id_user"]) || empty($_REQUEST["id_user"])){
			__errorjsonp("wrong id_user".$id_user.", ".__LINE__);			
		}else{

			$id_user = intval(post_clean($_REQUEST["id_user"],100));			
			mail_the_order($id_user);
			__answerjsonp(["Order to delete"=>"sent"]);
		}
		
	}else{		
		__errorjsonp("Must authorisation, ".__LINE__);
	}

	function mail_the_order($id_user){
	
		global $CFG; 

		$user = new Smart_object("users",$id_user);
		if(!$user || !$user->valid()) { 
			__errorjsonp("STOP ERR: unknown user, ".__LINE__); 
			return false; 
		}		

		$link = RDSDeluser::get_link_by_user($user);
		if(!$link){
			__errorjsonp("STOP ERR LINK, ".__LINE__); 
			return false;
		}
		
		$cafes = new Smart_collect("cafe","WHERE id_user='".$user->id."'");
		$str_cafe = "";
		$str_menu = "";

		if(J_ENV_LOCAL){
			$str_cafe.="<p><strong>[Запрос с локального сервера $CFG->wwwroot]</strong></p>";
		}

		if($cafes&&$cafes->full()){
			$cafe = $cafes->get(0);

			$full_link_to_cafe = $CFG->http.$CFG->wwwroot."/cafe/".mb_strtolower((string) $cafe->uniq_name);
			
			$str_cafe .= "<p>Также удалится кафе: <a href='".$full_link_to_cafe."'>".$cafe->cafe_title."</a></p>";			

			$allmenu = new Smart_collect("menu","WHERE id_cafe='".$cafe->id."'");

			if($allmenu&&$allmenu->full()){
				$arr_sections_title = [];
				foreach ($allmenu->get() as $menu) { array_push($arr_sections_title, $menu->title); }
				$str_menu_sections = implode(", ", $arr_sections_title);
				$str_menu = "<p>В меню кафе есть разделы: $str_menu_sections</p>";
			}

		}

		$body = "<html>";
		$body = "<head><meta charset='UTF-8'></head>";
		$body.= "<body>";
		$body.="<p>Запрос на удаление пользователя ".$user->email."</p>";
		$body.="<p>Для подтверждения: <a href='$link'>подтверждение удаления <strong>".$user->email.".</strong></a></p>";
		$body.= $str_cafe;
		$body.= $str_menu;
		$body.= "</body>";
		$body.="</html>";

		global $CFG;

		$m = new Email_simple();
		if(!$m->send("RDS",$CFG->admin_email,"Request to delete User",$body)){
			__errorjsonp("Cant send the mail: ".$mail->ErrorInfo);
		}else{
			__answerjsonp(["mail has been sent about delete user!"]);
		}


	}


?>