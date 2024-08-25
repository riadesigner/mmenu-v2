import {GLB} from './glb.js';
import $ from 'jquery';

export var ITEM_MODIF_PANEL = {
	init:function($item, opt) {

		this.$item = $item;
		this.CN = "mm2-";
		this._CN = "."+this.CN;
		this.opt = opt;
		this.CLASS_SHOWED_MODIFIERS = 'showed-modifiers';

		this.$modif_panel_list = this.$item.find(this._CN+"item-modifiers-panel__list");
		this.$modif_panel_footer = this.$item.find(this._CN+"item-modifiers-panel__footer");		
		this.$modif_btn_cart = this.$modif_panel_footer.find('.btn-cart');
		this.$modif_btn_plus = this.$modif_panel_footer.find('.btn-plus');
		this.$modif_btn_minus = this.$modif_panel_footer.find('.btn-minus');		

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