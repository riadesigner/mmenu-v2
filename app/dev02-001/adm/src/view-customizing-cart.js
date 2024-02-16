import {GLB} from './glb.js';

export var VIEW_CUSTOMIZING_CART = {
	
	init:function(options){
		
		this._init(options);

		this.$btnSave = this.$view.find('.save');
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		
		this.$buttonsCartMode = this.$view.find('.customizing-cart__cart-mode');
		this.$buttonsDeliveryMode = this.$view.find('.customizing-cart__delivery-mode');
		this.$buttonsOrderWay = this.$view.find('.customizing-cart__order-way');
		
		this.sa_bnt_upd_tg_keys =  this.$view.find('button[name="update_all_tg_keys"]');
		
		this.$iikoSectionOnly = this.$view.find('.iiko-section-only');

		this.$btn_tg_key_waiter =  this.$view.find('button.key-waiter');
		this.$btn_tg_key_manager =  this.$view.find('button.key-manager');
		this.$btn_tg_key_supervisor =  this.$view.find('button.key-supervisor');
		

		this.behavior();

		return this;

	},
	reset:function(){
		this._reset();
		this._need2save(false);
		this._page_to_top();
		this.iiko_set_section_visibility(GLB.THE_CAFE.is_iiko_mode());
	},

	update:function(USER){
		

		var _this=this;
		this._update();
		this._page_hide();
		
		var cafe = GLB.THE_CAFE.get();
		
		this.ID_CAFE = cafe.id;
		
		this.NEW_CART_MODE = parseInt(cafe.cart_mode,10);
		this.NEW_DELIVERY_MODE = parseInt(cafe.has_delivery,10);		
		this.NEW_ORDER_WAY = parseInt(cafe.order_way,10);		
	
		this.reset();		
		this.rebuild();
		
		this.load_tg_keys_async()
		.then((keys)=>{				
			this.update_tg_keys_buttons(keys);		
			setTimeout(()=>{ 
				this._end_loading();
				this._page_show(); 
			},300);
		})
		.catch((vars)=>{
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:"Не удалось загрузить ключи для телеграма",
				btn_title:GLB.LNG.get('lng_ok')
			});
			setTimeout(()=>{ 
				this._end_loading();
				this._page_show(); 
			},300);
		})
	},

	update_tg_keys_buttons:function(tg_keys){
		const all_keys = tg_keys.reduce((acc,key)=>{
			const role = key['role'] || "";
			if(role) { acc[role] = key };
			return acc;			
		},{});			
		this.TG_KEYS = all_keys;			
		const waiter = all_keys['waiter']['tg_key'];
		const manager = all_keys['manager']['tg_key'];
		const supervisor = all_keys['supervisor']['tg_key'];		
		waiter && this.$btn_tg_key_waiter.html(waiter.toUpperCase());		
		manager && this.$btn_tg_key_manager.html(manager.toUpperCase());		
		supervisor && this.$btn_tg_key_supervisor.html(supervisor.toUpperCase());
	},
	rebuild:function(){
		
		var _this=this;		
		var cafe = GLB.THE_CAFE.get();

		this.$buttonsCartMode.html("");
		var arrBtns = ["Корзина выключена","Корзина включена"];
		for(let i=0;i<arrBtns.length;i++){
			let checked = parseInt(GLB.THE_CAFE.get().cart_mode,10) == i?" checked":"";
			let $btn = $("<div class='std-form__radio-button "+checked+"' mode='"+i+"'>"+arrBtns[i]+"</div>\n");
			$btn.on("touchend",function(e){
				if(!_this.VIEW_SCROLLED){
					if(!$(this).hasClass('checked')){
						_this.NEW_CART_MODE = parseInt($(this).attr('mode'),10);
						$(this).addClass('checked');	
						$(this).siblings().removeClass('checked');
						_this.check_need_to_save();
					}
				};
				e.originalEvent.cancelable && e.preventDefault();
			});
			this.$buttonsCartMode.append($btn);
		};

		this.$buttonsDeliveryMode.html("");
		var arrBtns = ["Нет доставки","Есть доставка"];
		for(let i=0;i<arrBtns.length;i++){
			let checked = parseInt(GLB.THE_CAFE.get().has_delivery,10) == i?" checked":"";
			let $btn = $("<div class='std-form__radio-button "+checked+"' mode='"+i+"'>"+arrBtns[i]+"</div>\n");
			$btn.on("touchend",function(e){
				if(!_this.VIEW_SCROLLED){
					if(!$(this).hasClass('checked')){
						_this.NEW_DELIVERY_MODE = parseInt($(this).attr('mode'),10);
						$(this).addClass('checked');	
						$(this).siblings().removeClass('checked');
						_this.check_need_to_save();
					}
				};
				e.originalEvent.cancelable && e.preventDefault();
			});
			this.$buttonsDeliveryMode.append($btn);
		};

		this.$buttonsOrderWay.html("");
		var arrBtns = ["1. Только в TG", "2. В TG, затем в iiko"];
		for(let i=0;i<arrBtns.length;i++){
			let checked = parseInt(GLB.THE_CAFE.get().order_way,10) == i?" checked":"";
			let $btn = $("<div class='std-form__radio-button "+checked+"' mode='"+i+"'>"+arrBtns[i]+"</div>\n");
			$btn.on("touchend",function(e){
				if(!_this.VIEW_SCROLLED){
					if(!$(this).hasClass('checked')){
						_this.NEW_ORDER_WAY = parseInt($(this).attr('mode'),10);
						$(this).addClass('checked');	
						$(this).siblings().removeClass('checked');
						_this.check_need_to_save();
					}
				};
				e.originalEvent.cancelable && e.preventDefault();
			});
			this.$buttonsOrderWay.append($btn);
		};				

		this.check_need_to_save();											

	},
	iiko_set_section_visibility:function(visible_mode) {
		visible_mode ? this.$iikoSectionOnly.show() : this.$iikoSectionOnly.hide();
	},
	behavior:function()	{
		var _this = this;

		this._behavior();
		
		this.$btnBack.on('touchend',function(){
			_this._blur({onBlur:function(){
				!_this.LOADING && GLB.VIEWS.goBack();
			}});			
			return false;
		});

		this.$btnSave.on('touchend',function(){
			_this._blur({onBlur:function(){
				!_this.LOADING && _this.save({onReady:function(){				
					_this._go_back();
				}});
			}});
			return false;
		});
		
		this.$btn_tg_key_waiter.on('touchend',(e)=>{
			!_this.VIEW_SCROLLED && _this._blur({onBlur:()=>{						
				const role = 'waiter';
				this.$btn_tg_key_waiter.focus();
				setTimeout(()=>{
					_this.tg_key_to_clipboard(this.TG_KEYS[role].tg_key,role);
				},300)
				
			}});
			e.originalEvent.cancelable && e.preventDefault();
		});
		this.$btn_tg_key_manager.on('touchend',(e)=>{
			!_this.VIEW_SCROLLED && _this._blur({onBlur:()=>{
				const role = 'manager';
				this.$btn_tg_key_manager.focus();
				setTimeout(()=>{
				_this.tg_key_to_clipboard(this.TG_KEYS[role].tg_key,role);
				},300)
			}});
			e.originalEvent.cancelable && e.preventDefault();
		});
		this.$btn_tg_key_supervisor.on('touchend',(e)=>{
			!_this.VIEW_SCROLLED && _this._blur({onBlur:()=>{				
				const role = 'supervisor';
				this.$btn_tg_key_supervisor.focus();
				setTimeout(()=>{
				_this.tg_key_to_clipboard(this.TG_KEYS[role].tg_key,role);				
				},300)
			}});
			e.originalEvent.cancelable && e.preventDefault();
		});					

		this.sa_bnt_upd_tg_keys.on('touchend',(e)=>{
			!_this.VIEW_SCROLLED && this.sa_update_all_tg_keys();
			e.originalEvent.cancelable && e.preventDefault();
		});

	},
	
	tg_key_to_clipboard:function(tg_key, role) {		
		navigator.clipboard.writeText(tg_key.toUpperCase()).then(() => {
			let msg = "Ключ не удалось скопировать";
			if(role==='waiter'){
				msg = 'Ключ <strong>для официанта</strong> скопирован';
			}else if(role==='manager'){
				msg = 'Ключ <strong>для менеджера</strong> скопирован';
			}else if(role==='supervisor'){
				msg = 'Ключ <strong>для администратора</strong> скопирован';
			}	
			GLB.VIEWS.modalMessage({
				title:'Отлично!',
				message:msg,
				btn_title:GLB.LNG.get('lng_ok')
			});		  
		},() => {
			let msg = "Ключ не удалось скопировать";
			GLB.VIEWS.modalMessage({
				title:'Ошибка',
				message:msg,
				btn_title:GLB.LNG.get('lng_ok')
			});		  
		});

	},

	check_need_to_save:function(){
		this._need2save(false);
		if(GLB.THE_CAFE.is_iiko_mode()){
			if( parseInt(GLB.THE_CAFE.get().cart_mode,10)!==this.NEW_CART_MODE ||
			parseInt(GLB.THE_CAFE.get().order_way,10)!==this.NEW_ORDER_WAY ||
			parseInt(GLB.THE_CAFE.get().has_delivery,10)!==this.NEW_DELIVERY_MODE){
				this._need2save(true);
			}
		}else{
			if(parseInt(GLB.THE_CAFE.get().cart_mode,10)!==this.NEW_CART_MODE ||
				parseInt(GLB.THE_CAFE.get().has_delivery,10)!==this.NEW_DELIVERY_MODE){
				this._need2save(true);	
			}
		}
	},
	
	load_tg_keys_async:function(){
		return new Promise((res,rej)=>{
			
			var PATH = 'adm/lib/';
			var url = PATH + 'lib.get_tg_keys.php';			
			
			this._now_loading();
	
			var data = {
				cafe_uniq_name:GLB.THE_CAFE.get().uniq_name
			};
	
			this.AJAX = $.ajax({
				url: url+"?callback=?",
				data:data,
				method:"POST",
				dataType: "jsonp",
				success: function (response) {
					console.log('==response==',response)
					if(response && !response.error){
						res(response)						
					}else{
						rej(response)						
					}
				},
				error:function(response) {
					console.log('==err response==',response)
					rej(response)
				}
			});

		})
	},

	save:function(opt){

		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.save_cart_settings.php';			
		
		this._now_loading();

		var data = {
			id_cafe:GLB.THE_CAFE.get().id,			
			cart_mode:this.NEW_CART_MODE,
			order_way:this.NEW_ORDER_WAY,
			has_delivery:this.NEW_DELIVERY_MODE
		};

		var onSuccess = function(cafe){
        	_this._end_loading();				            	
        	if(!cafe.error){					
				GLB.THE_CAFE.update(cafe);
				opt.onReady && opt.onReady();
			}else{
				_this._end_loading();
				console.log(cafe.error);
			}
		};

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            data:data,
            method:"POST",
            dataType: "jsonp",
            success: function (response) {
            	console.log('response',response)
            	if(response && !response.error){
	            	var cafe = response;
	            	var cafe_rev = cafe.rev;            	
	            	setTimeout(function(){onSuccess(cafe);},300);
            	}else{
            		console.log('response err:',response)
            	}
            },
            error:function(response) {
            	_this._end_loading();
		        console.log("err load cafe info",response);
			}
        });
			
	},
	sa_update_all_tg_keys:function(){
		var ask = `Вы уверены, что хотите заменить ключи телеграма. Всем сотрудникам придется зайти в телеграм канал заново.`;
		GLB.VIEWS.modalConfirm({
			title:"Внимание",
			ask:ask,
			action:(mode)=>{				
				if(mode){
					this.sa_update_all_tg_keys_asynq()
					.then((keys)=>{									
						this.update_tg_keys_buttons(keys);
						this._end_loading();				
					})
					.catch((vars)=>{					
						GLB.VIEWS.modalMessage({
							title:GLB.LNG.get("lng_attention"),
							message:"Не удалось обновить ключи для телеграма",
							btn_title:GLB.LNG.get('lng_ok')
						});
						console.log(vars);
						this._end_loading();				
					});
				}else{
					console.log("CANCELED");
				}
			},
			buttons:[GLB.LNG.get("lng_ok"),GLB.LNG.get("lng_cancel")]
		});	
	},
	sa_update_all_tg_keys_asynq:function(){
		return new Promise((res,rej)=>{

			var PATH = 'adm/lib/';
			var url = PATH + 'lib.update_all_tg_keys.php';			

			this._now_loading();

			var data = {				
				cafe_uniq_name:GLB.THE_CAFE.get().uniq_name
			};

			this.AJAX = $.ajax({
				url: url+"?callback=?",
				data:data,
				method:"POST",
				dataType: "jsonp",
				success: function (response) {					
					if(response && !response.error){
						var keys = response;
						res(keys);
					}else{
						rej(response);
					}
				},
				error:function(response) {
					rej(response);					
				}
			});

		})
	}

};