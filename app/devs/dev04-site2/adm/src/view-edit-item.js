import {GLB} from './glb.js';
import {LNG_TABS} from './lng_tabs.js';

export var VIEW_EDIT_ITEM = {

	init:function(options){

		this._init(options);

		this.$itemsWrapper = this.$view.find('.all-items-wrapper');

		this.$btnSave = this.$view.find('.save');
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		
		this.$sectionChooseMenu = this.$view.find('.edit-item-choose-section');
		this.$btnChangeSection = this.$view.find('.edit-item-choose-section__button');

		// IIKO MODE ONLY
		this.$item_iiko_modifiers =  this.$form.find('.iiko-modifiers');
		this.$item_iiko_modifiers_title =  this.$form.find('.iiko-modifiers-title');				
		this.$item_iiko_volume_title = this.$form.find('.item-volume-title__iiko');
		this.$item_iiko_volume = this.$form.find('.item-volume__iiko');
		this.$item_iiko_price_title = this.$form.find('.item-price-title__iiko');
		this.$item_iiko_price = this.$form.find('.item-price__iiko');

		// CHESMENU MODE ONLY
		this.$price_list = this.$form.find('.price-list-section .price-list');
		this.$price_list_row_tpl = $('#templates .tpl-item-edit__price-list-row')
		this.$btn_price_list_add_row = this.$form.find('.price-list-section .btn-add-price');
		this.$btn_price_list_del_row = this.$form.find('.price-list-section .btn-del-price');

		// MULTI-LANGUAGE SECTIONS
		this.$item_edits_container = this.$form.find('.item-edits-container');
		this.$tpl_item_edit_form = $('#templates .tpl-item-edit__extra-lang');

		this.CURR_LANG_TO_EDIT = "ru";

		this.reset();		
		this.behavior();
		return this;
	},
	reset:function(){	
		this._reset();
		this.need2save(false);	
		this._page_to_top();
		this.change_current_lang('ru');
		this.$btnChangeSection.html("");		
	},		
	update:function(opt){		

		this._update();
		this._update_tabindex();
		this._page_hide();

		this.CURRENT_MENU = opt.menu;		
		this.ITEM = opt.item;
		this.POS_ORDER = opt.pos;						
		this.DO_AFTER_SAVE = opt.doAfterSave;
		this.reset();

		this.LTABS = $.extend({},LNG_TABS).init(this,this.$page,GLB.THE_CAFE.get());
		$(this.LTABS).on("change",(e,code)=>{
			this.change_current_lang(code);
		});
		
		this.rebuild_data_sections();

		const MODE_CLASS = GLB.THE_CAFE.is_iiko_mode()?'iiko-mode-only':'chefsmenu-mode-only';
		this.$view.addClass(MODE_CLASS);

		if(!this.ITEM){	

			// -------------------------------------
			// ADDING ITEM – FOR CHEFCMENU MODE ONLY
			// -------------------------------------

			this.ACTION_MODE = 'add';
			this._update_title(GLB.LNG.get("lng_item_add"));
			this.$sectionChooseMenu.hide();		
			this.sizes_rebuild();	

		}else{		

			this.ACTION_MODE = 'edit';	
			this._update_title(GLB.LNG.get("lng_item_edit"));
			this.$sectionChooseMenu.show();		
			
			this.id_item = this.ITEM.id;
			var price = price?price:0;

			// FILLING FIELDS WITH RUSSIAN VALUES			
			this.set_data("item-title",this.ITEM.title,'ru');
			this.set_data("item-description",this.ITEM.description,'ru');

			// FILLING EXTRA DATA WITH OTHER LANGUAGES
			const ALL_LANGS = this.LTABS.get_langs();
			const extra_data = this.ITEM.extra_data?JSON.parse(this.ITEM.extra_data,1):[];
			for(let lang in ALL_LANGS){
				if(ALL_LANGS.hasOwnProperty(lang)){
					if(lang!=="ru" && extra_data[lang]){
						extra_data[lang].title && this.set_data("item-title",extra_data[lang].title,lang);
						extra_data[lang].description && this.set_data("item-description",extra_data[lang].description,lang);				
					}
				}
			};			

			if(GLB.THE_CAFE.is_iiko_mode()){
				
				// ---------------------------
				// IIKO MODE / EDITITING ITEM
				// ---------------------------				

				// SHOW IIKO MODIFIERS
				var arr_modifiers = [];
				if(this.ITEM.iiko_modifiers!==""){
					var iiko_modifiers = JSON.parse(this.ITEM.iiko_modifiers);
					if(iiko_modifiers.length>0){
						for(var m in iiko_modifiers){
							var mod_group_title = iiko_modifiers[m].name;
							var modifiers = iiko_modifiers[m].items;
							if(modifiers.length>0){
								var arr_tags = [];
								for(var nm in modifiers){
									arr_tags.push(modifiers[nm].name);
								};
								var arr_tags_str = '<ul class="std-form__ui_tags"><li>'+arr_tags.join('</li><li>')+'</li></ul>';
								var str = '<div class="std-form__ul_tags_title">– '+mod_group_title+'</div>'+arr_tags_str;
								arr_modifiers.push(str);
							}
						};
						this.$item_iiko_modifiers.html( arr_modifiers.join('') );
						this.$item_iiko_modifiers_title.show();						
					}else{						
						this.$item_iiko_modifiers.html('').hide();
						this.$item_iiko_modifiers_title.hide();
					};
					
				}else{
						this.$item_iiko_modifiers.html('').hide();
						this.$item_iiko_modifiers_title.hide();					
				}

				// SHOW IIKO MANAGED PRICE
				var price_title = "Стоимость("+GLB.CURRENCY.get_current()+"):";
				this.$item_iiko_price_title.html(price_title);				
				var arr_price = [];
				var iiko_sizes = this.ITEM.iiko_sizes ? JSON.parse(this.ITEM.iiko_sizes):[];
				for(var s in iiko_sizes){
					arr_price.push(iiko_sizes[s].price);					
				};				
				this.$item_iiko_price.val( arr_price.join(' / ') ).attr({disabled:true});

				// SHOW IIKO MANAGED PRICE
				var volume_unit = this.ITEM.iiko_measure_unit;
				this.$item_iiko_volume_title.html("Вес ("+volume_unit+"):");						
				var arr_volumes = [];
				for(var s in iiko_sizes){
					var param = {
						sizeCode:iiko_sizes[s].sizeCode,
						sizeName:iiko_sizes[s].sizeName,
						weight:iiko_sizes[s].portionWeightGrams
					};
					arr_volumes.push(param.sizeName+' '+ param.weight+'г');					
				};				
				this.$item_iiko_volume.val(arr_volumes.join(' / ')).attr({disabled:true});

			}else{

				// ------------------------------
				// CHEFSMENU MODE / EDITITING ITEM
				// ------------------------------
				this.sizes_rebuild();
			};

			this.$btnChangeSection.html(this.CURRENT_MENU.title);

		}

		setTimeout(()=>{ 
			this._page_show();
		},350);
		
	},	

	// SIZES PART FOR CHEFSMENU MODE	
	sizes_update_buttons:function(){
		const $rows = this.$price_list.find('.tpl-item-edit__price-list-row');
		if($rows.length > 1 ){
			this.$btn_price_list_del_row.show();
		}else{
			this.$btn_price_list_del_row.hide();
		};
		if($rows.length > 4 ){
			this.$btn_price_list_add_row.hide();
		}else{
			this.$btn_price_list_add_row.show();
		}		
	},
	sizes_del_row:function(){
		const $rows = this.$price_list.find('.tpl-item-edit__price-list-row');
		if($rows.length > 1){ $rows.eq($rows.length-1).remove(); }		
	},
	sizes_add_row:function(row_data){
		const row = row_data ? row_data : this.sizes_get_default();
		const $tpl_row = this.$price_list_row_tpl.clone();		
		const $volume = $tpl_row.find('.item-volume');
		const $price = $tpl_row.find('.item-price');
		$volume.val(row.volume);
		$price.val(row.price);
		$volume.on('keyup',()=>{this.need2save(true);});
		$price.on('keyup',()=>{this.need2save(true);});
		this.$price_list.append($tpl_row);		
	},
	sizes_get_default:function(){
		return {volume:'0 г', price:0 };
	},
	sizes_rebuild:function(){		
		this.$price_list.html('');
		if(this.ITEM){
			const sizes = this.ITEM.sizes?JSON.parse(this.ITEM.sizes,1):[];								
			const arr_sizes = sizes.length ? sizes : [this.sizes_get_default()];
			for(let i in arr_sizes){
				this.sizes_add_row(arr_sizes[i]);
			}
		}else{
			this.sizes_add_row();
		};
		this.sizes_update_buttons();
	},
	sizes_behaviors:function(){
		this.$btn_price_list_add_row.on('touchend',()=>{
			this._blur({onBlur:()=>{
				if(!this.LOADING && !this.VIEW_SCROLLED){
					this.sizes_add_row();
					this.sizes_update_buttons();
					this.need2save(true);
				};
			}});
			return false;	
		});
		this.$btn_price_list_del_row.on('touchend',()=>{
			this._blur({onBlur:()=>{
				if(!this.LOADING && !this.VIEW_SCROLLED){
					this.sizes_del_row();
					this.sizes_update_buttons();
					this.need2save(true);
				};
			}});
			return false;	
		});		
	},

	// MULTI-LANGUAGE SECTIONS BUILDING
	rebuild_data_sections:function() {		
		this.$item_edits_container.html("");
		const fn = {
			build_inputs:(lang_code)=>{
				let $edit_forms = this.$tpl_item_edit_form.clone().hide();
				$edit_forms.attr({'data-extra-lang-code':lang_code});
				this.CURR_LANG_TO_EDIT==lang_code && $edit_forms.show();			
				this.$item_edits_container.append($edit_forms);				
			}
		};
		const ALL_LANGS = this.LTABS.get_langs();
		// BUILD INPUTS FOR EACH LANG
		// EVEN, IF WE HAVE ONLY RUSSIAN
		for(let lang_code in ALL_LANGS){				
			fn.build_inputs(lang_code);
		};

		this.$item_edits_container.find('input, textarea')
		.on('keyup',(e)=>{ 
			this.need2save(true);
		});
	},
	change_current_lang:function(lang) {
		this.CURR_LANG_TO_EDIT = lang;		
		this.$view.find('.extra-lang-section').hide()
		.filter(`div[data-extra-lang-code=${lang}]`).show();
	},
	set_data:function (name,value,lang_code) {		
		const $extra_lng_section = this.$view.find(`div[data-extra-lang-code=${lang_code}]`);
		const $input = $extra_lng_section.find(`.${name}`);		
		$input.val(value);
	},
	get_data:function (name,lang_code) {				
		const $extra_lng_section = this.$view.find(`div[data-extra-lang-code=${lang_code}]`);
		const $input = $extra_lng_section.find(`.${name}`);		
		let val = $input.val();
		val = $.clear_user_input(val,GLB.INPUTS_LENGTH.get(name));
		return val;
	},
	// @return array
	get_chefsmode_sizes:function(){
		let arr_sizes = [];
		this.$price_list.find('.tpl-item-edit__price-list-row').each((i, el)=>{
			const volume = $(el).find('input[name=item-volume]').val();  
			const price = $(el).find('input[name=item-price]').val();  
			const size = { volume:volume, size:price};
			arr_sizes.push(size);
		});
		console.log('size',arr_sizes);
	},
	price_to_number:function(price){
		var cafe = GLB.THE_CAFE.get();
		var price = price.replace(/\,/g,'.');
		if(isNaN(price)){
			return 0;
		}else{
			return parseInt(price,10);
		}
	},	

	behavior:function()	{
	
		this._behavior();

		this.$btnSave.on('touchend',()=>{
			this._blur({onBlur:()=>{
				!this.LOADING && this.save({ onReady:()=>{ this._go_back();} });
			}});			
			return false;
		});

		this.$btnBack.on('touchend',()=>{
			this._blur({onBlur:()=>{
				!this.LOADING && this._go_back();
			}});
			return false;
		});

		var fn = {
			open_view_replacing:()=>{
				GLB.VIEW_REPLACING_PARENT_SECTION.update(this.ITEM);
				GLB.VIEWS.setCurrent(GLB.VIEW_REPLACING_PARENT_SECTION.name);
			}
		};

		this.$btnChangeSection.on('touchend',()=>{
			this._blur({onBlur:()=>{
				if(!this.LOADING && !this.VIEW_SCROLLED){
					if(this.NEED_TO_SAVE){
						this.save({ onReady:()=>{ fn.open_view_replacing(); }});
					}else{
						fn.open_view_replacing();
					}
				};
			}});
			return false;	
		});

		// FOR CHESMENU MODE ONLY
		if(!GLB.THE_CAFE.is_iiko_mode()){						
			this.sizes_behaviors();
		};
	},

	need2save:function(mode){
		this.NEED_TO_SAVE = mode;
		if(mode){
			this.$view.addClass('need-to-save');
		}else{
			this.$view.removeClass('need-to-save');
		}
	},
	get_user_inputs:function(lang) {		
		if(!GLB.THE_CAFE.is_iiko_mode()){
			// CHEFSMENU MODE
			return {
				title: this.get_data('item-title',lang),
				description: this.get_data('item-description',lang),
				sizes: this.get_chefsmode_sizes(),
				volume: '',
				price: 0 			
			};			
		}else{
			// IIKO MODE
			return {
				title: this.get_data('item-title',lang),
				description: this.get_data('item-description',lang),
			};			
		};		
	},
	colllect_all_inputs:function() {
		const all_inputs = {};
		const ALL_LANGS = this.LTABS.get_langs();
		for(let lng in ALL_LANGS){
			if(ALL_LANGS.hasOwnProperty(lng)){
				all_inputs[lng] = this.get_user_inputs(lng);
			}
		};
		return all_inputs;
	},
	inputs_has_empty_values:function(inputs) {		
		return (!inputs['title'] || !inputs['description']);
	},
	save:function(opt){
						
		const id_menu = this.CURRENT_MENU.id;
		const id_item = this.ITEM?this.ITEM.id:0;
		const pos = this.POS_ORDER;
		const created_by = this.ITEM?this.ITEM.created_by:"chefsmenu";

		const all_inputs = this.colllect_all_inputs();

		if(!all_inputs['ru'].title){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:"Укажите название блюда",
				btn_title:GLB.LNG.get('lng_close'),
				on_close:()=>{this._page_to_top();}
			});
			return false;
		}else if(all_inputs['ru'].title.length<3){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:"Название блюда слишком короткое",
				btn_title:GLB.LNG.get('lng_close'),
				on_close:()=>{this._page_to_top();}
			});
			return false;
		};		

		// ------------------------
		// FOR CHEFSMENU MODE ONLY
		// ------------------------
		if(!GLB.THE_CAFE.is_iiko_mode()){
			// TODO			
			// if(!all_inputs['ru'].price){
			// 	all_inputs['ru'].price = 0;
			// }else{			
			// 	var price_msk = /^[\d]*[\.\,]*[\d]*$/;
			// 	var res = price_msk.test(all_inputs['ru'].price); 
			// 	if(!res){
			// 		GLB.VIEWS.modalMessage({
			// 			title:GLB.LNG.get("lng_attention"),
			// 			message:"Введите корректное значение стоимости",
			// 			btn_title:GLB.LNG.get('lng_close')					
			// 		});
			// 		return false;
			// 	}else{					
			// 		all_inputs['ru'].price = this.price_to_number( all_inputs['ru'].price );				
			// 	}
			// };
		};

		let some_extra_fields_is_empty = 0;
		const ALL_LANGS = this.LTABS.get_langs();
		if(Object.keys(ALL_LANGS).length>1){
			// search if any inputs in extra langs is empty;			
			for(let code in ALL_LANGS){
				if(this.inputs_has_empty_values(all_inputs[code])){
					some_extra_fields_is_empty++;
				}				
			};
		};

		const data = {			
			all_inputs,
			id_menu,
			id_item,
			created_by,
			pos
		};

		const errMessage = `Ошибка. Не удалось сохранить.`;
		const errMessageLimitsItems = `<p>Общее количество блюд в вашем меню достигло лимита.</p>
					<p>Откройте «Помощь», чтобы ознакомиться со всеми возможностями и ограничениями сервиса.</p>`;		

		const fn = {
			save:()=>{
				this.save_async(data)
				.then((result)=>{
            
	            	if(!result.error){
	            		
	            		this.need2save(false);
	            		var cafe_rev = result['cafe-rev'];
						this.ITEM = result['item'];							
						this.DO_AFTER_SAVE && this.DO_AFTER_SAVE(this.ITEM);
						opt&&opt.onReady&&opt.onReady();
						setTimeout(()=>{ 
							this._end_loading(); 
						},300);

	            	}else if(result.error && typeof result.error=="string" && result.error.indexOf('total_items')!==-1){		            		
	            		this._end_loading();
	            		this.error_message(errMessageLimitsItems);
	            	}else{		            		
	            		this._end_loading();
	            		this.error_message(errMessage);
	            	};
	            	console.log('result 1',typeof result.error, result);						

				})
				.catch((result)=>{
					console.log('result 2',typeof result.error, result);						
					this.error_message(errMessage);
					this._end_loading();
				});
			}
		};		

		if(some_extra_fields_is_empty){
			console.log("some extra fields is empty")
			GLB.VIEWS.modalConfirm({
				title:"Внимание!",
				ask:'Некоторые переводы не заполнены. Все равно сохранить?',
				action:(mode)=>{ mode&&fn.save(); },
				buttons:["Да","Нет"]
			});			
		}else{
			fn.save();
		};

	},
	save_async:function(data) {
		return new Promise((res,rej)=>{

			var PATH = 'adm/lib/';
			var url = PATH + 'lib.save_item.php';

			this._now_loading();

	        this.AJAX = $.ajax({
	            url: url+"?callback=?",
	            dataType: "jsonp",
	            data:data,
	            method:"POST",
	            success: function (result){
					res(result);	            	
	            },
	            error:function(result) {	            	
					rej(result);
				}
	        });
		});		

	},
	error_message:function(msg){		
		GLB.VIEWS.modalMessage({
			title:GLB.LNG.get("lng_attention"),
			message:msg,
			btn_title:GLB.LNG.get('lng_close'),
			on_close:()=>{this._page_to_top();}
		});		
	}	
};