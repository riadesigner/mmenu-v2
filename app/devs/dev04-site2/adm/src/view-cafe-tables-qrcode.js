import {GLB} from './glb.js';

export var VIEW_CAFE_TABLES_QRCODE = {
	
	init:function(options){
		
		this._init(options);
									
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$btnSend = this.$view.find('.send');
				
		this.NEED_TO_SAVE = false;
		this.$list_of_tables = this.$form.find('.wrapper-qrcode-list-of-table-sections');				
		this.$list_of_menulinks = this.$form.find('.wrapper-menulinks-for-tables-sections');		
		
		this.reset();		
		this.behavior();
		return this;

	},	

	update:function(){
		var _this=this;
		
		this.reset();		
		this._update();
		this._page_hide();
		this._update_tabindex();
		
		const CAFE = GLB.THE_CAFE.get();		
		this.TABLE_MENU_LINK = `${CFG.http}${CFG.www_url}/cafe/${CAFE.uniq_name}/table/`;

		// SHOW TABLES INFO
		const table_sections = CAFE.iiko_tables?JSON.parse(CAFE.iiko_tables):[];
		const tables_uniq_names = CAFE.tables_uniq_names?JSON.parse(CAFE.tables_uniq_names):[];
		this.update_tables_list(table_sections);
		this.update_menulinks_list(table_sections, tables_uniq_names);		
		
		setTimeout(function(){							
			_this._page_show();
		},300);		
	},

	/**
	* @param table_sections: array
	* @param tables_uniq_names: object {'tabel-1':'1-uniq-name', ...}
	* @return void
	*/
	update_menulinks_list:function(table_sections, tables_uniq_names){	
		this.$list_of_menulinks.html("");		
		const str = this.build_tables_list(table_sections, tables_uniq_names);		
		if(str){
			this.$list_of_menulinks.html(str);
			let $btns = this.$list_of_menulinks.find('li');
			$btns.each((index, el)=>{
				$(el).on("touchend",(e)=>{
					if(!this.LOADING && !this.VIEW_SCROLLED){	
						$btns.removeClass("checked");					
						$(el).addClass("checked");
						// -------------------------
						//  OPEN THE MENU FOR TABLE
						// -------------------------
						const table_uniq_name = $(el).data('table-uniq-name');
						const url = `${this.TABLE_MENU_LINK}${table_uniq_name}`;
						window.open(url, '_blank').focus();						
					};
					e.originalEvent.cancelable && e.preventDefault();
				});
			});						
		}else{			
			this.$list_of_menulinks.html("<p>Нет зарегистрированных столов. Вернитесь назад и обновите столы.</p>");
		}
	},

	/**
	* @param table_sections: array
	* @return void
	*/	
	update_tables_list:function(table_sections) {				
		this.$list_of_tables.html("");
		const str = this.build_tables_list(table_sections);
		if(str){
			this.$list_of_tables.html(str);
			const $btns = this.$list_of_tables.find('li');
			$btns.each((index, el)=>{
				$(el).on("touchend",(e)=>{
					if(!this.LOADING && !this.VIEW_SCROLLED){						
						$(el).toggleClass("checked");						
						this.check_need_to_save();
					};
					e.originalEvent.cancelable && e.preventDefault();
				});
			});
		}else{			
			this.$list_of_tables.html("<p>Нет зарегистрированных столов. Вернитесь назад и обновите столы.</p>");
		}
	},
	/**
	* @param table_sections: array
	* @param tables_uniq_names: object|null
	* @return string|null
	*/	
	build_tables_list:function(table_sections, tables_uniq_names=null){
		if(table_sections && table_sections.length>0){
			let str_table_sections = "";
			for(let i in table_sections){
				if(table_sections.hasOwnProperty(i)){					
					let section = table_sections[i];
					let section_name = section['section_name'];
					let section_id = section['section_id'];
					let tables = section['tables'];						
					if(tables.length){												
						str_table_sections += `<h2>${section_name}</h2>`;
						let str_tables = "";
						for (let t in tables){
							if(tables.hasOwnProperty(t)){
								let tbl = tables[t];
								let str_table_name = section_name+": "+ tbl['name'];								
								// LINK TO TABLE MENU
								const table_index = `table-${tbl['number']}`;
								let str_uniq_name = "";
								let str_uniq_name_data = "";
								if(tables_uniq_names!==null && tables_uniq_names[table_index]){									
									str_uniq_name = tables_uniq_names[table_index];
									str_uniq_name_data = `data-table-uniq-name="${str_uniq_name}"`;
								};	
								let str_btn = `<li class="std-form__radio-button mini" 
										${str_uniq_name_data}
										data-table-name="${str_table_name}" 
										data-table-number="${tbl['number']}" 
										data-table-id="${tbl['id']}">
										${str_table_name}</li>`;
								str_tables += str_btn;
							}
						};
						str_table_sections += `<ul class="std-form__structure" 
								data-section-id="${section_id}" 
								data-section-name="${section_name}">${str_tables}</ul>`;
					}
				}
			};
			return str_table_sections;
		}else{
			return null;
		}
	},
	reset:function(){		
		this._reset();
		this._need2save(false);
		this._page_to_top();		
		// this.$inputDelKey.val("");
		// this.NEW_IIKO_EXTMENU_ID = "";
	},
	check_need_to_save:function(){
		let arr_tables = this.get_all_checked_tables();
		let need_2_save = arr_tables.length;
		this._need2save(need_2_save);		
		this.NEED_TO_SAVE = need_2_save;
		return need_2_save;
	},
	get_all_checked_tables:function() {		
		let arr_tables = [];
		let $btns = this.$list_of_tables.find('li');
		$btns.each((index,el)=>{						
			if($(el).hasClass('checked')){
				let name = $(el).data("table-name");
				let id = $(el).data("table-id");
				let number = $(el).data("table-number");				
				arr_tables.push({id,name,number});
			}
		});		
		return arr_tables;
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

		// this.$inputDelKey.on('keyup',()=>{
		// 	this.check_if_need2save();
		// });		

		this.$btnSend.bind('touchend',(e)=>{
			this._blur({onBlur:()=>{
				if(this.NEED_TO_SAVE && !this.LOADING){	
					this.save();
				};
			}});
			e.originalEvent.cancelable && e.preventDefault();
		});			
	},

	save:function() {

	   const user_email = CFG.user_email;

       const fn = {
            okMessage:function(opt){
                var msg = `<p>QR-коды успешно отправлены на адрес администратора (${user_email}).</p>`;
                GLB.VIEWS.modalMessage({
                    title:GLB.LNG.get("lng_attention"),
                    message:msg,
                    btn_title:GLB.LNG.get('lng_close'),
                    on_close:function(){
                        opt && opt.onClose && opt.onClose();
                    }
                });                 
            },
            errMessage:function(){
                var msg = "<p>Что-то пошло не так. Попробуйте позже или обратитесь к Администратору Сервиса</p>";
                GLB.VIEWS.modalMessage({
                    title:GLB.LNG.get("lng_error"),
                    message:msg,
                    btn_title:GLB.LNG.get('lng_close')
                });
            }            
        }; 		
		let arr_tables = this.get_all_checked_tables();
		console.log('arr_tables = ', arr_tables);
		if(arr_tables.length>0){			
			this._now_loading();
			this.send_qr_codes_to_email_asynq(arr_tables)
			.then((vars)=>{
				console.log('vars')
				fn.okMessage({onClose:()=>{					
					this._go_back();
				}});
				setTimeout(()=>{
					this._end_loading();	
				},300);
			})
			.catch((vars)=>{
				console.log('vars')
				fn.errMessage();
				setTimeout(()=>{
					this._end_loading();	
				},300);
			});
		}else{
			console.log("Nothing to save",arr_tables);
		}
	},
	send_qr_codes_to_email_asynq:function(arr_tables){
		return new Promise((res,rej)=>{

            let PATH = 'adm/lib/iiko/';
            let url = PATH + 'iiko.send_qrcode_for_tables.php';
        	let CAFE = GLB.THE_CAFE.get();

        	// lib.send_qr_code.php
        	
        	if(!arr_tables.length) {
        		rej("невозможно отправить пустой список");
        		return false;	
        	};

            let data = {
                id_cafe:CAFE.id,
                user_email:CFG.user_email,
                arr_tables:arr_tables                
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
	}    

};