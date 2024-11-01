import {GLB} from './glb.js';
import $ from 'jquery';

export var ITEM_MODIF_PANEL = {
	init:function($item, opt) {

		this.$item = $item;
		this.CN = "mm2-";
		this._CN = "."+this.CN;
		this.opt = opt;
		this.CLASS_SHOWED_MODIFIERS = 'showed-modifiers';		
		this.$modif_panel_space = this.$item.find(this._CN+"item-modifiers-panel__tops-space");
		this.$modif_panel_list_wrapper = this.$item.find(this._CN+"item-modifiers-panel__list-wrapper");
		this.$modif_panel_footer = this.$item.find(this._CN+"item-modifiers-panel__footer");		
		this.$modif_btn_cart = this.$modif_panel_footer.find('.btn-cart');
		this.$modif_btn_plus = this.$modif_panel_footer.find('.btn-plus');
		this.$modif_btn_minus = this.$modif_panel_footer.find('.btn-minus');		
		this.$modif_total_in_cart = this.$modif_panel_footer.find('.total-in-cart');

		this.MODIFIERS_LIST_SHOWING_NOW = false;				


		this.behavior();				
		return this;

	},
	behavior:function(){
		GLB.MOBILE_BUTTONS.bhv([
			this.$modif_btn_cart,
			this.$modif_btn_plus,
			this.$modif_btn_minus,
		]);			
		this.$modif_btn_cart.on('touchend click',(e)=>{
			this.on_pressed_cart && this.on_pressed_cart();
			e.originalEvent.cancelable && e.preventDefault();						
		});
		this.$modif_btn_minus.on('touchend click',(e)=>{			
			this.on_pressed_minus && this.on_pressed_minus();
			e.originalEvent.cancelable && e.preventDefault();						
		});
		this.$modif_btn_plus.on('touchend click',(e)=>{			
			this.on_pressed_plus && this.on_pressed_plus();
			e.originalEvent.cancelable && e.preventDefault();						
		});	
		this.$modif_panel_space.on("touchend click touchmove",(e)=> {
			this.close();
		});	

	},
	close:function(){
		this.$item.removeClass(this.CLASS_SHOWED_MODIFIERS);	
		this.opt && this.opt.on_close && this.opt.on_close();
	},
	open:function(){		
		this.$item.addClass(this.CLASS_SHOWED_MODIFIERS);		
		this.opt && this.opt.on_open && this.opt.on_open();
	},	
	// @param <IIKO_MODIFIERS|CHEFS_MODIFIERS instance> modifiers
	add:function(modifiers){
		this.MODIFIERS_LIST = modifiers;
		this.$modif_panel_list_wrapper.append(this.MODIFIERS_LIST.get_ui());	
	},
	// @param cost:number
	// @param with_options:boolean
	show_price:function(price, count){
		const num_selected = this.MODIFIERS_LIST ? this.MODIFIERS_LIST.get_selected().length : 0 ;
		const with_options = num_selected > 0; 				
		let str = `${price * count} руб.`;		
		str = !with_options? `Без опций<br>${str}`: `В корзину<br>${str}`; 
		this.$modif_btn_cart.html(str);
		this.$modif_total_in_cart.html(count.toString());
	},
	reset:function(){
		this.MODIFIERS_LIST && this.MODIFIERS_LIST.reset();
	},
	on_pressed_cart:function(foo){
		this.on_pressed_cart = foo;
	},
	on_pressed_plus:function(foo){
		this.on_pressed_plus = foo;
	},
	on_pressed_minus:function(foo){
		this.on_pressed_minus = foo;
	}
};