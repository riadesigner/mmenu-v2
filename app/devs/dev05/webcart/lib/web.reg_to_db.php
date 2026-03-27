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

$data = json_decode(file_get_contents('php://input'), true);
$endpoint = $data['endpoint'];     
$p256dh = $data['keys']['p256dh'];       
$auth = $data['keys']['auth'];  

glog("endpoint".$endpoint)."\n";
glog("p256dh".$p256dh)."\n";
glog("auth".$auth)."\n";


__answerjson("alright!");


	


?>