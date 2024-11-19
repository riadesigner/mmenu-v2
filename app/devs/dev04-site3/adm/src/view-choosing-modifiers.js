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
		this.NEW_MODIFIERS = this.get_modifiers_from_item();
		this.rebuild();
		setTimeout(function(){ _this._page_show(); },200);
	},

	reset:function(){
		this._reset();
		this._page_to_top();
		this._need2save(false);		
	},
	rebuild:function(){
		var _this=this;
		this.$allMenuSection.html("");		
		
		const currentMenuId = this.ITEM.id_menu; // string		
		let arrMenus = GLB.VIEW_ALL_MENU.get();
		arrMenus = arrMenus.filter((m)=>parseInt(m.id,10)!==currentMenuId);
		
		const modifiers = this.NEW_MODIFIERS;
		console.log('modifiers = ',modifiers)

		const fn = {
			modif_toggle_min:(modif_id)=>{							
				this.NEW_MODIFIERS.map((m)=>{
					console.log(`m.id===modif_id ${m.id===modif_id}, ${m.id}`)
					if(m.id===modif_id){
						m.min = m.min==='0'?'1':'0';						
					}
				});				
				console.log('this.NEW_MODIFIERS',this.NEW_MODIFIERS)
				this.rebuild();
				this.on_change();
			},
			modif_toggle_mode:(modif_id)=>{				
				this.NEW_MODIFIERS.map((m)=>{
					if(m.id===modif_id){
						m.mode = m.mode==='AND'?'OR':'AND';							
					}
				});	
				console.log('this.NEW_MODIFIERS',this.NEW_MODIFIERS)			
				this.rebuild();
				this.on_change();
			}
		};
		for(var i=0;i<arrMenus.length;i++){			
			const id_menu = arrMenus[i].id;			
			let arr = modifiers.filter((m)=>{ return m['id']===id_menu; });
			const currentModif = arr.length?arr[0]:null;
			const checked = currentModif ? 'checked':'';						
			const btnTitleStr = `<div class="btn-title">${arrMenus[i].title}</div>`;			
			const minStr = currentModif?currentModif.min:'0';
			const modeStr = currentModif?currentModif.mode:'AND';
			const btnsMinModeStr = `<div class="btns-min-mode"><div class="btn-min">${minStr}</div><div class="btn-mode">${modeStr}</div></div>`;			
			const btnRowStr = `<div class="btn-modifier ${checked}" id="${id_menu}">${btnTitleStr} ${btnsMinModeStr}</div>`;
			const $btnRow = $(btnRowStr);						
			$btnRow.find('.btn-title').on("touchend",function(){				
				if(!_this.VIEW_SCROLLED){
					const $parent = $(this).parent();
					if($parent.hasClass('checked')){
						$parent.removeClass('checked')
						_this.remove_modifier($parent.attr('id'));
					}else{						
						// set default values
						$parent.find('.btn-min').html('0');
						$parent.find('.btn-mode').html('AND');
						$parent.addClass('checked');									
						_this.add_modifier($parent.attr('id'));
					}					
				};
				return false;
			});
			$btnRow.find('.btn-min').on('touchend',()=>{fn.modif_toggle_min($btnRow.attr('id'));})
			$btnRow.find('.btn-mode').on('touchend',()=>{fn.modif_toggle_mode($btnRow.attr('id'));});
			this.$allMenuSection.append($btnRow);
		};
		setTimeout(function(){
			_this._page_show();
		},600);
	},
	get_modifiers_from_item:function(){
		return this.ITEM.modifiers ? JSON.parse(this.ITEM.modifiers): [];
	},
	remove_modifier:function(id_menu){
		this.NEW_MODIFIERS = this.NEW_MODIFIERS.filter((m)=>m['id']!==id_menu);
		this.on_change();
	},
	/**
	 * @param {string} id_menu 
	 * @param {string} name 
	 * @return {void}
	 */
	add_modifier:function(id_menu){		
		let menu = GLB.VIEW_ALL_MENU.get(id_menu);
		let name = menu['title'];
		let arr = this.NEW_MODIFIERS.filter((m)=>{ return m['id']===id_menu; });		
		if(!arr.length){
			const modifier = {'id':id_menu,'name':name,min:'0',mode:'AND'};
			this.NEW_MODIFIERS = [...this.NEW_MODIFIERS, modifier];			
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
						_this.save({
							onReady:function(){																		
								// GLB.VIEW_ALL_ITEMS.update(menu);
								GLB.VIEWS.jumpTo(GLB.VIEW_ALL_ITEMS.name);				
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
            	if(!response.error){
					_this.ITEM.modifiers = JSON.stringify(_this.NEW_MODIFIERS);
            		setTimeout(function(){
	            		_this._end_loading();
		            	opt&&opt.onReady&&opt.onReady();
            		},300);
            	}else{					
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