import {GLB} from './glb.js';

export var VIEW_CAFE_LINK = {
	
	init:function(options){
		
		this._init(options);
		
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$btnGetQRcode = this.$footer.find('.btn-get-qrcode');

		this.$home_menu_link = this.$form.find('.menu-home-link a');		
		this.$qrCode = this.$form.find('.view-cafe-link__qr-code');

		this.USER_EMAIL = CFG.user_email; 

		this.behavior();
		return this;

	},

	update:function(){
		var _this=this;						

		this._update();		
		this._page_hide();
		this._now_loading();
		this.reset();
		this._update_tabindex();
		
		var cafe = GLB.THE_CAFE.get();
				
		this.$home_menu_link.html(GLB.THE_CAFE.get_link('name'))
		.attr({ href : GLB.THE_CAFE.get_link('url') });

		const qrcodeStr = `${cafe.qrcode}?r=${Math.random()}`;
		
		this.$qrCode.html(`<img src='${qrcodeStr}'>`);

		var img = new Image();
		img.onload = function(){
			_this._end_loading();
			_this._page_show();
		};
		img.onerror = function(err){
			_this._end_loading();			
			_this.ask_to_recreate_qrcode({
				on_action:()=>{					
					_this.do_recreating_qrcode_async()
					.then((vars)=>{
						_this._end_loading();
						_this._page_show();
						console.log('vars',vars)
						const qrcode = vars['qrcode_image_url'];
						GLB.THE_CAFE.set({qrcode:qrcode});
						_this.update();						
					})
					.catch((err)=>{
						_this._end_loading();
						const errMessage = [
							"<p>Не удалось пересоздать qr-code.</p>",
							"<p>Попробуйте позже или обратитесь к разработчику сервиса.</p>"
						].join('');
						_this.show_err_message_and_back(errMessage);
						console.log('err = ',err);
					})
				},
				on_cancel:()=>{
					_this._go_back();
				}
			});
		};		
		img.src = qrcodeStr;
		
	},
	reset:function() {		
		this._reset();
		this._page_to_top();
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
	

		var fn = {
			beforeMessage:function(opt){
				var ask = [
						"<p>На ваш адрес: "+_this.USER_EMAIL,
						"будет отправлен QR-код вашего Меню и инструкция по применению</p>",
					].join(' ');
				GLB.VIEWS.modalConfirm({
					title:"Отправка сообщения",
					ask:ask,
					action:function(){
						opt.on_action && opt.on_action();
					},
					cancel:function(){},
					buttons:[GLB.LNG.get("lng_ok"),GLB.LNG.get("lng_cancel")]
				});				
			},
			okMessage:function(opt){
				var _that =this;
				GLB.VIEWS.modalMessage({
					title:"Отлично!",
					message:[
						"Информация отправлена ",
						"на ваш электронный адрес: "+_this.USER_EMAIL,
					].join(''),
					btn_title:GLB.LNG.get('lng_close'),
					on_close:function(){						
						setTimeout(function(){
							_this._go_back();
						},300);						
					}
				});				
			},
			limitMessage:function(){
				GLB.VIEWS.modalMessage({
					title:"Внимание!",
					message:[
						"<p>Вы уже запрашивали QR-код менее 5 минут назад.</p>", 
						"На ваш электронный адрес "+_this.USER_EMAIL+" было направлено письмо",
						"с кодом и инструкцией.</p>",
						"<p>Если Вы не получили ответ, загляните, на всякий случай, в папку Спам,",
						"попробуйте получить код позже или обратитесь в контактный отдел Сервиса</p>"
					].join(' '),
					btn_title:GLB.LNG.get('lng_close')
				});
			},			
			errMessage:function(){
				var message = [
						"Информация не может быть отправлена ",
						"на ваш адрес "+_this.USER_EMAIL+", ",
						"попробуйте позже или обратитесь ",
						"к администратору Сервиса"
					].join('');					
				GLB.VIEWS.modalMessage({
					title:GLB.LNG.get("lng_error"),
					message: message,
					btn_title:GLB.LNG.get('lng_close')
				});				
			}
		};

		this.$btnGetQRcode.on('touchend',function(){			
			_this._blur({onBlur:function(){

				!_this.LOADING && fn.beforeMessage({on_action:function(){				
					_this.send({
						onReady:function(){
							fn.okMessage();
						},
						onLimit:function(){
							fn.limitMessage();
						},
						onError:function(){
							fn.errMessage();
						}
					});
					return false;
				}});

			}});	
			return false;
		});		

		this.$view.find('.btn-cafe-description').on('touchend',function(){
			GLB.VIEWS.setCurrent(GLB.VIEW_MAIN_HELP.name);
			return false;
		});

	},
	ask_to_recreate_qrcode:function(opt){
		const ask = [
			`</p>Не получилось загрузить (найти) qr-code.</p>`,
			`</p>Пересоздать его?</p>`,
		].join(' ');
		GLB.VIEWS.modalConfirm({
			title:"Отправка сообщения",
			ask:ask,
			action:function(){
				opt.on_action && opt.on_action();
			},
			cancel:function(){
				opt.on_cancel && opt.on_cancel();
			},
			buttons:[GLB.LNG.get("lng_yes"),GLB.LNG.get("lng_no")]
		});	
	},
	show_err_message_and_back(errMessage){			
		GLB.VIEWS.modalMessage({
			title:GLB.LNG.get("lng_error"),
			message: errMessage,
			btn_title:GLB.LNG.get('lng_close'),
			on_close:()=>{
				this._blur({onBlur:()=>{
					this._go_back();
				}});
			}
		});				
	},
	do_recreating_qrcode_async:function(){
		return new Promise((res,rej)=>{

			this._now_loading();
			
			const PATH = 'adm/lib/';
			const url = PATH + 'lib.recreate_qrcodeimg.php';

			const data = {
				id_cafe:GLB.THE_CAFE.get().id
			};
	
			this.AJAX = $.ajax({
				url: url+"?callback=?",
				data:data,
				method:"POST",
				dataType: "jsonp",
				success: function (response) {					
					if(!response.error){
						res(response);
					}else{
						rej(response.error);
					};
				},
				error:function(response) {					
					rej(response);					
				}
			});
		})		
	},
	send:function(opt){

		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.send_qr_code.php';
		
		this._now_loading();

		var data = {
			id_cafe:GLB.THE_CAFE.get().id
		};

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            data:data,
            method:"POST",
            dataType: "jsonp",
            success: function (response) {
            	console.log("response=",response)
            	_this._end_loading();
            	if(!response.error){
					opt.onReady && opt.onReady();
            	}else if(response.error && response.error.indexOf('limit message per day') !== -1){
					opt.onLimit && opt.onLimit();
            	}else{
            		opt.onError && opt.onError();
            	};
            },
            error:function(response) {
            	_this._end_loading();
				opt.onError && opt.onError();
		        console.log("err",response);
			}
        });
			
	}

};