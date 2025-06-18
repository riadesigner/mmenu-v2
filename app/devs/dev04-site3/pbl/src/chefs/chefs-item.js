import {GLB} from '../glb.js';
import {CHEFS_ITEM_SIZER} from './chefs-item-sizer.js';


/**
 * @param item_data: Object
 * 
*/
export const CHEFS_ITEM = {
	init:function(item_data, opt) {
		this.item_data = item_data;	
		this.opt = opt;				
		this.on_update_size = this.opt.on_update_size;
		this.on_update_total_in_cart = this.opt.on_update_total_in_cart;
		// this.MODIF_PANEL = this.opt.modifiers_panel;
		this.TOTAL_ADD_TO_CART = 1;
		this._prepare();
		return this;
	},
	get:function(){
		return this.item_data;
	},
	// @return boolean
	has_modifiers:function() {		
		// TODO modifiers fo chefsmenu mode
		// return this.item_data.modifiers!=="";
		return false;		
	},	
	// @return boolean
	has_sizes:function() {		
		return this.item_data.sizes_parsed && this.item_data.sizes_parsed.length > 1;
	},
	get_preorder:function(count){
		
		const s = this.CHEFS_SIZER.get();		
						
		const uniq_name = this.calc_order_uniq_name(`chefs-order-${this.item_data.id}`);
		// const chosen_modifiers = this.IIKO_MODIFIERS.get_selected();
		// const modifiers_cost = this.IIKO_MODIFIERS.get_cost();
		// const total_price = (this.get_price() + modifiers_cost);
		const total_price = this.get_price();

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
	 *   uniq_name: string; // chefs-order-7330-930162801
	 *	 units: string; // г|мл|л|кг
	 *   sizeName:string;
	 *	 volume: number;
	 * }
	*/

		const pre_order = {
		itemId: this.item_data.id,
			uniq_name: uniq_name,
			price: total_price,
			count: count,			
			
			volume: s.volume,
			units: s.units,
			item_data: this.item_data,	

			sizeName: s.sizeName,
			chosen_modifiers:""
		};			
		return pre_order;
	},
	
	// @return number
	get_price:function() {
		const s = this.CHEFS_SIZER.get();
		return parseInt(s.price,10);
	},
	sizer_get_vars:function(){
		return this.CHEFS_SIZER.get();
	},
	get_ui_price_buttons:function(){
		return this.CHEFS_SIZER.get_ui();
	},
	// private
	_prepare:function(){

		if(this.item_data.sizes!==""){
			this.item_data.sizes_parsed = JSON.parse(this.item_data.sizes);				
		};	

		// BUILDING SIZES UI
		this.CHEFS_SIZER = $.extend({},CHEFS_ITEM_SIZER);			
		this.CHEFS_SIZER.init(this.item_data,{onUpdate:this.on_update_size});

	},
	// update_modifiers_ui:function(){
	// 	const {arr_usr_chosen, total_modif_price} = this.IIKO_MODIFIERS.recalc();
	// 	const with_options = arr_usr_chosen.length > 0;
	// 	let price = this.get_price(); 
	// 	if(with_options){ price += total_modif_price;}				
	// 	this.MODIF_PANEL.show_price(price, this.TOTAL_ADD_TO_CART);
	// },
	calc_order_uniq_name:function(prefix){		
		// if item has sizes more than one
		// or user selected at least one modifier
		// make a uniq ID for order 
		// TODO:
		// make uniq hash for each order 
		// based on the options selected by the user		
		// and then compare the hashes
		// TODO if(this.has_sizes() || this.CHEFS_MODIFIERS.get_selected().length > 0){			
		if(this.has_sizes()){			
			let sufix = Math.floor((Date.now() * Math.random()) / 1000).toString();			
			return `${prefix}-${sufix}`;
		}else{			
			return prefix;
		}
	}
};