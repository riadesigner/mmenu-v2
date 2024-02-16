import {GLB} from './glb.js';

export var VIEW_ALL_ITEMS = {	
	init:function(options){

		this._init(options);

		this.$tpl_item = $(options.template_item);
		this.$itemsWrapper = this.$view.find('.all-items-wrapper');
		
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$btnAdd = this.$footer.find('.add');
		this.$btnSave = this.$footer.find('.save');		

		this.ID_MENU = 0;
		this.CURRENT_MENU = {};
		
		this.NEED_POS_TO_SAVE = false;		

		this.behavior(); 
		return this;
	},
		
	behavior:function(){
		var _this=this;

		this._behavior();


		var fn = {
			blur_and_go_back:function(){
			_this._blur({onBlur:function(){
				if(!_this.LOADING){
					if(_this.NEED_TO_SAVE || _this.NEED_POS_TO_SAVE){
						_this.AJAX && _this.AJAX.abort();
						_this.PAGES && _this.PAGES.stopLoadingImages();					
						_this.save({onReady:function(){
							_this.PAGELIST && _this.PAGELIST.hide(150,'del tail');						
							setTimeout(function(){ _this._go_back(); },150);
						}});
					}else{
						_this.PAGELIST && _this.PAGELIST.hide(150,'del tail');					
						setTimeout(function(){ _this._go_back(); },150);
					}
				};
			}});
			}
		};
		
		this.$viewTitle.on('touchend',function(){			
			fn.blur_and_go_back();
			return false;	
		});

		this.$btnBack.on('touchend',function(){			
			fn.blur_and_go_back();
			return false;	
		});

		this.$btnSave.on('touchend',function(){
			_this._blur({onBlur:function(){
				!_this.LOADING && _this.save({}); 
			}});			
			return false;		
		});		

		this.$btnAdd.on('touchend',function(){
			_this._blur({onBlur:function(){
				if(!_this.LOADING && !_this.NEED_TO_SAVE){
					_this.check_limit_items_in_section({
						onReady:function(){
							_this.item_add();
						}
					});				
				};

			}});
			return false;
		});

	},
		
	update:function( menu, opt){
		var _this = this;

		this._update();
		this.ID_MENU = menu.id;		

		GLB.THE_CAFE.is_iiko_mode() && this.$btnAdd.hide();

		this.reset();	

		this.$itemsWrapper.html("");

		this._now_loading();
		this._update_title(menu.title)

		var ico = GLB.MENU_ICONS.get(menu.id_icon);
		this.$viewTitleIcon.addClass(ico.className);

		this.CURRENT_MENU = menu;		

		_this._page_show();

		this.load_items_by_menu(
			menu.id,{
			onReady:function(items){
				GLB.ITEMS.update(menu.id,items);
				console.log('====items====',items)		
				setTimeout(function(){
					_this.build_html(opt);	
				},300);	
			}
		});

	},
	reset:function(){		
		this.NEED_TO_SAVE = false;
		this.NEED_POS_TO_SAVE = false;
		this.$view.removeClass('need-to-save');
		this.set_is_empty(false);
	},
	set_is_empty:function(mode){		
		mode ? this.$view.addClass("all-items-is-empty") : this.$view.removeClass("all-items-is-empty");
	},	
	build_html:function(opt){

		var _this=this;
		
		var ARR = GLB.ITEMS.get(this.CURRENT_MENU.id);
		var move2end = opt&&opt.move2end ? true : false;        
        var $tpl = this._update_lng(_this.$tpl_item.clone());
        var managedByIiko = GLB.THE_CAFE.is_iiko_mode();        
   
		this.PAGES = $.menuItems({
			arr:ARR,
			tpl:$tpl,			
			need2Save:function(item){_this._need2save(item);},
			need2SavePos:function(){ _this.need_pos_to_save(); },
			saveItem:function(item){ },
			saveItemsPos:function(){ },
			itemEdit:function(item){ _this.item_edit(item); }, 
			itemImageEdit:function(item){ _this.item_image_edit(item); },
			itemDelete:function(item){ _this.delete_item_confirm(item); },
			itemReplace:function(direction){ _this.item_replace(direction);},
			currency : GLB.THE_CAFE.get().cafe_currency,
			pricePrecision : GLB.THE_CAFE.get().price_precision,
			currencySign : GLB.CURRENCY.get_current(),
			managedByIiko:managedByIiko
		});

		this.PAGELIST = $.superPageList2({
			$parent:this.$itemsWrapper,
			Pages:this.PAGES,
			ALLITEMS:ARR,
			move2end:move2end,
			pauseBeforeShow:'500',
			on_resize:function(){
				if(GLB.VIEWS.getCurrentName()==_this.name){
					console.log('ON_RESIZE')
					_this.build_html();
				}
			}
		});

    	setTimeout(function(){
    		!ARR.length && _this.set_is_empty(true);			
			_this._end_loading();
    	},500);

	},
	item_replace:function(direction){		
		if(this.PAGELIST){
			direction=='next' ?	this.PAGELIST.change_pos_next() : this.PAGELIST.change_pos_prev();
		}
	},
	need_pos_to_save:function(){
		this.NEED_POS_TO_SAVE = true;
		this.$view.addClass('need-to-save');		
	},
	item_image_edit:function(item){
		var _this=this;

		setTimeout(function(){ _this.PAGELIST && _this.PAGELIST.reset(); }, 200);

		GLB.VIEW_ITEM_IMAGE_CHANGE.update({
			item:item,
			onReady:function(img_param){
				item.image_name = img_param.image_name;
				item.image_url = img_param.image_url;
				_this.PAGES && _this.PAGES.updateImageForCurrent();
			}
		});
		GLB.VIEWS.setCurrent(GLB.VIEW_ITEM_IMAGE_CHANGE.name);		
	},
	item_add:function(){
		var _this=this;

		var ARR = GLB.ITEMS.get(this.CURRENT_MENU.id)

		this.PAGELIST && this.PAGELIST.reset();

		var opt = {
			menu:this.CURRENT_MENU,
			item:false,
			pos:ARR.length,
			doAfterSave:function(item){
				ARR.push(item);
				_this.reset();	
				_this.set_is_empty(false);
				_this.PAGES && _this.PAGES.updateAfterAdd(function(){
					_this.PAGELIST && _this.PAGELIST.updateAfterAdd(function(){
						_this._end_loading();
					});
				});
			}
		};

		setTimeout(function(){
			GLB.VIEW_EDIT_ITEM.update(opt);
			GLB.VIEWS.setCurrent(GLB.VIEW_EDIT_ITEM.name);
		},300)



	},
	item_edit:function(item){
		
		var _this=this;

		var menu_id = this.CURRENT_MENU.id;
		var ALLITEMS = GLB.ITEMS.get(menu_id);		

		var opt = {			
			menu:this.CURRENT_MENU,
			item:item,
			pos:item.pos,
			doAfterSave:function(newItem){
				var updatedItem = GLB.ITEMS.replace_item(menu_id,newItem);				
				// ALLITEMS[newItem.pos] = newItem;
			 	_this.PAGES && _this.PAGES.updateItem(updatedItem);
			}			
		};

		this.save({onReady:function(){
			GLB.VIEW_EDIT_ITEM.update(opt);
			GLB.VIEWS.setCurrent(GLB.VIEW_EDIT_ITEM.name);
		}});

	},	
	delete_item_from_db:function(item){
		
		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.remove_item.php';		
		var id_user = this.ID_USER;
		var id_item = item.id;

		// findIndex by item.id	
		var ARR = this.get();		
		if(!ARR.length) return false;
		var index = -1;
		for(var i=0;i<ARR.length;i++){ if(ARR[i].id == id_item) {index = i; break;}}
		if(index==-1) { console.log('unknown index to delete'); this._end_loading(); return false;}

		this._now_loading();
		this.PAGELIST && this.PAGELIST.lock_behaviors(true);

		var item_last_from_right = index==ARR.length-1;

		var fn = {
			update_all_pos_before_deleting:function(index) {				
				_this.save_items_pos({onReady:function(){
					fn.removeItemFromDB(index);					
				}},"noEndLoading");
			},
			removeItemFromDB:function(index){
				
				_this.reset(); 

		        _this.AJAX = $.ajax({
		            url: url+"?callback=?",
		            dataType: "jsonp",
		            data:{id_item:id_item},
		            method:"POST",
		            success: function (response){		            	
		            	if(!response.error){		            		
							_this.PAGELIST && _this.PAGELIST.removeCurrentPage({
								onReady:function(){ 
									_this._end_loading();
									!_this.get().length && _this.set_is_empty(true);
								}
							});
		            	}else{
		            		this.PAGELIST && this.PAGELIST.lock_behaviors(false);
		            		_this._end_loading();

							GLB.VIEWS.modalMessage({
								title:GLB.LNG.get("lng_attention"),
								message:"Не удалось удалить позицию",
								btn_title:GLB.LNG.get('lng_ok')
							});
							console.log("err:",response.error);
		            	}
		            },
		            error:function(response) {
						_this._end_loading();
						GLB.VIEWS.modalMessage({
							title:GLB.LNG.get("lng_attention"),
							message:"Не удалось удалить позицию",
							btn_title:GLB.LNG.get('lng_ok')
						});
				        console.log("err save menu info",response);
					}
		        });

			}
		};

		if(ARR.length>1 && !item_last_from_right){
			ARR.splice(index,1);
			fn.update_all_pos_before_deleting(index);
		}else{
			ARR.splice(index,1);
			fn.removeItemFromDB(index);
		};

	},	
	delete_item_confirm:function(item) {
		var _this=this;

		_this.PAGELIST && _this.PAGELIST.reset();

		var lng_confirm_delete = GLB.LNG.get('lng_confirm_delete');
		var lng_delete = GLB.LNG.get('lng_delete');
		var lng_cancel = GLB.LNG.get('lng_cancel');

		setTimeout(function(){
			GLB.VIEWS.modalConfirm({
				title:item.title,
				ask:lng_confirm_delete,
				action:function(){
					_this.delete_item_from_db(item);
				},
				buttons:[lng_delete,lng_cancel]
			});
		},300);

	},	
	save:function(opt){
		var _this=this;
		
		var fn = {
			do_save:function(){
				if(_this.NEED_TO_SAVE){
					_this.save_item_flags(_this.NEED_TO_SAVE,opt);	
				}else if(_this.NEED_POS_TO_SAVE){
					_this.save_items_pos(opt);
				}else{
					opt.onReady && opt.onReady();
				}
			}
		};

		if(!_this.LOADING && _this.PAGELIST){
			_this.PAGELIST.reset({onReady:function(){
				fn.do_save();	
			}});
		}

	},
	save_items_pos:function(options,noEndLoading){

		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.save_items_pos.php';		

		this._now_loading();

		var arrpos = []; 
		var ARR = this.get(); 

		for(var i=0;i<ARR.length;i++){ 
			arrpos.push(ARR[i].id);
			ARR[i].pos = i;
		};

		var data ={id_menu:this.ID_MENU,arrpos:arrpos};	
		
        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            data:data,
            method:"POST",
            success: function (response) {
        		_this.reset();        		
            	if(!response.error){
            		var cafe_rev = response['cafe-rev'];
            		var arr_items = response['arr_items'];
            		setTimeout(function(){
						!noEndLoading && _this._end_loading();
	 					options.onReady && options.onReady();
            		},300);
            	}else{
					_this._end_loading();
					console.log("err:",response.error);
            	}
            },
            error:function(response) {
				_this._end_loading();
		        console.log("err save menu info",response);
			}
        });

	},
	save_item_flags:function(item, options){

		var _this = this;

		if(this.LOADING){ return false; }		

		this._now_loading();	
		
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.save_item_flags.php';

		var itemFlags = {
				flag_spicy:item.mode_spicy,
				flag_hit:item.mode_hit,
				flag_vege:item.mode_vege
			};
		
		var fn = {
			saveFlags:function(opt){

		        _this.AJAX = $.ajax({
		            url: url+"?callback=?",
		            dataType: "jsonp",
		            data:{id_item:item.id,itemFlags:itemFlags},
		            method:"POST",
		            success: function (response) {
		            	setTimeout(function(){
			            	_this._end_loading();
			            	if(!response.error){
			            		var cafe_rev = response['cafe-rev'];
			            		var message = response['message'];
								opt.onReady && opt.onReady();
			            	}else{
								console.log("err:",response.error);
			            	}
		            	},1000)
		            },
		            error:function(response) {
						_this._end_loading();
				        console.log("err save flags",response);
					}
		        });				

			}
		};

		if(item){			
			fn.saveFlags({
				onReady:function(){
					if(_this.NEED_POS_TO_SAVE){
						// flag saved, goto save pos
						_this.save_items_pos(options);
					}else{
						_this.reset();						
						setTimeout(function(){
							_this._end_loading();
							options.onReady && options.onReady();	
						},300);
						
					}
				}
			});			
		}else{
			setTimeout(function(){				
				_this.reset();
				_this._end_loading(); 
			},300);
		}

	},
	get:function(){		
		return GLB.ITEMS.get(this.CURRENT_MENU.id);
	},

	load_items_by_menu:function(id_menu,opt){
	
		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.get_all_items.php';		

		var data ={id_menu:id_menu};

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            data:data,
            method:"POST",
            success: function (response) {            	
            	if(!response.error){
            		var NEW_APP_VERSION = response['app-version'];
            		var all_items = response['all-items'];
            		if(CFG.app_version !== NEW_APP_VERSION){
            			_this._update_app(NEW_APP_VERSION);
            			return false;
            		}else{		            
		            	opt && opt.onReady && opt.onReady(all_items);
            		}
            	}else{
            		_this._end_loading();
					console.log("err:",response.error);
            	}
            },
            error:function(response) {				
				_this._reload_by_err();
				console.log("err load items",response);
				return false;        
			}
        });

	},

	check_limit_items_in_section:function(opt){
				
		var cafe = GLB.THE_CAFE.get();
		var limits = parseInt(cafe.cafe_status,10)!==2 ? CFG.limits.test:CFG.limits.full;
		var has_items_in_section = this.get().length;
		
		var can_add_item = has_items_in_section < limits.items_in_section ? true : false;
		
		var limitMsg = "";
		if(parseInt(cafe.cafe_status,10)!==2){
			limitMsg = GLB.LNG.get("lng_limits_items_in_section__test");			
		}else{
			limitMsg = GLB.LNG.get("lng_limits_items_in_section__full");
		};
	
		if(can_add_item){
			opt.onReady && opt.onReady();
		}else{
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:limitMsg,
				btn_title:GLB.LNG.get('lng_close')
			});
		}
	}	
		
};