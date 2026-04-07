import {GLB} from './glb.js';

export var VIEW_CUSTOMIZE_INTERFACE = {
	
	init:function(options){
		
		this._init(options);

		this.$btnSave = this.$view.find('.save');
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$buttonsSkin = this.$view.find('.customize-interface__skin');

		this.$extra_lang_container = this.$view.find('.customize-interface__extra_lang');		
		this.$inp_new_lang = this.$view.find('input[name=new-lang]');		
		this.$inp_lang_to_delete = this.$view.find('input[name=lang-to-delete]');		
		

		this.behavior();

		return this;

	},

	update:function(USER){
		var _this=this;
		
		this._update();
		this._page_hide();
		
		this.USER = USER;
		
		var cafe = GLB.THE_CAFE.get();		
		this.reset();
		
		this._now_loading();

		const extra_langs = GLB.THE_CAFE.get("extra_langs");
		const ARR_EXTRA_LANGS = extra_langs?JSON.parse(extra_langs):[];

		this._rebuild_extra_langs_list(ARR_EXTRA_LANGS);

		// RELOAD SKIN IF NEEDS
		this.NEW_CAFE_SKIN = cafe.skin_label;

		if(!this.SKINS){
			this.load_all_skins({
				onReady:(all_skins)=>{
					this.SKINS = all_skins;				
					this.rebuild();
					setTimeout(()=>{
						this._page_show();
						this._end_loading();
					},600);
				}
			});				
		}else{
			setTimeout(()=>{
				this._page_show();
				this._end_loading();
			},300);
		}

		
	},

	load_all_skins:function(opt){
		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.get_all_skins.php';
		
		this._now_loading();

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            data:{},
            method:"POST",
            dataType: "jsonp",
            success: function (response) {
            	console.log('response',response)
            	if(response && !response.error){
	            	var all_skins = response['all-skins'];
	            	opt && opt.onReady && opt.onReady(all_skins);
            	}else{
            		_this._end_loading();
            		console.log('response err:',response)
            	}
            },
            error:function(response) {
            	_this._end_loading();
		        console.log("err load cafe info",response);
			}
        });		
	},
	reset:function(){
		this._reset();
		this._need2save(false);
		this._page_to_top();
	},
	rebuild:function(){
		this.rebuild_skins_list();
		this.check_need_to_save();
	},
	rebuild_skins_list:function(){

		var _this=this;
		this.$buttonsSkin.html("");
		
		console.log('this.SKINS= ',this.SKINS);

		var defaultSkin = 'light-lemon';
		var allSkins = this.SKINS['all-skins'];
		var skinGroups = this.SKINS['skin-groups']
		if(!this.NEW_CAFE_SKIN){
			this.NEW_CAFE_SKIN = defaultSkin;
		};

		for (var i in skinGroups){
			
			var groupLabel = skinGroups[i];	
					
			for(var s in allSkins){				
				if (allSkins[s]['group'] == groupLabel){
					var skinLabel = allSkins[s]['label'];
					var checked = this.NEW_CAFE_SKIN==skinLabel?'checked':'';
					var $btnSkin = $('<div>',{
						attr:{label:skinLabel},
						class:'std-form__radio-button '+checked,
						}).html(allSkins[s]['name']['ru']);
					$btnSkin.on("touchend",function(){
						if(!_this.VIEW_SCROLLED){
							if(!$(this).hasClass('checked')){
								_this.NEW_CAFE_SKIN = $(this).attr('label');
								$(this).addClass('checked');	
								$(this).siblings().removeClass('checked');
								_this.check_need_to_save();
							}
						};
						return false;			
					});

					this.$buttonsSkin.append($btnSkin);
				}
			}
			
		};						
		
	},
	behavior:function()	{
		

		this._behavior();
		
		this.$btnBack.on('touchend',()=>{
			this._blur({onBlur:()=>{
				!this.LOADING && GLB.VIEWS.goBack();
			}});			
			return false;
		});

		this.$btnSave.on('touchend',()=>{
			this._blur({onBlur:()=>{
				if(!this.LOADING){
				 this.save_async()
				 .then((vars)=>{				 	
				 	const cafe = vars;
				 	GLB.THE_CAFE.update(cafe);
				 	this._go_back();
				 })
				 .catch((vars)=>{
				 	this._show_err_message(vars);				 	
				 });
				}				
			}});
			return false;
		});

		this.$inp_new_lang.on('keyup',(e)=>{
			this.$inp_lang_to_delete.val("");
			this.check_need_to_save();
		});

		this.$inp_lang_to_delete.on('keyup',(e)=>{
			this.$inp_new_lang.val("");
			this.check_need_to_save();
		});

	},

	check_need_to_save:function(){
		this._need2save(false);
		let new_lng = this.$inp_new_lang.val().trim();
		let lang_to_delete = this.$inp_lang_to_delete.val().trim();	
		let skin_label = GLB.THE_CAFE.get().skin_label;
		if( skin_label!==this.NEW_CAFE_SKIN || new_lng!=="" || lang_to_delete!=="" ){
				this._need2save(true);
		}
	},

	save_async:function(){
		return new Promise((res,rej)=>{
			
			const PATH = 'adm/lib/';
			const url = PATH + 'lib.save_interface.php';			
			
			this._now_loading();

			const id_cafe = GLB.THE_CAFE.get().id;
			const new_lang = this.$inp_new_lang.val().trim();
			const lang_to_delete = this.$inp_lang_to_delete.val().trim();			
			const skin_label = this.NEW_CAFE_SKIN;

			const data = { id_cafe, new_lang, lang_to_delete, skin_label};

	        this.AJAX = $.ajax({
	            url: url+"?callback=?",
	            data:data,
	            method:"POST",
	            dataType: "jsonp",
	            success:  (result)=> {	 
	            console.log('result1',result)           	
	            	if(result && !result.error){
		            	res(result);		            	
	            	}else{
	            		rej(result);
	            	}
	            },
	            error:(result)=> {
	            	console.log('result2',result)           	
					rej(result);
				}
	        });

		});			
	},
	_show_err_message(vars){
		
		let errMessage = 'Не могу загрузить данные. Нужна помощь Администратора сервиса.';

		if(vars.error && typeof vars.error=='string' &&vars.error.indexOf('Unknown lang')!==-1){
			errMessage = 'Не найден язык. Проверьте введенное название.';
		}else if(vars.error && typeof vars.error=='string' && vars.error.indexOf('Too short lang name')!==-1){
			errMessage = 'Проверьте введенное название языка. Слишком короткое.';
		};
        
        // console.log('vars',vars)

		GLB.VIEWS.modalMessage({
			title:GLB.LNG.get("lng_error"),
			message:errMessage,
			btn_title:GLB.LNG.get('lng_close')
		});

        this._end_loading();
	},
	_rebuild_extra_langs_list:function(all) {

		this.$extra_lang_container.html("");
		if(Object.keys(all).length>0){
			let $ul = $("<ul></ul>");
			for(let lng in all){
				if(all.hasOwnProperty(lng)){
					$ul.append(`<li>${all[lng]} <strong>(${lng})</strong></li>`);
				}
			};
			this.$extra_lang_container.html($ul);
		}else{	
			let $html = "<p>Не найдены</p>";		
			this.$extra_lang_container.html($html);
			return false;
		}				
	}

};