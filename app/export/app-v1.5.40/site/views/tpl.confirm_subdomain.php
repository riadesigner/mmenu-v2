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
	

	if(Subdomain::is_confirmed()){

		$okMsg = Subdomain::get_ok_message();

		if(str_contains((string) $okMsg, '--already confirmed')){
			$msg = SITE::get_lang()=="ru" ? "Адрес подтвержден ранее." : "The address has confirmed already.";	
		}else{
			$msg = SITE::get_lang()=="ru" ? "Отлично. Новый адрес подтвержден." : "Ok. The address is confirmed.";
		}
		
	
	}else{

		$errMsg = Subdomain::get_err_message();
		
		if( 
			str_contains((string) $errMsg, '--illegal name') ||
			str_contains((string) $errMsg, '--wrong link') ||
			str_contains((string) $errMsg, '--too short name')) {
			$msg = SITE::get_lang()=="ru" ? "Неправильная ссылка." : "Wrong link";	

		}else if(str_contains((string) $errMsg, '--already confirmed')){

			$msg = SITE::get_lang()=="ru" ? "Адрес уже подтвержден." : " The address already confirmed.";	

		}else{
			
			if(SITE::get_lang()=="ru"){
				$msg = "Не удается подтвердить. Попробуйте позже или обратитесь в поддержку.";	
			}else{
				$msg = "Unable to verify. Try again later or contact support.";
			}
		}

	}


	if(Subdomain::is_confirmed()){

		echo "<p>$msg</p>";

		$link_text = SITE::get_lang()=="ru"?"Открыть меню":"Open the menu";
		$link_url = $CFG->http.Subdomain::get().".".$CFG->wwwroot;
		echo sprintf("<p><a href='%s'>%s</a></p>",$link_url,$link_text);	

	}else{

		echo "<p>$msg</p>";
		
		$link_text = SITE::get_lang()=="ru"?"На главную страницу":"To main page";
		echo sprintf("<p><a href='%s'>%s</a></p>",SITE::get_base_url(),$link_text);	

	}

?>

	

	</div>
</body>	
</html>