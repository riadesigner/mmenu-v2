import {GLB} from './glb.js';
import {LNG_TABS} from './lng_tabs.js';

export var VIEW_EDIT_MENU = {
	init:function(options){

		this._init(options);
		
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$btnSave = this.$view.find('.save');		

		this.$langsSections = this.$view.find('.menu-inputs-extra-langs');
		this.$btn_cat_hide = this.$view.find('.btn-cat-hide');
		this.MENU_ICON_ID = 0;
		this.$icons  = this.build_icons();
		
		this.reset();
		this.behavior();
		
		return this;
	},

	build_icons:function(){
		var _this=this;	
		var total_icons = GLB.MENU_ICONS.get_total();
		var $icons = this.$view.find('.add-menu-icons');
		for(var i=0;i<total_icons;i++){
			var ico = GLB.MENU_ICONS.get(i);
		 	var $div = $('<div></div>',{'data-title':ico.title})
		 	.html($('<span></span>',{class:ico.className}));
		 	if(i==_this.MENU_ICON_ID){$div.addClass('checked')}
		 	$icons.append($div);
		};
		return $icons.find('div');
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
		return (!inputs['title']);
	},
	get_user_inputs:function(lang) {		
		return {
			title: this.get_data('menu-title',lang)				
		};
	},
	update:function(options){
				
		this._update();	
		this._update_tabindex();
		
		this.ID_CAFE = options.id_cafe;		
		this.MENU = options.menu;
		this.ID_MENU = this.MENU ? this.MENU.id : null;		
		this.MENU_ICON_ID = this.MENU?this.MENU.id_icon:0;
		this.NEW_VISIBLE_MODE = this.MENU ? this.MENU.visible : 1;

		this.LTABS = $.extend({},LNG_TABS).init(this,this.$page,GLB.THE_CAFE.get());
		$(this.LTABS).on("change",(e,code)=>{
			this.change_current_lang(code);
		});
		
		this.rebuild_data_sections();
		this.update_visibility();
		this.update_title_icon();
		this.reset();

		this.DO_AFTER_SAVE = options.onReady || function(){};

		const titleEdit = GLB.LNG.get('lng_view_edit_menu__edit'); 
		const titleAdd = GLB.LNG.get('lng_view_edit_menu__add'); 

		const viewTitleTxt = this.MENU?titleEdit:titleAdd;
		this._update_title(viewTitleTxt);		

		if(this.MENU){
			// FILLING FIELDS WITH RUSSIAN VALUES
			this.set_data('menu-title',this.MENU.title,'ru');

			// FILLING EXTRA DATA WITH OTHER LANGUAGES
			const ALL_LANGS = this.LTABS.get_langs();
			const extra_data = this.MENU.extra_data?JSON.parse(this.MENU.extra_data,1):[];
			for(let lang in ALL_LANGS){
				if(ALL_LANGS.hasOwnProperty(lang)){
					if(lang!=="ru" && extra_data[lang]){
						extra_data[lang].title && this.set_data("menu-title",extra_data[lang].title,lang);
					}
				}
			};

		};
	
		this.$icons.removeClass('checked');		
		this.$icons.eq(this.MENU_ICON_ID).addClass('checked');		

		this._now_loading();
  
		this.get_app_version({
			onReady:()=>{
			this._page_show();
			setTimeout(()=>{
				this._end_loading();	
			},300);
		}});
		
	},
	rebuild_data_sections:function() {
		// MULTI-LANGUAGE SECTIONS BUILDING		
		this.$langsSections.html("");
		const fn = {
			build_inputs:(lang_code)=>{				
				let $edit_forms = $(`<div class="extra-lang-section">
					<input class="std-form__input menu-title" type="text" name="menu-title" maxlength="555"></div>`);
				$edit_forms.attr({'data-extra-lang-code':lang_code}).hide();
				this.CURR_LANG_TO_EDIT==lang_code && $edit_forms.show();			
				this.$langsSections.append($edit_forms);				
			}
		};
		const ALL_LANGS = this.LTABS.get_langs();
		// BUILD INPUTS FOR EACH LANG
		// EVEN, IF WE HAVE ONLY RUSSIAN
		for(let lang_code in ALL_LANGS){				
			fn.build_inputs(lang_code);
		};

		this.$langsSections.find('input, textarea')
		.on('keyup',(e)=>{ 
			this._need2save(true);
		});		
	},
	get_app_version:function(opt){
		
		var _this=this;

		var PATH = 'adm/lib/';
		var url = PATH + 'lib.get_app_version.php';

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            data:{},
            method:"POST",
            success: function (response) {            	
            	if(!response.error){
            		var NEW_APP_VERSION = response['app-version'];
            		if(CFG.app_version !== NEW_APP_VERSION){
            			_this._update_app(NEW_APP_VERSION);
            			return;
            		}else{		            
		            	opt && opt.onReady && opt.onReady();
            		}            		
            	}else{            		
					_this.error_message('Ошибка подключения. Попробуйте зайти позже.');
					_this._end_loading();
            	}
            },
            error:function(response) {            	
            	_this.error_message('Ошибка подключения. Попробуйте зайти позже.');
				_this._end_loading();
			}
        });
	},
	update_title_icon:function(){	
		var ico = GLB.MENU_ICONS.get(this.MENU_ICON_ID);
		var arr = GLB.MENU_ICONS.get();
		for(var i in arr){
		  if (arr.hasOwnProperty(i)) {
		    	this.$viewTitleIcon.removeClass(arr[i].className); 
		  }
		}
		this.$viewTitleIcon.addClass(ico.className);
	},
	behavior:function()	{
		var _this = this;

		this._behavior();

		this.$btnBack.on('touchend',(e)=>{
			this._blur({onBlur:()=>{
				!this.LOADING && this._go_back();
			}});			
			return false;
		});

		this.$btnSave.on('touchend',(e)=>{
			this._blur({onBlur:()=>{
				!this.LOADING && this.save();
			}});			
			return false;
		});		

		this.$icons.each(function(i){
			$(this).on('touchend',function(ev){
				if(!$(this).hasClass('checked') && !_this.VIEW_SCROLLED){					
					_this.MENU_ICON_ID = i;
					_this.update_title_icon();
					$(this).siblings().removeClass('checked');
					$(this).addClass('checked');
					_this._need2save(true);	
				}
				ev.originalEvent.cancelable && ev.preventDefault();
				ev.stopPropagation();
			});
		});
		
		this.$btn_cat_hide.on('touchend',(e)=>{			
			this.toggle_visibility();			
			e.originalEvent.cancelable && e.preventDefault();
		});	

	},

	update_visibility:function(){
		if(this.NEW_VISIBLE_MODE > 0){
			this.$btn_cat_hide.removeClass('off');
		}else{
			this.$btn_cat_hide.addClass('off');
		}		
	},
	toggle_visibility:function(){		
		if(this.NEW_VISIBLE_MODE > 0){
			this.NEW_VISIBLE_MODE = 0;
		}else{
			this.NEW_VISIBLE_MODE = 1;
		};
		this._need2save(true);
		this.update_visibility();
	},
	reset:function(){
		this._reset();
		this._page_to_top();
		this._page_hide();
		this._need2save(false);
		this.change_current_lang('ru');
		this.$form.find('input,textarea').val("");
		console.log('this.MENU',this.MENU)
	},
	save:function(){

		var _this = this;			
		var id_cafe = this.ID_CAFE;
		var id_menu = this.ID_MENU || "";
		const id_icon = this.MENU_ICON_ID;
		const visible = this.NEW_VISIBLE_MODE;

		if(!this.NEED_TO_SAVE && !this.LOADING) {			
			this._go_back();
		};

		// AT LEAST RUSSIAN NAME MUST BE ENABLED
		const russ_menu_title = this.get_data('menu-title','ru');
		if(!russ_menu_title){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:GLB.LNG.get("lng_required_field"), 
				btn_title:GLB.LNG.get('lng_close')
			});
			this._end_loading();
			return;
		}else if(russ_menu_title.length<2){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:"Минимальная длина <nobr>названия –</nobr> две буквы", 
				btn_title:GLB.LNG.get('lng_close')
			});
			this._end_loading();
			return;
		};

		const all_inputs = this.colllect_all_inputs();		

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

		const data ={
			id_cafe,
			id_icon,
			id_menu,
			all_inputs,
			visible
		};

		const errMessage = `<p>Невозможно добавить/сохранить раздел.</p>
        				<p>Попробуйте позже или обратитесь в поддержку.</p>`;
		const errMessageLimitSections = `<p>Количество разделов в меню достигло лимита.</p><p>Откройте «Помощь», 
        				чтобы ознакомиться со всеми возможностями и ограничениями сервиса.</p>`;        				

        const fn = {
        	save:()=>{
        		this._now_loading();
				this.save_async(data)
				.then((response)=>{
			        console.log(response);
		        	if(!response.error){
		        		const cafe_rev = response['cafe-rev'];
		        		const menu = response['menu'];
		        		this.DO_AFTER_SAVE && this.DO_AFTER_SAVE(menu);            		
		        		this._end_loading();
		        		setTimeout(()=>{            			
							this._go_back();
		        		},300);
		        	}else{
		        		this._end_loading();
		        		if(response.error && typeof response.error=="string" && response.error.indexOf('total_sections')!==-1){
		        			this.error_message(errMessageLimitSections);
		        		}else{
		        			this.error_message(errMessage);
		        		}
		        	}
				})
				.catch((result)=>{
			        console.log(result);
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
				action:()=>{ fn.save(); },
				buttons:["Да","Нет"]
			});			
		}else{
			fn.save();
		};

	},
	save_async:function(data) {
		return new Promise((res,rej)=>{

		const PATH = 'adm/lib/';
		const url = PATH + 'lib.save_menu.php';

	        this.AJAX = $.ajax({
	            url: url+"?callback=?",
	            dataType: "jsonp",
	            data:data,
	            method:"POST",
	            success: function (result) {
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
			btn_title:GLB.LNG.get('lng_close')			
		});		
	}	

};