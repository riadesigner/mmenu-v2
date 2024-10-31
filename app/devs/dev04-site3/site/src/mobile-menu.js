import {GLB} from './glb.js';

export var Mobilemenu = {
	init:function() {	
		this.$body = $("body");
		this.$allsite = $(".all-site").css({transition:'.3s'});
		this.$mobileMenu = $(".mobile-menu").css({transition:'.3s'});
		this.$btnMenu = $(".mobile-btn-burger");		
		this.behavior();
	},
	is_open:function(){
		return this.$body.hasClass("mobile-menu-opened");
	},
	toggle:function(){		
		!this.is_open()?this.open():this.close();
	},
	close:function(){
		if(this.is_open()){
			$(this).triggerHandler("close");
			this.$body.toggleClass("mobile-menu-opened");
			this.$allsite.css({transform:"translateY(0)",transition:0});
		}
	},
	open:function(){
		if(!this.is_open()){
			$(this).triggerHandler("open");
			var height = this.$mobileMenu.height();
			this.$body.toggleClass("mobile-menu-opened");
			this.$allsite.css({transform:"translateY("+height+"px)"});
		}
	},
	behavior:function(){

		var _this=this;
		$(window).on("resize",function(){
			if(_this.TMR){clearTimeout(_this.TMR);}
			_this.TMR = setTimeout(function(){
				_this.close();
			},100);
		});

		$(window).on("scroll",function(){
			_this.TMR_SCROLL && clearTimeout( _this.TMR_SCROLL );
			_this.TMR_SCROLL = setTimeout( _this.close.bind(_this), 100);
		});

		this.$btnMenu.on("touchend click",function(ev){
			ev.originalEvent.cancelable && ev.preventDefault();
			ev.stopPropagation();
			if(!GLB.Bhv.page_scrolled()){
				_this.toggle();	
			}
		});
		

	}
};