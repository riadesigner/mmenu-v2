import {GLB} from './glb.js';
import $ from 'jquery';

export var VIEW_ORDER_CANCEL = {
	init:function(options){
		
		this._init(options);

		this.$btnBasket= this.$view.find(this._CN+"btn-basket");
		this.$btnClose = this.$view.find(this._CN+"btn-close-menu");
		this.$btnBack = this.$view.find(this._CN+"btn-back, "+this._CN+"std-header-btn-back, "+this._CN+"btn-close-items");
		
		this.$msgCancelReport = this.$view.find(this._CN+"order-cancel-message");
		
		this.behavior();

		return this;
	},
	behavior:function(){
		var _this =this;

		var arrMobileButtons = [
			this.$btnBasket,
			this.$btnBack,
			this.$btnClose
		];

		this._behavior(arrMobileButtons);


		this.$btnBack.on("touchend click",function() {
			GLB.UVIEWS.set_current("the-ordering");
			return false;
		});

	},	
	update:function(msg){
		this.$msgCancelReport.html(msg);
		this.chefsmenu.end_loading();
	}
};