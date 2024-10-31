import {GLB} from './glb.js';

export var VIEW_CHOOSING_MODIFIERS = {
	
	init:function(options){
		
		this._init(options);

		this.$btnSave = this.$view.find('.save');
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$allMenuSection =  this.$view.find('.all-menu-sections');
		
		this.behavior();
		this.reset();
		return this;

	},

	update:function(item){
		var _this=this;
		this.reset();
		this._update();
		this._page_hide();
		this.ITEM = item;		
		this.rebuild();
		setTimeout(function(){ _this._page_show(); },300);
	},

	reset:function(){
		this._reset();
		this._page_to_top();
		this._need2save(false);
		this.NEW_MODIFIERS = [];				
	},
	rebuild:function(){
		var _this=this;
		this.$allMenuSection.html("");		
		
		const currentItemId = parseInt(this.ITEM.id_menu,10);
		let arrMenus = GLB.VIEW_ALL_MENU.get();
		arrMenus = arrMenus.filter((m)=>parseInt(m.id,10)!==currentItemId);
		
		const modifiers = this.ITEM.modifiers || [];

		console.log('modifiers',modifiers)

		for(var i=0;i<arrMenus.length;i++){			
			const itemId = arrMenus[i].id;			
			const checked = modifiers.length && modifiers.includes(itemId) ? 'checked':'';						
			var $btnMenu = $('<div class="std-form__radio-button '+checked+'" id="'+arrMenus[i].id+'">'+arrMenus[i].title+'</div>\n');
			$btnMenu.on("touchend",function(){
				if(!_this.VIEW_SCROLLED){
					if($(this).hasClass('checked')){
						$(this).removeClass('checked')
						_this.remove_modifier($(this).attr('id'));
					}else{
						$(this).addClass('checked')
						_this.add_modifier($(this).attr('id'));
					}					
				};
				return false;
			});
			this.$allMenuSection.append($btnMenu);
		};
		setTimeout(function(){
			_this._page_show();
		},600);
	},
	remove_modifier:function(id_menu){
		this.NEW_MODIFIERS = this.NEW_MODIFIERS.filter((id)=>id!==id_menu);
		this.on_change();
	},
	add_modifier:function(id_menu){
		if(!this.NEW_MODIFIERS.includes(id_menu)){
			this.NEW_MODIFIERS = [...this.NEW_MODIFIERS, id_menu];
		}
		this.on_change();
	},	
	on_change:function(){
		console.log('this.NEW_MODIFIERS',this.NEW_MODIFIERS)
		this._need2save(true);
	},
	behavior:function()	{
		var _this = this;

		this._behavior();

		this.$btnSave.on('touchend',function(){
			_this._blur({onBlur:function(){

				if(!_this.LOADING){
					if(!_this.NEED_TO_SAVE){					
						_this._go_back();
					}else{
						var menu = GLB.VIEW_ALL_MENU.get(_this.NEW_MODIFIERS);					
						_this.save({
							onReady:function(item){						
								// GLB.VIEW_ALL_ITEMS.update(menu,{move2end:true});
								// GLB.VIEWS.jumpTo(GLB.VIEW_ALL_ITEMS.name);						
								_this._go_back();
							}
						});
					}				
				};

			}});
			return false;
		});

		this.$btnBack.on('touchend',function(){
			_this._blur({onBlur:function(){
				!_this.LOADING && _this._go_back();
			}});			
			return false;
		});				

	},
	save:function(opt){
		var _this=this;
		
		this._now_loading();	
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.save_item_modifiers.php';				
		var data ={id_item:this.ITEM.id,new_item_modifiers:this.NEW_MODIFIERS};

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType:"jsonp",
            data:data,
            method:"POST",
            success:function(response) {
            	console.log('response',response)
            	if(!response.error){
            		var cafe_rev = response['cafe-rev'];
            		var item = response['item'];
					_this.ITEM.modifiers = _this.NEW_MODIFIERS;
            		setTimeout(function(){
	            		_this._end_loading();
		            	opt&&opt.onReady&&opt.onReady(item);
            		},300);
            	}else{
					console.log(response.error);
					_this._end_loading();
					_this.errMessage();			        
            	}
            },
            error:function(response) {
				console.log("err save item modifiers",response);
				_this._end_loading();
				_this.errMessage();		        
			}
        });		
	},
	errMessage:function(){
		var message = [
				"Не получается сохранить изменения. <br> ",
				"Попробуйте позже или обратитесь ",
				"к разработчику"
			].join('');					
		GLB.VIEWS.modalMessage({
			title:GLB.LNG.get("lng_error"),
			message: message,
			btn_title:GLB.LNG.get('lng_close')
		});				
	}	

};