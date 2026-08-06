<!DOCTYPE html>
<html>
<head>
<title><?=SITE::get_title();?></title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
<meta http-equiv="Cache-Control" content="no-cache"/>

<link rel="shortcut icon" href="favicon.png" type="image/png">
<base href="<?=$CFG->base_app_url;?>">

<meta name="robots" content="noindex, follow"/>

<link rel="stylesheet" href="./site/css/style_confirm.css<?=$ver;?>">

</head>
<body class='<?=Site::get_body_classes();?>'>
	<div class="all-site">
<?php

	if(Password::is_confirmed()){
		if(SITE::get_lang()=="ru"){
			echo "Отлично. Ваш новый пароль активирован.";
		}else{
			echo "Success! Your password have been activated!";	
		}	
	}else{
		echo Password::get_err_message();
	}

	$home_link_text = SITE::get_lang()=="ru"?"На главную":"Main Page";

?>

	<p class="btn-go-home"><a href="<?=SITE::get_link('home');?>"><?=$home_link_text;?></a></p>

	</div>
</body>	
</html>