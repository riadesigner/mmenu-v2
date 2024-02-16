import {GLB} from './glb.js';

export var VIEW_MAIN_HELP = {
	
	init:function(options){
		
		this._init(options);
		
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$btnSend = this.$footer.find('.send');

		this.$askMessage = this.$form.find('.main-help-user-ask');		

		this.USER_EMAIL = CFG.user_email;

		var $useremail = this.$form.find('.main-help-user-mail');
		var txt = $useremail.html();		
		
		txt = txt.replace('[email]','<strong>'+this.USER_EMAIL+'</strong>');
		$useremail.html(txt);

		this.$btns_wrapper =  this.$form.find('.view_main_help__links-btn');
		this.build_asks_btn();

		this.reset();
		this.behavior();
		return this;

	},

	update:function(){
		var _this=this;
		this.reset();
		this._update();
		this._page_hide();		
		this._update_tabindex();
		setTimeout(function(){ _this._page_show(); },300);
	},
	reset:function(){
		this._reset();
		this._page_to_top();
		this._need2save(false);
	},
	behavior:function()	{
		var _this = this;

		this._behavior();

		this.$btnBack.on('touchend',function(){
			_this._blur({onBlur:function(){
				!_this.LOADING && _this._go_back();
			}});			
			return false;
		});

		this.$askMessage.on('keyup',function(){
			if(!_this.LOADING){
				var empty = $(this).val().trim() == "";
				!empty ? _this._need2save(true) : _this._need2save(false);
			};
			return false;
		});

		this.$btnSend.on('touchend',function(){
			_this._blur({onBlur:function(){

				!_this.LOADING && _this.send_question({
					onReady:function(){
						GLB.VIEWS.modalMessage({
							title:GLB.LNG.get("lng_thank"),
							message:"Ваше сообщение отправлено. <br>Ответ придет на адрес "+_this.USER_EMAIL,
							btn_title:GLB.LNG.get('lng_close')
						});
						_this._end_loading();
						_this.reset();
						_this._go_back();
					}
				});

			}});
			return false;
		});

	},

	build_asks_btn:function() {

		var _this=this;


		var arr_links = [
			{
				title:GLB.LNG.add([
					'-',
					'Разделы меню: создание и редактирование (видео)'
					]),
				link: CFG.http+CFG.site_links['help_edit_sections']
			},
			{
				title:GLB.LNG.add([
					'-',
					'Блюда: создание и редактирование (видео)'
					]),
				link: CFG.http+CFG.site_links['help_edit_items']
			},
			{
				title:GLB.LNG.add([
					'-',
					'Настройка интерфейса'
					]),
				link: CFG.http+CFG.site_links['help_edit_customize_ui']
			},
			{
				title:GLB.LNG.add([
					'-',
					'Технические возможности и ограничения сервиса'
					]),
				link: CFG.http+CFG.site_links['features']
			}			
		];

		$.each(arr_links,function(i) {
			var $btn = $('<div>',{class:'std-form__help-button'}).html(this.title);
			var link = this.link;
			$btn.on('touchend',function() {				
				if(!_this.VIEW_SCROLLED &&	window.open(link,"_blank")){
					return false;	
				}				
			});
			_this.$btns_wrapper.append($btn);
		});
	
	},

	send_question:function(opt){

		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.send_user_question.php';
		
		this._now_loading();

		var inp_length = GLB.INPUTS_LENGTH.get('help-user-ask');
		var user_question = $.clear_user_input( this.$askMessage.val(), inp_length );

		var data = {
			user_question:user_question,
			id_cafe:GLB.THE_CAFE.get().id
		};

		this.AJAX && _this.AJAX.abort();

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            data:data,
            method:"POST",
            dataType: "jsonp",
            success: function (response) {
            	console.log('response',response)
            	if(response && !response.error){            		
					opt.onReady && opt.onReady();
				}else{	
				     _this._end_loading();            	
					GLB.VIEWS.modalMessage({
						title:GLB.LNG.get("lng_error"),
						message:"Не удается отправить сообщение.",
						btn_title:GLB.LNG.get('lng_close')
					});
				}
            },
            error:function(response) {
            	_this._end_loading();
				GLB.VIEWS.modalMessage({
					title:GLB.LNG.get("lng_error"),
					message:"Не удается отправить сообщение.",
					btn_title:GLB.LNG.get('lng_close')
				});
		        console.log(response);
			}
        });
			
	}



};


