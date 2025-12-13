import {GLB} from './glb.js';

export var Bhv = {
	init:function(){		
		this.$body = $('body');
		this.PAGE_WAS_MOVED = false;
		this.behavior();
	},
	behavior:function(){
		
		var _this=this;

		this.$body.on('touchstart',function(ev){
			_this.PAGE_WAS_MOVED = false;
		});
		this.$body.on('touchmove',function(ev){
			_this.PAGE_WAS_MOVED = true;			
		});

		$(window).on('focus',function(){
			GLB.TABINDEX.clear();
			// console.log("window focused");
		});

		$(window).on('blur',function(){
			GLB.TABINDEX.clear();
			// console.log("window blured");
		});

	},
	page_scrolled:function(){
		return this.PAGE_WAS_MOVED;
	}
};