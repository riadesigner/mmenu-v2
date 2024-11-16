import {GLB} from './glb.js';

export var VIEW_CUSTOMIZE_ALL = {
	
	init:function(options){
		
		this._init(options);
				
		this.$btnCafeInfo = this.$form.find('.btn-cafe-info');
		this.$btnMenuLink = this.$form.find('.btn-menu-link');
		this.$btnChangeLanguage = this.$form.find('.btn-change-language');
		this.$btnChangeTables = this.$form.find('.btn-change-tables');

		// for cafe-status-test or cafe-status-archive 
		this.$btnGetContract = this.$form.find('.btn-get-contract');		
		this.$titleGetContract = this.$form.find('.title-get-contract');				

		this.$btnChangePassword = this.$form.find('.btn-change-password');
		this.$btnChangeCartSettings = this.$form.find('.btn-cart-settings');
		this.$btnChangeSubdomain = this.$form.find('.btn-change-subdomain');
		this.$btnAddIiko = this.$form.find('.btn-add-iiko');				
		this.$btnIikoCustomization = this.$form.find('.btn-iiko-customization');				
		this.$btnIikoModifDict = this.$form.find('.btn-iiko-modifiers-dictionary');		
		
		this.$btnAccountExit = this.$form.find('.btn-account-exit');
		this.$btnBack = this.$footer.find('.back, .close, .cancel');

		this.$cafe_status = this.$view.find('.view-customize-all__cafe-status');
		this.$adm_email = this.$view.find('.view-customize-all__user-email');
		this.$app_version = this.$view.find('.view-customize-all__app-version');
		
		this.siteUrl = CFG.http+CFG.www_url;
		this.update_app_version(CFG.app_version);					
		
		this.behavior();
		this.reset();

		return this;
		
	},

	update_iiko_mode:function() {		
		this.IIKO_MODE = GLB.THE_CAFE.is_iiko_mode();
		this.IIKO_MODE ? this.$view.addClass("iiko-mode"):this.$view.addClass("chefsmenu-mode");
	},
	update_cafe_status:function(new_cafe_status) {
		let status_name="";				
		let status_mode = "";

  		switch (new_cafe_status){
  			case 2: 
  				status_name = "Договор"; 
  				status_mode = "cafe-status-contract";
  				break;
  			case 1: 
  				status_name = "В архиве"; 
  				status_mode = "cafe-status-archive";
  				break;  			
  			default: 
  				status_name = "Тестовый период";
  				status_mode = "cafe-status-test";
  		};
		
		this.$view.addClass(status_mode);							
		this.$cafe_status.html("Статус: <span>" + status_name + "</span>");
	},

	update:function(){

		this._update();
		this.reset();
		this._page_hide();		
		this._now_loading();
		
		this.update_iiko_mode();		

		this.load_user_info()
		.then((vars)=>{						
			let [user, new_app_version ] = vars;
				 
			if(CFG.app_version !== new_app_version){
			
				// reload admin app version changed
				this._update_app(new_app_version);
			
			}else{		

				this.USER = user;
				this.$adm_email.html("Администратор: <span>" + user.email +"</span>");

				
				this.load_cafe_info()
				.then((cafe)=>{					
					let current_cafe_status = parseInt(GLB.THE_CAFE.get('cafe_status'),10);
					let new_cafe_status = parseInt(cafe.cafe_status,10);
            		if(new_cafe_status !== current_cafe_status){            			

            			// reload admin if status cafe changed
            			this._update_app_as_status_changed(new_cafe_status);
            		}else{
	            		this.update_cafe_status(new_cafe_status);				
						this._page_show();
						setTimeout(()=>{this._end_loading();},300);
            		}
				});
			}
			
		});

	},

	update_app_version:function(app_version){
		var str = this.$app_version.html().replace('[app-version]',app_version);
		this.$app_version.html(str);
	},

	reset:function(){
		this._reset();
		this._page_to_top();
		this.$view.find('.user-mail span').html('...');
	},	
	behavior:function()	{
		var _this = this;

		this._behavior();

		this.$btnBack.on('touchend',function(e){			
			!_this.LOADING && _this._go_back();
			e.originalEvent.cancelable && e.preventDefault();
		});	

		this.$btnCafeInfo.on('touchend',function(e){			
			if(!_this.VIEW_SCROLLED){				
			GLB.VIEW_CUSTOMIZING_CAFE.update();
			GLB.VIEWS.setCurrent(GLB.VIEW_CUSTOMIZING_CAFE.name);
			};
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$btnChangeLanguage.on('touchend',function(e){			
			if(!_this.VIEW_SCROLLED){				
			GLB.VIEW_CUSTOMIZE_INTERFACE.update(_this.USER);
			GLB.VIEWS.setCurrent(GLB.VIEW_CUSTOMIZE_INTERFACE.name);
			};
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$btnChangeTables.on('touchend',function(e){			
			if(!_this.VIEW_SCROLLED){				
			GLB.VIEW_CAFE_TABLES.update(_this.USER);
			GLB.VIEWS.setCurrent(GLB.VIEW_CAFE_TABLES.name);
			};
			e.originalEvent.cancelable && e.preventDefault();
		});		

		this.$btnChangeCartSettings.on('touchend',function(e){			
			if(!_this.VIEW_SCROLLED){				
			GLB.VIEW_CUSTOMIZING_CART.update(_this.ID_USER);
			GLB.VIEWS.setCurrent(GLB.VIEW_CUSTOMIZING_CART.name);
			};
			e.originalEvent.cancelable && e.preventDefault();
		});		

		this.$btnMenuLink.on('touchend',function(e){			
			if(!_this.VIEW_SCROLLED){				
			GLB.VIEW_CAFE_LINK.update();
			GLB.VIEWS.setCurrent(GLB.VIEW_CAFE_LINK.name);
			};
			e.originalEvent.cancelable && e.preventDefault();
		});
	
		this.$btnGetContract.on('touchend',function(e){			
			if(!_this.VIEW_SCROLLED){
			GLB.VIEW_GET_CONTRACT.update(_this.USER);
			GLB.VIEWS.setCurrent(GLB.VIEW_GET_CONTRACT.name);
			};
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$btnChangePassword.on('touchend',function(e){			
			if(!_this.VIEW_SCROLLED){
			GLB.VIEW_CHANGE_PASSWORD.update(_this.ID_USER);
			GLB.VIEWS.setCurrent(GLB.VIEW_CHANGE_PASSWORD.name);
			};
			e.originalEvent.cancelable && e.preventDefault();
		});


		this.$btnChangeSubdomain.on('touchend',function(e){			
			if(!_this.VIEW_SCROLLED){
			GLB.VIEW_CHANGE_SUBDOMAIN.update();
			GLB.VIEWS.setCurrent(GLB.VIEW_CHANGE_SUBDOMAIN.name);
			};
			e.originalEvent.cancelable && e.preventDefault();
		});


		this.$btnAddIiko.on('touchend',function(e){			
			if(!_this.VIEW_SCROLLED){
			GLB.VIEW_IIKO_ADDING_API_KEY.update();
			GLB.VIEWS.setCurrent(GLB.VIEW_IIKO_ADDING_API_KEY.name);
			};
			e.originalEvent.cancelable && e.preventDefault();
		});		

		this.$btnIikoCustomization.on('touchend',function(e){			
			if(!_this.VIEW_SCROLLED){				
			GLB.VIEW_IIKO_CUSTOMIZATION.update();
			GLB.VIEWS.setCurrent(GLB.VIEW_IIKO_CUSTOMIZATION.name);
			};
			e.originalEvent.cancelable && e.preventDefault();
		});			

		this.$btnIikoModifDict.on('touchend',function(e){			
			if(!_this.VIEW_SCROLLED){				
			GLB.VIEW_IIKO_MODIF_DICTIONARY.update();
			GLB.VIEWS.setCurrent(GLB.VIEW_IIKO_MODIF_DICTIONARY.name);
			};
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$btnAccountExit.on('touchend',function(e){			
			if(!_this.VIEW_SCROLLED){				
				_this.exit();				
			};
			e.originalEvent.cancelable && e.preventDefault();
		});

	},

	exit:function() {		
		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'usr.exit.php';
			
		this._now_loading();

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType:"jsonp",
            data:{},
            method:"POST",
            success:function(answer) {
            	_this._end_loading();
            	if(answer&&!answer.error){
	            	location.href = _this.siteUrl;
            	}else{					
					GLB.VIEWS.modalMessage({
						title:GLB.LNG.get("lng_error"),
						message:answer.error,
						btn_title:GLB.LNG.get('lng_close')
					});			        
            	}
            },
            error:function(response) {
				_this._end_loading();
				GLB.VIEWS.modalMessage({
					title:GLB.LNG.get("lng_error"),
					message:"Не могу выйти из аккаунта",
					btn_title:GLB.LNG.get('lng_close')
				});
			}
        });
	},

	load_user_info:function(){

		return new Promise((res,rej)=>{

			var _this = this;
			var PATH = 'adm/lib/';
			var url = PATH + 'usr.get_info.php';

	        this.AJAX = $.ajax({
	            url: url+"?callback=?",
	            dataType:"jsonp",
	            data:{},
	            method:"POST",
	            success:function(response) {
	            	_this._end_loading();
	            	if(!response.error){	            

	            		res([response['user'] , response['app-version']]); 

	            	}else{					
						GLB.VIEWS.modalMessage({
							title:GLB.LNG.get("lng_error"),
							message:"Не могу загрузить данные пользователя",
							btn_title:GLB.LNG.get('lng_close')
						});
				        console.log("err:",response.error);
	            	}
	            },
	            error:function(response) {
					_this._end_loading();		        
			        _this._reload_by_err();
			        console.log("err load page",response);
			        return false;
				}
	        });

		});
	},
	load_cafe_info:function(){
		return new Promise((res,rej)=>{

			var _this = this;
			var PATH = 'adm/lib/';
			var url = PATH + 'lib.get_cafe_info.php';

	        this.AJAX = $.ajax({
	            url: url+"?callback=?",
	            dataType:"jsonp",
	            data:{},
	            method:"POST",
	            success:function(response) {	            	
	            	if(!response.error){
	            		console.log('---load_cafe_info response---',response)
	            		res(response['cafe']);	            		
	            	}else{					
						GLB.VIEWS.modalMessage({
							title:GLB.LNG.get("lng_error"),
							message:"Не могу загрузить данные меню",
							btn_title:GLB.LNG.get('lng_close')
						});
				        console.log("err:",response.error);
	            	}
	            },
	            error:function(response){
	            	_this._end_loading();				
			        _this._reload_by_err();
			        console.log("err load page",response);
			        return false;
				}
	        });


		});
	}

};