<?php

/*
	generating QR code image by string
*/	
	

	header('content-type: application/json; charset=utf-8');
	$callback = $_REQUEST['callback'] ?? 'alert';
	if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }	

	define("BASEPATH",__file__);
	
	require_once getenv('WORKDIR').'/config.php';
	 
	require_once WORK_DIR.APP_DIR.'core/common.php';	
	
	require_once WORK_DIR.APP_DIR.'core/class.sql.php';
	require_once WORK_DIR.APP_DIR.'core/class.tg_keys.php';
	 
	require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
	require_once WORK_DIR.APP_DIR.'core/class.user.php';

	use chillerlan\QRCode\{QRCode, QROptions};

	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	if(!isset($_POST['str_link']) || empty($_POST['str_link'])) __errorjsonp('need`s get a string to generate qrcode');
	
	$str_link = trim((string) $_POST['str_link']);

	// ----- CREATING PNG QR-CODE ----- \
	$options = new QROptions([
		'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
		'returnResource'=> true,
		'scale' => 40,
		'quietzoneSize'=> 1,
	]);
	
	$im = (new QRCode($options))->render($str_link);

	ob_start();
	imagepng($im);
	$imageQrcode = ob_get_contents();
	ob_end_clean();
	// ----- CREATING PNG QR-CODE ----- /

	__answerjsonp(["image" => base64_encode($imageQrcode) ]);

?>
