import {GLB} from './glb.js';
import $ from 'jquery';

export var META_VIEWPORT = {
	init:function(){
		this.$body = $('body');
		this.MOBILE_STRING = 'width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0';
		this.isAndroid = /(android)/i.test(navigator.userAgent);
		this.is_iOS = /iPad|iPhone|iPod/i.test(navigator.userAgent);
		this.initialHeight = window.outerHeight;
		this.metaViewport = document.querySelector('meta[name=viewport]');				
		this.behaviors();
	},
	behaviors:function(){
		var _this=this;

		// support mobile android keyboard
		$(window).on('resize',function(){
			if(_this.isAndroid){
				_this.TMR_RESIZE && clearTimeout(_this.TMR_RESIZE);
				_this.TMR_RESIZE = setTimeout(function(){
					_this.update_meta();
				},150);
			}
		});

	},
	is_mobile:function() {
		return	this.isAndroid || this.is_iOS;
	},
	is_showed_any_menu:function(){
		var count = window[GLB.G_DATA].SHOWED;
		return count > 0;
	},
	update_meta:function(){
		var _this=this;		
		if(!this.is_showed_any_menu()){ return; }		
		if(window.innerHeight < _this.initialHeight/3*2){
			_this.$body.addClass('android_keyboard_open');
			_this.metaViewport.setAttribute('content', 'height=' + _this.initialHeight + ', width=device-width, initial-scale=1.0');
		}else{
			_this.$body.removeClass('android_keyboard_open');
			_this.metaViewport.setAttribute('content', this.MOBILE_STRING);
		}
	},
	prepare:function(){	
        var $viewport  = $('meta[name=viewport]');
        if(!$viewport.length){
        	// add viewport params if no exist	
            this.OLD_VIEWPORT = "";
            var metaViewport = document.createElement("meta");
            metaViewport.name = "viewport";
            metaViewport.content = "";
            document.getElementsByTagName('head')[0].appendChild(metaViewport);
            $viewport  = $('meta[name=viewport]');
        }else{
        	// save current viewport params	
            this.OLD_VIEWPORT = $viewport[0].content.toString();
        }; 		
	},
    turn_on_mobile:function(mode){
        var $viewport  = $('meta[name=viewport]');
        if(mode){
        	// restore mobile viewport params    	
            $viewport[0].content = this.MOBILE_STRING;
        }else{
        	// restore old viewport params    	
            $viewport[0].content = this.OLD_VIEWPORT;
        };
    }	
};