<!DOCTYPE html>
<html>
<head>
<title><?=SITE::get_title();?></title>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0"> 
<meta http-equiv="Cache-Control" content="no-cache"/>
<link rel="shortcut icon" href="<?=$CFG->base_url;?>favicon.png" type="image/png">

<base href="<?=Site::get_app_url();?>">

<link rel="stylesheet" href="./site/css/style_cafe.css<?=$ver;?>" type="text/css">
<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">

<script type="text/javascript" src="<?=$CFG->base_url;?>jquery/jquery.min.js"></script>
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

console.log('SITE_CFG = ',SITE_CFG);

$(function() {
	if(SITE_CFG.register!=='waiter' 
	&& SITE_CFG.register!=='manager' 
	&& SITE_CFG.register!=='supervisor' ){
		alert('Неправильная ссылка');
		location.href=SITE_CFG.home_page+'404';
	}
});

</script>

<style>
	body{
		background-color: #180e09;
		color:white;
		padding:20px;
	}
	
</style>

</head>
<body class='<?=Site::get_body_classes();?>' <?=Site::get_body_data();?> >

<h2>Регистрация пользователя</h2>

<div>загрузка...</div>

</body>
</html>