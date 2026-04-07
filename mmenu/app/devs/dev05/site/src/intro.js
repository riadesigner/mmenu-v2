import {GLB} from './glb.js';

export var Intro = {
	init:function(mode){		
		this.mode = mode;
		this.$body = $('body');
		this.$player = $('#video-intro');
		if(!this.$player.size()) {
			// console.log('can`t find video player')
			return;
		};		
		this.video = this.$player.find('video')[0];
		if(this.mode!=='720p' && this.mode!=='480p') {return};
		this.VIDEO_OK = true;

		this.update_size_param();
		this.update_sourse();		
		this.behavior();
		return this;
	},
	behavior:function(){		
		var _this=this;
		this.$player.on("touchend click",function(ev){
			_this.VIDEO_OK && _this.toggle();			
			ev.cancelable && ev.preventDefault();			
			ev.stopPropagation();			
		});
		$(window).on("resize",function(){
			_this.TMR_RESIZE && clearTimeout(_this.TMR_RESIZE);
			_this.TMR_RESIZE = setTimeout(function(){
				_this.update_size_param();
			},100);
		});
		$(window).on("scroll",function(){
			_this.TOP_SCROLL = $(window).scrollTop();
			_this.TMR_BODY_SCROLLED && clearTimeout(_this.TMR_BODY_SCROLLED);
			_this.TMR_BODY_SCROLLED = setTimeout(function(){
				_this.pause_video_off_screen();
			},100);
		});
		$(GLB.Mobilemenu).on("open",this.pause.bind(this));
		$(GLB.Mobilemenu).on("close",this.play.bind(this));
		$(GLB.CreateMenu).on("open",this.pause.bind(this));
		$(GLB.CreateMenu).on("close",this.play.bind(this));
		
	},
	pause_video_off_screen:function(){			
		if(this.TOP_SCROLL > Math.floor(this.PLAYER_TOP+this.PLAYER_HEIGHT/2)){			
			this.pause("because its off screen");
		}else{
			if(!GLB.CreateMenu.is_open() && !this.is_chefsmenu_showed()){			
				this.play();				
			};
		}
	},
	is_chefsmenu_showed:function() {
		return this.$body.hasClass('chefsmenu-is-showed');		
	},	
	pause_if_chefsmenu_shown:function(){		
		var _this = this;
		// console.log('video intro, check if menu show')
		this.TMR_CHEFSMENU_VERIFY && clearTimeout(this.TMR_CHEFSMENU_VERIFY);	
		this.TMR_CHEFSMENU_VERIFY = setTimeout(function() {
			if(_this.is_chefsmenu_showed()){				
				_this.pause('by chefsmenu');	
			}else{
				_this.pause_if_chefsmenu_shown();
			}
		},1000);
	},

	update_size_param:function(){
		// console.log("hi!");
		this.PLAYER_HEIGHT = this.$player.height(); 
		this.PLAYER_TOP = this.$player.offset().top;
		// console.log('this.PLAYER_HEIGHT,this.PLAYER_TOP',this.PLAYER_HEIGHT,this.PLAYER_TOP)
	},
	toggle:function(){		
		if(!this.video.paused){
			this.pause('by toggle');
		}else{
			this.play();
		}
	},	
	pause:function(str_reason){		
		if(this.VIDEO_OK){
			if(!this.video.paused){
				// console.log('video paused:',str_reason);
				this.video.pause();
				this.$player.addClass('paused');
				this.TMR_CHEFSMENU_VERIFY && clearTimeout(this.TMR_CHEFSMENU_VERIFY);	
			}
		}
	},
	play:function(){
		if(this.VIDEO_OK){
			if(this.video.paused){
				// console.log('intro is plaing now');
				this.video.play();		
				this.$player.removeClass('paused');				
				this.pause_if_chefsmenu_shown();
			}
		}
	},
	update_sourse:function(){
		var _this=this;
		var $source = this.$player.find('.'+this.mode);
		var src = $source.data('src');
		$source.attr({src:src});		
		this.load(src);
		setTimeout(function(){
			GLB.CreateMenu && GLB.CreateMenu.is_open() && _this.pause();
		},1000);
	},
	load:function(src){
		var _this=this;
		_this.$player.addClass('loading-now');
		this.video.load();
		this.video.addEventListener('canplay',function() {
			_this.play();
			_this.$player.removeClass('loading-now');
		});
		
	}
};
