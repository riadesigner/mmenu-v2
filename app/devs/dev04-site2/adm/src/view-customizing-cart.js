import {GLB} from './glb.js';

export var VIEW_CUSTOMIZING_CART = {
	
	init:function(options){
		
		this._init(options);

		this.$btnSave = this.$view.find('.save');
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		
		this.$buttonsCartMode = this.$view.find('.customizing-cart__cart-mode');
		this.$buttonsDeliveryMode = this.$view.find('.customizing-cart__delivery-mode');
		
		this.sa_bnt_upd_tg_keys =  this.$view.find('button[name="update_all_tg_keys"]');
		this.$section_tgusers = this.$view.find('.customizing-cart__all-tgusers');
				
		this.$iikoSectionOnly = this.$view.find('.iiko-section-only');

		this.$link_tg_section_attention = this.$view.find('.customizing-cart__tg-links-section-attention').hide();
		this.$link_tg_section = this.$view.find('.customizing-cart__tg-links-section');		

		this.$link_reg_tg_waiter = this.$view.find('.customizing-cart__all-keylinks a.link-waiter'); 
		this.$link_reg_tg_manager = this.$view.find('.customizing-cart__all-keylinks a.link-manager'); 
		this.$link_reg_tg_supervisor = this.$view.find('.customizing-cart__all-keylinks a.link-supervisor'); 

		this.$btn_tg_invite_link_waiter =  this.$view.find('button.invite-link-waiter');
		this.$btn_tg_invite_link_manager =  this.$view.find('button.invite-link-manager');
		this.$btn_tg_invite_link_supervisor =  this.$view.find('button.invite-link-supervisor');

		this.$btn_tg_invite_qrcode_waiter =  this.$view.find('button.invite-qrcode-waiter');
		this.$btn_tg_invite_qrcode_manager =  this.$view.find('button.invite-qrcode-manager');
		this.$btn_tg_invite_qrcode_supervisor =  this.$view.find('button.invite-qrcode-supervisor');		

		this.TG_KEYS = null; // null | {waiter:string, manager:string, supervisor:string}					
		this.TG_KEY_LINKS = null; // null | {waiter:string, manager:string, supervisor:string} 	

		this.tgbot_link = options.vars['tgbot_link'];

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

			this.show_tg_links_section(true);
			this.update_tg_keys_buttons(keys);

			this.load_tg_users_async()
			.then((tg_users)=>{								
				this.update_tg_users_list(tg_users);
				this.end_updating();
			})
			.catch((vars)=>{
				this.end_updating_with_error("Не удалось проверить пользователей телеграм чата для кафе");
			})
		})
		.catch((vars)=>{
			console.log('vars',vars)
			this.end_updating_with_error("Не удалось найти ключи для телеграма.", ()=>{
				this.show_tg_links_section(false);
				console.log('needs new keys');				
				this.end_updating();
			});
		})
	},
	show_tg_links_section:function(mode){
		if(mode){
			this.$link_tg_section.show();
			this.$link_tg_section_attention.hide();			
		}else{
			this.$link_tg_section.hide();
			this.$link_tg_section_attention.show();						
		}
	},
	update_tg_users_list:function(tg_users){

		const $waiters = this.$section_tgusers.find('.tgusers-role-waiter span');
		const $managers = this.$section_tgusers.find('.tgusers-role-manager span');
		const $supervisors = this.$section_tgusers.find('.tgusers-role-supervisor span');	

		const foo = {
			make_string:function(users,$el){
				let html = "";					
				if(users.length>0){						
					let count = 0;
					for(let i in users){											
						let name_string = users[i].nickname ? `${users[i].nickname} (${users[i].name})` : users[i].name;							
						html+=`<strong>${name_string}</strong>`;
						count++;
						if(count<users.length){
							html+=", ";
						}
					};
					console.log('html',html);
					$el.html(html);
				}				
			},
			reset_names:function(){
				const $empty_string = "нет";
				$waiters.html($empty_string);
				$managers.html($empty_string);
				$supervisors.html($empty_string);				
			}
		};

		foo.reset_names();

		if(tg_users && tg_users.length){			

			const users = {waiters:[],managers:[],supervisors:[]};

			for(let i in tg_users){
				switch(tg_users[i].role){
					case 'waiter':
					users.waiters.push(tg_users[i]);
					break;
					case 'manager':
					users.managers.push(tg_users[i]);
					break;
					case 'supervisor':
					users.supervisors.push(tg_users[i]);										
					break;
				}				
			};
			foo.make_string(users.waiters,$waiters);
			foo.make_string(users.managers,$managers);
			foo.make_string(users.supervisors,$supervisors);
		}
	},
	end_updating_with_error(error_message, foo){
		if(error_message){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:error_message,
				btn_title:GLB.LNG.get('lng_ok')
			});
		};		
		foo ? foo() : this.end_updating();
	},

	end_updating:function(){
		setTimeout(()=>{ 
			this._end_loading();
			this._page_show(); 
		},300);		
	},

	update_tg_keys_buttons:function(tg_keys){

		const all_keys = tg_keys.reduce((acc,key)=>{
			const role = key['role'] || "";
			if(role) { acc[role] = key };
			return acc;			
		},{});		

		this.TG_KEYS = all_keys;					
		this.TG_KEY_LINKS = {
			waiter : this.tgbot_link + all_keys['waiter']['tg_key'],
			manager : this.tgbot_link + all_keys['manager']['tg_key'],
			supervisor : this.tgbot_link + all_keys['supervisor']['tg_key']
		};
		this.$link_reg_tg_waiter.attr({href : this.TG_KEY_LINKS['waiter']});
		this.$link_reg_tg_manager.attr({href : this.TG_KEY_LINKS['manager']});
		this.$link_reg_tg_supervisor.attr({href : this.TG_KEY_LINKS['supervisor']});

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
		

		this.$btn_tg_invite_link_waiter.on('touchend',(e)=>{
			if(!this.VIEW_SCROLLED){
				const role = 'waiter';				
				this.tg_link_to_clipboard(this.TG_KEY_LINKS[role], role);
			} 
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$btn_tg_invite_link_manager.on('touchend',(e)=>{
			if(!this.VIEW_SCROLLED){
				const role = 'manager';				
				this.tg_link_to_clipboard(this.TG_KEY_LINKS[role], role);
			} 
			e.originalEvent.cancelable && e.preventDefault();
		});
		
		this.$btn_tg_invite_link_supervisor.on('touchend',(e)=>{
			if(!this.VIEW_SCROLLED){
				const role = 'supervisor';				
				this.tg_link_to_clipboard(this.TG_KEY_LINKS[role], role);
			} 
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$btn_tg_invite_qrcode_waiter.on('touchend',(e)=>{
			if(!this.VIEW_SCROLLED){
				const role = 'waiter';				
				this.tg_link_to_qrcode(this.TG_KEY_LINKS[role], role);
			} 
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$btn_tg_invite_qrcode_manager.on('touchend',(e)=>{
			if(!this.VIEW_SCROLLED){
				const role = 'manager';				
				this.tg_link_to_qrcode(this.TG_KEY_LINKS[role], role);
			} 
			e.originalEvent.cancelable && e.preventDefault();
		});
		
		this.$btn_tg_invite_qrcode_supervisor.on('touchend',(e)=>{
			if(!this.VIEW_SCROLLED){
				const role = 'supervisor';				
				this.tg_link_to_qrcode(this.TG_KEY_LINKS[role], role);
			} 
			e.originalEvent.cancelable && e.preventDefault();
		});		


		this.sa_bnt_upd_tg_keys.on('touchend',(e)=>{
			!_this.VIEW_SCROLLED && this.su_update_all_tg_keys();
			e.originalEvent.cancelable && e.preventDefault();
		});

	},
	
	tg_link_to_qrcode:function(tg_link, role){
		
		this._now_loading();

		this.get_link_to_qrcode_asynq(tg_link)
		.then((data)=>{					
			
			const imgUrl = `data:image/png;base64, ${data.image}`;

			const arr_message = {
				waiter:`Это код-приглашение <strong>официанта</strong>. <p><img src="${imgUrl}"></p>`,
				manager:`Это код-приглашение <strong>менеджера</strong>. <p><img src="${imgUrl}"></p>`,
				supervisor:`Это код-приглашение <strong>администратора</strong>. <p><img src="${imgUrl}"></p>`,
			};		
			const msg = arr_message[role];
			GLB.VIEWS.modalMessage({
				title:'Супер!',
				message:msg,
				btn_title:GLB.LNG.get('lng_ok')
			});

			this._end_loading();
		})
		.catch(()=>{
			let errorMsg = " QR-код не удалось создать";
			GLB.VIEWS.modalMessage({
				title:'Ошибка',
				message:errorMsg,
				btn_title:GLB.LNG.get('lng_ok')
			});
			
			this._end_loading();
		});

	},

	tg_link_to_clipboard:function(tg_link, role) {				
		navigator.clipboard.writeText(tg_link).then(() => {			
			const arr_message = {
				waiter:"Ссылка <strong>для официанта</strong> скопирована",
				manager:"Ссылка <strong>для менеджера</strong> скопирована",
				supervisor:"Ссылка <strong>для администратора</strong> скопирована",
			};
			const msg = arr_message[role];
			GLB.VIEWS.modalMessage({
				title:'Отлично!',
				message:msg,
				btn_title:GLB.LNG.get('lng_ok')
			});
		},() => {
			let errorMsg = "Ссылку не удалось скопировать";
			GLB.VIEWS.modalMessage({
				title:'Ошибка',
				message:errorMsg,
				btn_title:GLB.LNG.get('lng_ok')
			});		  
		});

	},

	get_link_to_qrcode_asynq:function(str_link){
		return new Promise((res, rej)=>{

			var PATH = 'adm/lib/';
			var url = PATH + 'lib.get_qr_image.php';			
	
			var data = {
				str_link:str_link
			};
	
			this.AJAX = $.ajax({
				url: url+"?callback=?",
				data:data,
				method:"POST",
				dataType: "jsonp",
				success: function (response) {
					
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

	load_tg_users_async:function(){
		return new Promise((res,rej)=>{
			
			var PATH = 'adm/lib/';
			var url = PATH + 'lib.get_tg_users.php';			
			
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
	su_update_all_tg_keys:function(){
		var ask = `Вы уверены, что хотите заменить ключи телеграма. Всем сотрудникам придется зайти в телеграм канал заново.`;
		GLB.VIEWS.modalConfirm({
			title:"Внимание",
			ask:ask,
			action:()=>{				
				this.su_update_all_tg_keys_asynq()
				.then((keys)=>{									
					this.update_tg_keys_buttons(keys);
					this.update_tg_users_list(false);
					this.show_tg_links_section(true);
					this._end_loading();
				})
				.catch((vars)=>{					
					GLB.VIEWS.modalMessage({
						title:GLB.LNG.get("lng_attention"),
						message:"Не удалось обновить ключи для телеграма",
						btn_title:GLB.LNG.get('lng_ok')
					});
					console.log(vars);
					this.show_tg_links_section(false);
					this._end_loading();				
				});
			},
			cancel:()=>{
				console.log("CANCELED");
			},
			buttons:[GLB.LNG.get("lng_ok"),GLB.LNG.get("lng_cancel")]
		});	
	},
	su_update_all_tg_keys_asynq:function(){
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