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
$isNew = isset($data['isNew']) ? (bool) $data['isNew'] : false;

if(empty($endpoint)||empty($p256dh)||empty($auth)){
	__errorjson(["error"=>"endpoint, p256dh, auth are required"]);
}
glog("endpoint= ".$endpoint)."\n";
glog("p256dh= ".$p256dh)."\n";
glog("auth= ".$auth)."\n";
glog('isNew= '. $isNew?"yes":"no")."\n";


$endpoints = new Smart_collect("push_users","where push_endpoint = '".$endpoint."'");
if($endpoints && $endpoints->full()){
	$webuser = $endpoints->get(0);
	glog("endpoint already exists")."\n";	
	__answerjson(["webuser"=>($webuser->export()), "isNew"=>"false"]);		
}else{
	$webuser = new Smart_object("push_users");
	$webuser->push_endpoint = $endpoint;
	$webuser->push_p256dh = $p256dh;
	$webuser->push_auth = $auth;
	
	
	
	if($webuser->save()){
		glog("created new webuser")."\n";		
		__answerjson(["webuser"=>$webuser->export(), "isNew"=>true]);	
	}else{
		glog("error creating webuser")."\n";
		__errorjson(["error"=>"error creating webuser"]);	
	}
}


?>