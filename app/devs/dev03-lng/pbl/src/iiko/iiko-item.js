import {GLB} from '../glb.js';
import $ from 'jquery';
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
		return this.item_data.iiko_sizes_parsed && this.item_data.iiko_sizes_parsed.length;
	},
	get_preorder:function(count){
		
		const s = this.IIKO_SIZER.get();
		const price = this.get_price();
		const uniq_name = this.calc_order_uniq_name(`iiko-order-${this.item_data.id}`);
		const chosen_modifiers = '';

		const preorderObject = {
		itemId: this.item_data.id,
			uniq_name: uniq_name,
			price: price,
			count: count,
			volume: s.volume,
			item_data: this.item_data,			
			sizeName: s.sizeName,
			sizeId: s.sizeId,
			sizeCode: s.sizeCode,			
			chosen_modifiers:chosen_modifiers
		};			
		return preorderObject;
	},
	
	// @return number
	get_price:function() {		
				
		const s = this.IIKO_SIZER.get();
		
		// let [chosen_modifiers, price_modifiers] = this.recalc_modifiers();
		// let result_price = parseInt(s.price,10) + parseInt(price_modifiers,10);
		const modif_price = 0;
		const result_price = parseInt(s.price,10) + modif_price;
		return result_price; 

	},	
	sizer_get_vars:function(){
		return this.IIKO_SIZER.get();
	},
	get_ui_price_buttons:function(){
		return this.IIKO_SIZER.get_ui();
	},
	get_ui_modifiers:function(){
		return this.IIKO_MODIFIERS.get_ui();
	},
	// private
	_prepare:function(){

		if(this.item_data.iiko_sizes!==""){
			this.item_data.iiko_sizes_parsed = JSON.parse(this.item_data.iiko_sizes);				
		};				
		if(this.item_data.iiko_modifiers){
			this.item_data.iiko_modifiers_parsed = JSON.parse(this.item_data.iiko_modifiers);				
		};		

		// BUILDING SIZES UI
		this.IIKO_SIZER = $.extend({},IIKO_ITEM_SIZER);			
		this.IIKO_SIZER.init(this.item_data,{onUpdate:this.opt.on_update_size});

		// BUILDING IIKO MODIFIERS UI
		this.IIKO_MODIFIERS = $.extend({},IIKO_ITEM_MODIFIERS);	
		this.IIKO_MODIFIERS.init(this.item_data);

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