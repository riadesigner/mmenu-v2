import {GLB} from './glb.js';

export var VIEW_IIKO_WEB_HOOKS = {
	
	init:function(options){
		
		this._init(options);
									
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$btnSave = this.$view.find('.save');
				
		this.$previewApiLoginName = this.$form.find('.iiko-api-login-name span');
		this.$previewIikoWebHook = this.$form.find('.iiko-web-hook_preview');
		this.$inputNewWebHook = this.$form.find('input[name=new-iiko-web-hook]');

		this.reset();		
		this.behavior();		

		return this;

	},	

	update:function(){		
		
		this.reset();
		
		this._update();
		this._page_hide();
		this._update_tabindex();
		
		// SHOW CURRENT IIKO WEB HOOK
		this.read_iiko_web_hook({onReady:(webHooksUri, apiLoginName)=>{
			this.show_iiko_web_hook(webHooksUri, apiLoginName);
			this._end_loading();
		}});
		
		setTimeout(()=>{							
			this._page_show();
		},300);
		
	},

	show_iiko_web_hook:function(webHooksUri, apiLoginName){
		this.$previewApiLoginName.html(apiLoginName);
		this.$previewIikoWebHook.html(`<a href="#" target="_blank">${webHooksUri}</a>`);
	},

	read_iiko_web_hook:function(opt){

		this._now_loading();

		let PATH = 'adm/lib/iiko/';
		let url = PATH + 'lib.iiko_get_webhook_url.php';
	
		var data = {
			id_cafe:GLB.THE_CAFE.get().id,
		};     		
		this.AJAX = $.ajax({
			url:url,
			dataType:"json",
			data:data,
			method:"POST",
			xhrFields: {
				withCredentials: true  // Для отправки cookies при CORS
			},			
			success:(result)=> {                    
				console.log('result',result)
				if(result && !result.error){                                              					
					const {apiLoginName, webHooksUri} = result.webHooks;
					console.log(apiLoginName, webHooksUri)
					opt && opt.onReady && opt.onReady(webHooksUri, apiLoginName);
				}else{
					this.show_modal_error(result.error);					
					this._end_loading();
				}				
			},
			error:(result)=> {
				console.log('err result',result)
				this.show_modal_error();
				this._end_loading();
			}
		});
	},
		
	check_if_need2save:function() {
		
		let need2save = true;
		
		// SUMMARY		
		this._need2save(need2save);
		return need2save;
	},
	reset:function(){		
		this._reset();
		this._need2save(false);
		this._page_to_top();		
		// this.$inputDelKey.val("");		
	},
	
	behavior:function()	{
		var _this = this;

		this._behavior();

		this.$btnBack.bind('touchend',(e)=>{
			this._blur({onBlur:()=>{
				!this.LOADING && this._go_back();
			}});			
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$inputNewWebHook.on('keyup',()=>{
			this.check_if_need2save();
		});		

		this.$btnSave.bind('touchend',function(e){
			_this._blur({onBlur:function(){
				if(_this.NEED_TO_SAVE && !_this.LOADING){	
					_this.check_if_need2save() && _this.save();
				};
			}});
			e.originalEvent.cancelable && e.preventDefault();
		});	

	},
 
	save:function() {

		// // CHECK SPECIAL WORD
		// if(this.$inputDelKey.val()=="delete"){
		// 	this.remove_iiko_login()	
		// }else{
		// 	this.save_current_params_asynq()
		// 	.then((vars)=>{
		// 		this.show_all_updated_and_reload();
		// 	})
		// 	.catch((vars)=>{
		// 		console.log('error',vars);
		// 		this.show_modal_error();
		// 		setTimeout(()=>{ this._end_loading();},300);
		// 	})
		// };
		
	},

	show_modal_error:function(msg=""){
		const strMsg = msg!=="" ? msg : "<p>Что-то пошло не так. Попробуйте позже или обратитесь к разработчику Сервиса</p>"; 
		GLB.VIEWS.modalMessage({
			title:GLB.LNG.get("lng_error"),
			message:strMsg,
			btn_title:GLB.LNG.get('lng_close')
		});
	},

	show_modal_ok:function(msg="", opt){
        const fn = {
            okMessage:function(){                
                GLB.VIEWS.modalMessage({
                    title:GLB.LNG.get("lng_attention"),
                    message:msg,
                    btn_title:GLB.LNG.get('lng_close'),
                    on_close:function(){
                        opt && opt.onClose && opt.onClose();
                    }
                });
            }
        };
		fn.okMessage();
	}

};