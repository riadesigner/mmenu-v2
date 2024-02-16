<!DOCTYPE html>
<html>
<head>
<title><?=SITE::get_title();?></title>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
<meta http-equiv="Cache-Control" content="no-cache"/>

<base href="<?=$CFG->base_url;?>">

<link rel="shortcut icon" href="../favicon.png" type="image/png">
<link rel="stylesheet" href="./site/css/style.css<?=$ver;?>" type="text/css">

<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;400&display=swap" rel="stylesheet">

<meta name="description" content="<?=SITE::get_description();?>" />

<meta name="yandex-verification" content="847e35d58afa80e8" />

<meta property="og:title" content="<?=SITE::get_title();?>" />
<meta property="og:description" content="<?=SITE::get_description();?>" />
<meta property="og:locale" content="<?=SITE::get_locale();?>" />
<meta property="og:image" content="<?=SITE::get_link("home");?>/logo/chefsmenu-logo-02.png<?=$ver;?>" />
<meta property="og:image:width" content="450">
<meta property="og:image:height" content="450">
<meta property="og:url" content="<?=SITE::get_current_url();?>">
<meta property="og:type" content="website" />

<script type="text/javascript" src="../jquery/jquery.min.js"></script>
<script type="text/javascript" src="../loader.js<?=$ver;?>"></script>


<script type="text/javascript" src="./site/dist/app.js<?=$ver;?>"></script>

<script>

const GLB_APP_URL = '<?=Site::get_app_url();?>'; 

var SITE_CFG = {
	lang:	'<?=SITE::get_lang();?>',
	home_page: '<?=SITE::get_link("home");?>/',
	admin_url: '<?=SITE::get_link("admin");?>',
	base_url: '<?=$CFG->base_url;?>'
};


App && App();

</script>

</head>

<!--mobile-menu-opened start-to-create -->
<body class='<?=Site::get_body_classes();?>'>

<div class="mobile-menu">
	<ul class="mobile-main-menu">		
		<li><a href="<?=SITE::get_link('help');?>"><?=LNG::get("lng_help");?></a></li>		
		<li><a href="<?=SITE::get_link('price');?>"><?=LNG::get("lng_price");?></a></li>
		<li><a href="<?=SITE::get_link('contacts');?>"><?=LNG::get("lng_contacts");?></a></li>
		<li><a href="<?=SITE::get_link('admin');?>"><?=LNG::get("lng_enter");?></a></li>
	</ul>
</div> 

<div class="shadow-layer"></div>

<div class="maintenance-layer">
	<h1>Сервис в режиме тестирования</h1>
	<p> Поддержка: <a href="mailto:<?=$CFG->support_email;?>"><?=$CFG->support_email;?></a></p>
	<p>2022-<?=Date('Y');?>, Chef's Menu</p>	
</div>

<div class="all-site">
	
	<div class="beta-string">
		Внимание! Сервис находится в стадии тестирования. Ознакомьтесь с 
		<a href="<?=SITE::get_link('terms');?>">Условиями использования</a>
	</div>
	
	<div class="site-header">		
		<a href="<?=SITE::get_link('home');?>">
			<img class="site-logo" src="./site/i/chefs-logo.svg">
		</a>
		<div class="mobile-btn-signin">
			<span>Войти</span>
			<img src="./site/i/the-lock.svg">			
		</div>		
		<div class="site-top-slogan"><?=LNG::get("lng_description");?></div>				
		<div class="mobile-btn-burger"><span></span><span></span></div>
	</div>
