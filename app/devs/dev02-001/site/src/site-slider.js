import {GLB} from './glb.js';

export var SiteSlider = {
	init:function(path2images) {
		this.$body = $('body');
		this.$sl = $('.site-slider');
		if(!this.$sl.length) { return false; }

		this.$slSmall = this.$sl.find('.site-slider__small');
		this.$slLarge = this.$sl.find('.site-slider__large');
		this.path2images = path2images;				

		this.ARR_LARGE_SRC = ['slider-large-01.png','slider-large-02.png','slider-large-03.png','slider-large-04.png'];
		this.ARR_SMALL_SRC = ['slider-small-01.png','slider-small-02.png','slider-small-03.png','slider-small-04.png'];

		this.AUTO_PLAY_PAUSE = 6000; 
		this.ANIM_SPEED = 800;
		this.SLIDE_TRANSITION = this.ANIM_SPEED/1000+'s cubic-bezier(0.22, 1, 0.36, 1)';

		this.IMAGES_LOADED = {large:[],small:[]};
		this.CONTAINERS = {};
		this.READY_TO_USE = false;
		this.NOW_MOVING = false;
		this.CURRENT_SLIDE = 0;		
		this.IS_ON_SCREEN = true;
		this.PAUSED = false;

		// total slider = total + 1,
		// because last slide is copy of first slide for loop plaing		
		this.TOTAL_SLIDES = this.ARR_LARGE_SRC.length+1;		
		//
		this.pre_build();
		this.show_preview();
		this.update_size_param();
		this.behavior();
	},
	play:function() {
		this.PAUSED = false;
	},
	pause:function() {
		this.PAUSED = true;
	},	
	// private
	auto_play:function() {
		var p = this.AUTO_PLAY_PAUSE;
		this.TMR_AUTOPLAY && clearTimeout(this.TMR_AUTOPLAY);
		this.TMR_AUTOPLAY = setTimeout(this.go_next.bind(this),p);		
	},
	is_chefsmenu_showed:function() {
		return this.$body.hasClass('chefsmenu-is-showed');		
	},		
	pre_build:function() {
		var _this=this;
		//duplicate first image in array for a loop plaing
		this.ARR_LARGE_SRC.push(this.ARR_LARGE_SRC[0]);
		this.ARR_SMALL_SRC.push(this.ARR_SMALL_SRC[0]);
		
		var fn = {
			add_dots:function() {
				var $all_dots = $('<div>',{class:'site-slider__dots'});		
				var total = _this.TOTAL_SLIDES-1;
				var space = 4;
				var dotWidth = (100-(total-1)*space)/total;
				for (var i=0;i<total;i++){
					var marginLeft = !i?'0':space;
					var $dot = $('<div>',{
						style:[
							'float:left;height:100%;',
							'margin-left:'+marginLeft+'%;',
							'width:'+ dotWidth +'%;'							
						].join('')
					});
					!i && $dot.addClass('current');
					$all_dots.append($dot);
				};				
				return $all_dots;
			}
		};

		this.$slider_dots = fn.add_dots();
		this.$sl.append(this.$slider_dots);

	},
	build_all:function() {		
		this.$sl.addClass('ready-to-use');
		this.READY_TO_USE = true;		
		this.build_slider('large');		
		this.build_slider('small');
		this.hide_preview();
		this.auto_play();
	},	
	show_preview:function() {
		var src_small = this.path2images+this.ARR_SMALL_SRC[0];
		var src_large = this.path2images+this.ARR_LARGE_SRC[0];
		this.$slSmall.css({'background':'url('+src_small+') no-repeat center/cover'});	
		this.$slLarge.css({'background':'url('+src_large+') no-repeat center/cover'});
	},
	hide_preview:function() {
		this.$slSmall.css({'background-image':'none'});
		this.$slLarge.css({'background-image':'none'});
	},
	restart:function() {
		var _this=this;

		if(!this.READY_TO_USE){
			// start all images reload
			setTimeout(function() {				
				_this.now_loading();
				_this.load_large_images({onReady:function() {
					_this.load_small_images({onReady:function() {						
						// _this.stop_loading();
						_this.build_all();
					}});
				}});
			},500);
		}else{
			this.build_all();
		}

	},
	build_slider:function(sizeMode) {

		var arr = this.IMAGES_LOADED[sizeMode];

		var $container = $('<div>',{
			class:'site-slider-'+sizeMode+'__container',
			style:[
				'will-change: transform;',
				'width:100%;height:100%;',				
				'position:absolute;'
			].join('')
		});
		var counter = 0;
		for(var i in arr){			
			var $el = $('<div>',{
				class:'site-slider-'+sizeMode+'__slide',
				style:[					
					'background: url(' +arr[i].src+ ') no-repeat center/cover;',
					'width:100%;height:100%;',
					'position:absolute;top:0;left:'+(counter*100)+'%;'
				].join('')
			});
			counter++;			
			$container.append($el);
			this.CONTAINERS[sizeMode] = $container;
		}
		sizeMode=='large'? this.$slLarge.html($container) : this.$slSmall.html($container);
	},
	build_small_slider:function() {
		var arr = this.IMAGES_LOADED['small'];

	},	
	loop_image_loading:function(arr,size,opt){
		var _this=this;
		if(arr.length){
			var src  = arr.shift();
			var im = new Image();
			im.onload = function() {				
				_this.IMAGES_LOADED[size].push(this);
				_this.loop_image_loading(arr,size,opt);
			}
			im.src = _this.path2images + src;
		}else{
			opt&&opt.onReady&&opt.onReady();
		}
	},
	load_large_images:function(opt) {
		var arr_src = this.ARR_LARGE_SRC.slice(0,this.ARR_LARGE_SRC.length);
		this.loop_image_loading(arr_src,'large',{onReady:function() {
			// console.log("loaded large");
			opt&&opt.onReady&&opt.onReady();
		}});
	},
	load_small_images:function(opt) {
		var arr_src = this.ARR_SMALL_SRC.slice(0,this.ARR_SMALL_SRC.length);
		this.loop_image_loading(arr_src,'small',{onReady:function() {
			// console.log("loaded small");
			opt&&opt.onReady&&opt.onReady();
		}});
	},
	now_loading:function() {
		this.$sl.addClass('now-loading');
	},
	stop_loading:function() {
		this.$sl.removeClass('now-loading');
	},
	update_size_param:function(){	
		this.SIZE = {
			height:this.$sl.height(),
			top:this.$sl.offset().top
		}		
	},
	behavior:function() {
		var _this=this;
		// start load slider 
		// when page is loaded
		$(function() {
			_this.restart();
		});

		$(window).on('blur',function() {_this.pause(); });
		$(window).on('focus',function() {_this.play(); });

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
				_this.pause_if_off_screen();
			},100);
		});

		this.$sl.on('touchend click',function(e) {
			_this.go_next();
			e.originalEvent.cancelable && e.preventDefault();
		})
	},

	pause_if_off_screen:function(){			
		if(this.TOP_SCROLL > Math.floor(this.SIZE.top+this.SIZE.height/2)){			
			this.IS_ON_SCREEN = false; // stop
		}else{
			this.IS_ON_SCREEN = true; // play
		}
	},

	go_next:function() {
		if(this.READY_TO_USE){

			if( !this.NOW_MOVING
				&& this.IS_ON_SCREEN
				&& !this.PAUSED
				&& !GLB.CreateMenu.is_open() 
				&& !GLB.Mobilemenu.is_open()
				&& !this.is_chefsmenu_showed() ){

				this.CURRENT_SLIDE++;
				if(this.CURRENT_SLIDE > this.TOTAL_SLIDES-1){	this.CURRENT_SLIDE = 0;	}
				this._move_slide(this.CURRENT_SLIDE,'small');
				this._move_slide(this.CURRENT_SLIDE,'large');
				this._update_dots(this.CURRENT_SLIDE);

				// console.log('slider go next');

			}else{				
				// console.log("try -go next- later")
			}

			this.auto_play();			
		}		
	},
	is_last_slide:function() {
		return this.CURRENT_SLIDE==this.TOTAL_SLIDES-1;
	},
	_update_dots:function(index) {
		if(this.$slider_dots.length){
			index = this.is_last_slide()?0:index;
			this.$slider_dots.find('div').removeClass('current')
			.eq(index).addClass('current');
		}
	},
	_move_slide:function(index,sizeMode){
		var _this=this;
		this.NOW_MOVING = true;
		
		var $ctn = this.CONTAINERS[sizeMode];		

		var fn = {
			move:function(i,speed) {
				$ctn.css({
					transform:'translateX(-'+(i*100)+'%)',
					transition : speed
				});
			}
		};

		fn.move(index, _this.SLIDE_TRANSITION );
		
		var is_last = this.is_last_slide();
		
		is_last && setTimeout(function() {
				fn.move(0,'0s');
				_this.CURRENT_SLIDE = 0;
			},this.ANIM_SPEED+100);

		setTimeout(function() {
			_this.NOW_MOVING = false;
		},this.ANIM_SPEED+200);
	}
};	 