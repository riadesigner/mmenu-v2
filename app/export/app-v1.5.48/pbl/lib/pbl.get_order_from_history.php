<?php

/*
	MENU: get the order from History by order uniq_id
*/

	header('content-type: application/json; charset=utf-8');

	define("BASEPATH",__file__);
	
	require_once getenv('WORKDIR').'/config.php';
	require_once WORK_DIR.APP_DIR.'core/common.php';
	require_once WORK_DIR.APP_DIR.'core/class.sql.php';	 
	require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
	require_once WORK_DIR.APP_DIR.'core/class.user.php';

	SQL::connect();

	if(!isset($_REQUEST['orderUniqId']) || empty(trim((string) $_REQUEST['orderUniqId'])))__errorjson("0. unknown orderUniqId");

	$orderUniqId = trim((string) $_REQUEST['orderUniqId']);

	$orders = new Smart_collect("orders","WHERE id_uniq='{$orderUniqId}'");

	if(!$orders->full())__errorjson('Нет такого заказа в базе данных - '. $orderUniqId);	
	$order = $orders->get(0);		
	__answerjson($order->export());

?>