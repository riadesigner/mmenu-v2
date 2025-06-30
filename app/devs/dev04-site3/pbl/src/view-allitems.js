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
		this.ALL_ITEMS = {}; // saved items data in two way: associative array and array		
		this.ITEMS = {}; // GLB.ITEM objects
		this.CURRENT = 0;
		this.SAFE_NUMBER = 5;

		this.behavior();

		return this;
	},
	update:function(menu) {		

		this.MENU = menu;
		this.upade_header();
		this._content_hide();		

		var allitems = this.chefsmenu.get_allitems_for_menu(this.MENU);		
		console.log("allitems",allitems);		
		
		var fn = {
			start_items_build:()=>{

				const sizeInBytes = new TextEncoder().encode(JSON.stringify(this.ALL_ITEMS)).length;
				console.log(`Размер объекта this.ALL_ITEMS: ${GLB.CMN.formatBytes(sizeInBytes)} байт`);				

				console.log("start items build")
				this.$itemsContainer.html("");		
				this.CURRENT = 0;
				this.build_instances_async()
				.then((ITEMS)=>{
					this.ITEMS = ITEMS;					
					this.render_actual_range();
					this.go_to(this.CURRENT,"fast");			
					this.update_tpl_page_counter();										
					this.hide_bhv_btns(false);
					this._content_show();					
					setTimeout(()=>{ 
						this.chefsmenu.end_loading(); 
					},100);
				})
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
			let ITEMS_LOADED_OK = false;

			setTimeout(()=>{
				
				console.log("start items loading")
				this.load_items_async()
				.then((arr_items)=>{

					ITEMS_LOADED_OK = true;
					console.log('arr_items loaded',arr_items)
					
					this.ALL_ITEMS = fn.arr2obj(arr_items);					
					this.TOTAL_ITEMS = arr_items.length;							
					this.chefsmenu.set_allitems_for_menu(this.MENU,this.ALL_ITEMS);					
					
					console.log('created all_items object',this.ALL_ITEMS);					
					
					fn.start_items_build();
				})
				.catch((err)=>{
					this._show_modal_win("Ой, не удалось загрузить. Меню будет перезагружено.",{onClose:()=>{
						window.location.reload();
					}});
					console.log("error loading items",err);
				});

				setTimeout(() => {
					if (! ITEMS_LOADED_OK) {												
						this._show_modal_win("Ой, не удалось загрузить. Меню будет перезагружено.",{onClose:()=>{
							window.location.reload();
						}});
					}
				}, 3000); 

			},400);
		};

	},	
	upade_header:function(){
		this.$headerTitle.find("span").html(this.MENU.title);
		for(var i in GLB.MENU_ICONS.get()){ 
			this.$headerIcon.removeClass(this.CN+"icon-"+GLB.MENU_ICONS.get(i));
		}
		this.$headerIcon.addClass(this.CN+"icon-"+ GLB.MENU_ICONS.get(this.MENU.id_icon));		
	},
	render_actual_range:function() {
		const range = this.calc_actual_range();			
		for(let i=0;i< this.ALL_ITEMS.arr.length;i++){
			let item_id = this.ALL_ITEMS.arr[i].id;			
			if(i>=range.start && i<=range.end){				
				this.ITEMS[item_id].render();				
			}else{				
				this.ITEMS[item_id].unmount();
			}
		}	
	},
	/**
	 * calculating range items 
	 * what maximum can rendered in the listitems
	 * at the same time,
	 * max_on_page = sn * 2 + 1
	 */
	calc_actual_range:function() {		
		let current = this.CURRENT;
		let total = this.get_total();
		let safe_number = this.SAFE_NUMBER;
		const max_on_page = safe_number*2+1;
		if(total<=max_on_page){
			let range = {
				start:0,
				end:total
			};
			console.log('range = 0 to total: ',range, `curr: ${current}`);
			return range;
		}else{
			const range = {
				start:current-safe_number,
				end:current+safe_number
			};
			if(range.start<0){
				range.end+=Math.abs(current-safe_number);
				range.start=0;
			}
			if(range.end>total){
				let new_start = range.start-Math.abs(total-range.end);
				if(new_start >= 0) {range.start=new_start;} 			
				range.end=total;
			};
			console.log('calculated new range',range, `curr: ${current}`);
			return range;
		}

	},
	get_menu_data:function(){
		return this.MENU;		
	},
	get_total:function() {
		return this.TOTAL_ITEMS;
	},
	get_all_items:function(id_item) {
		return id_item!==undefined ? this.ALL_ITEMS[id_item] : this.ALL_ITEMS;
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
	get_instances:function(items){
		const item_instances = [];
		for(let i=0;i<items.length;i++){
			this.ITEMS[items[i].id] && item_instances.push(this.ITEMS[items[i].id]);
		}
		return item_instances;
	},
	get_items_from_range:function(range) {
		return this.get_all_items().arr.slice(range.start, range.end);
	},
	/**
	 * @return {array} arr_items
	 */
	load_items_async:function(){
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
	build_instances_async:function() {
		return new Promise((res,rej)=>{						
			const ITEMS = {};				
			var arr_items = this.get_all_items().arr;
			if(this.get_total()){
				for(var i=0;i<arr_items.length;i++){
					// creating new instance of ITEM model
					var newItem  = $.extend({},GLB.ITEM); 
					ITEMS[arr_items[i].id] = newItem.init( this, arr_items[i], i );
				}
			};		
			res(ITEMS);
		})
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
	get_element:function() {
		return this.$itemsContainer;
	},
	all_items_show_large_images:function(){		
		var arr = this.ALL_ITEMS.arr;
		if(!arr.length){return false;}
		for(var i=0;i<arr.length;i++){
			var fast = (i!==this.CURRENT) ? true : false;
			var currId = arr[i].id;
			var item = this.ITEMS[currId];
			item && item.portrait_show_large_image(fast);
		}
	},
	all_items_close_large_images:function(){
		var arr = this.ALL_ITEMS.arr;
		if(!arr.length){return false;}
		for(var i=0;i<arr.length;i++){
			var fast = (i!==this.CURRENT) ? true : false;
			var currId = arr[i].id;
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
			this.render_actual_range();			
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
			this.render_actual_range();
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