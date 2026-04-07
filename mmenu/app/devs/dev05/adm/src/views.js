/*

	SAMPLE

	VIEWS.init({viewsParent:'path-to-parent'});

	VIEWS.addNewView({
		name:'cafe',
		background:'gold',
		anim: left | up | fade, defaul:left 
	});
	
	VIEWS.setCurrent('cafe');

	VIEWS.actionSheet([['titleCmd',foo],['titleCmd',foo2]]);
	VIEWS.actionSheet([['titleCmd',foo,class],['titleCmd',foo2]],viewTitle,viewMenu);

*/

import {GLB} from './glb.js';


export 
var VIEWS = {
	init:function(options){
		
		this.$allviews = $(options.viewsParent);

		this.arrViews = {};
		this.arrHistory = [];
		this.addMiddleLayer();
		this.CURRENT_VIEW = '';

		this.DBLTAP_DELAY = 800;
		this.DBLTAP = false; // too fast request go back
		this.PAUSE_BEFORE_ANIM_CURRENT = 50; // while lift up zIndex 
		this.ANI = {
			left:{
				transition:'.6s cubic-bezier(0.23, 1, 0.32, 1)',
				transition2:'.3s cubic-bezier(0.23, 1, 0.32, 1)', // for low layer while goBack
				delay:700},
			zoom:{transition:'.3s',delay:400},
			modal:{transition:'.3s',delay:400}
		};		
	},
	getArrViews:function(){
		return this.arrViews;
	},
	addView:function(VIEW){
		this.$allviews.append(VIEW.$view);
		this.arrViews[VIEW.name] = VIEW;
		this.parkView(VIEW);		
	},
	getCurrentName:function() {
		return this.CURRENT_VIEW;
	},
	setCurrent:function(name){		
		if(this.preventDoubleTap()){ return false;}
		if(this.CURRENT_VIEW == name ) return false;		
		var VIEW = this.arrViews[name];	
		if(!this.arrHistory.length){
			this.arrHistory.push(name);
			VIEW.$view.css({transform:'translate3d(0,0,0)',zIndex:100});
		}else{
			this.arrHistory.push(name);			
			this.showViewAnimation(name);
		};
		this.CURRENT_VIEW = name;
	},	
	jumpTo:function(name){
		var _this=this;
		this.toStart();		
		if(this.CURRENT_VIEW == name ) return false;
		this.arrHistory.push(name);			
		var VIEW = this.arrViews[name];		
		var zIndex = this.arrHistory.length*10+100;
		var PARENT = this.getParent();
		this.TMR&&clearTimeout(this.TMR);
		VIEW.$view.css({transform:'translate3d(0,0,0)',transition: '0s',zIndex:zIndex});
		if(PARENT) PARENT.$view.css({transform:'translate3d(-50%,0,0)',transition:'0s'});
		this.CURRENT_VIEW = name;
	},	
	goBack:function(){
		var _this=this;
		console.log("BACK!")
		if(this.preventDoubleTap()){ return false; }

		var PARENT = this.getParent();
		var name = this.arrHistory.pop();
		var VIEW = this.arrViews[name];
		var anim = this.getAnimMethod(VIEW);	
		
		switch(anim){
			case 'animLeft':			
				VIEW.$view.css({transform:'translate3d(120%,0,0)',transition: _this.ANI.left.transition});
				if(PARENT) PARENT.$view.css({transform:'translate3d(0,0,0)',transition: _this.ANI.left.transition2});				
				this.TMR&&clearTimeout(this.TMR);
				this.TMR = setTimeout(function(){
					VIEW.$view.css({zIndex:0});
				},_this.ANI.left.delay);		
			break;
			case 'zoomOut':
				VIEW.$view.css({transform:'translate3d(0,0,0) scale(1.1)',transition: _this.ANI.zoom.transition ,opacity:0});
				this.TMR&&clearTimeout(this.TMR);
				this.TMR = setTimeout(function(){
					VIEW.$view.css({transform:'translate3d(120%,0,0)',transition:'0s',zIndex:0});
				},_this.ANI.zoom.delay);
			break;
		};
		this.CURRENT_VIEW = PARENT.name;
	},
	toStart:function(){
		var firstName = this.arrHistory[0];
		for(var i in this.arrHistory){
			var VIEW = this.arrViews[this.arrHistory[i]];
			this.parkView(VIEW);
		};
		this.arrHistory = [];
		this.modalMessageShowed && this.hideModalMessage();
		this.modalConfirmShowed && this.hideModalConfirm();
		this.actionSheetShowed && this.hideActionSheet();
		this.setCurrent(firstName);
	},
	//private
	preventDoubleTap:function(){
		var _this=this;
		if(this.DBLTAP){
			console.log('prevent double tap');
			return true;
		}else{
			this.DBLTAP = true;	
			setTimeout(function(){ _this.DBLTAP=false; },_this.DBLTAP_DELAY);
		}
	},	
	parkView:function(VIEW){	
		VIEW.$view.css({transform:'translate3d(120%,0,0)',transition:'0s',zIndex:0});
	},
	getAnimMethod:function(VIEW){
		var anim = VIEW.anim;
		anim = (anim === undefined) ? 'animLeft':anim;
		return anim;
	},
	showViewAnimation:function(name){
		var _this=this;
		var VIEW = this.arrViews[name];
		var anim = this.getAnimMethod(VIEW);
		var zIndex = this.arrHistory.length*10+100;
		var PARENT = this.getParent();		
		VIEW.$view.css({zIndex:zIndex});

		this.TMR&&clearTimeout(this.TMR);
		this.TMR = setTimeout(function(){
			switch (anim){
				case 'animLeft':			
					VIEW.$view.css({transform:'translate3d(100%,0,0)',transition:'0s'});
					setTimeout(function(){
						VIEW.$view.css({transform:'translate3d(0,0,0)',transition:_this.ANI.left.transition});
						if(PARENT) PARENT.$view.css({transform:'translate3d(-50%,0,0)',transition: _this.ANI.left.transition});
					},100);
				break;
				case 'zoomOut':
					VIEW.$view.css({transform:'translate3d(0,0,0) scale(1.1)',transition:'0s',opacity:0});
					setTimeout(function(){
						VIEW.$view.css({transform:'translate3d(0,0,0) scale(1)',transition: _this.ANI.zoom.transition ,opacity:1});
					},50);
				break;
			}			
		},this.PAUSE_BEFORE_ANIM_CURRENT);

	},
	getParent:function(){
		if(this.arrHistory.length>1){
			var name = this.arrHistory[this.arrHistory.length-2];
			return this.arrViews[name];
		}else{
			return false;
		}
	},
	addMiddleLayer:function(){
		this.$middleLayer = $('<div class="middle-layer"></div>');
		this.$allviews.append(this.$middleLayer);		
	},
	// public
	modalMessage:function(opt){
		GLB.VIEW_MODAL_MESSAGE.update(opt);
		this.modalConfirmShowed && this.hideModalConfirm();
		this.actionSheetShowed && this.hideActionSheet();
		this.modalMessageShowed = true;
		this.showModal('view-modal-message');
	},	
	hideModalMessage:function(){
		this.hideModal('view-modal-message');		
		this.modalMessageShowed = false;
	},
	modalConfirm:function(opt){		
		GLB.VIEW_MODAL_CONFIRM.update(opt);		
		this.modalMessageShowed && this.hideModalMessage();
		this.actionSheetShowed && this.hideActionSheet();
		this.modalConfirmShowed = true;
		this.showModal('view-modal-confirm');
	},
	hideModalConfirm:function(){
		this.hideModal('view-modal-confirm');		
		this.modalConfirmShowed = false;
	},
	//private
	hideModal:function(name){
		var _this=this;
		this.$middleLayer.hide();
		var VIEW = this.arrViews[name];		
		VIEW.$view.css({transform:'translate3d(0,0,0) scale(1.1)',transition:_this.ANI.modal.transition,opacity:0});		
		setTimeout(function(){
			VIEW.$view.css({transform:'translate3d(120%,0,0)',transition:'0s'});
		},_this.ANI.modal.delay);		
	},
	showModal:function(name){
		var _this=this;		
		var VIEW = this.arrViews[name];
		// this.$middleLayer.fadeIn();		
		VIEW.$view.css({transform:'translate3d(0,0,0) scale(1.1)',transition:'0s',opacity:0,zIndex:1100})
		setTimeout(function(){
			VIEW.$view.css({transform:'translate3d(0,0,0) scale(1)',opacity:1,transition:_this.ANI.modal.transition});
		},10);
	},
	//public
	actionSheet:function(opt,viewTitle,menuTitle){
		var _this=this;
		// this.$middleLayer.fadeIn();
		this.modalConfirmShowed && this.hideModalConfirm();
		this.modalMessageShowed && this.hideModalMessage();
		this.actionSheetShowed = true;
		var VIEW = this.arrViews['view-action-sheet'];
		VIEW.$view.css({transform:'translate3d(0,0,0)',transition:'0s',zIndex:1100});
		VIEW.$view.find('.action-sheet-menu').addClass('shown');
		GLB.VIEW_ACTION_SHEET.update(opt,viewTitle,menuTitle);
	},
	hideActionSheet:function(){
		var _this=this;
		// this.$middleLayer.hide();
		this.actionSheetShowed = false;
		var VIEW = this.arrViews['view-action-sheet'];
		VIEW.$view.find('.action-sheet-menu').removeClass('shown');
		setTimeout(function(){
			VIEW.$view.css({transform:'translate3d(120%,0,0)',transition:'0s'});
		},_this.ANI.modal.delay);
	}

};

