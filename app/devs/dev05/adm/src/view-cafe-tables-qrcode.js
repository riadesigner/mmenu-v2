import {GLB} from './glb.js';
import {IikoTables} from './iiko/iiko-tables.js';

export var VIEW_CAFE_TABLES_QRCODE = {
	
	init:function(options){
		
		this._init(options);
									
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$btnSend = this.$view.find('.send');
				
		this.NEED_TO_SAVE = false;
		this.$list_of_tables = this.$form.find('.wrapper-qrcode-list-of-table-sections');				
		this.$list_of_menulinks = this.$form.find('.wrapper-menulinks-for-tables-sections');	
		this.$adm_email = this.$form.find('.adm_email');	
		
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
		const TOTAL_TABLE = GLB.THE_CAFE.get('tables_amount');
		this.$adm_email.html(CFG.user_email);
		
		this.TABLE_MENU_LINK = GLB.THE_CAFE.get_link().url+'/table/';		
		const tables_uniq_names = CAFE.tables_uniq_names?JSON.parse(CAFE.tables_uniq_names):[];					
		this.rebuild_menulinks_list(tables_uniq_names, TOTAL_TABLE);				
		
		setTimeout(()=>{ 

			if(GLB.THE_CAFE.is_iiko_mode()){				
				const iiko_params = GLB.THE_CAFE.get('iiko_params');
				if(iiko_params && iiko_params.tables){
					const tables_data = JSON.parse(iiko_params.tables);
					IikoTables.init(tables_data);			
					const $total_iiko_tables = this.$form.find('.iiko-tables-total-message');
					const msg = `В iiko настроено всего <span>${IikoTables.get_total_tables()}</span> стола (ов)`;
					$total_iiko_tables.html(msg);
				}
			}

			this._page_show();			
		},300);

	},

	/**
	* @param tables_uniq_names: array ['1-swes', '2-dfr3', ...];
	* @return void;
	*/
	rebuild_menulinks_list:function(tables_uniq_names, total_nums=5){	
		
		this.$list_of_tables.html("");
		this.$list_of_menulinks.html("");
		const str = this.build_tables_list(tables_uniq_names, total_nums);					
		if(str){			
			let $btns;
			// update qr-codes links
			this.$list_of_tables.html(str);
			$btns = this.$list_of_tables.find('li');
			$btns.each((index, el)=>{
				$(el).on("touchend",(e)=>{
					if(!this.LOADING && !this.VIEW_SCROLLED){													
						// -------------------------
						//  SELECT TABLE
						// -------------------------
						$(el).toggleClass("checked");
						this.check_need_to_save();
					};
					e.originalEvent.cancelable && e.preventDefault();
				});
			});						
			// update menu links
			this.$list_of_menulinks.html(str);
			$btns = this.$list_of_menulinks.find('li');
			$btns.each((index, el)=>{
				$(el).on("touchend",(e)=>{
					if(!this.LOADING && !this.VIEW_SCROLLED){	
						// -------------------------
						//  OPEN THE MENU FOR TABLE
						// -------------------------
						const uniq_name = $(el).data('table-uniq-name');
						const url = `${this.TABLE_MENU_LINK}${uniq_name}`;
						window.open(url, '_blank').focus();						
					};
					e.originalEvent.cancelable && e.preventDefault();
				});
			});						
		} else {			
			this.$list_of_menulinks.html("<p><i><strong>Нет зарегистрированных столов. Вернитесь назад и укажите количество столов.</strong></i></p>");
		}
	},


	/**
	* @param tables_uniq_names: array|null
	* @return string|null
	*/	
	build_tables_list:function( tables_uniq_names = null, total_nums = 5){		
		
		const total = Math.min(total_nums,tables_uniq_names.length);	
		if( total > 0){

			const str_all_tbls = [];
			for(let n = 0; n< total; n++){
				let tbl = tables_uniq_names[n];
				let num = n+1;
				let str_tbl = `<li class="std-form__radio-button mini" 
					data-table-name="Стол ${num}" 
					data-table-number="${num}" 
					data-table-uniq-name="${tbl}">Стол ${num}</li>`;
				str_all_tbls.push(str_tbl);
			};
			return `<ul class="std-form__structure">${str_all_tbls.join('')}</ul>`;			
		} else {
			return null;
		}
	},
	reset:function(){		
		this._reset();
		this._need2save(false);
		this._page_to_top();
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
				let number = $(el).data("table-number");				
				arr_tables.push(number);
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

		this.$btnSend.bind('touchend',(e)=>{
			this._blur({onBlur:()=>{
				if(this.NEED_TO_SAVE && !this.LOADING){	
					this.send();
				};
			}});
			e.originalEvent.cancelable && e.preventDefault();
		});			
	},

	send:function() {

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
		
		console.log('arr_tables ==== ', arr_tables);

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

            let PATH = 'adm/lib/';
            let url = PATH + 'lib.send_qrcode_for_tables.php';
        	let CAFE = GLB.THE_CAFE.get();
        	
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