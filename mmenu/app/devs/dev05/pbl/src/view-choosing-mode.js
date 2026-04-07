import {GLB} from './glb.js';
import $ from 'jquery';

export var VIEW_CHOOSING_MODE = {
	init:function(options){
		this._init(options);
		this.$btnBasket= this.$view.find(this._CN+"btn-basket");
		this.$btnClose = this.$view.find(this._CN+"btn-close-menu");
		this.$btnBack = this.$view.find(this._CN+"btn-back, "+this._CN+"std-header-btn-back, "+this._CN+"btn-close-items");				
		this.$btnInPickUp = this.$view.find(this._CN+'btn-choosing-pick-up');
		this.$btnDelivering = this.$view.find(this._CN+'btn-choosing-delivering');
		this.behavior();		
		return this;
	},

	update:function(){
		var _this=this;
		this._update_tabindex();
	},

	behavior:function(){
		var _this=this;

		var arrMobileButtons = [
			this.$btnInPickUp,
			this.$btnBack,
			this.$btnDelivering
		];

		this._behavior(arrMobileButtons);

		this.$btnBack.on("touchend click",(e)=> {			
			GLB.UVIEWS.go_back();
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$btnInPickUp.on("touchend click",(e)=> {
			GLB.VIEW_ORDERING.update({pickupMode:true});
			GLB.UVIEWS.set_current("the-ordering");			
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$btnDelivering.on("touchend click",(e)=> {
			if(!this.chefsmenu.is_loading_now()){
				this.chefsmenu.now_loading();
				GLB.VIEW_ORDERING.update();
				GLB.UVIEWS.set_current("the-ordering");
			};
			e.originalEvent.cancelable && e.preventDefault();
		});		

	}
};