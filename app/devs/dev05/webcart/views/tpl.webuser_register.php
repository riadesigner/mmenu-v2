<!DOCTYPE html>
<html>
<head>
<title><?=SITE::get_title();?></title>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0"> 
<meta http-equiv="Cache-Control" content="no-cache"/>
<link rel="shortcut icon" href="<?=$CFG->base_url;?>favicon.png" type="image/png">
<base href="<?=Site::get_app_url();?>">
<link rel="manifest" href="./webcart/manifest.json">
<link rel="stylesheet" href="./webcart/css/style.css<?=$ver;?>">
<script type="text/javascript" src="<?=$CFG->base_url;?>jquery/jquery.min.js"></script>
<script type="text/javascript" src="./webcart/dist/app.js<?=$ver;?>"></script>

<script type="text/javascript">

const GLB_APP_URL = '<?=Site::get_app_url();?>'; 

<?php
$R = Site::get_router();
$pushConfig = require_once WORK_DIR.APP_DIR.'webcart/config/push.php';
$publicKey = $pushConfig['publicKey'];     
?>

var SITE_CFG = {
	home_page: '<?=SITE::get_link("home");?>/',
	register:'<?=$R->get(1);?>',	
	token:'<?=$R->get(2);?>',
	vapidPublicKey: '<?=$publicKey;?>',
};


App && App(SITE_CFG);

</script>


</head>
<body class='<?=Site::get_body_classes();?>' <?=Site::get_body_data();?> >

<h2>Регистрация пользователя</h2>
<div class="webuser-nickname">Никнейм: ...</div>

<div id="regMainInfo" class="section">
	<div class="webuser-role">Роль: ...</div>
	<div class="app-status">...</div>
	<div id="notification-status"></div>
	<div class="err-message"></div>
	<div class="ok-message"></div>
</div>
<div id="regSelectNickname" class="section">
	<div class="levels">
		<div>Ваше имя:</div>
		<input class="input" type="text" placeholder='Ваня' value=''>
		<button class="button">ok</button> 
	</div>
</div>



<script>
	
</script>
</body>
</html>