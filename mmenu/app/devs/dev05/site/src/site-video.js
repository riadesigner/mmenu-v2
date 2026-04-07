import {GLB} from './glb.js';


var SVid = {
	init:function(opt){		

		this.mode = opt.mode;		
		this.$player = opt.$player;		
		this.PLAYER_ID = this.$player.attr("id") || "Unknown ID";
		if(!this.$player.size()) {
			// console.log('can`t find video player')
			return;
		};		
		this.video = this.$player.find('video')[0];
		if(this.mode!=='720p' && this.mode!=='480p') {
			return this;
		};

		// console.log("this.mode=",this.mode)

		this.VIDEO_OK = false;
		this.PLAYER_HEIGHT = 0;
		this.PLAYER_TOP = 0;
		this.update_size_param();
		this.update_sourse();
		this.behavior();

		return this;
	},
	behavior:function(){		
		var _this=this;
		this.$player.on("touchend click",function(ev){
			!GLB.Bhv.page_scrolled() && _this.VIDEO_OK && _this.toggle();
			ev.cancelable && ev.preventDefault();
			ev.stopPropagation();
		});
		$(window).on("resize",function(){
			_this.TMR_RESIZE&& clearTimeout(_this.TMR_RESIZE);
			_this.TMR_RESIZE = setTimeout(function(){
				_this.update_size_param();
			},100);
		});
		$(window).on("scroll",function(){
			_this.TOP_SCROLL = $(window).scrollTop();
			_this.pause_video_while_off_screen();
		});		

		
	},
	pause_video_while_off_screen:function(){	
		var _this=this;
		if(this.TOP_SCROLL > this.PLAYER_TOP+this.PLAYER_HEIGHT/6*5){
			this.$player.addClass('off-screen')	
			this.pause();
		}else if((this.TOP_SCROLL + this.WIN_HEIGHT/6*5) < this.PLAYER_TOP){
			this.$player.addClass('off-screen')	
			this.pause();
		}else{
			this.$player.removeClass('off-screen')	
		}
	},
	update_size_param:function(){
		// console.log("hi!");
		this.PLAYER_HEIGHT = this.$player.height(); 
		this.PLAYER_TOP = this.$player.offset().top;
		this.WIN_HEIGHT = $(window).height();
	},
	toggle:function(){
		if(!this.video.paused){			
			this.pause();
		}else{
			this.play();
		}
	},	
	pause:function(){		
		if(this.VIDEO_OK){
			!this.video.paused&&this.video.pause();
			this.$player.removeClass('playing-now');
		}
	},
	play:function(){			
		if(this.VIDEO_OK){			
			this.video.paused&&this.video.play();		
			this.$player.addClass('playing-now');
		}
	},
	update_sourse:function(){
		var _this=this;
		var $source = this.$player.find('.'+this.mode);
		$source.siblings().remove();
		// console.log('$source.length',$source.length)
		var src = $source.attr('data-src');
		$source.attr({src:src});
		this.load(src);
	},
	load:function(src){
		var _this=this;				
		this.video.load();		
		
		this.video.addEventListener('canplay', function(event) { 
			_this.VIDEO_OK = true;
				// console.log('canplay=',event);				
		 }, true);		

		this.video.addEventListener('error', function(event) { 
			// _this.VIDEO_OK = false;
				// console.log('ERR: can`t load, src=',src,event);				
		 }, true);
	}
};


export var SiteVideo = {
	init:function(mode){				
		var _this=this;
		this.$allVid = $('.site-video');
		this.$allVid.each(function() {
			var sv = $.extend({},SVid);
			sv.init({$player:$(this),mode:mode});
		});
		return this;
	}
};
