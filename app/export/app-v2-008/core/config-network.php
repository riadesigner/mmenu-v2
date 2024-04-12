<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//
// 
//		  c o n f i g   n e t w o r k
//
//


if(J_ENV_LOCAL){

	$CFG->dblocation    = $_ENV['LOCAL_DBLOCATION'];
	$CFG->dbname        = $_ENV['LOCAL_DBNAME'];
	$CFG->dbuser        = $_ENV['LOCAL_DBUSER'];
	$CFG->dbpasswd      = $_ENV['LOCAL_DBPASS'];
	$CFG->http			= $_ENV['LOCAL_HTTP'];	
	$CFG->wwwroot	    = $_ENV['LOCAL_WWWROOT'];
	$CFG->dirroot	    = $_ENV['WORKDIR'].'/';
	$CFG->hide_index_page = false; // for testing
	$CFG->session_secure = false;

	$CFG->S3 = [
		'region' => $_ENV['LOCAL_S3_REGION'],
		'key'=> $_ENV['LOCAL_S3_KEY'],
		'secret'=> $_ENV['LOCAL_S3_SECRET'],
		'endpoint'=> $_ENV['LOCAL_S3_ENDPOINT'],
		'bucket'=> $_ENV['LOCAL_S3_BUCKET']
		];

}else{

	$CFG->dblocation    = $_ENV['PROD_DBLOCATION'];
	$CFG->dbname        = $_ENV['PROD_DBNAME'];
	$CFG->dbuser        = $_ENV['PROD_DBUSER'];
	$CFG->dbpasswd      = $_ENV['PROD_DBPASS'];
	$CFG->http			= $_ENV['PROD_HTTP'];	
	$CFG->wwwroot	    = $_ENV['PROD_WWWROOT'];
	$CFG->dirroot	    = $_ENV['WORKDIR'].'/';
	$CFG->hide_index_page = true; // for testing
	$CFG->session_secure = true; // session through https

	$CFG->S3 = [
		'region' => $_ENV['LOCAL_S3_REGION'],
		'key'=> $_ENV['LOCAL_S3_KEY'],
		'secret'=> $_ENV['LOCAL_S3_SECRET'],
		'endpoint'=> $_ENV['LOCAL_S3_ENDPOINT'],
		'bucket'=> $_ENV['LOCAL_S3_BUCKET']
		];

}

	$CFG->dbh           = '';// соединение с БД;
	$CFG->admin_sub		=  $_ENV['ADMIN_SUB'];
	$CFG->admin_login	=  $_ENV['ADMIN_LOGIN'];
	$CFG->admin_pass	=  $_ENV['ADMIN_PASS'];
	$CFG->admin_email   = 'admin@chefsmenu.ru';

	$CFG->tg_cart_token =  $_ENV['TG_CART_TOKEN'];

	$CFG->base_rds_url = $CFG->http.$CFG->admin_sub.'.'.$CFG->wwwroot.'/'.APP_DIR.'';
	$CFG->base_url = $CFG->http.$CFG->wwwroot.'/'.APP_DIR.'';

	$CFG->log_file_path = "logs/";
	$CFG->log_file_name	= 'menu_log';		

	$CFG->user_cookie_time = 3600*24*30*3; // 3 mounth		

	$CFG->contract_cost_in_rub = '500';

	//     __  ___   ___      ____   __
	//    /  |/  /  /   |    /  _/  / /
	//   / /|_/ /  / /| |    / /   / /
	//  / /  / /  / ___ |  _/ /   / /___
	// /_/  /_/  /_/  |_| /___/  /_____/


	$CFG->support_email =  $_ENV['SUPPORT_EMAIL'];
	$CFG->support_phone =  $_ENV['SUPPORT_PHONE'];;	
	$CFG->support_telegram =  $_ENV['SUPPORT_TELEGRAM'];
	$CFG->tg_cart_bot =  $_ENV['TG_CART_BOT'];

	$CFG->email_sender = [
		'host'=> $_ENV['EMAIL_SENDER_HOST'], 
		'username'=> $_ENV['EMAIL_SENDER_USERNAME'], 
		'password'=> $_ENV['EMAIL_SENDER_PASSWORD'], 
		'from' => ['email'=>$CFG->support_email,'name'=>'ChefsMenu.ru'], 'port'=>465];
		// yandex 360	

	// if(J_ENV_LOCAL){
	// 	// remove ssl verify for localhost
	// 	$CFG->smtp_ssl_options = array("ssl"=>array("verify_peer"=>false,"verify_peer_name"=>false,"allow_self_signed"=>true));	
	// }else{
	// 	$CFG->smtp_ssl_options = array();
	// }	




?>