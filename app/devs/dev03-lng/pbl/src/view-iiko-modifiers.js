import {GLB} from './glb.js';
import $ from 'jquery';
import {IIKO_ITEM_MODIFIERS} from './iiko/iiko-item-modifiers.js';

export var VIEW_IIKO_MODIFIERS = {
	init:function(options) {
		
		this._init(options);		
	
		this.$headerIcon = this.$view.find(this._CN+"std-header-icon"); 
		this.$headerTitle = this.$view.find(this._CN+"std-header-title"); 

		this.$btnBasket= this.$view.find(this._CN+"btn-basket");
		this.$btnClose = this.$view.find(this._CN+"btn-close-menu");
		this.$btnBack = this.$view.find(this._CN+"btn-back, "+this._CN+"std-header-btn-back, "+this._CN+"btn-close-items");

		this.$item_title = this.$view.find(this._CN+"item-modifiers__item-title");
		this.$item_price = this.$view.find(this._CN+"item-modifiers__item-price");				
		this.$item_volume = this.$view.find(this._CN+"item-modifiers__item-volume");	

		this.$modifiers_list = this.$view.find(this._CN+"item-modifiers__list-section");
		this.$modifiers_btn_add = this.$view.find(this._CN+"footer-modifiers__count-buttons_plus");
		this.$modifiers_btn_sub = this.$view.find(this._CN+"footer-modifiers__count-buttons_minus");
		this.$modifiers_counter = this.$view.find(this._CN+"footer-modifiers__count-buttons_counter");
		this.$modifiers_btn_cart = this.$view.find(this._CN+"footer-modifiers__basket");		

		// this.onAddToCart = opt.onAddToCart;		

		this.STARTSCROLL=0;
		this.MODIF_SCROLLED = false;
		this.MODIFIERS_SHOWING_NOW = false;		

		this.behavior();

		return this;
	},
	// insert_data:function(){
	// 	var _this=this;		
	// 	let arr = this.MODIFIERS.get();
	// 	console.log("---- arr modifiers ----",arr)			

	// 	if(arr.length){
	// 		const $m_list = $('<ul></ul>');  
	// 		for(let m in arr){
	// 			let m_name = arr[m].name;
	// 			let m_price = arr[m].price;
	// 			$m_list.append([
	// 				'<li>',
	// 					'<div class="m-check"><span></span></div>',
	// 					'<div class="m-title">'+m_name+'</div>',
	// 					'<div class="m-price">+'+m_price+' руб.</div>',
	// 				'</li>'
	// 				].join(''));								
	// 		}						
	// 		this.$modifiers_list.html("").append($m_list);			
			
	// 		const $btns = this.$modifiers_list.find('li');			

	// 		$btns.on('touchend click',function(e, index){
	// 			if(!_this.MODIF_SCROLLED){
	// 				$(this).toggleClass('chosen');
	// 				_this.recalc_sum();	
	// 			};				
	// 			e.originalEvent.cancelable && e.preventDefault();
	// 		});

	// 		GLB.MOBILE_BUTTONS.bhv([$btns]);

	// 		this.$MODIFIERS_BTNS = $btns;

	// 	}else{
	// 		this.$modifiers_list.html("");
	// 	};

	// },	
	update:function(menu,iiko_item,sizer,opt) {
				
		this.ITEM_DATA = iiko_item.get();
		this.MENU = menu;
		this.SIZER = sizer;		
		this.TOTAL_ADD_TO_CART=1;		
		this.onAddToCart = opt.onAddToCart;
					
		this.MODIFIERS =  $.extend({},IIKO_ITEM_MODIFIERS);
		this.MODIFIERS.init(this.ITEM_DATA);		
		
		// this.collect_modifiers();
		this.insert_data();
		this.update_header(menu);
		
		this.recalc_sum();
		

		this.$item_title.html(item.title);		
		
		const s = sizer.get();
		let price = s.price;
		let volume = s.volume;
		// let sizeName = s.sizeName;
		// let sizeId = s.sizeId;
	
		const currency_symbol = GLB.CAFE.get('cafe_currency').symbol;
		this.$item_price.html(price + " " + currency_symbol);
		this.$item_volume.html(volume);

	},
	update_header:function(menu) {
		this.$headerTitle.find("span").html(menu.title);
		for(var i in GLB.MENU_ICONS.get()){ 
			this.$headerIcon.removeClass(this.CN+"icon-"+GLB.MENU_ICONS.get(i));
		}
		this.$headerIcon.addClass(this.CN+"icon-"+ GLB.MENU_ICONS.get(menu.id_icon));
	},

	behavior:function() {
		
		const arrMobileButtons = [
			this.$btnBack,			
			this.$btnClose,
			//
			this.$modifiers_btn_add,
			this.$modifiers_btn_sub,
			this.$modifiers_btn_cart			
			];

		this._behavior(arrMobileButtons);

		this.$btnBack.on("touchend click",(e)=>{			
			this.hide();
			return false;
		});		

		this.$modifiers_btn_add.on('touchend click',(e)=>{
			this.TOTAL_ADD_TO_CART++;
			this.recalc_sum();
			e.originalEvent.cancelable && e.preventDefault();
		});
		this.$modifiers_btn_sub.on('touchend click',(e)=>{
			this.TOTAL_ADD_TO_CART--;
			this.recalc_sum();			
			e.originalEvent.cancelable && e.preventDefault();
		});		

		this.$modifiers_list.on("touchstart",(e)=>{
			this.MODIF_SCROLLED = false;
		});

		this.$modifiers_list.on("touchmove",(e)=> {
			this.MODIF_SCROLLED = true;
		});		

		this.$modifiers_btn_cart.on('touchend click',(e)=>{
			this.add_order_to_cart();
			e.originalEvent.cancelable && e.preventDefault();
		});

	},
	add_order_to_cart:function() {		
		let total_in_cart = GLB.CART.add_order(this.ITEM_DATA, this.PRE_ORDER);
		this.onAddToCart && this.onAddToCart(total_in_cart);	
		this.hide();
	},
	// recalc_sum:function() {		
				
	// 	const s = this.SIZER.get();
	// 	const price = s.price;
	// 	const volume = s.volume;
	// 	const sizeName = s.sizeName;
	// 	const sizeId = s.sizeId;
	// 	const sizeCode = s.sizeCode;		

	// 	const price_item = parseInt(price,10);				
	// 	let [chosen_modifiers, price_modifiers] = this.recalc_modifiers();
	// 	let price_items_and_modifieers = price_item + price_modifiers;
		
	// 	let total_price = this.TOTAL_ADD_TO_CART * price_items_and_modifieers;
				
	// 	this.PRE_ORDER = {
	// 		itemId:this.ITEM_DATA.id,				
	// 		price:price_items_and_modifieers,
	// 		count:this.TOTAL_ADD_TO_CART,
	// 		volume:volume,
	// 		sizeName:sizeName,
	// 		sizeId:sizeId,
	// 		sizeCode:sizeCode,
	// 		item_data:this.ITEM_DATA,
	// 		chosen_modifiers:chosen_modifiers
	// 	};		
		
	// 	this.update_ui(this.TOTAL_ADD_TO_CART, total_price);

	// 	if(!this.TOTAL_ADD_TO_CART){
	// 		this.PRE_ORDER = false;
	// 		GLB.UVIEWS.go_back();
	// 	}
	// },	
	update_ui:function(total_add_to_cart, total_price){
		const str_total_price = "<div>В корзину</div><div> "+total_price+" руб.</div>";
		this.$modifiers_counter.find('span').html(""+total_add_to_cart);
		this.$modifiers_btn_cart.find('span').html(str_total_price);
	},
	// recalc_modifiers:function() {
	// 	let _this = this;		
	// 	let total_modif_price = 0;
	// 	let arr_usr_chosen = [];
	// 		this.MODIFIERS.get().length && this.$MODIFIERS_BTNS && this.$MODIFIERS_BTNS.each(function(index){
	// 		if($(this).hasClass('chosen')){
	// 			const mod = _this.MODIFIERS.get(index);
	// 			const id = mod.modifierId;
	// 			const price = mod.price;
	// 			const name = mod.name
	// 			const modifierGroupId = mod.modifierGroupId;
	// 			const modifierGroupName = mod.modifierGroupName;								
	// 			arr_usr_chosen.push({id,name,price,modifierGroupId,modifierGroupName});
	// 			total_modif_price += parseInt(price,10);
	// 		}
	// 	});
	// 	return [ arr_usr_chosen, parseInt(total_modif_price,10)];
	// },
	hide:function() {
		GLB.UVIEWS.go_back();
	}

};