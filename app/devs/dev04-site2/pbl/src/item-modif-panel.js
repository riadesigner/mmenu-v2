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
		this.$modif_panel_list = this.$item.find(this._CN+"item-modifiers-panel__list");
		this.$modif_panel_footer = this.$item.find(this._CN+"item-modifiers-panel__footer");		
		this.$modif_btn_cart = this.$modif_panel_footer.find('.btn-cart');
		this.$modif_btn_plus = this.$modif_panel_footer.find('.btn-plus');
		this.$modif_btn_minus = this.$modif_panel_footer.find('.btn-minus');		
		this.$modif_total_in_cart = this.$modif_panel_footer.find('.total-in-cart');

		this.MODIF_STARTSCROLL=0;
		this.MODIF_SCROLLED = false;
		this.MODIFIERS_SHOWING_NOW = false;				

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
		this.$modif_panel_list.on("touchstart",(e)=>{
			this.MODIF_SCROLLED = false;
		});
		this.$modif_panel_list.on("touchmove",(e)=> {
			this.MODIF_SCROLLED = true;
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
	insert:function($modifiers_list){		
		this.$modif_panel_list.append($modifiers_list);		
		this.$modif_rows = $modifiers_list.find('.btn-modifier');
		this.$modif_rows.on('touchend click',(e)=>{
			if(!this.MODIF_SCROLLED){				
				$(e.currentTarget).toggleClass('chosen');
				this.on_press_modif && this.on_press_modif();
			};				
			e.originalEvent.cancelable && e.preventDefault();
		});		
		GLB.MOBILE_BUTTONS.bhv([this.$modif_rows]);
	},
	// @param cost:number
	// @param with_options:boolean
	show_price:function(price, count){
		const with_options = this.get_selected_rows().length > 0; 
		let str = `${price * count} руб.`;		
		str = !with_options? `Без опций<br>${str}`: `В корзину<br>${str}`; 
		this.$modif_btn_cart.html(str);
		this.$modif_total_in_cart.html(count.toString());
	},
	get_selected_rows:function(){
		let arr = [];
		this.$modif_rows.each((i, row)=>{			
			$(row).hasClass('chosen') && arr.push(row);
		});
		return arr;
	},
	reset:function(){
		this.$modif_rows && this.$modif_rows.removeClass('chosen');
	},
	on_press_modif:function(foo){
		this.on_press_modif = foo;
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