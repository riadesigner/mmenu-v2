import {GLB} from './glb.js';
import $ from 'jquery';
import {IIKO_ITEM} from './iiko/iiko-item.js';
import {CHEFS_ITEM} from './chefs/chefs-item.js';
import {ITEM_MODIF_PANEL} from './item-modif-panel.js';
import swipe from './plugins/swipe.js'; // no delete from here

export var ITEM = {
	/**
	 * @param {Object} objParent - items container
	 * @param {Object} itemData - item data
	 * @param {Number} index - index of item in parent object
	 * 
	 */
	init:function( objParent, itemData, index ) {

		this.NOW_IN_VIEWPORT = false

		this.objParent = objParent;
		this.$elParent = objParent.get_element();
		this.item_data = itemData;		
		this.INDEX = index;

		this.CN = "mm2-";
		this._CN = "."+this.CN;		

		this.CART_OFF = GLB.CART.is_off();
		this.CLASS_DISABLED = this.CN+'disabled';		

		this.$tpl = $("#mm2-templates "+this._CN+"item");
		this.$item = this.$tpl.clone();
		this.$item.css({transform:"translateX("+(this.INDEX*100)+"%)"});

		this.item_data.image_url == "" && this.$item.addClass("has-no-image");

		this.$btnAddToCart = this.$item.find(this._CN+"item-btn-addtocart");
		this.$totalInCart = this.$item.find(this._CN+"item-btn-addtocart__count");
		
		this.$itemData = this.$item.find(this._CN+"item-data");
		this.$itemFlags = this.$item.find(this._CN+"item-flags2");

		this.$itemImageContainer = this.$item.find(this._CN+"item-image");		
		this.$itemDataContainer = this.$item.find(this._CN+"item-data-container");
		this.$descr =  this.$item.find(this._CN+"item-description");
		this.$bhvPortrait = this.$item.find(this._CN+"item-bhv-portrait");
		this.$bhvLandscape = this.$item.find(this._CN+"item-bhv-landscape");
		
		this.CLASS_PLAY_ADDTOCART = this.CN+"play-addtocart";
		this.CLASS_LARGE_IMAGE = this.CN+"show-large-image";
		this.CLASS_FULL_DESCRIPTION = this.CN+"show-full-description";
		this.CLASS_IMAGE_READY_TO_ZOOM = this.CN+"image-ready-to-zoom";		
				
		this.STARTSCROLL=0;
		this.DESCR_SCROLLED = false;

		this.CART_OFF && this.$btnAddToCart.addClass(this.CLASS_DISABLED);		

		// NEW, FOR IIKO MODE
		this.$item_price = this.$item.find(this._CN+"item-price span");
		this.$item_volume = this.$item.find(this._CN+"item-volume");
		this.$item_volume2 = this.$item.find(this._CN+"item-volume2");				
		this.$item_btns_sizes_wrapper_mobile = this.$item.find(this._CN+"item-btns-sizes-mobile");		
		this.$item_btns_sizes_wrapper_desktop = this.$item.find(this._CN+"item-btns-sizes-desktop");

		// UNIVERSAL MODIFIERS PANEL (ONLY FOR IIKO_MODE UNTIL NOW)		
		this.MODIF_PANEL = $.extend({},ITEM_MODIF_PANEL);
		this.MODIF_PANEL.init(this.$item, {
			on_open:()=>{this.objParent.hide_bhv_btns(true);},
			on_close:()=>{this.objParent.hide_bhv_btns(false);}
		});

		this.behavior();
		
		this.IIKO_MODE = GLB.CAFE.is_iiko_mode();		

		if(this.IIKO_MODE){
			this.init_model_item(IIKO_ITEM);									
		}else{
			this.init_model_item(CHEFS_ITEM);
		}

		this.insert_data();
		this.update_lng();

		return this;

	},	
	render:function() {
		if(!this.NOW_IN_VIEWPORT){
			this.NOW_IN_VIEWPORT = true;
			this.$elParent.append(this.$item);	
			this.update_layout();
			console.log("render ",this.item_data.title);
			this.load_image(this.get().image_url);
		}
	},
	unmount:function(){
		if(this.NOW_IN_VIEWPORT){
			this.NOW_IN_VIEWPORT = false;
			this.$item.detach();
			console.log("unmount ",this.item_data.title);
			this.cancel_loading_image();
		}
	},
	get_view:function(){
		return this.$item;
	},
    update_lng:function(){
        this.get_view().find('[lang]').each(function(i){
            $(this).html(GLB.LNG.get($(this).attr('lang')));
        });
    },	
	get:function() {
		return this.item_data;	
	},
	get_element:function() {
		return this.$item;
	},	
	// @param model object (CHEFS_ITEM|IIKO_ITEM)
	init_model_item:function(model){

		// INITIATION MODEL FOR ITEM
		this.MODEL_ITEM = $.extend({},model);	
		this.MODEL_ITEM.init(this.item_data, {
			modifiers_panel:this.MODIF_PANEL,
			on_update_size:(vars)=>{
				this.update_price_and_volume(vars);
			},
			on_update_total_in_cart:(total_in_cart)=>{
				this.update_cart_btn(total_in_cart);
				this.play_smile_animation();
			}						
		});

		// BUILD UI SIZE BUTTONS
		if(this.MODEL_ITEM.has_sizes()){
			const [$btns_mobiles,$btns_desktop] = this.MODEL_ITEM.get_ui_price_buttons();		
			this.$item.addClass('item-sized');
			this.$item_btns_sizes_wrapper_mobile.prepend($btns_mobiles);	
			this.$item_btns_sizes_wrapper_desktop.prepend($btns_desktop);	
		}

	},
	add_item_to_cart:function() {		

		console.log('---this.MODEL_ITEM---', this.MODEL_ITEM)
		console.log('---this.MODEL_ITEM.has_modifiers---', this.MODEL_ITEM.has_modifiers())

		if(this.MODEL_ITEM.has_modifiers()){
			// SHOW MODAL WINDOW WITH MODIFIERS OPTIONS		
			this.MODIF_PANEL.reset();
			this.MODIF_PANEL.show_price(this.MODEL_ITEM.get_price(), 1);
			this.MODIF_PANEL.open();
		}else{
			// JUST ADDING TO CART THE ONE
			const preorder = this.MODEL_ITEM.get_preorder(1);
			let total_in_cart = GLB.CART.add_preorder(preorder);
			this.update_cart_btn(total_in_cart);	
			this.play_smile_animation();	
		}

	},
	play_smile_animation:function() {
		this.$item.removeClass(this.CLASS_PLAY_ADDTOCART);				
		setTimeout(()=>{ this.$item.addClass(this.CLASS_PLAY_ADDTOCART);},60);
	},

	insert_data:function() {

		var item = this.item_data;
		this.$item.attr({id:item.id});

		item.mode_spicy = parseInt(item.mode_spicy,10);
		item.mode_hit = parseInt(item.mode_hit,10);
		item.mode_vege = parseInt(item.mode_vege,10);

		var countFlags = 0;
		if(item.mode_spicy) countFlags++;
		if(item.mode_hit) countFlags++;
		if(item.mode_vege) countFlags++;
		
		var countFlagsClass = "";
		switch(countFlags){
			case 1: countFlagsClass = "item-has-one-flag"; break;
			case 2: countFlagsClass = "item-has-two-flags"; break;
			case 3:	countFlagsClass = "item-has-three-flags"; break;			
		};

		var hasFlags = item.mode_spicy || item.mode_hit || item.mode_vege;
		hasFlags && this.$item.addClass(this.CN+"item-has-flags");
		countFlagsClass && this.$item.addClass(this.CN+countFlagsClass); 

		item.mode_spicy && this.$item.addClass(this.CN+"item-has-flag-spicy");
		item.mode_hit && this.$item.addClass(this.CN+"item-has-flag-hit");
		item.mode_vege && this.$item.addClass(this.CN+"item-has-flag-vege");

		var total_orders = GLB.CART.get_total_orders(item.id);		
		this.update_cart_btn(total_orders);

		this.$item.find(this._CN+"item-title").html(item.title);
		this.$item.find(this._CN+"item-about").html(item.description);
		
		this.update_price_and_volume(this.MODEL_ITEM.sizer_get_vars());
		

	},
	update_cart_btn:function(total_orders) {				
		this.$totalInCart.html(total_orders);
		if(total_orders){
			this.$btnAddToCart.addClass("this-cart-full");	
		}else{
			this.$btnAddToCart.removeClass("this-cart-full");	
		}
	},
	update_price_and_volume:function(vars) {			
		var currency_symbol = GLB.CAFE.get('cafe_currency').symbol;
		this.$item_price.html(vars.price + " " + currency_symbol);		
		const sizeNameStr = vars.sizeName ? `${vars.sizeName}<br>` : '';		
		const volume = parseInt(vars.volume, 10);
		let str_volume = `${sizeNameStr} ${vars.volume} ${vars.units}`;
		if(volume===0 || volume===1000){
			str_volume = '';
		}
		this.$item_volume.html(str_volume);						
		this.$item_volume2.html(str_volume);
	},
	is_portrait:function() {
		return $(window).height() > $(window).width()-1;
	},
	calc_data_size:function() {
		return {width:this.$itemImageContainer.width(),height:this.$itemImageContainer.height()};	
	},	
	calc_offset_vertical:function() {
		var dataSize = this.calc_data_size();
		if(this.is_portrait()){
			var delta = Math.round(dataSize.height/100*35);						
			var Hoffset = this.has_class_large()?0:-delta;
			if(this.has_class_description()){ Hoffset = -dataSize.height;};
			return {left:0,top:Hoffset}
		}else{			
			return {left:0,top:0}		
		}		
	},
	calc_offset_size_btns:function(offset) {
		var dataSize = this.calc_data_size();
		if(this.is_portrait()){						
			var delta = Math.round(dataSize.height/100*61);
			var delta2 = Math.round(dataSize.height/100*26);
			var Hoffset = this.has_class_large()?-delta2:-delta;
			if(this.has_class_description()){ Hoffset = -dataSize.height - delta2;};			
			return {posY:Hoffset}
		}else{			
			return {posY:0}		
		}						
	},
	update_item_data_container:function() {
		var offset = this.calc_offset_vertical();
		var offsetSizeBtns = this.calc_offset_size_btns(offset);
		this.$descr.scrollTop(0);
		this.$itemDataContainer.css({ transform:"translate("+offset.left+"px,"+offset.top+"px)"});
		this.$item_btns_sizes_wrapper_mobile.css({ transform:"translate(0, "+offsetSizeBtns.posY+"px)"});
	},
	image_now_loading:function() {
		this.$item.addClass("mm2-image-loading");
	},
	image_end_loading:function() {
		this.$item.removeClass("mm2-image-loading");		
	},
	calc_image_bounds:function() {		

		if(!this.IMAGE && !this.IMAGE.width) return false;

		var img = this.IMAGE;
		var optHeight = 0;
		var optWidth = 0;

		var dataSize = this.calc_data_size();	

		if(this.is_portrait()){
			optHeight = this.calc_offset_vertical().top;
		}else{			
			optWidth = !this.has_class_large() ? - Math.round(dataSize.width*.4) : 0
		};
		
		var dataWidth = dataSize.width+optWidth;
		var dataHeight = dataSize.height+optHeight;

		var k = dataWidth/dataHeight;
		var imgK = img.width/img.height;
		var Wmax = k>imgK;
		var scale = Wmax?dataWidth/img.width : dataHeight/img.height;		

		var left = Math.round((img.width*scale-dataWidth)/2)+optWidth;
		var top = Math.round((img.height*scale-dataHeight)/2)+optHeight;

		return { top:-top,left:-left,scale:scale }			
	},
	has_class_large:function() {
		return this.$item.hasClass(this.CLASS_LARGE_IMAGE);
	},
	has_class_description:function() {
		return this.$item.hasClass(this.CLASS_FULL_DESCRIPTION);
	},
	cancel_loading_image:function() {
		if(this.AJX_IMG_LOADING){
			this.AJX_IMG_LOADING.abort();
			this.AJX_IMG_LOADING = null;
		}
	},
	load_image:function(url) {
		if(!this.IMAGE && url){
			this.image_now_loading();

			this.AJX_IMG_LOADING = $.ajax({
				url: url,
				method: 'GET',
				cache: true,
				xhrFields: {
					responseType: 'blob'
				},
				success: (blob)=> {
					this.TOTAL_BYTES_LOADED = blob.size;
					console.log('Загружено:', GLB.CMN.formatBytes(blob.size), url);
					const img = new Image();
					img.onload = ()=>{ this.insert_image(img); }
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
	},
	insert_image:function(image_object) {
				
		this.IMAGE = image_object;
		this.image_end_loading();

		var params = this.calc_image_bounds();
	
		this.$image = $(image_object).css({
			position:"absolute",
			opacity:0,
			"transform-origin":"top left",
			transform:"translate("+params.left+"px,"+(params.top)+"px) scale("+(params.scale)+")" 
		});
		this.$itemImageContainer.html(this.$image);

		setTimeout(()=> {
			this.$image.css({opacity:1,transition:"opacity 1s,transform .5s"});			
			this.$item.addClass(this.CLASS_IMAGE_READY_TO_ZOOM);
		},100);

	},	
	update_image:function(fast) {
		var speed = fast?'0s':'.5s';
		if(this.IMAGE && this.IMAGE.width){
			var params = this.calc_image_bounds();
			this.$image && this.$image.css({
				transform:"translate("+params.left+"px,"+params.top+"px) scale("+params.scale+")",
				transition: speed
			});
		};
	},
	update_layout:function(fast){
		this.update_item_data_container(fast);
		this.update_image(fast);
	},	
	on_resize:function(){
		var _this=this;		
		if(this.TMR_RESIZE){clearTimeout(this.TMR_RESIZE);};
		this.TMR_RESIZE = setTimeout(function(){				
			_this.update_item_data_container();
			_this.update_image();
		},200);		
	},
	behavior:function() {
		var _this = this;

		GLB.MOBILE_BUTTONS.bhv([
			this.$btnAddToCart
		]);		

		var fn = {
			bhvSwipe:(opt)=> {
				if(!this.is_portrait()) {return false;}
				if(opt.direction==="up" || opt.direction==="down"){		
					this.bhv_portrait_touched(opt.direction);
				}else if(opt.direction==="left"){
					this.objParent.try_next();
				}else if(opt.direction==="right"){
					this.objParent.try_prev();
				}
			}		
		};

		this.$bhvPortrait.swipe({
			onSwipe:(opt)=>{ 
				fn.bhvSwipe(opt); 
			}, distance:40, 
			enableMouse:false
		});					
		
		this.$bhvPortrait.on("click",function(e){	
			console.log(e);		
			var event = e.originalEvent;
			var offset = _this.calc_offset_vertical();
			switch(_this.get_vertical_pos()){
				case 0:
					var show_description = ($(this).height()-event.offsetY) < Math.abs(offset.top);
					const mode = show_description?"up":"down";
					_this.bhv_portrait_touched(mode);
					break;
				case 1:
					_this.bhv_portrait_touched("up");
					break;
				case 2:
					_this.bhv_portrait_touched("down");
				break;					
			}
			
		});

		$(window).on("resize",function() {
			_this.on_resize();
			_this.update_item_data_container();
		});

		this.$bhvLandscape.on("click",function(e) {			
			_this.bhv_landscape_touched();
			return false;
		});		
		
		this.$btnAddToCart.on("touchend click",function() {			
			!_this.CART_OFF && _this.add_item_to_cart();
			return false;
		});

	},

	get_vertical_pos:function(){
		if(this.has_class_large()){
			return 1;
		}else if(this.has_class_description()){
			return 2;
		}else{
			return 0;
		}
	},

	bhv_landscape_touched:function() {
		if(this.has_class_large()){
			this.$item.removeClass(this.CLASS_LARGE_IMAGE);
		}else{
			this.$item.addClass(this.CLASS_LARGE_IMAGE);
		};
		this.update_image();		
	},
	bhv_portrait_touched:function(direction) {
		if(direction=="down"){
			switch(this.get_vertical_pos()){
				case 0: this.portrait_show_large_image(); break;
				case 2: this.portrait_close_descr(); break;
			}
		}else if(direction=="up"){
			switch(this.get_vertical_pos()){
				case 1: this.portrait_close_large_image(); break;
				case 0: this.portrait_show_descr(); break;										
			}
		};
	},

	portrait_close_large_image:function(fast){
		this.$item.removeClass(this.CLASS_LARGE_IMAGE);
		this.update_layout(fast);
	},	
	portrait_show_large_image:function(fast){
		this.$item.addClass(this.CLASS_LARGE_IMAGE);
		this.update_layout(fast);
	},
	portrait_close_descr:function(){				
		this.$item.removeClass(this.CLASS_FULL_DESCRIPTION);
		this.update_layout();
	},
	portrait_show_descr:function(){				
		this.$item.addClass(this.CLASS_FULL_DESCRIPTION);
		this.update_layout();
	},
	close_modifiers_panel:function(){
		this.MODIF_PANEL.close();
	}
};
