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
		this.IIKO_SIZER.init(this.item_data,{onUpdate:this.on_update_size});

		// BUILDING IIKO MODIFIERS UI
		this.IIKO_MODIFIERS = $.extend({},IIKO_ITEM_MODIFIERS);	
		this.IIKO_MODIFIERS.init(this.item_data);

		// SETUP MODIFIERS PANEL
		if(this.has_modifiers() && this.MODIF_PANEL){
			// build ui list of midifiers
			const $modifiers_list = this.IIKO_MODIFIERS.get_ui();
			this.MODIF_PANEL.insert($modifiers_list);
			this.MODIF_PANEL.on_press_modif(()=>{
				this.update_modifiers_ui();
			});
			// update behaviors
			this.MODIF_PANEL.on_pressed_cart(()=>{ 				
				const preorder = this.get_preorder(1);
				let total_in_cart = GLB.CART.add_preorder(preorder);		
				this.on_update_total_in_cart && this.on_update_total_in_cart(total_in_cart);									
				this.MODIF_PANEL.close();
			});
			this.MODIF_PANEL.on_pressed_plus(()=>{ 				
				this.TOTAL_ADD_TO_CART++;
				this.update_modifiers_ui();
			});
			this.MODIF_PANEL.on_pressed_minus(()=>{
				if(this.TOTAL_ADD_TO_CART-1 > 0){
					this.TOTAL_ADD_TO_CART--;
					this.update_modifiers_ui();									
				}else{
					this.MODIF_PANEL.close();					
				}
			});
			this.update_modifiers_ui();
		}
	},
	update_modifiers_ui:function(){
		const {arr_usr_chosen, total_modif_price} = this.IIKO_MODIFIERS.recalc();
		const with_options = arr_usr_chosen.length > 0;
		let total_price = this.get_price(); 
		if(with_options){ total_price += total_modif_price;}				
		total_price*=this.TOTAL_ADD_TO_CART;
		this.MODIF_PANEL.update_ui(total_price, this.TOTAL_ADD_TO_CART, with_options);
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