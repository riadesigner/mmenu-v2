<!DOCTYPE html>
<html>
<head>
<title><?=SITE::get_title();?></title>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0"> 
<meta http-equiv="Cache-Control" content="no-cache"/>
<link rel="shortcut icon" href="<?=$CFG->base_url;?>favicon.png" type="image/png">

<base href="<?=Site::get_app_url();?>">

<link rel="stylesheet" href="./site/css/style_cafe.css<?=$ver;?>" type="text/css">
<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">

<script type="text/javascript" src="<?=$CFG->base_url;?>jquery/jquery.min.js"></script>

<link rel="stylesheet" href="jquery/tarek_auto_complete.css<?=$ver;?>" type="text/css">
<script type="text/javascript" src="<?=$CFG->base_url;?>jquery/tarek_auto_complete.min.js<?=$ver;?>"></script>

<script type="text/javascript" src="<?=$CFG->base_url;?>loader.js<?=$ver;?>"></script>

<meta name="description" content="<?=SITE::get_description();?>" />

<meta property="og:title" content="<?=SITE::get_title();?>" />
<meta property="og:description" content="<?=SITE::get_description();?>" />
<meta property="og:locale" content="<?=SITE::get_locale();?>" />
<meta property="og:image" content="<?=SITE::get_link("home");?>/identity/chefsmenu-logo-02.png<?=$ver;?>" />
<meta property="og:image:width" content="450">
<meta property="og:image:height" content="450">
<meta property="og:url" content="<?=SITE::get_current_url();?>">
<meta property="og:type" content="website" />


<script type="text/javascript">

const GLB_APP_URL = '<?=Site::get_app_url();?>'; 

var SITE_CFG = {
	lang:	'<?=SITE::get_lang();?>',
	home_page: '<?=SITE::get_link("home");?>/',	
};

$(function() {
	setTimeout(function(){
		$('body').addClass('chefsmenu-show-preload');		
	},100);
});

</script>

</head>
<body class='<?=Site::get_body_classes();?>' <?=Site::get_body_data();?> >

<div autoload="yes" noclose="yes" class="chefsmenu-link" data-cafe="<?=Site::$UNIQ_CAFE;?>"></div>

<div class="menupage-logo"><img src="./pbl/i/pbl_chefs_logo.svg"></div>

<div class="archive-message">Кафе находится в архиве</div>

<div class="menupage-loader">
	<div class="menupage-loader-wrapper">
		<div class="menupage-loader-air-wrapper">
		<div class="menupage-loader-air">
			<svg viewBox="0 0 70 44">
			  <defs><style>.cls-1 {fill: none;}</style></defs>
			  <rect class="cls-1" width="70" height="44" />
			</svg>
		</div>
		</div>
		<div class="menupage-loader-cup">
			<img src="./pbl/i/pbl_cafe_loader_cup.svg">
		</div>
	</div>
</div>
<div class="menupage-footer">Ria Design Studio, <?=Date('Y');?></span></div>
	


</body>	
</html>