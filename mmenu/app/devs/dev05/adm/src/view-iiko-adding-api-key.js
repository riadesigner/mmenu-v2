import {GLB} from './glb.js';

export var VIEW_IIKO_ADDING_API_KEY = {
	
	init:function(options){
		
		this._init(options);
									
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$btnSave = this.$view.find('.save');
		
		this.$inputIikoKey =  this.$form.find('input[name=iiko-api-key]');		
		this.$linkIikoHelp =  this.$form.find('a[name=link-iiko-help]');
		
		this.SITE_URL = CFG.base_url;
		this.USER_EMAIL = CFG.user_email;

		this.IIKO_HELP_URL = "/iiko-connection"; 

		this.update_content_once();


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
		
		_this.update_content();
		setTimeout(function(){				
			console.log("NOW!")
			_this._page_show();
		},300);
		
	},
	reset:function(){		
		this._reset();
		this._need2save(false);
		this._page_to_top();				
	},
	update_content_once:function(){
		this.$linkIikoHelp.attr({href:this.IIKO_HELP_URL});
	},
	update_content:function(){		
		let CAFE = GLB.THE_CAFE.get();
		let iikoApiKey = CAFE.iiko_api_key;
		iikoApiKey = iikoApiKey?iikoApiKey:"";
		this.$inputIikoKey.val(iikoApiKey);
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
					_this.save_api_key();
				};
			}});
			return false;
		});

		this.$inputIikoKey.on('keyup',function(e){			
			_this._need2save(_this.$inputIikoKey.val()!=="");
		});

	},
	save_api_key:function() {

        const fn = {
            okMessage:function(opt){
                var msg = GLB.LNG.get([
                '-',
                '<p>Связь ChefsMenu с платформой iiko успешно установлена.<br>Сейчас Панель Управления будет перезагружена.</p>'
                ]);
                GLB.VIEWS.modalMessage({
                    title:GLB.LNG.get("lng_attention"),
                    message:msg,
                    btn_title:GLB.LNG.get('lng_close'),
                    on_close:function(){
                        opt && opt.onClose && opt.onClose();
                    }
                });                 
            },
            errMessage:function(msg){
                var msg = msg?msg:'<p>Что-то пошло не так. Попробуйте позже или обратитесь к Администратору Сервиса</p>';
                GLB.VIEWS.modalMessage({
                    title:GLB.LNG.get("lng_error"),
                    message:msg,
                    btn_title:GLB.LNG.get('lng_close')
                });
            }           
        };        
        
        let new_iiko_api_key = this.$inputIikoKey.val();
        let is_correct = this.is_correct_input(new_iiko_api_key);
        if(!is_correct){
        	this._end_loading();
        	return false;
        };     
        
        this._now_loading();        

        this.save_the_key_asynq(new_iiko_api_key)
        .then((result)=>{
        	console.log('result',result)
        	fn.okMessage({onClose:()=>{
        		location.reload();
        	}});
        })
        .catch((result)=>{
			if(result && result.error && (typeof result.error === 'string' || result.error instanceof String) ){
                if(result.error.indexOf('illegal name')!==-1 ){
                    fn.errMessage(`<p>Вероятно API Key содержит недопустимые символы, проверьте правильность вашего ключа.</p>`);
                }else if(result.error.indexOf('unknown login')!==-1 ){
                    fn.errMessage(`<p>Такой API LOGIN в Iiko не найден. Проверьте правильность введенного ключа.</p>`);
                }else if( result.error.indexOf('unknown organization')!==-1 ){
                    fn.errMessage(`<p>API LOGIN правильный, но не найдена ни одна организация, соответствующая данному логину в Iiko.</p>`);                
                }else if( result.error.indexOf('has not terminal groups')!==-1 ){
                    fn.errMessage(`<p>Не найдена ни одна терминальная группа. Терминалы необходимы для отправки и приема заказов</p>`);
                }else if( result.error.indexOf('has not actual terminals')!==-1 ){
                    fn.errMessage(`<p>Не найден ни один активный терминал. Терминалы необходимы для отправки и приема заказов</p>`);                                    
                }else if(result.error.indexOf('has not menus')!==-1 ){
                    fn.errMessage(`<p>API LOGIN правильный, но к нему не прикреплено ни одно Внешнее меню в Iiko.</p>`);
                }else{
                    fn.errMessage();
                };
            }else{
                fn.errMessage();
            };
        	setTimeout(()=>{
        		this._end_loading();
        	},300);			
        });
        
	},
    save_the_key_asynq:function(key) {
        return new Promise((res,rej)=>{
            
            let PATH = 'adm/lib/iiko/';
            let url = PATH + 'lib.add_new_iiko_api_key.php';
        
            var data = {
                id_cafe:GLB.THE_CAFE.get().id,
                new_iiko_api_key:key
            };            
            
            this.AJAX = $.ajax({
                url: url+"?callback=?",
                dataType:"jsonp",
                data:data,
                method:"POST",
                success:function(result) {                    
                    console.log('result',result)
                    if(result && !result.error){                                              
                        res(result);
                    }else{
                        rej(result);    
                    }
                },
                error:function(result) {
                    console.log('err result',result)
                    rej(result)
                }
            });

        });
    },
    is_correct_input:function(iiko_api_key){   

        const old_iiko_api_key = GLB.THE_CAFE.get().iiko_api_key;        

        var errMsg = GLB.LNG.get([
            '-',
            'Вы не можете заменить ключ на тот же'
            ]);
        if(iiko_api_key==old_iiko_api_key){
            GLB.VIEWS.modalMessage({
                title:GLB.LNG.get("lng_attention"),
                message:errMsg,
                btn_title:GLB.LNG.get('lng_close')
            });
            return false;           
        };

        var errMsg = GLB.LNG.get(['','Слишком короткий ключ API.']);
        if(iiko_api_key.length > 0 && iiko_api_key.length < 3){
            GLB.VIEWS.modalMessage({
                title:GLB.LNG.get("lng_attention"),
                message:errMsg,
                btn_title:GLB.LNG.get('lng_close')
            });
            return false;
        };      

        var msk = /^[0-9a-zA-Z].[\-0-9a-zA-Z]*$/g;
        var errMsg = 'Несуществующий API-key.';
        if(!msk.test(iiko_api_key)){
            GLB.VIEWS.modalMessage({
                title:GLB.LNG.get("lng_attention"),
                message:errMsg,
                btn_title:GLB.LNG.get('lng_close')
            });
            return false;
        };

        return true;        
    }     

};