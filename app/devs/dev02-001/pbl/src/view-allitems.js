import {GLB} from './glb.js';
import $ from 'jquery';

export var VIEW_ALLITEMS = {
	init:function(options) {
		
		this._init(options);		
	
		this.$headerIcon = this.$view.find(this._CN+"std-header-icon"); 
		this.$headerTitle = this.$view.find(this._CN+"std-header-title"); 

		this.$btnBasket= this.$view.find(this._CN+"btn-basket");
		this.$btnClose = this.$view.find(this._CN+"btn-close-menu");
		this.$btnBack = this.$view.find(this._CN+"btn-back, "+this._CN+"std-header-btn-back, "+this._CN+"btn-close-items");
		
		this.$btnNextItem = this.$view.find(this._CN+"item-btn-next");
		this.$btnPrevItem = this.$view.find(this._CN+"item-btn-prev");
		this.$page_counter = this.$view.find(this._CN+"footer-item-pagecounter span");
		this.$itemsContainer = this.$view.find(this._CN+"allitems-container");		
		this.$bhv = this.$view.find(this._CN+"bhv");		

		this.TOTAL_ITEMS = 0;
		this.ALL_ITEMS = []; // items data
		this.ITEMS = {}; // GLB.ITEM objects
		this.CURRENT = 0;

		this.behavior();

		return this;
	},
	update:function(menu) {
		
		var _this=this;

		this.MENU = menu;		

		this.$headerTitle.find("span").html(menu.title);

		for(var i in GLB.MENU_ICONS.get()){ 
			this.$headerIcon.removeClass(_this.CN+"icon-"+GLB.MENU_ICONS.get(i));
		}

		this.$headerIcon.addClass(_this.CN+"icon-"+ GLB.MENU_ICONS.get(this.MENU.id_icon));

		this._content_hide();
		
		var allitems = this.chefsmenu.get_allitems(this.MENU);		

		var fn = {
			on_built:function(){
				console.log("ok!")
				_this._content_show();					
				setTimeout(function(){ 
					_this.chefsmenu.end_loading(); 
					setTimeout(function(){ 
						_this.start_load_images();
					},300);
				},100);				
			}
		};

		if(allitems){
			this.ALL_ITEMS = allitems;			
			this.TOTAL_ITEMS = this.ALL_ITEMS.arr.length;
			setTimeout(function(){
				_this.build({onReady:function(){
						fn.on_built();
					}
				});				
			},600);
		}else{
			setTimeout(function(){
				_this.load_items({onReady:function(){
					console.log("start build")
					_this.build({onReady:function(){
							fn.on_built();							
						}						
					});
				}});
			},400);
		};

	},	
	get_menu_data:function(){
		return this.MENU;		
	},
	get_total:function() {
		return this.TOTAL_ITEMS;
	},
	get_items:function(index) {
		return index!==undefined ? this.ALL_ITEMS[index] : this.ALL_ITEMS;
	},
	hide_bhv_btns:function(mode) {
		if(mode){
			this.$bhv.hide();
		}else{
			this.$bhv.show();
		}		
	},
	behavior:function() {
		var _this=this;

		var arrMobileButtons = [
			this.$btnBasket,
			this.$btnBack,			
			this.$btnClose,	
			//		
			this.$btnNextItem,
			this.$btnPrevItem
			];

		this._behavior(arrMobileButtons);

		this.$btnBasket.on("touchend click",(e)=> {
			GLB.VIEW_CART.update();
			GLB.UVIEWS.set_current("the-showcart");
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$btnBack.on("touchend click",(e)=> {			
			GLB.UVIEWS.go_back();
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$btnNextItem.on("touchend click",(e)=> { 
			this.try_next(); 
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$btnPrevItem.on("touchend click",(e)=> {
			this.try_prev(); 
			e.originalEvent.cancelable && e.preventDefault();
		});				

	},
	update_btns:function(){

		if(this.CURRENT==0){
			this.$view.removeClass("items-last-page");
			this.$view.addClass("items-first-page");
		}else if(this.CURRENT==this.TOTAL_ITEMS-1){
			this.$view.addClass("items-last-page");
			this.$view.removeClass("items-first-page");
		}else{
			this.$view.removeClass("items-last-page");
			this.$view.removeClass("items-first-page");			
		}
	
	},
	start_load_images:function() {
		var _this=this;
		this.NOW_IMG_LOADING = false;
		this.ARR_NEED_TO_LOAD_IMAGE = [];		
		var items = this.get_items();

		if(this.get_total()){
			for(var i=0;i<items.arr.length;i++){
				items.arr[i].image_url && this.ARR_NEED_TO_LOAD_IMAGE.push(items.arr[i]);
				items.arr[i].image_url && this.ITEMS[items.arr[i].id].image_now_loading();
			}
		};		

		var fn = {
			next_load_image:function(){
				var item = _this.ARR_NEED_TO_LOAD_IMAGE.shift();								
				if(item){					
					fn.load_image(item,function(image_object) {						
						setTimeout(function(){
							_this.ITEMS[item.id].insert_image(image_object);
							fn.next_load_image();
						},300);
					});
				}else{
					console.log('end load')					
					_this.NOW_IMG_LOADING = false;
				}
			},
			load_image:function(item, doAfterLoad) {
				_this.NOW_IMG_LOADING = new Image();
				_this.NOW_IMG_LOADING.onload = function(){
					doAfterLoad && doAfterLoad(this);
				};
				_this.NOW_IMG_LOADING.src = item.image_url;
			}
		};

		// console.log('_this.ARR_NEED_TO_LOAD_IMAGE',_this.ARR_NEED_TO_LOAD_IMAGE);
		fn.next_load_image();		

	},
	load_items:function(opt){
		var _this=this;
		
		var url = GLB_APP_URL+"pbl/lib/pbl.get_all_items.php";

        var fn = {
            arr2obj:function(arr){
                var obj = {};
                for(var i=0;i<arr.length;i++){
                    obj[arr[i].id] = arr[i];
                }
                obj.arr = arr;
                return obj;
            }
        };        

        var data = {menu:this.MENU.id};

        this.AJAX = $.ajax({
            url: url + "?callback=?",
            jsonpCallback:GLB.CALLBACK_RANDOM.get(),
            dataType: "jsonp",
            method:"POST",
            data:data,
            success: function (allitems) {        
            	console.log('allitems',allitems)    	                  	
            	$(allitems).each(function() { this.price = this.price || 0;	});            	
            	_this.TOTAL_ITEMS = allitems.length;
                _this.ALL_ITEMS = fn.arr2obj(allitems);
                _this.chefsmenu.set_allitems(_this.MENU,_this.ALL_ITEMS);
                opt && opt.onReady && opt.onReady();
            },
            error:function(response) {
                console.log("err response",response);
            }
        });		

		
	},
	build:function(opt) {
		var _this=this;
		_this.$itemsContainer.html("");		
		this.ITEMS = {};
		var items = this.get_items();
		console.log('- - - -items- - - -',items)
		
		if(this.get_total()){
			for(var i=0;i<items.arr.length;i++){
				var newItem  = $.extend({},GLB.ITEM); 
				this.ITEMS[items.arr[i].id] = newItem.init(_this,items.arr[i],i);				
			}
		};
		this.CURRENT = 0;
		this.go_to(this.CURRENT,"fast");		
		this.update_page_counter();
		opt && opt.onReady && opt.onReady();
	},
	get_element:function() {
		return this.$itemsContainer;
	},
	all_items_show_large_images:function(){		
		var arr = this.ALL_ITEMS.arr;
		if(!arr.length){return false;}
		for(var i=0;i<arr.length;i++){
			var fast = (i!==this.CURRENT) ? true : false;
			var currId = this.ALL_ITEMS.arr[i].id;
			var item = this.ITEMS[currId];
			item && item.portrait_show_large_image(fast);
		}
	},
	all_items_close_large_images:function(){
		var arr = this.ALL_ITEMS.arr;
		if(!arr.length){return false;}
		for(var i=0;i<arr.length;i++){
			var fast = (i!==this.CURRENT) ? true : false;
			var currId = this.ALL_ITEMS.arr[i].id;
			var item = this.ITEMS[currId];
			item && item.portrait_close_large_image(fast);
		}
	},	
	current_item_close_description:function(){
		var currId = this.ALL_ITEMS.arr[this.CURRENT].id;
		var item = this.ITEMS[currId];	
		item && item.portrait_close_descr();
	},
	try_next:function(){		
		if(!this.get_total()) return false;
		
		this.current_item_close_description();

		if(this.CURRENT<this.get_total()-1){
			this.CURRENT+=1;
			this.go_to(this.CURRENT);
		}else{
			this.show_cant_next();
		}
	},
	try_prev:function(){		
		if(!this.get_total()) return false;
		
		this.current_item_close_description();
		
		if(this.CURRENT>0){
			this.CURRENT-=1;
			this.go_to(this.CURRENT);
		}else{
			this.show_cant_prev();
		}
	},	
	go_to:function(page,fast) {
		var speed = fast?"0s":"0.5s";
		this.$itemsContainer.css({transform:"translateX(-"+(page*100)+"%)",transition:speed});
		this.update_page_counter();
		this.update_btns();
	},
	pages_move:function(delta,speed) {
		this.$itemsContainer.css({transform:"translateX("+delta+")",transition:"transform "+speed});		
	},
	show_cant_prev:function() {
		var _this=this;
		this.pages_move(3+"%","0.1s");
		setTimeout(function(){ _this.pages_move("0%","0.5s"); },100);
	},
	show_cant_next:function() {
		var _this=this;
		var left = (this.get_total()-1)*100;
		this.pages_move(-(left+3)+"%","0.1s");
		setTimeout(function(){ _this.pages_move(-left+"%","0.5s"); },100);
	},
	update_page_counter:function() {
		this.$page_counter.html( (this.CURRENT+1)+"/"+this.get_total() );
	}
};