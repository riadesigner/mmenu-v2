import {GLB} from './glb.js';

export var VIEW_GET_CONTRACT = {
	
	init:function(options){
		
		this._init(options);
		
		
		this.$btnGetContract = this.$view.find('.view-get-contract__btn-send');		
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		
		this.update_contract_cost();	

		this.behavior();
	
		return this;

	},

	update:function(user){
		var _this=this;
		this.reset();
		this._update();
		this._page_hide();
		this.USER = user;
		this.USER_EMAIL = this.USER.email;		
		setTimeout(function(){ _this._page_show(); },300);
	},
	reset:function(){
		this._reset();
		this._page_to_top();
		this._need2save(false);
	},
	update_contract_cost:function(){
		// var $text = this.$view.find('.view-get-contract__text');				
		// var html = $text.html();
		// var cost_in_year = CFG.contract*12;
		// var contract_cost = "<nobr>"+cost_in_year+" руб./год</nobr>"; 		
		// var html = html.replace('[contract_cost]', contract_cost);
		// $text.html(html);
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
						" будет отправлена информация для подключения</p>",
					].join('');
				GLB.VIEWS.modalConfirm({
					title:"Подключение Меню",
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
						"<p>Вы уже отправляли заявку на подключение сегодня.</p>", 
						"На ваш электронный адрес "+_this.USER_EMAIL+" направлено письмо",
						"с номером договора и инструкцией.</p>",
						"<p>Если Вы не получили ответ, загляните, на всякий случай, в папку Спам ",
						"или обратитесь в контактный отдел Сервиса. Спасибо</p>"
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

		this.$btnGetContract.on('touchend',function(){
			_this._blur({onBlur:function(){

				!_this.LOADING && fn.beforeMessage({on_action:function(){

					_this.request_contract({
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

				}});

			}});
			return false;
		});

	},

	request_contract:function(opt){

		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.send_request_contract.php';
		
		this._now_loading();

		var data = {
			user_email:this.USER_EMAIL,
			id_cafe:GLB.THE_CAFE.get().id
		};

		this.AJAX && _this.AJAX.abort();

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            data:data,
            method:"POST",
            dataType: "jsonp",
            success: function (response) {
            	console.log('777 response',response)
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
		        console.log('777 err response',response)
			}
        });
			
	}



};


