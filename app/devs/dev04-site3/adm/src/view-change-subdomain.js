import {GLB} from './glb.js';

export var VIEW_CHANGE_SUBDOMAIN = {
	
	init:function(options){
		
		this._init(options);
							
		this.$inputSubdomain = this.$view.find('input[name=new-subdomain]');
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$btnSave = this.$view.find('.save');
		
		this.$bestAddress =  this.$form.find('.view-change-subdomain__best-address');
		this.$bestAddressText = this.$form.find('.view-change-subdomain__best-address_text');
		this.$addressPreview =  this.$form.find('.view-change-subdomain__preview'); 
		this.$allwaysAddressText = this.$form.find('.view-change-subdomain__allways-address_text');		

		this.$inputExternalURL = this.$view.find('input[name=external-url]');
		this.$externalURLPreview =  this.$form.find('.view-change-subdomain__external-url-preview'); 
		
		this.NEED_TO_SAVE = false;

		this.SITE_URL = CFG.base_url;
		this.USER_EMAIL = CFG.user_email;
		
		this.reset();
		this.behavior();		
		return this;

	},	

	update:function(){
		var _this=this;
		
		this._reset();
		this._update();
		this._page_hide();
		this._update_tabindex();		

		this.load({onReady:function(cafe){
	
			if(cafe.subdomain!==GLB.THE_CAFE.get('subdomain')){
				GLB.THE_CAFE.set({subdomain:cafe.subdomain});
			}
			if(cafe.external_url!==GLB.THE_CAFE.get('external_url')){
				GLB.THE_CAFE.set({external_url:cafe.external_url});
			}			

			_this.CAFE = GLB.THE_CAFE.get();
			_this.reset();
			_this.update_content();

			setTimeout(function(){				
				_this._page_show();
			},300);

		}});		
	},
	reset:function(){		
		this._reset();
		this._need2save(false);
		this._page_to_top();		
		this.update_address_preview(this.CAFE?this.CAFE.subdomain:"");
		this.update_external_url_preview(this.CAFE?this.CAFE.external_url:"");				
	},
	update_content:function(){

		var class_has_subdomain  = '.view-change-subdomain__if-has-subdomain';
		var class_no_subdomain  = '.view-change-subdomain__if-no-subdomain';

		if(this.CAFE.subdomain==""){
			this.$form.find(class_has_subdomain).hide();
			this.$form.find(class_no_subdomain).show();
		}else{
			this.$form.find(class_has_subdomain).show();
			this.$form.find(class_no_subdomain).hide();
		};

		var http = CFG.http;
		var allways_address = CFG.www_url+'/cafe/'+this.CAFE.uniq_name;

		if(this.CAFE.subdomain==""){
			var bestAddress = allways_address;
		}else{
			var bestAddress = this.CAFE.subdomain + '.' + CFG.www_url;
		};
		
		var bestAddressUrl = '<a href="'+http+bestAddress+'" >'+bestAddress+'</a>';
		var allwaysAddressUrl = '<a href="'+http+allways_address+'" >'+allways_address+'</a>';

		this.$bestAddressText.html(bestAddressUrl);
		this.$allwaysAddressText.html(allwaysAddressUrl);

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

		this.$btnSave.bind('touchend',function(){
			_this._blur({onBlur:function(){

				if(_this.NEED_TO_SAVE && !_this.LOADING){
									
					const saving_subdomain = _this.$inputSubdomain.val() !=="";
					saving_subdomain ? 
					 _this.save({onReady:function(){					
						setTimeout(function(){
							_this._go_back();
						},300);
					}}):
					_this.save_external_url_async()
					.then(()=>{
						setTimeout(()=>{
							_this._go_back();
						},300);						
					})	
				};

			}});
			return false;
		});

		this.$inputSubdomain.on('keyup',()=>{
			this.is_need_to_save();
			this.update_address_preview(this.$inputSubdomain.val());
		});

		this.$inputExternalURL.on('keyup',()=>{
			this.is_need_to_save();
			this.update_external_url_preview(this.$inputExternalURL.val());
		});


	},

	is_need_to_save:function(){
		this.$inputSubdomain.val() !=="" ||  this.$inputExternalURL.val()!=="" ? this._need2save(true): this._need2save(false);
	},
	update_address_preview:function(new_subdomain=""){				
		const str_preview = `<strong>${new_subdomain||"yourname"}</strong>.${CFG.www_url}`;
		this.$addressPreview.html(str_preview);			
	},

	update_external_url_preview:function(url=""){
		const defaultExternalUrl = this.CAFE?this.CAFE.external_url:""; 		
		const str_external_url =  `<strong>${url||defaultExternalUrl||"не указан"}</strong>`;
		this.$externalURLPreview.html(str_external_url);			
	},	

	is_correct_input:function(new_subdomain){

		var errMsg = GLB.LNG.get([
			'You cannot replace the name with the same',
			'Вы не можете заменить имя на тоже самое'
			]);
		if(new_subdomain==this.CAFE.subdomain){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:errMsg,
				btn_title:GLB.LNG.get('lng_close')
			});
			return false;			
		};

		var msk = /^[0-9a-zA-Z].[\-0-9a-zA-Z]*$/g;
		var errMsg = GLB.LNG.get([
			'Only Latin characters, numbers, and dashes are allowed for a name. The name must begin with a letter or number.',
			'Для имени допустимы только бувы латинского алфавита, цифры и тире. Имя должно начинаться с буквы или цифры.'
				]);
		if(!msk.test(new_subdomain)){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:errMsg,
				btn_title:GLB.LNG.get('lng_close')
			});
			return false;
		};

		var errMsg = GLB.LNG.get(['Name too short','Слишком короткое имя']);
		if(new_subdomain.length < 3){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:errMsg,
				btn_title:GLB.LNG.get('lng_close')
			});
			return false;
		};

		return true;
		
	},

	load:function(opt){
		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.get_cafe_info.php';

		
		var fn = {
			errMessage:function(){				
				GLB.VIEWS.modalMessage({
					title:GLB.LNG.get("lng_error"),
					message:[
						"<p>Невозможно загрузить информацию о Меню.</p>",
						"<p>Попробуйте позже, или обратитесь к администратору сервиса</p>"
					].join(" "),
					btn_title:GLB.LNG.get('lng_close')
				});
			}
		};

		this._now_loading();

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType:"jsonp",
            data:{},
            method:"POST",
            success:function(response) {
            	console.log(response);
            	_this._end_loading();
            	if(response && !response.error){
	            	_this._end_loading();
	            	opt && opt.onReady && opt.onReady(response.cafe);
            	}else{
            		response.error && fn.errMessage();
            	}
            },
            error:function(response) {
            	console.log(response);
				_this._end_loading();
				fn.errMessage();
			}
        });

	},

	save:function(opt){

		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'usr.subdomain_update.php';

		var inp_length = GLB.INPUTS_LENGTH.get('new-subdomain');
		var new_subdomain = $.clear_user_input(this.$inputSubdomain.val(), inp_length);
		if(!this.is_correct_input(new_subdomain)) {
			return false;	
		};

		var fn = {
			okMessage:function(opt){
				var msg = GLB.LNG.get([
				['<p>An email has been sent to <b>'+ _this.USER_EMAIL +'</b>.</p>',
					'<p>Please, open the letter <nobr>and confirm</nobr> the name change.</p>'].join(''),
				['<p>На ваш адрес <b>'+_this.USER_EMAIL+'</b> отправлено письмо.</p>',
					'<p>Откройте письмо <nobr>и подтвердите</nobr> смену имени.</p>'].join('')
				]);
				GLB.VIEWS.modalMessage({
					title:GLB.LNG.get("lng_attention"),
					message:msg,
					btn_title:GLB.LNG.get('lng_close'),
					on_close:function(){
						opt && opt.on_close && opt.on_close();
					}
				});					
			},
			errMessage:function(){
				var msg = GLB.LNG.get([
					'Something wrong. Please try later or ask to Service Administrator',
					'Что-то пошло не так. Попробуйте позже или обратитесь к Администратору Сервиса'
					]);
				GLB.VIEWS.modalMessage({
					title:GLB.LNG.get("lng_error"),
					message:msg,
					btn_title:GLB.LNG.get('lng_close')
				});
			},
			alreadyExistMessage:function(){
				var msg = GLB.LNG.get([
					'This address already exists, try a different name',
					'Такой адрес уже существует, попробуйте другое имя'
					]);
				GLB.VIEWS.modalMessage({
					title:GLB.LNG.get("lng_error"),
					message:msg,
					btn_title:GLB.LNG.get('lng_close')
				});
			},
			illegalNameMessage:function(){
				var msg = GLB.LNG.get([
					'Forbidden address, try a different name',
					'Запрещенный адрес, попробуйте другое имя'
					]);
				GLB.VIEWS.modalMessage({
					title:GLB.LNG.get("lng_error"),
					message:msg,
					btn_title:GLB.LNG.get('lng_close')
				});				
			},
			limitedModeMessage:function(){
				var msg = GLB.LNG.get([
					['<p>Your Menu works in test mode.</p>',
					'<p>To take full advantage of the service, please',
					'go to Settings and select Remove limitations.</p>'].join(''),
					['<p>Ваше Меню работает в тестовом режиме.</p>',
					'<p>Чтобы воспользоваться всеми возможностями сервиса, ',
					'перейдите в Настройки и выберите пункт Снять ограничения.</p>'].join('')
					]);
				GLB.VIEWS.modalMessage({
					title:GLB.LNG.get("lng_attention"),
					message:msg,
					btn_title:GLB.LNG.get('lng_close')
				});
			}
		};


		if(parseInt(this.CAFE.cafe_status,10)!==2){
			fn.limitedModeMessage();
			return;
		};

		var data = {
			id_cafe:this.CAFE.id,
			new_subdomain:new_subdomain
		};	

		this._now_loading();

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType:"jsonp",
            data:data,
            method:"POST",
            success:function(confirm) {                     
            	_this._end_loading();
            	if(confirm && !confirm.error){	            	
	            	fn.okMessage({on_close:function(){
			        	opt&&opt.onReady&&opt.onReady();        		
	            	}});
            	}else{
            		if(confirm.error && confirm.error.indexOf("already exist")!==-1){
            			fn.alreadyExistMessage();
            		}else if(confirm.error && confirm.error.indexOf("illegal name")!==-1){
            			fn.illegalNameMessage();
					}else if(confirm.error && confirm.error.indexOf("limited mode")!==-1){
						fn.limitedModeMessage();
            		}else{            			
            			fn.errMessage();
            		}
            	}
            },
            error:function(response) {
            	console.log(response);
				_this._end_loading();
				fn.errMessage();
			}
        });

	},
	save_external_url_async:function(){
		return new Promise((res, rej)=>{

			const _this = this;
			const PATH = 'adm/lib/';
			const url = PATH + 'usr.external_url_update.php';

			var fn = {
				errMessage:function(){
					var msg = 'Не удалось обновить внешний адрес. Попробуйте позже или обратитесь к Администратору Сервиса';
					GLB.VIEWS.modalMessage({
						title:GLB.LNG.get("lng_error"),
						message:msg,
						btn_title:GLB.LNG.get('lng_close')
					});
				}				
			};

			const data = {
				id_cafe:this.CAFE.id,
				new_external_url:this.$inputExternalURL.val(),
			};	

			this._now_loading();

			this.AJAX = $.ajax({
				url: url,
				dataType:"json",
				data:data,
				method:"POST",
                xhrFields: {
                    withCredentials: true  // Для отправки cookies при CORS
                },				
				success:function(result) {                     
					_this._end_loading();
					if(result && !result.error){
						res({success:'ok'});
					}else{
						fn.errMessage(result.error);
						rej(result.error);
					}
				},
				error:function(err) {
					console.log(err);
					_this._end_loading();
					fn.errMessage();
					rej(err);
				}
			});
			
		})
	}

};