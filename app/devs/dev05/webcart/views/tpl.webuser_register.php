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
?>

var SITE_CFG = {
	home_page: '<?=SITE::get_link("home");?>/',
	register:'<?=$R->get(1);?>',	
	token:'<?=$R->get(2);?>',	
};

App && App(SITE_CFG);

</script>


</head>
<body class='<?=Site::get_body_classes();?>' <?=Site::get_body_data();?> >

<h2>Регистрация пользователя</h2>

<div class="webuser-role">Роль: ...</div>
<br>
<div class="app-status">...</div>
<br>
<div class="err-message"></div>

<script>
	
</script>
</body>
</html>