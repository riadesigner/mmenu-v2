<?php


/*
	ADMIN APP: get all skins
*/	

	header('content-type: application/json; charset=utf-8');
	$callback = $_REQUEST['callback'] ?? 'alert';
	if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }	

	define("BASEPATH",__file__);
		
	require_once '../../../config.php';	
	
	require_once '../../core/common.php';

	__answerjsonp(['all-skins'=>$CFG->public_skins]);

?>
