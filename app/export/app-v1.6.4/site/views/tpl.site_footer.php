
<!-- tutorial_url lng_tutorial-->
<!-- help_url lng_answers -->
<div class="site-new-footer">
	
	<div class="site-new-footer__top">
		<div class="site-new-footer__main-menu">
			<ul class="menu">
				<li><a href="<?=SITE::get_link('help');?>"><?=LNG::get("lng_help");?></a></li>				
				<li><a href="<?=SITE::get_link('price');?>"><?=LNG::get("lng_price");?></a></li>
				<li><a href="<?=SITE::get_link('contacts');?>"><?=LNG::get("lng_contacts");?></a></li>
			</ul>
		</div>
		<div class="site-new-footer__invite">
			<div class="site-new-footer__invite-button">
				<span class="site-new-footer__invite-button_image"></span>
				<span class="site-new-footer__invite-button_title"><?=LNG::get("lng_getfree");?></span>
				<span class="site-new-footer__invite-button_arrow"></span>
			</div>
		</div>
	</div>

	<div class="site-new-footer__bottom">
		<div class="site-new-footer__bottom-one">
			<div class="site-new-footer__bottom-one_social">				
				<a href="https://t.me/<?=$CFG->support_telegram;?>" target="_blank"><div class="site-new-footer__bottom-one_support-tg"></div></a>
			</div>
			<div class="site-new-footer__bottom-one_button"><a href="<?=SITE::get_link('home');?>/#"><div></div></a></div>
			<div class="site-new-footer__bottom-one_description">
				Chef’s Menu сервис просто работает. Нужные настройки всегда рядом, 
				в&nbsp;понятном интерфейсе. Мы&nbsp;работаем над техническими деталями, 
				чтобы вы&nbsp;могли сосредоточиться на творчестве.
			</div>			
		</div>
		<div class="site-new-footer__bottom-two">
			<div class="site-new-footer__bottom-two__content">
			<div class="site-new-footer__bottom-two__menu">
				<ul class="menu">
					<a href="<?=SITE::get_link('privacy');?>"><li><?=LNG::get("lng_privacy");?></li></a>
					<a href="<?=SITE::get_link('terms');?>"><li><?=LNG::get("lng_terms");?></li></a>
					<a href="<?=SITE::get_link('features');?>"><li><?=LNG::get("lng_features");?></li></a>					
				</ul>
			</div>				
			<div class="site-new-footer__bottom-two__copy">
				<p>Design&code RiaDesign</p>
				<p>© 2019-<?=Date("Y");?>, Chef`s Menu</p>
			</div>
			</div>
		</div>		
	</div>

	<div class="site-new-footer__border"></div>
	
</div>

</div> <!-- END ALL SITE -->

<!-- SITE SIGN IN -->

<div class="sign-in-panel">
	<div class="sign-in-panel-container">
	<div class="sign-in-sections">
		<div class="section section-sign-in">
			<div class="section-container">
				<div class="section-sign-in__inputs">
					<input type="text" name="email" maxlength="50" placeholder="<?=LNG::get("lng_your_email");?>">
					<input type="text" name="pass" maxlength="50" placeholder="<?=LNG::get("lng_your_pass");?>">
				</div>
			<div class="sign-in-loader"><?=LNG::get("lng_checking");?></div>
			<div class="message sign-in-err-message"></div>
			<div class="sign-in-buttons">
				<div class="sign-in-btn-fogot"><a href="<?=SITE::get_link('home');?>/admin#"><?=LNG::get("lng_forgot_pass");?></a></div>
				<div class="sign-in-btn-enter"><?=LNG::get("lng_enter");?></div>
			</div>
			</div>
		</div>
		<div class="section section-get-pass">
			<div class="section-container">
				<div class="section-get-pass__inputs">
					<input name="email" type="text" maxlength="50" placeholder="<?=LNG::get("lng_your_email");?>">
				</div>				
			<div class="sign-in-loader"><?=LNG::get("lng_sending");?></div>
			<div class="message sign-in-err-message"></div>
			<div class="sign-in-buttons">
				<div class="sign-in-fogot-description"><?=LNG::get("lng_get_new_pass");?></div>
				<div class="sign-in-btn-get"><?=LNG::get("lng_get");?></div>
			</div>
			</div>
		</div>
		<div class="section section-touchonly">
			<div class="section-container">	
				<div>
					<h1><?=LNG::get("lng_friends");?></h1>
					<p><?=LNG::get("lng_singin_touch_only");?></p>
				</div>
			</div>
		</div>	
		<div class="section section-done">
			<div class="section-container">	
				<div>
					<div class="message sign-in-ok-message"></div>
					<div class="sign-in-buttons">
						<div class="sign-in-btn-close3"><?=LNG::get("lng_close");?></div>
					</div>
				</div>
			</div>
		</div>			
	</div>
	<div class="sign-in-btn-close"></div>
	</div>
</div>


<?php
// !J_ENV_LOCAL && require_once('tpl.yandex_metrica.php');
?>

</body>
</html>