
import {GLB} from './glb.js';

export var VIEW_ALL_MENU = {
	init:function(options){
		
		this._init(options);		

		this.CURRENT_CAFE = "";
		this.ID_USER = "";		
		
		this.$btnMenuHomeUrl = this.$view.find('.view-header-buttons__button_home');
		this.$btnMainSite = this.$view.find('.view-header-title_icon');
		this.$btnAdd = this.$view.find('.add');		
		this.$btnSave = this.$view.find('.save');
		this.$btnCustomize = this.$view.find('.customize');
		this.$btnIikoReload = this.$view.find('.app-view-integration-btn');

		this.$btnAdd.hide();			
		this._need2save(false);
		this.behavior();

		console.log(this);

		return this;
	},

	now_loading:function(){
		this._now_loading();
		this.MENU_LIST && this.MENU_LIST.stopBehaviors();
	},

	end_loading:function(){
		this._end_loading();
		this.MENU_LIST && this.MENU_LIST.startBehaviors();
	},	

	behavior:function()	{
		var _this = this;

		this._behavior();

		this.$btnCustomize.on("touchend",function(){
			_this._blur({onBlur:function(){
				if(!_this.LOADING){				
					GLB.VIEW_CUSTOMIZE_ALL.update();
					GLB.VIEWS.setCurrent(GLB.VIEW_CUSTOMIZE_ALL.name);
				};
			}});			
			return false;
		});

		// this.$viewTitleText.on("touchend",function(){
		// 	GLB.VIEW_CUSTOMIZING_CAFE.update();
		// 	GLB.VIEWS.setCurrent('view-customizing-cafe');			
		// 	return false;
		// });

		this.$btnMenuHomeUrl.on("touchend",function(){			
			var cafe_uniq = GLB.THE_CAFE.get().uniq_name.toLowerCase();
			window.open("/cafe/"+cafe_uniq,"_blank");
			return false;
		});

		this.$btnMainSite.on('touchend',function() {			
			location.href = CFG.http+CFG.www_url;
		});

		this.$btnAdd.on('touchend',function(){			
			if(!_this.LOADING){
				_this.MENU_LIST && _this.MENU_LIST.closeAll();
				_this.save_before(function(){
					setTimeout(function(){_this.end_loading();},100);					
					_this.check_limit_sections({onReady:function(){
						_this.edit_menu(false);
					}});
				});
			};
			return false; 
		});	

		this.$btnSave.on('touchend',function(){			
			!_this.LOADING && _this.NEED_TO_SAVE && _this.save();
			return false;
		});

		this.$btnIikoReload.on("touchstart",function(){$(this).addClass("active");});
		this.$btnIikoReload.on("touchend",function(){$(this).removeClass("active"); return false;});		

		this.$btnIikoReload.on("touchend",()=>{
			if(!this.LOADING){
				this.now_loading();
				this._page_hide();				
				this.ask_to_reload_iiko_menu();
			}else{
				console.log("try later, please")
			};
			return false;
		});

	},

	ask_to_reload_iiko_menu:function() {

		const menu_for_loading = this.get_current_menu_for_loading_from_iiko();

		console.log('menu_for_loading = ', menu_for_loading);

		GLB.VIEWS.modalConfirm({
			title:GLB.LNG.get("lng_attention"),
			ask:`<p>Будет загружено меню «${menu_for_loading.name}». Источник: «${menu_for_loading.source}»</p>`,
			action:()=>{									
				this.do_reload_iiko_menu();
			},
			cancel:()=>{
				this.end_loading();
				this._page_show();				
				console.log("cancel reloading")
			},
			buttons:[GLB.LNG.get("lng_ok"),GLB.LNG.get("lng_cancel")]
		});

	},	

	get_current_menu_for_loading_from_iiko:function(){
		const EXTERNALMENU_MODE = this.get_externalmenu_mode();
		const id_menu_for_loading = this.get_current_extmenu_id(EXTERNALMENU_MODE);
		const fn = {
			calc_name:()=>{
				return this.get_iiko_extmenus_array().find(item=>item.id===id_menu_for_loading).name;
			}
		}
		return {
			source:EXTERNALMENU_MODE?"Внешнее Меню":"Номенклатура",
			id:id_menu_for_loading,
			name:fn.calc_name(),
		}
	},

	// @return array|bool [ {id: string, name: string }, ... ,] 
	get_iiko_extmenus_array:function(){		
		const iiko_params = GLB.THE_CAFE.get('iiko_params');
		const EXTERNALMENU_MODE = this.get_externalmenu_mode();
		let menus = EXTERNALMENU_MODE ? iiko_params['extmenus'] : iiko_params['oldway_menus']; 
		menus = menus?JSON.parse(menus):false;
		return menus;
	},
	get_current_extmenu_id:function(externalmenu_mode){		
		const iiko_params = GLB.THE_CAFE.get('iiko_params');
		if(externalmenu_mode){
			return iiko_params['current_extmenu_id']??"";
		}else{
			return iiko_params['current_oldway_menu_id']??"";
		}
	},	

	// @return boolean
	get_externalmenu_mode:function(){
		const iiko_params = GLB.THE_CAFE.get('iiko_params');
		return !parseInt(iiko_params['nomenclature_mode'],10)?true:false;
	},

	do_reload_iiko_menu:function(){				
				
		const cafe = GLB.THE_CAFE.get();

		console.log("starting reload iiko menu")
				
		const EXTERNALMENU_MODE = this.get_externalmenu_mode();
		const id_menu_for_loading = this.get_current_extmenu_id(EXTERNALMENU_MODE);

		if(!id_menu_for_loading){
			this.ask_to_reconnect_to_iiko();			
			setTimeout(()=>{ this.end_loading(); this._page_show();},300);
			return;
		};

		const Loader = GLB.IIKO_LOADER.init();

		Loader.load_extmenu_asynq(id_menu_for_loading, EXTERNALMENU_MODE)
		.then((vars)=>{

			console.log('vars = ', vars);

			// const [roughMenu, roughMenuHash, need2update] = vars;
			const [idMenuSaved, roughMenuHash, need2update] = vars;

			// --------------------
			//  IF MENU DATA WRONG
			// --------------------
			if(!idMenuSaved || !roughMenuHash){
				this.error_message();
				setTimeout(()=>{ this.end_loading(); this._page_show();},200);
				return false
			}
			
			if(idMenuSaved && roughMenuHash && need2update){
				
				// --------------------------------------
				//  IF MENU WAS IMPORTED AND SAVED IN DB
				// --------------------------------------
				
				let newIdMenuSaved;

				if(EXTERNALMENU_MODE){
					// если режим внешнего меню, 
					// то нужно его еще преобразовать в формат CHEFS
					// TODO - не реализовано в данной версии!
					newIdMenuSaved = GLB.IIKO_EXT_MENU_PARSER.parse(idMenuSaved);

				}else{
					// если меню из номенклатуры, то оно уже в нужном формате, 
					// поэтому парсить не нужно  
					newIdMenuSaved = idMenuSaved;
				}				 
				
				// const total_categories = Object.keys(newMenu.categories).length;				
				// if(!total_categories){
				// 	this.error_message('Внешнее меню пустое.');
				// 	setTimeout(()=>{ this.end_loading(); this._page_show();},200);
				// 	return false;
				// }
								
				this.do_update_iiko_menu(cafe, newIdMenuSaved, roughMenuHash);

			}else{

				// ----------------
				//  IF ACTUAL MENU
				// ----------------
				// ----------------------------------------					
				//  ask if needs to reload iiko menu force
				// ----------------------------------------
				let msg = `У вас сейчас актуальное меню. 
					Обновлять не требуется.`;
				GLB.VIEWS.modalConfirm({
					title:GLB.LNG.get("lng_attention"),
					ask:`<p>${msg}</p><p>Все равно обновить?</p>`,
					action:()=>{
						// -------------------------
						//  IF NEED TO RELOAD FORCE	
						// -------------------------								
						this.do_reload_iiko_menu_force();
					},
					cancel:()=>{
						this.end_loading(); 
						this._page_show();
					},
					buttons:["Да","Нет"]
				});				
			}

		})
		.catch((vars)=>{					
			this.error_message();
			setTimeout(()=>{ this.end_loading(); this._page_show();},200);
			console.log('err load iiko ext menu',vars)	
		});

	},
	do_reload_iiko_menu_force:function(){	
		// ---------------------------------------------
		//  clearing menu hash before load last version
		// ---------------------------------------------
		console.log('start clearing hash')
		this.clear_menu_hash_async()
		.then((vars)=>{					
			console.log('ok clearing hash')
			const iiko_params = GLB.THE_CAFE.get('iiko_params');
			iiko_params['current_extmenu_hash'] = '';
			this.do_reload_iiko_menu();
		})
		.catch((err)=>{
			let msg = `<p>Не удалось обновить существующее меню. 
			Попробуйте позже или обратитесь к разработчику</p>`;
			this.error_message(msg);
			this.end_loading();
			this._page_show();
		})
	},
	do_update_iiko_menu:function(cafe, idMenuSaved,roughMenuHash){
		const _this=this;

		const opt = {
			id_cafe:cafe.id,
			idMenuSaved:idMenuSaved,
			roughMenuHash:roughMenuHash,
			onReady:function() {											
				console.log("Menu was updated!");							
				setTimeout(()=>{
					_this.update(_this.ID_USER); // reload & redraw list of categories
				},300);							
			},
			onError:function(errMsg) {
				console.log("errMsg",errMsg);
				let msg = `<p>Не удалось обновить существующее меню, 
				попробуйте позже или обратитесь к разработчику</p>`;
				_this.error_message(msg);
				_this.end_loading();
				_this._page_show();
			}};											
		GLB.IIKO_UPDATER.init(opt);		
	},		
	clear_menu_hash_async:function(){
		return new Promise((res,rej)=>{

			let PATH = 'adm/lib/iiko/';
			
            let url = PATH + 'lib.clear_iiko_extmenu_hash.php';
        	let CAFE = GLB.THE_CAFE.get();

            let data = {
                id_cafe:CAFE.id           
            };

            this.AJAX = $.ajax({
                url: url+"?callback=?",
                dataType:"jsonp",
                data:data,
                method:"POST",
                success:function(result) {             
                    console.log('result',result);
                    if(result && !result.error){
                        res(result); 
                    }else{
                        rej(result.error);
                    }
                },
                error:function(result) {                    
                	console.log('result',result);
                    rej(result);                
                }
            });
			
		});
	},
	update_iiko_mode:function(){		
		if(GLB.THE_CAFE.is_iiko_mode()){
			$('body').addClass("iiko-mode");
			$('body').removeClass("chefsmenu-mode");
		}else{
			$('body').addClass("chefsmenu-mode");
			$('body').removeClass("iiko-mode");				
		};
	},
	update:function(id_user){	
		var _this=this;
		
		this._update();
		this.ID_USER = id_user;		

		var fn = {
			helloMessage:function(){
				var msg = [
					"<p>Вы входите в Панель Управления меню.</p>",
					"<p>Для просмотра готового меню, которое увидят ваши посетители, ",
					"нажмите на значок раскрытой книги вверху страницы.</p>",
					].join(" ");
					GLB.VIEWS.modalMessage({
						title:"Здравствуйте!",
						message:msg,
						btn_title:GLB.LNG.get('lng_ok')
					});
			},
			archiveMessage:function(){
				var msg = [
				"<p>Ваше меню находится в архиве.",
				"Предлагаем вам перейти в основой режим ", 
				"и подключить все возможности сервиса.</p>",
				"<p>Перейдите в настройки, ",
				"чтобы снять все ограничения.</p>"
				].join(" ");
				GLB.VIEWS.modalMessage({
					title:"Внимание!",
					message:msg,
					btn_title:GLB.LNG.get('lng_ok')
				});											
			},
			unknownUserMessage:function(){
				var msg = [
					"<p>Неизвестный пользователь.</p>",
					"<p>Зайдите позже или обратитесь к администратору</p>"
					].join(" ");
				_this.error_message(msg);
			}			
		};

		if(!id_user) {
			fn.unknownUserMessage();
			setTimeout(function() {
				location.href = "/";
			},1500);
			return;
		};

		this._page_hide();
		this.now_loading();

		
		GLB.THE_CAFE.load({
			onReady:function(cafe) {								
				_this.CAFE = cafe;
				_this.ID_CAFE = cafe.id;
				_this._update_title(cafe.cafe_title);
				_this.update_iiko_mode();
				
				_this.load_menu_by_cafe(
					cafe.id,{
					onReady:function(allmenu){
						
						GLB.MENU.update(allmenu);						
						_this._need2save(false);							

						_this.build({onReady:function() {
								///
						}});
						// if(cafe.created_date===cafe.updated_date){
						// 	fn.helloMessage();
						// }else if(parseInt(cafe.cafe_status,10) == 1){
						// 	fn.archiveMessage();
						// }
					}
				});		
			}
		});

	},


	load_menu_by_cafe:function(id_cafe,opt){

		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.get_all_menu.php';
		console.log("LOAD_MENU_BY_CAFE")
        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            method:"POST",
            data:{id_cafe:id_cafe},
            success: function (res) {            	
            	var cafe_rev = res['cafe-rev'];
            	var all_menu = res['all-menu'];            	
				opt.onReady && opt.onReady(all_menu);
            },
            error:function(res) {
            	_this.error_message("Не удалось загрузить Меню. Попробуйте позже или обратитесь к администратору");
		        console.log("err load all menu",res);
			}
        });

	},
	build:function(opt){
	
		var _this=this;
		
		!GLB.THE_CAFE.is_iiko_mode() && this.$btnAdd.show();

		var fn = {
			delete_the_menu:function(id_menu, context){

				context.startRemove(id_menu,{onReady:function(){					
					// save all positions before delete row
					_this.save_before(function(){	
						setTimeout(function(){

						_this.delete_menu(id_menu,{
							onSuccess:function(id_menu){
								context.endRemove(id_menu);
								setTimeout(function(){_this.end_loading();},1000);
							},
							onError:function(id_menu){
								context.cancelRemoving(id_menu);								
								_this.error_message("Не удалось удалить Меню. Попробуйте позже или обратитесь к администратору");
								_this.end_loading();
							}
						});

						},600);
					},'leaveLoader');	

				}});

			}
		};


		var BUTTONS = {
			left:[
				{
					title:"Delete",
					btnClass:"btn-delete",
					btnAction:function(id_menu, context){
						if(_this.get().length < 2){
							GLB.VIEWS.modalMessage({
								title:GLB.LNG.get("lng_attention"),
								message:GLB.LNG.get("lng_min_menu"),
								btn_title:GLB.LNG.get('lng_close')
							});	
							context.cancelRemoving(id_menu); 
						}else{
							GLB.VIEWS.modalConfirm({
								title:GLB.LNG.get("lng_attention"),
								ask:"Вы уверены, что хотите удалить раздел со всеми блюдами?",
								action:function(){									
									fn.delete_the_menu(id_menu, context);									
								},
								cancel:function(){									
									_this.MENU_LIST && _this.MENU_LIST.closeAll();
								},
								buttons:[GLB.LNG.get("lng_delete"),GLB.LNG.get("lng_cancel")]
							});
						}



					}				
				}
			],
			right:[
				{
					title:"Edit",
					btnClass:"btn-edit",
					btnAction:function(id_menu, context){
						_this.MENU_LIST && _this.MENU_LIST.closeAll();
						setTimeout(function(){
							_this.save_before(function(){
								setTimeout(function(){_this.end_loading();},100); 
								_this.edit_menu(id_menu);
							});
						},300);						
					}
				}
			]
		};

		this._page_hide();		
				
		this.MENU_LIST = $.repos51({
			$parent:_this.$view.find('.app-view-page-container'),			
			ALLROWS:GLB.MENU.get(),
			BUTTONS:BUTTONS,
			onTouchend:function(menu){
				console.log("SAVE_BEFORE")
				_this.save_before(function(){
					GLB.VIEW_ALL_ITEMS.update(menu);
					GLB.VIEWS.setCurrent(GLB.VIEW_ALL_ITEMS.name);
					_this.end_loading();
				});	
			},
			need2save:function(mode){				
				_this._need2save(mode);
			},
			getRowIcon:function(id_icon){  return GLB.MENU_ICONS.get(id_icon); },						
			onReady:function(){ _this._page_show();}
		});		

		setTimeout(function(){
			opt.onReady&&opt.onReady();
			_this.end_loading();
		},300);

	},
	get:function(id_menu){				
		if(id_menu){
			return GLB.MENU.get_by_id(id_menu);
		}else{
			return GLB.MENU.get();
		}
	},
	get_index_by_id:function(id_menu){
		return GLB.MENU.get_index_by_id(id_menu);
	},	
	save_before:function(doAfterSave){		
		if(this.NEED_TO_SAVE){
			this.save(doAfterSave);			
		}else{
			doAfterSave && doAfterSave();
		}		
	},
	save:function(doAfterSave, leaveLoader){

		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.save_menu_pos.php';	

		if(!_this.NEED_TO_SAVE){						
			doAfterSave&& doAfterSave();
			return;
		}

		this.now_loading();		
		
		var data = {
			id_cafe: this.ID_CAFE,
			arrpos: GLB.MENU.get_arr_id()
		};

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            method:"POST",
            data:data,
            success: function (response) {            	
            	
            	console.log('response=',response);

            	!leaveLoader && setTimeout(function(){_this.end_loading();},100);            	
            	if(!response.error){
            		_this._need2save(false);
					doAfterSave&&doAfterSave();
            	}else{
            		_this.end_loading();
					console.log(response);					
            	}
            },
            error:function(response) {            	
            	_this.end_loading();
		        console.log(response);
			}
        });
	},
	edit_menu:function(id_menu){
		var _this=this;

		var id_cafe = GLB.THE_CAFE.get().id;
		var id_menu = id_menu;		

		var menu = id_menu? _this.get(id_menu) : false;		

		console.log('edit menu ',menu)

		if(menu){ 
			//update menu
			GLB.VIEW_EDIT_MENU.update({					
				id_cafe:id_cafe,
				menu:menu,
				onReady:function(menu){
										
					var index = _this.get_index_by_id(menu.id);
					if(index<0) { console.log('unknown id menu'); return;}
					GLB.MENU.get()[index] = menu;					
					_this._page_hide();				
					_this.update_menu_list(menu);

				}
			});
		}else{
			// add menu
			GLB.VIEW_EDIT_MENU.update({
				id_cafe:id_cafe,
				menu:menu,
				onReady:function(menu){	
					GLB.MENU.add(menu);
					_this.add_to_menu_list(menu);
				}
			});
		};

		GLB.VIEWS.setCurrent(GLB.VIEW_EDIT_MENU.name);
			
	},
	check_limit_sections:function(opt){
				
		var cafe = GLB.THE_CAFE.get();
		var limits = parseInt(cafe.cafe_status,10)!==2 ? CFG.limits.test:CFG.limits.full;

		var has_sections = this.get().length;
		var can_add_section = has_sections < limits.total_sections ? true : false;
		var limitMsg = "";
		if(cafe.cafe_status!==2){
			limitMsg = GLB.LNG.get("lng_limits_total_section__test");
		}else{
			limitMsg = GLB.LNG.get("lng_limits_total_section__full");
		};		 

		if(can_add_section && opt && opt.onReady){
			opt.onReady();
		}else{
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:limitMsg,
				btn_title:GLB.LNG.get('lng_close')
			});
		}
	},
	update_menu_list:function(menu){
		var _this=this;		
		if(!this.MENU_LIST) {console.log('cant update menu list'); return false;}
		this.MENU_LIST.updateMenu(menu);
		setTimeout(function(){
			_this._page_show();	
			_this.end_loading();
		},100);
	},	
	add_to_menu_list:function(menu){
		var _this=this;		
		if(!this.MENU_LIST) {console.log('cant update menu list'); return false;}
		this.MENU_LIST.addMenu(menu);
		setTimeout(function(){
			_this._page_show();	
			_this.end_loading();
		},100);		
	},	
	delete_menu:function(id_menu,opt){

		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.remove_menu.php';
		var id_menu = id_menu;
		var data = {id_menu:id_menu};

		this.now_loading();

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            data:data,
            method:"POST",
            success: function (response) {               	
            	if(!response.error){
            		var cafe_rev = response['cafe-rev'];
            		var message = response['message'];
            		opt.onSuccess && opt.onSuccess(id_menu);
            	}else{
            		opt.onError && opt.onError(id_menu);            		
					console.log("err:",response);
            	}
            },
            error:function(response) {
				_this.end_loading();
				opt.onError && opt.onError(id_menu);
		        console.log("err",response);
			}
        });

	},
	error_message:function(msg){		
		var msg = msg?msg:'<p>Что-то пошло не так. Попробуйте позже или обратитесь к Администратору Сервиса</p>';
		GLB.VIEWS.modalMessage({
			title:GLB.LNG.get("lng_error"),
			message:msg,
			btn_title:GLB.LNG.get('lng_close'),
			on_close:function(){}
		});		
		console.log('err:' + msg);
	},
	ask_to_reconnect_to_iiko:function(){

		const msg = [
			`<p>Не найдены параметры iiko для вашего меню.</p>`,
			`<p>Зайдите в Настроки и загрузите параметры iiko заново.</p>`,
		].join('');

		GLB.VIEWS.modalMessage({
			title:GLB.LNG.get("lng_error"),
			message:msg,
			btn_title:GLB.LNG.get('lng_close'),
			on_close:()=>{
				this.end_loading();		
				this._page_show();				
			}
		});

	}	

};