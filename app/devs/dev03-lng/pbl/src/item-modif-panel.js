import {GLB} from './glb.js';
import $ from 'jquery';

export var ITEM_MODIF_PANEL = {
	init:function($item, opt) {

		this.$item = $item;
		this.CN = "mm2-";
		this._CN = "."+this.CN;
		this.opt = opt;
		this.CLASS_SHOWED_MODIFIERS = 'showed-modifiers';

		// MODIFIERS PANEL (ONLY FOR IIKO_MODE)
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
		this.$modif_btn_cart.on('touchend click',()=>{
			this.close();
			return false;
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
	}
};