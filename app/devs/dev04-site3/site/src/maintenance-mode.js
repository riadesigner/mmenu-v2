import {GLB} from './glb.js';

export var MaintenanceMode = {
	init:function() {		
		this.$body = $('body');
		this.$protect_layer = $(".maintenance-layer");		
		this.touch_counter = 4;
		this.behavior();
	},
	behavior:function() {
		var _this=this;
		this.$protect_layer.on("touchend click",function() {
			// console.log(_this.touch_counter);
			_this.touch_counter--;
			if(_this.touch_counter<0){
				_this.hide();
			}
			return false;
		});
	},
	show:function() {
		// usually this class set through the config file on server; 
		this.$body.addClass("maintenance-mode");
	},
	hide:function() {
		this.$body.removeClass("maintenance-mode");
	}
};