import {GLB} from './glb.js';

export var VIEW_IIKO_CUSTOMIZATION = {
	
	init:function(options){
		
		this._init(options);
									
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$btnSave = this.$view.find('.save');
				
		this.$inputDelKey =  this.$form.find('input[name=iiko-del-key]');
		this.$btnIikoKey = this.$form.find('button[name=iiko-api-key]');
		this.$btnIikoVarsUpdate = this.$form.find('.btn-iiko-vars-update');
		
		this.$linkIikoQRCodeTablesHelp = this.$form.find('a[name=link-iiko-qrcode-tables]');

		this.$general_information = this.$form.find('.iiko-general-information');
		this.$extmenu_list = this.$form.find('.iiko-extmenu-list');			
		this.$terminals_list = this.$form.find('.iiko-terminals-sections');
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

		const iiko_params = GLB.THE_CAFE.get("iiko_params");

		// SHOW ORGANIZATIONS INFO (WITH CURRENT)
		let orgs = iiko_params['organizations']!==''?JSON.parse(iiko_params['organizations']):{};		
		this.CURRENT_ORGANIZATION_ID = iiko_params['current_organization_id'];				
		this.update_organizations_list(orgs, this.CURRENT_ORGANIZATION_ID);

		console.log(`this.CURRENT_ORGANIZATION_ID = ${this.CURRENT_ORGANIZATION_ID}`);
		console.log(`iiko_params = ${iiko_params}`);
		
		// SHOW TERMINAL INFO
		let terminal_groups = iiko_params['terminal_groups']?JSON.parse(iiko_params['terminal_groups']):[];
		this.CURRENT_TERMINAL_GROUP_ID = iiko_params['current_terminal_group_id'];
		this.update_terminals_list(terminal_groups, iiko_params['current_terminal_group_id']);				

		// SHOW TABLES INFO
		let table_sections = iiko_params['tables']?JSON.parse(iiko_params['tables']):[];
		this.update_tables_list(table_sections, terminal_groups);


		const iiko_arr_extmenus = iiko_params['extmenus']!==""?JSON.parse(iiko_params['extmenus']):[];				
		this.CURRENT_EXTMENU_ID = iiko_params['current_extmenu_id'].toString();					
		this.update_extmenus(iiko_arr_extmenus, this.CURRENT_EXTMENU_ID);
		
		setTimeout(()=>{							
			this._page_show();
		},300);
		
	},
	new_current_exmenu_id:function(new_menu_id) {		
		this.NEW_IIKO_EXTMENU_ID = new_menu_id.toString();
		this.check_if_need2save();		
	},
	new_current_organization_id:function(new_org_id){
		console.log('new_org_id = ', new_org_id);		
		this.NEW_ORGANIZATION_ID = new_org_id.toString();
		this.check_if_need2save();		
	},
	new_current_terminal_group_id:function(new_terminal_group_id){		
		this.NEW_TERMINAL_GROUP_ID = new_terminal_group_id.toString();
		this.check_if_need2save();		
	},	
	check_if_need2save:function() {
		
		let need2save = false;
		
		//CHECK THE MENUS ID				
		if(
			(this.NEW_IIKO_EXTMENU_ID && this.NEW_IIKO_EXTMENU_ID!==this.CURRENT_EXTMENU_ID)
			|| (this.NEW_ORGANIZATION_ID && this.NEW_ORGANIZATION_ID!==this.CURRENT_ORGANIZATION_ID)
			|| (this.NEW_TERMINAL_GROUP_ID && this.NEW_TERMINAL_GROUP_ID !==this.CURRENT_TERMINAL_GROUP_ID)
		){
			console.log('need to save!')
			console.log(`${this.NEW_IIKO_EXTMENU_ID}!==${this.CURRENT_EXTMENU_ID} `)
			console.log(`${this.NEW_ORGANIZATION_ID}!==${this.CURRENT_ORGANIZATION_ID} `)
			console.log(`${this.NEW_TERMINAL_GROUP_ID}!==${this.CURRENT_TERMINAL_GROUP_ID} `)
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
		this.NEW_ORGANIZATION_ID = "";
		this.NEW_TERMINAL_GROUP_ID = "";
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

		this.$btnIikoVarsUpdate.bind('touchend',(e)=>{
			this._blur({onBlur:()=>{
				if(!this.LOADING && !_this.VIEW_SCROLLED){
					this.iiko_vars_update();
				}
			}});			
			e.originalEvent.cancelable && e.preventDefault();
		});
			
	},

	update_extmenus:function(iiko_arr_extmenus, current_extmenu_id){		
		
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
									this.new_current_exmenu_id($(e.target).data("menu-id"));
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
		this.$extmenu_list.find('')
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
								this.new_current_terminal_group_id($(e.target).data("group-id"));
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
		console.log('table_sections = ', table_sections);
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
					const trminalGroupName = fn.calc_terminal_name(termGroupId);

					let table_numbers = '';
					if(section['tables'].length>0){
						for(let t in section['tables']){
						let tbl = section['tables'][t];
						table_numbers+=`[${tbl.number}] `;
						}						
					};

					const html = [
						`<li>`,
						`<strong>${section['section_name']}: ${section['tables'].length} столов</strong> <br>`,
						`Номера столов: ${table_numbers}<br>`,
						`терминалы: ${trminalGroupName}`,
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
 
	save:function() {

		// CHECK SPECIAL WORD
		if(this.$inputDelKey.val()=="delete"){
			this.remove_iiko_login()	
		}else{
			this.save_iiko_params();
		};
		return false; 
	},
	save_iiko_params:function() {				

		const new_menu_id = this.NEW_IIKO_EXTMENU_ID.toString();

        let PATH = 'adm/lib/iiko/';
        let url = PATH + 'lib.save_iiko_params.php';
    
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
                // console.log('result',result)
                if(result && !result.error){
					GLB.THE_CAFE.set({'iiko_params':result.iiko_params});					
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
    // iiko_tables_update_asynq:function() {
    // 	return new Promise((res,rej)=>{

    //         let PATH = 'adm/lib/iiko/';
    //         let url = PATH + 'iiko.tables_update.php';        	
    //     	let CAFE = GLB.THE_CAFE.get();            
            
    //         let organizationId = this.CURRENT_ORGANIZATION_ID;

    //     	if(!organizationId) {
    //     		rej("неизвестный id организации");
    //     		return false;
    //     	};

    //         let data = {
    //             id_cafe:CAFE.id,
    //             api_login:CAFE.iiko_api_key,
    //             organizationId:organizationId,
    //             terminalGroupId: this.CURRENT_TERMINAL_GROUP_ID
    //         };

    //         this.AJAX = $.ajax({
    //             url: url+"?callback=?",
    //             dataType:"jsonp",
    //             data:data,
    //             method:"POST",
    //             success:(result)=> {             
    //                 console.log('result',result);
    //                 if(result && !result.error){
    //                     res(result); 
    //                 }else{
    //                     rej(result.error);
    //                 }
    //             },
    //             error:(result)=> {                    
    //             	console.log('result',result);
    //                 rej(result);                
    //             }
    //         });  

    // 	});
    // },
	show_modal_error:function(msg=""){
		const strMsg = msg!=="" ? msg : "<p>Что-то пошло не так. Попробуйте позже или обратитесь к разработчику Сервиса</p>"; 
		GLB.VIEWS.modalMessage({
			title:GLB.LNG.get("lng_error"),
			message:strMsg,
			btn_title:GLB.LNG.get('lng_close')
		});
	}	

};