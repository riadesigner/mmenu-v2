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
		this.SAFE_NUMBER = 5; // ( total page / 2 ) to safely show in viewport in the same time
		this.TOTAL_BYTES_LOADED = 0;		

		this.behavior();

		return this;
	},
	update:function(menu) {

		this.MENU = menu;		
		
		console.log('start all-menu viewer updating');

		this.$headerTitle.find("span").html(menu.title);

		for(var i in GLB.MENU_ICONS.get()){ 
			this.$headerIcon.removeClass(this.CN+"icon-"+GLB.MENU_ICONS.get(i));
		}

		this.$headerIcon.addClass(this.CN+"icon-"+ GLB.MENU_ICONS.get(this.MENU.id_icon));

		this._content_hide();		
		
		var allitems = this.chefsmenu.get_allitems(this.MENU);		

		var fn = {
			start_items_build:()=>{

				const sizeInBytes = new TextEncoder().encode(JSON.stringify(this.ALL_ITEMS)).length;
				console.log(`Размер объекта this.ALL_ITEMS: ${GLB.CMN.formatBytes(sizeInBytes)} байт`);				

				console.log("start items build")
				this.build({onReady:()=>{
						fn.on_built();
					}
				});
			},
			on_built:()=>{				
				console.log("all built ok!")		
				this.hide_bhv_btns(false);					
				this._content_show();						
				setTimeout(()=>{ 
					this.chefsmenu.end_loading(); 
					setTimeout(()=>{ 
						/// TODO loading only current image and nears
						this.start_load_images();
					},300);
				},100);
			},
			arr2obj:function(arr){
				var obj = {};
				for(var i=0;i<arr.length;i++){
					obj[arr[i].id] = arr[i];
				}
				obj.arr = arr;
				return obj;
			}			
		};       		
		
		if(allitems){

			console.log("items already loaded")
			this.ALL_ITEMS = allitems;			
			this.TOTAL_ITEMS = this.ALL_ITEMS.arr.length;
			setTimeout(()=>{
				fn.start_items_build();
			},600);

		}else{
			setTimeout(()=>{
				
				console.log("start items loading")
				this.load_items_async()
				.then((arr_items)=>{
					console.log('arr_items loaded',arr_items)

					this.ALL_ITEMS = fn.arr2obj(arr_items);
					this.TOTAL_ITEMS = arr_items.length;		
					this.chefsmenu.set_allitems(this.MENU,this.ALL_ITEMS);					
					
					console.log('created all_items object',this.ALL_ITEMS);
					
					fn.start_items_build();
				})
				.catch((err)=>{
					console.log("error loading items",err);
				});

			},400);
		};

	},	
	get_menu_data:function(){
		return this.MENU;		
	},
	get_total:function() {
		return this.TOTAL_ITEMS;
	},
	get_all_items:function(index) {
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
			this.cancel_all_loadings_async()
			.then(()=>{
				this.cut_tail_before_go_back_async()
				.then(()=>{
					setTimeout(()=>{
						GLB.UVIEWS.go_back();
					},0);					
				});											
			});
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
		
		this.AJX_IMG_LOADING && this.AJX_IMG_LOADING.abort();

		this.ARR_NEED_TO_LOAD_IMAGE = [];		

		var items = this.get_all_items();

		if(this.get_total()){
			for(var i=0;i<items.arr.length;i++){
				items.arr[i].image_url && this.ARR_NEED_TO_LOAD_IMAGE.push(items.arr[i]);
				items.arr[i].image_url && this.ITEMS[items.arr[i].id].image_now_loading();
			}
		};		

		var fn = {
			next_load_image:()=>{
				var item = this.ARR_NEED_TO_LOAD_IMAGE.shift();								
				if(item){					
					fn.load_image(item,(image_object)=> {						
						setTimeout(()=>{
							this.ITEMS[item.id].insert_image(image_object);
							fn.next_load_image();
						},300);
					});
				}else{
					console.log('Всего загружено:', GLB.CMN.formatBytes(this.TOTAL_BYTES_LOADED));
					console.log('end loading images');
				}
			},
			load_image:(item, doAfterLoad)=> {
				
				const url = item.image_url;
			
				this.AJX_IMG_LOADING = $.ajax({
					url: url,
					method: 'GET',
					xhrFields: {
						responseType: 'blob'
					},
					success: (blob)=> {
						this.TOTAL_BYTES_LOADED += blob.size;
						console.log('Загружено:', GLB.CMN.formatBytes(blob.size), url);
						const img = new Image();
						img.onload = function(){ doAfterLoad && doAfterLoad(this); }
						img.src = URL.createObjectURL(blob);
					},
					error: (xhr, status, error)=> {
						if(error==='abort'){
							console.error('Загрузка отменена');
						}else{
							console.error('Ошибка загружки изображения:', error);
						}
						
					}
				});
			}
		};

		fn.next_load_image();		

	},
	load_items_async:function(opt){
		return new Promise((res, rej)=>{			
		
			var url = GLB_APP_URL+"pbl/lib/pbl.get_all_items.php";	
			var data = {menu:this.MENU.id};
	
			this.AJX_ITEMS = $.ajax({
				url: url + "?callback=?",
				jsonpCallback:GLB.CALLBACK_RANDOM.get(),
				dataType: "jsonp",
				method:"POST",
				data:data,
				success: (arr_items)=> {        
					res(arr_items);
				},
				error:(response)=> {
					console.log("err response",response);
					rej("ошибка загрузки");
				}
			});		
		})		
	},
	build:function(opt) {
		var _this=this;
		this.$itemsContainer.html("");		
		
		this.ITEMS = {};

		var items = this.get_all_items();		
		
		if(this.get_total()){
			for(var i=0;i<items.arr.length;i++){
				var newItem  = $.extend({},GLB.ITEM); 
				this.ITEMS[items.arr[i].id] = newItem.init( _this, items.arr[i], i );
			}
		};
		this.CURRENT = 0;
		this.go_to(this.CURRENT,"fast");		
		this.update_tpl_page_counter();

		// this.render_items_range();

		opt && opt.onReady && opt.onReady();
	},
	cancel_all_loadings_async:function(){		
		return new Promise((res,rej)=>{
			 
			console.log('- отменили загрузку позиций');
			this.AJX_ITEMS && this.AJX_ITEMS.abort();			

			console.log('- отменили загрузку изображений');
			this.ARR_NEED_TO_LOAD_IMAGE = [];
			this.AJX_IMG_LOADING && this.AJX_IMG_LOADING.abort();

			res();

		})
	},
	cut_tail_before_go_back_async:function(){
		return new Promise((res,rej)=>{
			console.log('cutting the tail...');
			var currId = this.ALL_ITEMS.arr[this.CURRENT].id;			
			for(let i in this.ITEMS){
				let item = this.ITEMS[i];
				// unmounting from view all except current item				
				currId!==item.get().id && item.unmount();				
			}			
			res();
		})
	},
	// render_items_range:function(){
	// 	const items_range = this.get_items_range();
	// 	for(i in items_range){
	// 		// items_range[i].render();
	// 	}		
	// },
	// get_items_range:function(){
	// 	const [from, to] = this.calc_current_range();
	// 	var items_range = [];		
	// 	console.log('from',from,'to',to);
	// 	for(var i=from;i<to+1;i++){			
	// 		// if(i<this.TOTAL_ITEMS){
	// 		// 	items_range.push(this.ALL_ITEMS.arr[i].id);
	// 		// }
	// 	}		
	// 	return items_range;
	// },	
	// calc_current_range:function(){
	// 	// const curr = this.CURRENT; 
	// 	const curr = 2; 
	// 	// let from, to = 0;
		
	// 	console.log('curr',curr, 'this.SAFE_NUMBER', this.SAFE_NUMBER)
	// 	const SN = this.SAFE_NUMBER;
	// 	if(curr < SN){			
	// 	}
	// 	const from = curr < SN ? 0 : SN - curr;
	// 	const remains = curr < SN ? SN - curr : 0;				
	// 	const to = curr + SN + Math.abs(remains);
	// 	return [from, to];
	// },
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
	current_item_close_modifiers:function(){
		var currId = this.ALL_ITEMS.arr[this.CURRENT].id;
		var item = this.ITEMS[currId];	
		item && item.close_modifiers_panel();
	},
	current_item_close_description:function(){
		var currId = this.ALL_ITEMS.arr[this.CURRENT].id;
		var item = this.ITEMS[currId];	
		item && item.portrait_close_descr();
	},
	try_next:function(){		
		if(!this.get_total()) return false;
		
		this.current_item_close_description();
		this.current_item_close_modifiers();

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
		this.current_item_close_modifiers();
		
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
		this.update_tpl_page_counter();
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
	update_tpl_page_counter:function() {
		this.$page_counter.html( (this.CURRENT+1)+"/"+this.get_total() );
	}
};