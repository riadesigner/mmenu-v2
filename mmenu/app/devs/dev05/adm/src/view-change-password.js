import {GLB} from './glb.js';

export var VIEW_CHANGE_PASSWORD = {
	
	init:function(options){
		
		this._init(options);
									
		this.$inputPass = this.$view.find('input[name=new-password]');
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$btnSave = this.$view.find('.save');

		this.SITE_URL = CFG.base_url;
		this.USER_EMAIL = CFG.user_email;

		this.behavior();
		this.reset();
		return this;

	},	

	update:function(id_user){	
		var _this=this;
		this._update();
		this._page_hide();
		this._update_tabindex();
		this.ID_USER = id_user;
		this.reset();
		setTimeout(function(){ _this._page_show();},300);
	},
	reset:function(){		
		this._reset();
		this._need2save(false);
		this._page_to_top();
		this.pass_show();
	},	
	behavior:function()	{
		var _this = this;

		this._behavior();

		this.$btnBack.bind('touchend',function(){
			_this._blur({onBlur:function(){
				!_this.LOADING && _this._go_back();
			}});			
			return false;
		});



		var fn = {
			beforeMessage:function(opt){
				var ask = [
						"<p>На ваш адрес <b>"+_this.USER_EMAIL+"</b>",
						"будет отправлена ссылка",
						"для подтверждения смены пароля.</p>"
					].join(" ");
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
					title:"Запрос отправлен!",
					message:[						
						"На ваш электронный адрес: <strong>"+_this.USER_EMAIL+"</strong>",
						"отправлена ссылка. Перейдите по ней, чтобы",
						"сохранить изменение пароля"
					].join(" "),
					btn_title:GLB.LNG.get('lng_close'),
					on_close:function(){						
						setTimeout(function(){
							_this._go_back();
						},300);						
					}
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

		this.$btnSave.bind('touchend',function(){
			_this._blur({onBlur:function(){

				if(_this.NEED_TO_SAVE){				
					!_this.LOADING && fn.beforeMessage({
						on_action:function(){
							_this.save({
								onReady:function(){
									fn.okMessage();								
								},
								onError:function(){
									fn.errMessage();
								}
							});		
						}
					});
				};

			}});			
			return false;
		});

		this.$inputPass.on('keyup',function(e){
			_this.is_need_to_save();
		});

	},

	is_need_to_save:function(){
		this.$inputPass.val() !=="" ? this._need2save(true): this._need2save(false);
	},

	is_correct_input:function(newpass){
		
		var msk = /^[\-\_0-9a-zA-Z]*$/g;
		
		if(!msk.test(newpass)){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:[
					"<p>Для пароля допустимы следующие символы:</p>",
					"<ul><li>Бувы латинского алфавита</li><li>Цифры</li>",
					"<li>Тире</li><li>Знак подчеркивания</li></ul></p>"
				].join(" "),
				btn_title:GLB.LNG.get('lng_close')
			});			
			return false;
		}else if(newpass.length < 6){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:GLB.LNG.get("lng_min_pass_length"),
				btn_title:GLB.LNG.get('lng_close')
			});
			return false;
		}else{
			return true;	
		}

	},
	pass_hide:function(){		
		this.$inputPass.attr({type:'password'})
	},
	pass_show:function(){
		this.$inputPass.attr({type:'input'})
	},

	save:function(opt){

		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'usr.password_update.php';

		var inp_length = GLB.INPUTS_LENGTH.get('new-password');
		var newpass = $.clear_user_input(this.$inputPass.val(), inp_length);
		if(!this.is_correct_input(newpass)) return;

		this.$inputPass.blur();
		this.pass_hide();

		var data ={newpass:newpass};
		this._now_loading();

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType:"jsonp",
            data:data,
            method:"POST",
            success:function(confirm) {
            	console.log('confirm',confirm);
            	_this._end_loading();
            	if(confirm && !confirm.error){
	            	_this._end_loading();
	            	opt&&opt.onReady&&opt.onReady();
            	}else{					
					opt&&opt.onError&&opt.onError();
					_this.pass_show();			        
            	}
            },
            error:function(response) {
				_this._end_loading();
				_this.pass_show();
		        console.log(response);
		        opt&&opt.onError&&opt.onError();
			}
        });

	}

};