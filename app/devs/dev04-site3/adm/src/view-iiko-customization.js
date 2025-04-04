import {GLB} from './glb.js';

export var VIEW_IIKO_CUSTOMIZATION = {
	
	init:function(options){
		
		this._init(options);
									
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$btnSave = this.$view.find('.save');
				
		this.$inputDelKey =  this.$form.find('input[name=iiko-del-key]');
		this.$btnIikoKey = this.$form.find('button[name=iiko-api-key]');
		this.$btnIikoVarsUpdate = this.$form.find('.btn-iiko-vars-update');
		this.btnDownloadNomenclature = this.$form.find('.btn-iiki-load-nomenclature');

		this.$linkIikoQRCodeTablesHelp = this.$form.find('a[name=link-iiko-qrcode-tables]');

		this.$general_information = this.$form.find('.iiko-general-information');
		this.$extmenu_list = this.$form.find('.iiko-extmenu-list');		
		this.$oldway_menu_list = this.$form.find('.iiko-oldway_menu-list');					
		this.$oldway_download_section = this.$form.find('.download-nomenclature-section');
		this.$oldway_choosing_section = this.$form.find('.choosing-nomenclature-section');
		this.$terminals_list = this.$form.find('.iiko-terminals-sections');
		this.$tables_list = this.$form.find('.iiko-table-sections');
		this.$current_organization_title = this.$form.find('.iiko-current-org-title span');		
		this.$terminal_status_info_name = this.$form.find('.iiko-terminal-status-info-name');
		this.$terminal_status_info = this.$form.find('.iiko-terminal-status-info');
		
		this.SITE_URL = CFG.base_url;
		this.USER_EMAIL = CFG.user_email;
		this.DATA = {};
		
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

		const iiko_params = GLB.THE_CAFE.get("iiko_params");
		console.log(' ----------- iiko_params = ',iiko_params);		

		// SHOW ORGANIZATIONS INFO (WITH CURRENT)
		this.DATA.orgs = iiko_params['organizations']!==''?JSON.parse(iiko_params['organizations']):{};		
		this.CURRENT_ORGANIZATION_ID = iiko_params['current_organization_id'];
		this.NEW_ORGANIZATION_ID = this.CURRENT_ORGANIZATION_ID;
		this.update_organizations_list(this.DATA.orgs, this.CURRENT_ORGANIZATION_ID);	
		
		this.update_current_organization_title(this.CURRENT_ORGANIZATION_ID, this.DATA.orgs);				
		
		// SHOW TERMINAL INFO
		this.DATA.terminal_groups = iiko_params['terminal_groups']?JSON.parse(iiko_params['terminal_groups']):[];
		this.CURRENT_TERMINAL_GROUP_ID = iiko_params['current_terminal_group_id'];
		this.NEW_TERMINAL_GROUP_ID = this.CURRENT_TERMINAL_GROUP_ID;
		this.update_terminals_list(this.DATA.terminal_groups, this.CURRENT_TERMINAL_GROUP_ID);		
		
		// SHOW STATUS OF CURRENT TERMINAL GROUP
		let current_terminal_group_name = this.get_terminal_group_name(this.CURRENT_TERMINAL_GROUP_ID, this.DATA.terminal_groups);
		let terminal_groups_status = iiko_params['current_terminal_group_status']||"Не определен";
		terminal_groups_status === 1||"true"||true ? terminal_groups_status = "Активен" : terminal_groups_status = "Не активен";
		this.update_terminal_status_info(current_terminal_group_name, terminal_groups_status);					

		// SHOW TABLES INFO
		this.DATA.table_sections = iiko_params['tables']?JSON.parse(iiko_params['tables']):[];
		this.update_tables_list(this.DATA.table_sections, this.DATA.terminal_groups);		

		// SHOW EXTMENUS INFO
		this.DATA.iiko_arr_extmenus = iiko_params['extmenus']!==""?JSON.parse(iiko_params['extmenus']):[];				
		this.CURRENT_EXTMENU_ID = iiko_params['current_extmenu_id'].toString();					
		this.NEW_EXTMENU_ID = this.CURRENT_EXTMENU_ID;
		if(this.DATA.iiko_arr_extmenus && this.DATA.iiko_arr_extmenus.length>0){
			this.update_extmenus(this.DATA.iiko_arr_extmenus, this.CURRENT_EXTMENU_ID);		
		}		

		// SHOW OLDWAY MENUS INFO
		this.NOMENCLATURE_MODE = parseInt(iiko_params['nomenclature_mode'],10)?true:false;
		this.DATA.oldway_menus = iiko_params['oldway_menus']?JSON.parse(iiko_params['oldway_menus']):[];				
		this.CURRENT_OLDWAY_MENU_ID = iiko_params['current_oldway_menu_id'].toString();					
		this.NEW_OLDWAY_MENU_ID = this.CURRENT_OLDWAY_MENU_ID;
		this.update_oldway_menus_list(this.DATA.oldway_menus, this.CURRENT_OLDWAY_MENU_ID);
		this.update_oldway_menu_section();
		setTimeout(()=>{							
			this._page_show();
		},300);
		
	},
	new_current_extmenu_id:function(new_menu_id) {		
		this.NEW_EXTMENU_ID = new_menu_id.toString();
		this.check_if_need2save();		
	},
	new_current_oldway_menu_id:function(new_menu_id) {		
		this.NEW_OLDWAY_MENU_ID = new_menu_id.toString();
		this.check_if_need2save();		
	},		
	new_current_organization_id:function(new_org_id){
		this.NEW_ORGANIZATION_ID = new_org_id.toString();
		this.check_if_need2save();		
	},
	new_current_terminal_group_id:function(new_terminal_group_id){		
		this.NEW_TERMINAL_GROUP_ID = new_terminal_group_id.toString();
		this.check_if_need2save();		
	},	
	check_if_need2save:function() {
		
		let need2save = false;
		
		// CHECK THE MENUS_ID, ORGANIZATION_ID, TERMINAL_GROUP_ID				
		if(
			(this.NEW_EXTMENU_ID && this.NEW_EXTMENU_ID!==this.CURRENT_EXTMENU_ID)			
			|| (this.NOMENCLATURE_MODE && this.NEW_OLDWAY_MENU_ID && this.NEW_OLDWAY_MENU_ID !==this.CURRENT_OLDWAY_MENU_ID)
			|| (this.NEW_ORGANIZATION_ID && this.NEW_ORGANIZATION_ID!==this.CURRENT_ORGANIZATION_ID)
			|| (this.NEW_TERMINAL_GROUP_ID && this.NEW_TERMINAL_GROUP_ID !==this.CURRENT_TERMINAL_GROUP_ID)
		){			
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
		this.NEW_EXTMENU_ID = "";
		this.NEW_OLDWAY_MENU_ID = "";
		this.NEW_ORGANIZATION_ID = "";
		this.NEW_TERMINAL_GROUP_ID = "";		
	},
	update_content_once:function(){
		this.$linkIikoQRCodeTablesHelp.attr({href:this.IIKO_QRCODE_TABLES_URL});
	},

	update_current_organization_title:function(current_organization_id, organizations){
		let current_organization = organizations.find(org=>org.id===current_organization_id);
		this.$current_organization_title.html(current_organization.name);
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

		this.$btnIikoVarsUpdate.bind('touchend',(e)=>{
			this._blur({onBlur:()=>{
				if(!this.LOADING && !_this.VIEW_SCROLLED){
					this.iiko_vars_update();
				}
			}});			
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.btnDownloadNomenclature.bind('touchend',(e)=>{
			this._blur({onBlur:()=>{
				if(!this.LOADING && !_this.VIEW_SCROLLED){
					this._now_loading();
					this.iiko_download_nomenclature_async()
					.then((answer)=>{												
						console.log('nomencl:', answer);
						// updating loaded iiko_menu params
						this.DATA.oldway_menus = answer.menus;
						this.DATA.current_oldway_menu_id = answer.current_oldway_menu_id;
						this.DATA.nomenclature_mode = answer.nomenclature_mode;
						// updating instanse vars
						this.CURRENT_OLDWAY_MENU_ID = answer.current_oldway_menu_id;										
						this.NOMENCLATURE_MODE = answer.nomenclature_mode;

						// updating ui oldway menu section
						this.update_oldway_menu_section();

						// updating ui oldway list
						this.update_oldway_menus_list(this.DATA.oldway_menus, this.CURRENT_OLDWAY_MENU_ID);

						this._end_loading();
					})
					.catch((err)=>{
						console.log(err);
						this.show_modal_error();
						this._end_loading();
					});
				}
			}});			
			e.originalEvent.cancelable && e.preventDefault();
		});			

	},	

	update_oldway_menu_section:function(){
		if(this.NOMENCLATURE_MODE){
			this.$oldway_download_section.hide();
			this.$oldway_choosing_section.show();
		}else{
			this.$oldway_download_section.show();
			this.$oldway_choosing_section.hide();			
		}
	},
	iiko_download_nomenclature_async:function(){
		return new Promise((res, rej) => {
            let PATH = 'adm/lib/iiko/';
            let url = PATH + 'iiko.get_nomenclature_list.php';
        
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

	update_terminal_status_info:function(current_terminal_group_name, terminal_groups_status){		
		this.$terminal_status_info_name.html(current_terminal_group_name);
		this.$terminal_status_info.html(terminal_groups_status);
	},

	update_oldway_menus_list:function(menus, current_oldway_menu_id){		
				
		if(menus && menus.length>0){	
			
			this.$oldway_menu_list.html("");

			for(let m in menus){
				if(menus.hasOwnProperty(m)){
					let menu = menus[m];										

					let $btnMenu = $('<div class="std-form__radio-button" data-menu-id="'+menu.id+'">'+menu.name+'</div>');
					
					$btnMenu.on("touchend",(e)=>{
						this._blur({onBlur:()=>{
							if(!this.LOADING && !this.VIEW_SCROLLED){
								if(!$(e.target).hasClass("checked")){
									$(e.target).siblings().removeClass("checked");
									$(e.target).addClass("checked");
									this.new_current_oldway_menu_id($(e.target).data("menu-id"));
								}						
							}
						}});
						e.originalEvent.cancelable && e.preventDefault();						
					});
					
					if(current_oldway_menu_id==menu.id){
						$btnMenu.addClass("checked");
					};
					this.$oldway_menu_list.append($btnMenu);
				}				
			}
		}else{
			this.$oldway_menu_list.html("Не загружено");
		}
	},

	update_extmenus:function(iiko_arr_extmenus, current_extmenu_id){		
		
		this.CURRENT_OLDWAY_MENU_ID = current_extmenu_id;
		
		this.$extmenu_list.html("");		
		
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
									this.new_current_extmenu_id($(e.target).data("menu-id"));
								}						
							}
						}});
						e.originalEvent.cancelable && e.preventDefault();						
					});
					
					if(current_extmenu_id==menu.id){
						$btnMenu.addClass("checked");
					};
					this.$extmenu_list.append($btnMenu);
				}				
			}
		};
		
	},
	
	update_organizations_list(arr_organizations, current_org_id){
		
		if(arr_organizations.length == 0) return;

		this.$general_information.html('');

		for(let i in arr_organizations){
			let org = arr_organizations[i];
			let org_address = org.restaurantAddress!=='' ? `<br><small>${org.restaurantAddress}</small>`:'';
			let $btn = $(`<div class="std-form__radio-button" data-org-id="${org.id}"><b>${org.name}</b>${org_address}</div>`);				

			$btn.on("touchend",(e)=>{
				this._blur({onBlur:()=>{
					if(!this.LOADING && !this.VIEW_SCROLLED){						
						const $el = ($(e.target).prop('tagName')).toLowerCase()==='div'?$(e.target) : $(e.target).parent();
						if(!$el.hasClass("checked")){
							$el.siblings().removeClass("checked");
							$el.addClass("checked");
							this.new_current_organization_id($el.data('org-id'));
						}						
					}
				}});
				e.originalEvent.cancelable && e.preventDefault();	
			});		
			
			if(current_org_id==org.id){
				$btn.addClass("checked");
			};

			this.$general_information.append($btn);
		}

	},
	update_terminals_list:function(terminal_groups, current_terminal_group_id){
		
		if(terminal_groups && terminal_groups.length>0){
			
			this.$terminals_list.html("");		
			
			for(let m in terminal_groups){
				
				let t_group = terminal_groups[m];	

				let t_group_address = t_group['address']!==''? `<br>адрес: ${t_group['address']}`:'';
				let $btn = $(`<div class="std-form__radio-button" data-group-id="${t_group.id}">${t_group.name}${t_group_address}</div>`);				
				
				$btn.on("touchend",(e)=>{
					this._blur({onBlur:()=>{
						if(!this.LOADING && !this.VIEW_SCROLLED){
							if(!$(e.target).hasClass("checked")){
								$(e.target).siblings().removeClass("checked");
								$(e.target).addClass("checked");
								let group_id = $(e.target).data("group-id");
								this.new_current_terminal_group_id(group_id);								
								this.update_tables_list(this.DATA.table_sections, this.DATA.terminal_groups);
							}						
						}
					}});
					e.originalEvent.cancelable && e.preventDefault();						
				});
				
				if(current_terminal_group_id==t_group.id){
					$btn.addClass("checked");
				};
				
				this.$terminals_list.append($btn);

			}
			
		}
	},
	update_tables_list:function(table_sections, terminal_groups) {
		const fn ={
			calc_terminal_name:(termGroupId)=>{
				let name = 'Без названия';
				for(let i in terminal_groups){
					let t_group = terminal_groups[i];
					if(t_group.id==termGroupId){
						name = t_group.name;
						break;
					}
				};				
				return 	name;
			}
		};
		if(table_sections && table_sections.length>0){
			let str_table_sections = "";
			for(let i in table_sections){
				if(table_sections.hasOwnProperty(i)){
					let section = table_sections[i];

					"<li>"+section['section_name']+", всего столов: "+section['tables'].length+"</li>";					
					const termGroupId = section['terminalGroupId'];
					const terminalGroupName = fn.calc_terminal_name(termGroupId);

					let table_numbers = '';
					if(section['tables'].length>0){
						for(let t in section['tables']){
						let tbl = section['tables'][t];
						table_numbers+=`[${tbl.number}] `;
						}						
					};
					const selected = termGroupId == this.NEW_TERMINAL_GROUP_ID ? 'selected' : '';

					const html = [
						`<li>`,
						`<strong>${section['section_name']}: ${section['tables'].length} столов</strong> <br>`,
						`Номера столов: ${table_numbers}<br>`,
						`терминалы: <span class="outlined-inline ${selected}">${terminalGroupName}</span>`,
						`</li>`
					].join('');
					str_table_sections += html;
				}
			};
			this.$tables_list.html(str_table_sections);
		}else{
			this.$tables_list.html("<li>Не найдено. Обновите столы.</li>");
		}		
	},
	get_terminal_group_name:function(terminal_group_id, terminal_groups){
		let name = 'Без названия';
		for(let i in terminal_groups){
			let t_group = terminal_groups[i];
			if(t_group.id==terminal_group_id){
				name = t_group.name;
				break;
			}
		};
		return name;
	},
	iiko_vars_update:function(){

		console.log('iiko vars now updating');
		this._now_loading();

		this.iiko_vars_update_asynq()
		.then((vars)=>{
			
			const okMessage = `<p>Информация из iiko успешно загружена</p>`;			
        	this.show_modal_ok(okMessage, {onClose:()=>{
        		location.reload();
        	}});
			setTimeout(()=>{				
				this._end_loading();
			},300);						

		})
		.catch((vars)=>{
			this.show_modal_error();
			setTimeout(()=>{
				this._end_loading();
			},300);						
			
		});

	},
	iiko_vars_update_asynq:function(){
		return new Promise((res,rej)=>{
			let PATH = 'adm/lib/iiko/';
            let url = PATH + 'lib.update_iiko_params.php';
        
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
 
	save:function() {

		// CHECK SPECIAL WORD
		if(this.$inputDelKey.val()=="delete"){
			this.remove_iiko_login()	
		}else{
			this.save_current_params_asynq()
			.then((vars)=>{
				const okMessage = `<p>Настройки успешно обновлены</p><p>Панель управления будет перезагружена.</p>`;			
				this.show_modal_ok(okMessage, {onClose:()=>{ location.reload();	}});				
				setTimeout(()=>{ this._end_loading();},300);
			})
			.catch((vars)=>{
				console.log('error',vars);
				this.show_modal_error();
				setTimeout(()=>{ this._end_loading();},300);
			})
		};
		
	},
	save_current_params_asynq:function() {
		return new Promise((res,rej)=>{
			let PATH = 'adm/lib/iiko/';
			let url = PATH + 'lib.update_current_params.php';
		
			let data = {
				id_cafe:GLB.THE_CAFE.get().id,
				current_organization_id:this.NEW_ORGANIZATION_ID,
				current_terminal_group_id:this.NEW_TERMINAL_GROUP_ID,
				current_extmenu_id:this.NEW_EXTMENU_ID
			};


			this._now_loading();
	
			this.AJAX = $.ajax({
				url: url+"?callback=?",
				dataType:"jsonp",
				data:data,
				method:"POST",
				success:(result)=> {
					if(result && !result.error){					
						res(result);
					}else{
						rej(result);
					}
				},
				error:(result)=> {                    
					rej(result);
				}
			}); 			
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