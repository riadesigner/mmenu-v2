import {GLB} from '../glb.js';
import {IIKO_ITEM_SIZER} from './iiko-item-sizer.js';
import {IIKO_ITEM_MODIFIERS} from './iiko-item-modifiers.js';

/**
 * @param item_data: Object
 * 
*/
export const IIKO_ITEM = {
	init:function(item_data, opt) {
		this.item_data = item_data;	
		this.opt = opt;				
		this.on_update_size = this.opt.on_update_size;
		this.on_update_total_in_cart = this.opt.on_update_total_in_cart;
		this.MODIF_PANEL = this.opt.modifiers_panel;
		this.TOTAL_ADD_TO_CART = 1;
		this._prepare();
		return this;
	},
	get:function(){
		return this.item_data;
	},
	// @return boolean
	has_modifiers:function() {		
		return this.item_data.iiko_modifiers!=="";
	},	
	// @return boolean
	has_sizes:function() {		
		return this.item_data.iiko_sizes_parsed && this.item_data.iiko_sizes_parsed.length > 1;
	},
	get_preorder:function(count){
		
		const s = this.IIKO_SIZER.get();
						
		const uniq_name = this.calc_order_uniq_name(`iiko-order-${this.item_data.id}`);
		const chosen_modifiers = this.IIKO_MODIFIERS.get_selected();
		const modifiers_cost = this.IIKO_MODIFIERS.get_cost();
		const item_price = this.get_price();
		const price_with_modifiers = (item_price + modifiers_cost);

	/**
	 * preorderObject = { 
	 *   chosen_modifiers: array; // (IIKO only)
	 *   count: number;
	 *   itemId: string;
	 *   item_data: object;
	 *   originalPrice: number; // (IIKO only) for virtual size (from modifier->sizes[0]->price)
	 *   sizeGroupId: string; // (IIKO only) for virtual size (from modifier->modifierGroupId )
	 *   price: number;
	 *	 sizeCode: string; // (IIKO only)
	 *	 sizeId: string; // (IIKO only)	 
	 *	 sizeName: string; // (IIKO only)	 
	 *   uniq_name: string; // (chefs|iiko)-order-7330-930162801
	 *	 units: string; // г|мл|л|кг
	 *   sizeName:string;
	 *	 volume: number;
	 * }
	*/	
		const pre_order = {
		itemId: this.item_data.id,
			uniq_name: uniq_name,
			price: item_price,
			price_with_modifiers: price_with_modifiers,
			
			count: count,

			volume: s.volume,
			units: s.units,
			item_data: this.item_data,			
			
			sizeName: s.sizeName,
			sizeId: s.sizeId||'',
			sizeCode: s.sizeCode||'',
			originalPrice: s.originalPrice||'',
			sizeGroupId: s.sizeGroupId||'',

			chosen_modifiers:chosen_modifiers
		};			
		
		// console.log('pre_order',pre_order)

		return pre_order;
	},
	
	// @return number
	get_price:function() {
		const s = this.IIKO_SIZER.get();
		return parseInt(s.price,10);
	},
	sizer_get_vars:function(){
		return this.IIKO_SIZER.get();
	},
	get_ui_price_buttons:function(){
		return this.IIKO_SIZER.get_ui();
	},
	// private
	_prepare:function(){

		if(this.item_data.iiko_sizes!==""){
			this.item_data.iiko_sizes_parsed = JSON.parse(this.item_data.iiko_sizes);				
		};				
		if(this.item_data.iiko_modifiers){
			this.item_data.iiko_modifiers_parsed = JSON.parse(this.item_data.iiko_modifiers);				
		}else{
			this.item_data.iiko_modifiers_parsed = [];
		}

		// BUILDING SIZES UI
		this.IIKO_SIZER = $.extend({},IIKO_ITEM_SIZER);			
		this.IIKO_SIZER.init(this.item_data,{onUpdate:this.on_update_size});

		// BUILDING IIKO MODIFIERS UI
		this.IIKO_MODIFIERS = $.extend({},IIKO_ITEM_MODIFIERS);	
		this.IIKO_MODIFIERS.init(this.item_data);
		this.IIKO_MODIFIERS.on_change(()=>{	this.update_modif_panel_ui(); });		

		// SETUP MODIFIERS PANEL
		if(this.has_modifiers() && this.MODIF_PANEL){			
			this.build_modifiers();
			// update behaviors
			this.MODIF_PANEL.on_pressed_cart(()=>{ 				
				const preorder = this.get_preorder(this.TOTAL_ADD_TO_CART);
				// количество товара в корзине
				let total_in_cart = GLB.CART.add_preorder(preorder);				
				this.on_update_total_in_cart && this.on_update_total_in_cart(total_in_cart);									
				this.MODIF_PANEL.close();
			});
			this.MODIF_PANEL.on_pressed_plus(()=>{ 				
				this.TOTAL_ADD_TO_CART++;
				this.update_modif_panel_ui();;
			});
			this.MODIF_PANEL.on_pressed_minus(()=>{
				if(this.TOTAL_ADD_TO_CART-1 > 0){
					this.TOTAL_ADD_TO_CART--;
					this.update_modif_panel_ui();;									
				}else{
					this.MODIF_PANEL.close();					
				}
			});
			this.update_modif_panel_ui();;
		}
	},
	build_modifiers:function(){
		this.MODIF_PANEL.add(this.IIKO_MODIFIERS);
	},
	calc_order_uniq_name:function(prefix){		
		// if item has sizes more than one
		// or user selected at least one modifier
		// make a uniq ID for order 
		// TODO:
		// make uniq hash for each order 
		// based on the options selected by the user		
		// and then compare the hashes
		if(this.has_sizes() || this.IIKO_MODIFIERS.get_selected().length > 0){			
			let sufix = Math.floor((Date.now() * Math.random()) / 1000).toString();			
			return `${prefix}-${sufix}`;
		}else{			
			return prefix;
		}
	},
	update_modif_panel_ui(){				
		let price = this.get_price();
		const {arr_usr_chosen, total_modif_price} = this.IIKO_MODIFIERS.recalc();
		const with_options = arr_usr_chosen.length > 0;		
		if(with_options){ price += total_modif_price;}				
		this.MODIF_PANEL.show_price(price, this.TOTAL_ADD_TO_CART);		
	}
};