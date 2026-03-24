<?php

/*
	registration new user to webcart

*/	
	
define("BASEPATH",__file__);
require_once getenv('WORKDIR').'/config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';	
require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';	

// if(!isset($_REQUEST['token']) || empty($_REQUEST['token']) ) __errorjson("неправильная ссылка");
// $token = post_clean($_REQUEST['token'],50);

// // проверем токен
// $tokens = new Smart_collect("push_keys","where push_key='$token'");
// if(!$tokens || !$tokens->full()) __errorjsonp("неправильная ссылка (токен)");			
// $valid_token = ($tokens->get(0));

// __answerjson($valid_token->export());	

__answerjson("alright!");


	


?>