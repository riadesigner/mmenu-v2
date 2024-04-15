import {GLB} from './glb.js';
import {IIKO_ITEM_SIZER} from './iiko/iiko-item-sizer.js';

import $ from 'jquery';

export var ITEM = {
	init:function(objParent,itemData,index) {

		this.objParent = objParent;
		this.$elParent = objParent.get_element();
		this.ITEM_DATA = itemData;		
		this.INDEX = index;

		this.CN = "mm2-";
		this._CN = "."+this.CN;		

		this.CART_OFF = GLB.CART.is_off();
		this.CLASS_DISABLED = this.CN+'disabled';		

		this.$tpl = $("#mm2-templates "+this._CN+"item");
		this.$item = this.$tpl.clone();
		this.$item.css({transform:"translateX("+(this.INDEX*100)+"%)"});

		this.ITEM_DATA.image_url == "" && this.$item.addClass("has-no-image");

		this.$btnAddToCart = this.$item.find(this._CN+"item-btn-addtocart");
		this.$totalInCart = this.$item.find(this._CN+"item-btn-addtocart__count");
		
		this.$itemData = this.$item.find(this._CN+"item-data");
		this.$itemFlags = this.$item.find(this._CN+"item-flags2");

		this.$itemImageContainer = this.$item.find(this._CN+"item-image");		
		this.$itemDataContainer = this.$item.find(this._CN+"item-data-container");
		this.$descr =  this.$item.find(this._CN+"item-description");
		this.$bhvPortrait = this.$item.find(this._CN+"item-bhv-portrait");
		this.$bhvLandscape = this.$item.find(this._CN+"item-bhv-landscape");

		this.$btnReadMore = this.$item.find(this._CN+"item-btn-more-largescreen"); 
		
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
		this.$iiko_btns_sizes_wrapper_mobile = this.$item.find(this._CN+"item-btns-sizes-mobile");		
		this.$iiko_btns_sizes_wrapper_desktop = this.$item.find(this._CN+"item-btns-sizes-desktop");
		
		this.behavior();

		this.$elParent.append(this.$item);		

		if(this.ITEM_DATA.created_by=="iiko"){			
			this.iiko_build_sizes_and_modifiers();
		};

		this.insert_data();
		this.update_lng();

		return this;

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
		return this.ITEM_DATA;	
	},
	get_element:function() {
		return this.$item;
	},
	add_item_to_cart:function() {						
		const IIKO_MODE = GLB.CAFE.get().iiko_api_key!=="";		
		if(GLB.CART){				
			if(IIKO_MODE){
				let menu = this.objParent.get_menu_data();
				let item = this.ITEM_DATA;				
				GLB.VIEW_IIKO_MODIFIERS.update(menu,item,this.IIKO_SIZER,{
					onAddToCart:(total_in_cart)=>{ 						
						this.update_cart_btn(total_in_cart);		
					}});
				GLB.UVIEWS.set_current("the-iiko-modifiers");
			}else{		
			// CHEFSMENU MODE
				let total_in_cart = GLB.CART.add_order(this.get(),{count:1});
				this.update_cart_btn(total_in_cart);
				this.play_smile_animation();
			}
		}
	},
	play_smile_animation:function() {
		this.$item.removeClass(this.CLASS_PLAY_ADDTOCART);				
		setTimeout(()=>{ this.$item.addClass(this.CLASS_PLAY_ADDTOCART);},60);
	},

	iiko_build_sizes_and_modifiers:function() {		

		const item = this.ITEM_DATA;		

		if(item.iiko_sizes){
			item.iiko_sizes_parsed = JSON.parse(item.iiko_sizes);				
		};		
		
		if(item.iiko_modifiers){
			item.iiko_modifiers_parsed = JSON.parse(item.iiko_modifiers);				
		};
		
		this.IIKO_SIZER = $.extend({},IIKO_ITEM_SIZER);	
		this.IIKO_SIZER.init(item,{onUpdate:(vars)=>{
			this.iiko_update_price_and_volume(vars);
		}});
		
		const [$btns_mobiles,$btns_desktop] = this.IIKO_SIZER.get_buttons();
		
		if($btns_mobiles && $btns_mobiles.size()){
			this.$iiko_btns_sizes_wrapper_mobile.prepend($btns_mobiles);	
			this.$iiko_btns_sizes_wrapper_desktop.prepend($btns_desktop);	
		}else{
			// this.$item.addClass('item-not-sized');
		}

	},
	insert_data:function() {
		var _this=this;
		
		const IIKO_MODE = GLB.CAFE.get().iiko_api_key!=="";

		var item = this.ITEM_DATA;
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
		
		if(IIKO_MODE){
			this.iiko_update_price_and_volume(this.IIKO_SIZER.get_all());
		}else{
			// CHEFSMENU MODE ONLY
			this.$item.find(this._CN+"item-price span").html(item.price+" "+GLB.CAFE.get('cafe_currency').symbol);
			this.$item.find(this._CN+"item-volume").html(item.volume);
		}

		this.update_item_data_container();		

	},
	update_cart_btn:function(total_orders) {				
		this.$totalInCart.html(total_orders);
		if(total_orders){
			this.$btnAddToCart.addClass("this-cart-full");	
		}else{
			this.$btnAddToCart.removeClass("this-cart-full");	
		}
	},
	iiko_update_price_and_volume:function(vars) {				
		var currency_symbol = GLB.CAFE.get('cafe_currency').symbol;
		this.$item_price.html(vars.price + " " + currency_symbol);
		this.$item_volume.html(vars.volume);							
		this.$item_volume2.html(vars.volume);
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
		this.$iiko_btns_sizes_wrapper_mobile.css({ transform:"translate(0, "+offsetSizeBtns.posY+"px)"});
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
	insert_image:function(image_object) {
		var _this=this;
		
		this.IMAGE = image_object;
		this.image_end_loading();
		

		var params = this.calc_image_bounds();
		var src = this.ITEM_DATA.image_url;
	
		this.$image = $(image_object).css({
			position:"absolute",
			opacity:0,
			"transform-origin":"top left",
			transform:"translate("+params.left+"px,"+(params.top)+"px) scale("+(params.scale)+")" 
		});
		this.$itemImageContainer.html(this.$image);

		setTimeout(function() {
			_this.$image.css({opacity:1,transition:"opacity 1s,transform .5s"});			
			_this.$item.addClass(_this.CLASS_IMAGE_READY_TO_ZOOM);
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

		GLB.MOBILE_BUTTONS.bhv([this.$btnAddToCart]);		

		var fn = {
			bhvSwipe:function(opt) {
				if(!_this.is_portrait()) return false;
				if(opt.direction=="up"){
					console.log("==up")
					_this.bhv_portrait_touched(true);
				}else if(opt.direction=="down" && !_this.has_class_large()){
					console.log("==down")
					_this.bhv_portrait_touched();
				}else if(opt.direction=="left"){
					_this.objParent.try_next();
				}else if(opt.direction=="right"){
					_this.objParent.try_prev();
				}
			},
			bhvDescrSwipe:function(opt) {
				if(!_this.is_portrait()) return false;
				if(opt.direction=="up"){					
				}else if(opt.direction=="down"){					
				}else if(opt.direction=="left"){
					_this.objParent.try_next();
				}else if(opt.direction=="right"){
					_this.objParent.try_prev();
				}
			}			
		};

		this.$bhvPortrait.swipe({
			onSwipe:function(opt){ 
				fn.bhvSwipe(opt); 
			}, distance:40, 
			enableMouse:false
		});					
		
		this.$bhvPortrait.on("click",function(e){
			var event = e.originalEvent;
			var offset = _this.calc_offset_vertical();
			var show_description = ($(this).height()-event.offsetY) < Math.abs(offset.top);
			_this.bhv_portrait_touched(show_description);
		});

		this.$descr.on("touchstart",function(e) {
			if(_this.is_portrait()){
				_this.DESCR_SCROLLED = false;
			}			
		});
		this.$descr.on("touchmove",function(e) {
			 if(_this.is_portrait()){
			 	_this.DESCR_SCROLLED = true;
			 }
		});

		this.$descr.swipe({
			onSwipe:function(opt){ 
				fn.bhvDescrSwipe(opt); 
			}, distance:40, 
			enableMouse:false,
			preventDefault:false
		});	

		this.$descr.on("touchend",function(e) {
			if(!_this.DESCR_SCROLLED && _this.is_portrait()){
				_this.portrait_close_descr();
			}					
			e.originalEvent.cancelable && e.preventDefault();
			e.stopPropagation();
		});

		this.$descr.on("mousedown",function(){
			if(_this.is_portrait()){
				_this.DESCR_SCROLLED = false;
			}			
		});
		this.$descr.on("mousemove",function(){
			if(_this.is_portrait()){
				_this.DESCR_SCROLLED = true;	
			}			
		});		

		this.$descr.on("click",function(){
			_this.is_portrait() 
			&& !_this.DESCR_SCROLLED 
			&& _this.portrait_close_descr();
			return false;
		});

		this.$btnReadMore.on("touchend click",function(){
			console.log("btnReadMore clicked")
			_this.is_portrait() 
			&& _this.has_class_description()
			&& _this.portrait_close_descr();
			return false;	
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

	bhv_landscape_touched:function() {
		if(this.has_class_large()){
			this.$item.removeClass(this.CLASS_LARGE_IMAGE);
		}else{
			this.$item.addClass(this.CLASS_LARGE_IMAGE);
		};
		this.update_image();		
	},
	bhv_portrait_touched:function(show_description) {
		// close large image or description if shown		
		if(this.has_class_description()){
			this.portrait_close_descr();
		}else if(this.has_class_large()){
			// this.portrait_close_large_image();
			this.objParent.all_items_close_large_images();
		}else{
			// if not shown desc or image			
			if(show_description){				
				this.portrait_show_descr();
			}else{
				this.objParent.all_items_show_large_images();
			};
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
	}	
};
