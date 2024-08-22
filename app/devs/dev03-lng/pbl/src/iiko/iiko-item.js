// import {GLB} from '../glb.js';
// import $ from 'jquery';

/**
 * @param item_data: Object
 * 
*/
export const IIKO_ITEM = {
	init:function(item_data) {
		this.item_data = item_data;	
		this.SIZER = null;	
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
		this.item_data.iiko_sizes_parsed && this.item_data.iiko_sizes_parsed.length;
		// return this.item_data.iiko_sizes!=="";
	},
	// @param IIKO_SIZER STATIC CLASS
	add_sizer:function(sizer){
		this.SIZER = sizer;
	},
	// @param IIKO_MODIFIERS STATIC CLASS
	add_modifiers:function(modifiers){
		this.MODIFIERS = modifiers;
	},	
	get_preorder:function(count){
		
		const price = this.get_price();
		const uniq_name = this.calc_order_uniq_name(`iiko-order-${this.item_data.id}`);
		const volume = '';
		const sizeName = '';
		const sizeId = '';
		const sizeCode = '';
		const chosen_modifiers = '';
		const preorderObject = {
		itemId: this.item_data.id,
			uniq_name: uniq_name,
			price: price,
			count: count,
			volume: volume,
			item_data: this.item_data,			
			sizeName:sizeName,
			sizeId:sizeId,
			sizeCode:sizeCode,			
			chosen_modifiers:chosen_modifiers
		};			
		return preorderObject;
	},
	
	// @return number
	get_price:function() {		
				
		const s = this.SIZER.get();
		
		// let [chosen_modifiers, price_modifiers] = this.recalc_modifiers();
		// let result_price = parseInt(s.price,10) + parseInt(price_modifiers,10);
		const modif_price = 0;
		const result_price = parseInt(s.price,10) + modif_price;
		return result_price; 

	},	

	get_modifiers:function(){

	},
	get_sizes:function(){

	},	
	// private
	_prepare:function(){
		if(this.item_data.iiko_sizes!==""){
			this.item_data.iiko_sizes_parsed = JSON.parse(this.item_data.iiko_sizes);				
		};				
		if(this.item_data.iiko_modifiers){
			this.item_data.iiko_modifiers_parsed = JSON.parse(this.item_data.iiko_modifiers);				
		};		
	},
	calc_order_uniq_name:function(prefix){
		if(this.has_sizes() || this.has_modifiers()){
			let uniq_by_time = Math.floor((Date.now() * Math.random()) / 1000).toString();			
			return `${prefix}-${uniq_by_time}`;
		}else{			
			return prefix;
		}
	}
};