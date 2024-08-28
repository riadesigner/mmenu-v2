import {GLB} from './glb.js';

export var VIEW_MODAL_CONFIRM = {
	init:function(options){

		this.name = options.name;
		this.anim = options.anim;
		this.$view = $(options.template).clone();
		
		this.CLASS_NAME = 'app-view-confirm-container';

		this.$title = this.$view.find('.'+this.CLASS_NAME+'__title');
		this.$ask = this.$view.find('.'+this.CLASS_NAME+'__message');
		this.$btn_ok = this.$view.find('.ok span');
		this.$btn_cancel = this.$view.find('.cancel span');

		this.update_lng();		
		this.behavior();
		
		return this;
	},
	update_lng:function($tpl){
		var _this=this;
		var $tpl = $tpl || this.$view;
		$tpl.find('[lang]').each(function(i){
			$(this).html(GLB.LNG.get($(this).attr('lang')));
		});
	},	
	update:function(opt){

		var title = opt.title || "Information";
		var ask = opt.ask || "The message";
		
		this.$title.html(title);
		this.$ask.html(ask);

		if(opt.buttons){
			this.$btn_ok.html(opt.buttons[0]);
			this.$btn_cancel.html(opt.buttons[1]);
		};

		this.CONFIRM_ACTION = opt.action || function(){};		
		this.CANCEL_ACTION = opt.cancel || function(){};		
		

	},	
	behavior:function(){
		var _this=this;
		this.$view.find('.ok').on('touchend',function(e){
			GLB.VIEWS.hideModalConfirm();
			_this.CONFIRM_ACTION();
			return false;
		});
		this.$view.find('.cancel').on('touchend',function(e){			
			GLB.VIEWS.hideModalConfirm();
			_this.CANCEL_ACTION();
			return false;
		});
	}
};