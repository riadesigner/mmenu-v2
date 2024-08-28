import {GLB} from './glb.js';
import $ from 'jquery';

/*
	PBL VIEWS
---------------------------- **/

export var UVIEWS = {
	init:function(path_to_all_view) {
		
		this.$allview = $(path_to_all_view);
		
		this.arrViews = {};	
		this.stackNames = [];	
		this.viewsCount = 0;
		this.CURRENT_VIEW_NAME = "";
		
		this.CFG = {
			speed:".6s",
			transition:'.6s cubic-bezier(0.25, 1, 0.5, 1)',
		};
		// 0.22, 1, 0.36, 1
		// 0.25, 1, 0.5, 1

		this.$protectLayer = $("<div></div>").css({
			background:"rgba(0,0,0,0)",width:"100%",height:"100%",
			position:"absolute",top:0,left:0,zIndex:1000,display:"none"
		});
		this.$allview.append(this.$protectLayer);

	},
	addview:function(view){
		 
		var $view = view._get_view();
		var name = view._get_name();

		$view.css({transform:"translate3d(100%,0,0)",opacity:0});			
		this.$allview.append($view);
		this.stackNames.push(name);
		this.arrViews[name] = {view:view,level:this.viewsCount};
		this.viewsCount ++;		
	},
	get:function() {
		return this.arrViews;
	},
	// private
	bhv_protect:function(time) {
		var _this=this;
		this.$protectLayer.show();
		setTimeout(function(){_this.$protectLayer.hide()},time);
	},
	get_name_by_level:function(level) {
		return this.stackNames[level];
	},	
	get_level_by_name:function(name) {
		return this.arrViews[name].level;	
	},	
	get_view_by_level:function(level) {
		return this.get_view_by_name(this.get_name_by_level(level));
	},
	get_anim_by_viewname:function(name) {
		return this.arrViews[name].view._get_anim();	
	},		
	get_view_by_name:function(name) {
		return this.arrViews[name].view._get_view();	
	},	
	get_current_view:function() {
		return this.get_view_by_name(this.CURRENT_VIEW_NAME);
	},
	get_current_level:function() {
		return this.CURRENT_VIEW_NAME ? this.get_level_by_name(this.CURRENT_VIEW_NAME) : 0;
	},		
	is_forward:function(name) {
		return this.get_level_by_name(this.CURRENT_VIEW_NAME) < this.get_level_by_name(name);
	},
	move_forward:function($currView,$nextView,anim) {
		var _this=this;
		// console.log('-------move_forward------anim-------------',anim)
		if(anim=="animLeft"){
			$currView && $currView.css({
				transform:"translate3d(-50%,0,0)",
				transition:"transform "+_this.CFG.transition
			});
			$nextView && $nextView.css({
				transform:"translate3d(0,0,0)",
				transition:"transform "+this.CFG.transition,
				opacity:1});
		}else if(anim=="zoomOut"){
			$nextView.css({transform:'translate3d(0,0,0) scale(1.1)',transition:'0s',opacity:0});
			setTimeout(()=>{
				$nextView.css({transform:'translate3d(0,0,0) scale(1)',transition: '.3s' ,opacity:1});
			},50);
		}
	},
	move_backward:function($currView,$nextView,anim) {
		var _this=this;
		// console.log('--------move_backward-----anim-------------',anim)
		if(anim=="animLeft"){
			$nextView && $nextView.css({transform:"translate3d(-50%,0,0)",transition:"all 0s",opacity:1});
			setTimeout(()=> {
				$nextView && $nextView.css({transform:"translate3d(0,0,0)",transition:"transform "+_this.CFG.transition});
				$currView && $currView.css({transform:"translate3d(100%,0,0)",opacity:0,transition:"transform "+_this.CFG.transition+", opacity 0s "+_this.CFG.speed});
			},50);
		}else if(anim=="zoomOut"){
			$currView && $currView.css({transform:'translate3d(0,0,0) scale(1.1)',transition: '.3s' ,opacity:0});			
			setTimeout(()=>{
				$currView && $currView.css({transform:'translate3d(100%,0,0)',transition:'0s',zIndex:0});
			},400);			
		}

	},	
	park_view:function($view) {		
		$view && $view.css({transform:"translate3d(100%,0,0)",opacity:0,transition:"transform 0s"});
	},
	// public	
	set_current:function(name) {
		var _this=this;
		if(!this.arrViews[name]){ console.log("unknown the name"); return false; };	
		if(name==this.CURRENT_VIEW_NAME){ console.log(" the same name"); return false; };	
				
		console.log("current name", name);

		this.bhv_protect(700);

		if(!this.CURRENT_VIEW_NAME){
			_this.CURRENT_VIEW_NAME = name; 
			const $currView = _this.get_current_view();
			$currView.css({transform:"translate3d(0,0,0)",opacity:1});
		}else{			
			const $currView = this.get_current_view();
			const $nextView = this.get_view_by_name(name);
			const anim = this.get_anim_by_viewname(name);
			// console.log("name/anim",name,anim)
			this.is_forward(name) ? this.move_forward($currView,$nextView,anim) : this.move_backward($currView,$nextView,anim);
			this.CURRENT_VIEW_NAME = name;
		}
	},
	go_back:function() {
		var _this=this;	
		var level = this.get_current_level();	
		if(level>0){
			this.bhv_protect(700);			
			const $currView = this.get_current_view();
			const $nextView = this.get_view_by_level(level-1);
			const currName = this.get_name_by_level(level);			
			const nextName = this.get_name_by_level(level-1);			
			const anim = this.get_anim_by_viewname(currName);
			// console.log("- - - - go_back - - - currName / anim - - - -",currName,anim);
			this.move_backward($currView,$nextView,anim);
			this.CURRENT_VIEW_NAME = nextName;
		}
	},
	go_first:function(fast) {
		var level = this.get_current_level();
		var toplevel = fast?1:0;
		for(var i=1;i<level+toplevel;i++){
			var $view = this.get_view_by_level(i);
			this.park_view($view);
		};

		var FIRST_NAME = this.get_name_by_level(0);

		if(fast){	
			if(FIRST_NAME!=this.CURRENT_VIEW_NAME){
				this.CURRENT_VIEW_NAME = FIRST_NAME; 
				var $currView = this.get_current_view();
				$currView.css({transform:"translate3d(0,0,0)",transition:"transform 0s",opacity:1});
			}
		}else{
			this.set_current(FIRST_NAME);
		}
	}
};




