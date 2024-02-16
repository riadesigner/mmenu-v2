import {GLB} from './glb.js';

export var SiteLng = {
	init:function(currlang){		
		this.currlang = currlang;
		this.LNG = {
			// SignIn
			'ph_enter_your_email':[
				'Enter your email and password, please!',
				'Введите почту и пароль, пожалуйста!'],
			'ph_check_is_your_email_right':[
				'Check the correctness of the entered email, please',
				'Проверьте правильность введенного email, пожалуйста'],
			'ph_unknown_error':[
				'Error, can not verify data',
				'Ошибка, невозможно проверить данные'],
			'ph_please_enter_email':[
				'Enter e-mail, please',
				'Введите e-mail, пожалуйста'],
			'ph_pass_has_been_sent':[
				'Excellent! A new password has been sent to your email <strong>[email]</strong>.',
				'Отлично! На ваш email <strong>[email]</strong> отправлен новый пароль.'],
			// CreateMenu
			'ph_all_fields_need_to_fill':[
				'It is necessary to fill both fields',
				'Необходимо заполнить оба поля'],
			'ph_the_message_has_been_sent':[
				'A link to enter your Control Panel has been sent to your email <strong>[email]</strong>. Try editing your menu right now!',
				'На ваш email <strong>[email]</strong> выслана ссылка для входа в Панель Управления. Начните редактировать ваше меню прямо сейчас!'],
			'ph_a_link_will_be_sent':[
				'On your email will come a letter with a&nbsp;link to enter the Control Panel.',
				'На вашу почту придет письмо с&nbsp;ссылкой для входа в&nbsp;Панель Управления.'],
			'ph_err_try_later':[
				'Cant send the email now. Please, try later.',
				'Невозможно отправить письмо сейчас. Попробуйте позже.']
		}
	},
	get:function(ph){	
		var index = 0;
		switch(this.currlang){
			case 'ru': index = 1;break;
			default: index = 0;break;
		};
		return this.LNG[ph] !== undefined ? this.LNG[ph][index] : "Unknown";
	}
};