<div class="site-section-create">	
	<div class="site-section-create-picture">
		<svg width="1920" height="1080" viewBox="0 0 1920 1080"></svg>
		<div class="site-slider">
			<div class="site-slider__large"></div>
			<div class="site-slider__small"></div>			
			<div class="site-slider__loader"><img src="./site/i/site-intro-loader.svg"></div>			
		</div>
	</div>	
	<div class="site-section-create-invite">
		<div class="site-section-create-invite-sizer"><svg width="250" height="90" viewBox="0 0 250 90"></svg></div>
		<div class="site-section-create-invite-wrapper">
			<div class="site-slogan"><?=LNG::get("lng_slogan");?></div>
			<div class="site-no-reg"><?=LNG::get("lng_noregistration");?></div>
			<div class="btn-create noselect"><?=LNG::get("lng_create");?></div>
		</div>
	</div>
	<div class="site-section-create-forms">		
		<div class="site-section-create-forms-close"></div>
		<div class="site-section-create-forms-title"><?=LNG::get('lng_form_description');?></div>
		<div class="site-section-create-forms-inputs">
			<div><input type="text" name="new-invite-cafe-title" placeholder="<?=LNG::get('lng_forms_name_of_your_cafe');?>"></div>
			<div><input type="text" name="new-invite-user-email" placeholder="<?=LNG::get('lng_forms_your_email');?>"></div>					
		</div>
		<div class="site-section-create-forms-answer">
			<div class="userinput-err-message" style="display: none;"><!-- message --></div>
			<div class="userinput-ok-message" ><?=LNG::get('lng_link_will_be_sent');?></div>
		</div>
		<div class="site-section-create-forms-button"><?=LNG::get('lng_bnt_next');?></div>
		<div class="get-invite-loader"><div></div><div></div></div>		
	</div>
	<div class="site-section-create-succeed">
		<div class="site-section-create-succeed-close"></div>
		<div class="site-section-create-succeed-message">			
			<h1><?=LNG::get('lng_menu_created');?></h1>
			<p class="done-message"><!-- message --></p>
			<img class="img-01" src="./site/i/site-succeed.svg" width='30px' height='auto'>
			<img class="img-02" src="./site/i/site-succeed-email.svg" width='30px' height='auto'>
			
		</div>
	</div>
</div>