<?php

/*
	edit users nickname

*/	
	
define("BASEPATH",__file__);
require_once getenv('WORKDIR').'/config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';	
require_once WORK_DIR.APP_DIR.'core/class.sql.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';	

glog("edit users nickname");
glog(var_export($_REQUEST, true));
glog(var_export($_POST, true));
glog(var_export($_GET, true));

if(!isset($_REQUEST['push_endpoint']) || empty($_REQUEST['push_endpoint']) ) __errorjson("неправильный запрос");
$push_endpoint = post_clean($_REQUEST['push_endpoint'],250);
$new_nickname = post_clean($_REQUEST['nickname'],20);

$push_users = new Smart_collect("push_users","where push_endpoint='$push_endpoint'");
if(!$push_users || !$push_users->full()) __errorjson("неправильная ссылка (токен)");			
$user = ($push_users->get(0));
$user->nickname = $new_nickname;
$user->save();
__answerjson($user->export());



	


?>