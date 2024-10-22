import {GLB} from './glb.js';

export var VIEW_IIKO_CUSTOMIZATION = {
	
	init:function(options){
		
		this._init(options);
									
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$btnSave = this.$view.find('.save');
				
		this.$inputDelKey =  this.$form.find('input[name=iiko-del-key]');
		this.$btnIikoKey = this.$form.find('button[name=iiko-api-key]');
		this.$btnIikoTablesUpdate = this.$form.find('.btn-iiko-tables-update');
		this.$btnIikoGetQrcodes = this.$form.find('.btn-iiko-get-qrcodes');
		this.$btnIikoVarsUpdate = this.$form.find('.btn-iiko-vars-update');
		
		this.$linkIikoQRCodeTablesHelp = this.$form.find('a[name=link-iiko-qrcode-tables]');

		this.$general_information = this.$form.find('.iiko-general-information');
		this.$extmenu_list = this.$form.find('.iiko-extmenu-list');	
		this.$tables_list = this.$form.find('.iiko-table-sections');
		
		this.SITE_URL = CFG.base_url;
		this.USER_EMAIL = CFG.user_email;
		
		this.IIKO_QRCODE_TABLES_URL = "/link-iiko-qrcode_tables"; 
		
		this.update_content_once();


		this.reset();		
		this.behavior();		

		return this;

	},	

	update:function(){		
		
		this.reset();
		
		this._update();
		this._page_hide();
		this._update_tabindex();
		
		const CAFE = GLB.THE_CAFE.get();

		// SHOW CURRENT IIKO API LOGIN
		const iiko_api_key = CAFE.iiko_api_key;
		this.$btnIikoKey.html(iiko_api_key);		

		// SHOW ORGANIZATIONS INFO (WITH CURRENT)
		let str_info = "";	
		let orgs = CAFE.iiko_organizations!==""?JSON.parse(CAFE.iiko_organizations):{};		
		this.CURRENT_ORGANIZATION_ID = orgs['current_organization_id'];
		for(let i in orgs['items']){
			let org = orgs['items'][i];
			str_info+=[
				"<p data-organization-id='"+org.id+"'>",
				"<strong>"+org.name+"</strong><br>",
				org.address,
				"</p>"
				].join('');
		};
		this.$general_information.html(str_info);		
		
		// SHOW TABLES INFO
		let table_sections = CAFE.iiko_tables?JSON.parse(CAFE.iiko_tables):[];
		this.update_tables_list(table_sections);

		// SHOW MENUS INFO (WITH CURRENT)
		this.$extmenu_list.html("");
		
		const iiko_arr_extmenus = CAFE.iiko_extmenus!==""?JSON.parse(CAFE.iiko_extmenus):[];				
		this.CURRENT_EXTMENU_ID = CAFE.iiko_current_extmenu_id.toString();		
		
		if(iiko_arr_extmenus.length>0){			
			for(let m in iiko_arr_extmenus){
				if(iiko_arr_extmenus.hasOwnProperty(m)){
					let menu = iiko_arr_extmenus[m];										

					let $btnMenu = $('<div class="std-form__radio-button" data-menu-id="'+menu.id+'">'+menu.name+'</div>');

					
						$btnMenu.on("touchend",(e)=>{
							this._blur({onBlur:()=>{
								if(!this.LOADING && !this.VIEW_SCROLLED){
									if(!$(e.target).hasClass("checked")){
										$(e.target).siblings().removeClass("checked");
										$(e.target).addClass("checked");
										this.new_current_exmenu_id($(e.target).data("menu-id"));
									}						
								}
							}});
							e.originalEvent.cancelable && e.preventDefault();						
						});
					

					if(this.CURRENT_EXTMENU_ID==menu.id){
						$btnMenu.addClass("checked");
					};
					this.$extmenu_list.append($btnMenu);
				}				
			}
		};
		this.$extmenu_list.find('')
		
		setTimeout(()=>{							
			this._page_show();
		},300);
		
	},
	new_current_exmenu_id:function(new_menu_id) {
		this.NEW_IIKO_EXTMENU_ID = new_menu_id.toString();
		this.check_if_need2save();		
	},
	check_if_need2save:function() {
		
		let need2save = false;
		
		//CHECK THE MENUS ID
		const CAFE = GLB.THE_CAFE.get();
				
		if(this.NEW_IIKO_EXTMENU_ID!=this.CURRENT_EXTMENU_ID){
			need2save = true;
		}
		// CHECK SPECIAL WORD
		if(this.$inputDelKey.val()=="delete"){
			need2save = true;			
		}
		// SUMMARY		
		this._need2save(need2save);
		return need2save;
	},
	reset:function(){		
		this._reset();
		this._need2save(false);
		this._page_to_top();		
		this.$inputDelKey.val("");
		this.NEW_IIKO_EXTMENU_ID = "";
	},
	update_content_once:function(){
		this.$linkIikoQRCodeTablesHelp.attr({href:this.IIKO_QRCODE_TABLES_URL});
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

		this.$inputDelKey.on('keyup',()=>{
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

		this.$btnIikoTablesUpdate.bind('touchend',(e)=>{
			this._blur({onBlur:()=>{
				if(!this.LOADING && !_this.VIEW_SCROLLED){
					this.iiko_tables_update();
				}
			}});			
			e.originalEvent.cancelable && e.preventDefault();
		});		

		this.$btnIikoVarsUpdate.bind('touchend',(e)=>{
			this._blur({onBlur:()=>{
				if(!this.LOADING && !_this.VIEW_SCROLLED){
					this.iiko_vars_update();
				}
			}});			
			e.originalEvent.cancelable && e.preventDefault();
		});		 

		this.$btnIikoGetQrcodes.bind('touchend',(e)=>{
			this._blur({onBlur:()=>{
				if(!this.LOADING && !_this.VIEW_SCROLLED){		
					GLB.VIEW_IIKO_QRCODE_WITH_TABLES.update();
					GLB.VIEWS.setCurrent(GLB.VIEW_IIKO_QRCODE_WITH_TABLES.name);
				}
			}});			
			e.originalEvent.cancelable && e.preventDefault();
		});
			
	},
	// update_terminal_groups_list:function(terminal_groups){
	// 	console.log('terminal_groups',terminal_groups);
	// 	let str_terminal_groups = '';
	// 	for(let i in terminal_groups['items']){
	// 		if(terminal_groups['items'].hasOwnProperty(i)){
	// 			let group = terminal_groups['items'][i];
	// 			let group_address = group['address']?group['address']:'не указан';
	// 			let str = `<li><strong>${group['name']}</strong> (адрес: ${group_address})</li>`;
	// 			str_terminal_groups+=str;
	// 		}
	// 	};		
	// 	str_terminal_groups && this.$terminals_list.html(str_terminal_groups);
	// },
	update_tables_list:function(table_sections) {
		if(table_sections && table_sections.length>0){
			let str_table_sections = "";
			for(let i in table_sections){
				if(table_sections.hasOwnProperty(i)){
					let section = table_sections[i];
					str_table_sections += "<li>"+section['section_name']+": "+section['tables'].length+" столов</li>";					
				}
			};
			this.$tables_list.html(str_table_sections);
		}else{
			this.$tables_list.html("<li>Не найдено. Обновите столы.</li>");
		}		
	},
	iiko_vars_update:function(){		

        const fn = {
            okMessage:function(opt){
                var msg = `<p>Информация из iiko успешно загружена</p>`;
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

		console.log('iiko vars now updating');
		this._now_loading();

		this.iiko_vars_update_asynq()
		.then((vars)=>{
			console.log("vars = ",vars)

        	fn.okMessage({onClose:()=>{
        		location.reload();
        	}});

			setTimeout(()=>{				
				this._end_loading();
			},300);						

		})
		.catch((vars)=>{
			
			// if (typeof vars === 'string' || vars instanceof String){
			// 	if(vars.indexOf("has not actual terminals")!==-1){
			// 		this.show_modal_error('<p>Не найдены зарегистрированные Терминалы. Проверьте настройки Iiko.</p>');
			// 	}else if(vars.indexOf("cant update cafe info")!==-1){
			// 		this.show_modal_error('<p>Не удалось сохранить информацию. Проблема на стороне ChefsMenu. Попробуйте позже.</p>');
			// 	}else{
			// 		this.show_modal_error();
			// 	};				
			// }else{
			// 	this.show_modal_error();
			// };

			this.show_modal_error();
			setTimeout(()=>{
				this._end_loading();
			},300);						
			
		});

	},
	iiko_vars_update_asynq:function(){
		return new Promise((res,rej)=>{
			let PATH = 'adm/lib/iiko/';
            let url = PATH + 'lib.update_iiko_vars.php';
        
            let data = {
                id_cafe:GLB.THE_CAFE.get().id
            };

            this.AJAX = $.ajax({
                url: url+"?callback=?",
                dataType:"jsonp",
                data:data,
                method:"POST",
                success:function(result) {             
                    // console.log('result',result)
                    if(result && !result.error){                        
                        res(result); 
                    }else{
                        rej(result.error);
                    }
                },
                error:function(result) {         
                	// console.log('result',result)           
                    rej(result);                
                }
            });   
		});
	},
	iiko_tables_update:function() {		
        
        const fn = {
            okMessage:function(opt){
                var msg = `<p>Столы успешно обновлены</p>`;
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

		this._now_loading();
		this.iiko_tables_update_asynq()
		.then((vars)=>{
			console.log("vars",vars)
			this.update_tables_list(vars);
			fn.okMessage();
			setTimeout(()=>{
				this._end_loading();
			},300);
		})
		.catch((vars)=>{
			
			if (typeof vars === 'string' || vars instanceof String){
				if(vars.indexOf("has not actual terminals")!==-1){
					this.show_modal_error('<p>Не найдены зарегистрированные Терминалы. Проверьте настройки Iiko.</p>');
				}else if(vars.indexOf("cant update cafe info")!==-1){
					this.show_modal_error('<p>Не удалось сохранить информацию. Проблема на стороне ChefsMenu. Попробуйте позже.</p>');
				}else{
					this.show_modal_error();
				};				
			}else{
				this.show_modal_error();
			};

			setTimeout(()=>{
				this._end_loading();
			},300);

		});
	},
	save:function() {

		// CHECK SPECIAL WORD
		if(this.$inputDelKey.val()=="delete"){
			this.remove_iiko_login()	
		}else{
			//CHECK THE MENUS ID
			const CAFE = GLB.THE_CAFE.get();

			const current_extmenu_id = CAFE.iiko_current_extmenu_id.toString() || "";
					
			if(this.NEW_IIKO_EXTMENU_ID!==current_extmenu_id){
				this.save_new_current_extmenu_id();	
			}else{
				return false;
			}
		};
		return false; 
	},
	save_new_current_extmenu_id:function() {				

		const new_menu_id = this.NEW_IIKO_EXTMENU_ID.toString();

        let PATH = 'adm/lib/iiko/';
        let url = PATH + 'lib.save_iiko_current_extmenu_id.php';
    
        let data = {
            id_cafe:GLB.THE_CAFE.get().id,
            extmenu_id:new_menu_id
        };

		this._now_loading();

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType:"jsonp",
            data:data,
            method:"POST",
            success:(result)=> {             
                // console.log('result',result)
                if(result && !result.error){
					GLB.THE_CAFE.set({iiko_current_extmenu_id:new_menu_id});
                	this._go_back(); 
					setTimeout(()=>{ 
						this._end_loading();						
					},300);
                }else{
                    this.show_modal_error();
                    setTimeout(()=>{
                    	this._end_loading();	
                    },300);
                }
            },
            error:(result)=> {                    
                // console.log(result)
                this.show_modal_error();
                setTimeout(()=>{
                	this._end_loading();	
                },300);
            }
        }); 		

	},
	remove_iiko_login:function() {		

		const ask = "<p>Вы отправляете специальное слово. Хотите удалить связь с iiko?</p>";
        const fn = {
            okMessage:function(opt){
                let msg = '<p>Связь ChefsMenu с платформой iiko успешно удалена.<br>Сейчас Панель управления будет перезагружена.</p>';
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

		GLB.VIEWS.modalConfirm({
		    title:"Внимание!",
		    ask:ask,
		    action:()=>{ 	
		    	this._now_loading();	    	
		    	this.remove_iiko_api_login_asynq()
		    	.then((result)=>{		    		
	    		    fn.okMessage({onClose:()=>{
                        location.reload();
                    }});
		    	})
		    	.catch((result)=>{
		    		this.show_modal_error();
                    setTimeout(()=>{
                        this._end_loading();
                    },300);
		    	});
		    },
		    cancel:()=>{ return false;},
		    buttons:[GLB.LNG.get("lng_ok"),GLB.LNG.get("lng_cancel")]
		}); 

	},
	remove_iiko_api_login_asynq:function(){

        return new Promise((res,rej)=>{
        
            let PATH = 'adm/lib/iiko/';
            let url = PATH + 'iiko.remove_iiko_api_key.php';
        
            let data = {
                id_cafe:GLB.THE_CAFE.get().id
            };

            this.AJAX = $.ajax({
                url: url+"?callback=?",
                dataType:"jsonp",
                data:data,
                method:"POST",
                success:(result)=> {             
                    // console.log('result',result)
                    if(result && !result.error){                        
                        res(result); 
                    }else{
                        rej(result.error);
                    }
                },
                error:(result)=> {         
                	// console.log('result',result)           
                    rej(result);                
                }
            });     

        });     
    },
    iiko_tables_update_asynq:function() {
    	return new Promise((res,rej)=>{

            let PATH = 'adm/lib/iiko/';
            let url = PATH + 'iiko.tables_update.php';        	
        	let CAFE = GLB.THE_CAFE.get();            
            
            let organizationId = this.CURRENT_ORGANIZATION_ID;

        	if(!organizationId) {
        		rej("неизвестный id организации");
        		return false;
        	};

            let data = {
                id_cafe:CAFE.id,
                api_login:CAFE.iiko_api_key,
                organizationId:organizationId,
                terminalGroupId: this.CURRENT_TERMINAL_GROUP_ID
            };

            this.AJAX = $.ajax({
                url: url+"?callback=?",
                dataType:"jsonp",
                data:data,
                method:"POST",
                success:(result)=> {             
                    console.log('result',result);
                    if(result && !result.error){
                        res(result); 
                    }else{
                        rej(result.error);
                    }
                },
                error:(result)=> {                    
                	console.log('result',result);
                    rej(result);                
                }
            });  

    	});
    },
	show_modal_error:function(msg=""){
		const strMsg = msg!=="" ? msg : "<p>Что-то пошло не так. Попробуйте позже или обратитесь к разработчику Сервиса</p>"; 
		GLB.VIEWS.modalMessage({
			title:GLB.LNG.get("lng_error"),
			message:strMsg,
			btn_title:GLB.LNG.get('lng_close')
		});
	}	

};