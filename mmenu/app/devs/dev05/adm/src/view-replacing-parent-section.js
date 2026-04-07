import {GLB} from './glb.js';

export var VIEW_REPLACING_PARENT_SECTION = {
	
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
		this.NEW_PARENT_MENU = 0;				
	},
	rebuild:function(){
		var _this=this;
		this.$allMenuSection.html("");		
		var arrMenus = GLB.VIEW_ALL_MENU.get();
			
		for(var i=0;i<arrMenus.length;i++){
			var checked = parseInt(_this.ITEM.id_menu,10) === parseInt(arrMenus[i].id,10) ? " checked":"";			
			var $btnMenu = $('<div class="std-form__radio-button '+checked+'" mode="'+arrMenus[i].id+'">'+arrMenus[i].title+'</div>\n');
			$btnMenu.on("touchend",function(){
				if(!_this.VIEW_SCROLLED){
					if(!$(this).hasClass('checked')){
						_this.NEW_PARENT_MENU = parseInt($(this).attr('mode'),10);
						$(this).addClass('checked');	
						$(this).siblings().removeClass('checked');
						_this.on_change();
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
	
	on_change:function(){
		var need = (!this.NEW_PARENT_MENU || parseInt(this.NEW_PARENT_MENU,10)===parseInt(this.ITEM.id_menu,10)) ? false : true; 
		this._need2save(need);
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
						var menu = GLB.VIEW_ALL_MENU.get(_this.NEW_PARENT_MENU);					
						_this.save({
							onReady:function(item){						
								GLB.VIEW_ALL_ITEMS.update(menu,{move2end:true});
								GLB.VIEWS.jumpTo(GLB.VIEW_ALL_ITEMS.name);						
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
		var url = PATH + 'lib.save_item_parent.php';		
		var data ={id_item:this.ITEM.id,new_parent_menu:this.NEW_PARENT_MENU,};

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
            		setTimeout(function(){
	            		_this._end_loading();
		            	opt&&opt.onReady&&opt.onReady(item);
            		},300);
            	}else{
			        console.log(response.error);
            	}
            },
            error:function(response) {
				_this._end_loading();
		        console.log("err save item parent",response);
			}
        });		
	}

};