<?php
require_once('tpl.site_header.php');

date_default_timezone_set("Asia/Vladivostok");
$local_time_now = date('G:i');

$telegram_link = "#";
$whatsapp_link = "#";

?>

<div class="page-main-content">
	<div class="page-breadcrumbs">
<a href="<?=SITE::get_link('home');?>"><?=LNG::get("lng_main");?></a> / <span><?=LNG::get("lng_contacts");?></span>
	</div>
	<div class="page-article">
		<h1>Возникли вопросы? Напишите нам или позвоните:</h1>
		<div class="page-article__section">

			
			<p>Почта для любых вопросов: </p>			
			<p><a class="page-article__large-link" href="mailto:<?=$CFG->support_email;?>"><?=$CFG->support_email;?></a></p>

			<p>Телеграм: <a href='https://t.me/'<?=$CFG->support_telegram;?> >@<?=$CFG->support_telegram;?></a> </p>			

			<p>Обратите внимание на то, что ответ может прийти не сразу, 
				но обязательно в <strong>течение 24 часов.</strong> 
			Наш офис находится во Владивостоке.</strong> 
			Местное время сейчас <strong><span class="local-time-now"><?=$local_time_now;?></span></strong>
			</p>
			

			<p>
				Так же вы сможете отправить вопрос непосредственно
				из Панели Управления вашим Меню. Если вы его еще не создали, создайте =) Это займет у вас времени меньше минуты.
			</p>

			

			



			<!-- 

			<p>Дополнительные каналы для связи:</p>
				
			<p>					
				<a href="<?=$telegram_link;?>" class="page-article__bright-link">Telegram: Chef's Menu</a><br>	
				<a href="<?=$whatsapp_link;?>" class="page-article__bright-link">WhatsApp: Chef's Menu</a><br>	
			</p>

			-->

			

		</div>
	</div>
</div>

<?php
	require_once('tpl.site_footer.php');
?>