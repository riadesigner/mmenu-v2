import {GLB} from './glb.js';

export var VIEW_ACTION_SHEET = {
	init:function(options){
		this.name = options.name;
		this.anim = options.anim;
		this.$view = $(options.template).clone();
		this.update_lng();
		this.behavior();
		return this;
	},

	// func in common ---\
	update_lng:function($tpl){
		var _this=this;
		var $tpl = $tpl || this.$view;
		$tpl.find('[lang]').each(function(i){
			$(this).html(GLB.LNG.get($(this).attr('lang')));
		});
	},
	page_hide:function(){
		this.$page.addClass('hidden');
	},
	page_show:function(){
		this.$page.removeClass('hidden');
	},
	now_loading:function(){		
		this.LOADING = true;
		this.$view.addClass('now-loading');
	},
	end_loading:function(){
		this.LOADING = false;
		this.$view.removeClass('now-loading');
	},
	update_title:function(msg) {
		this.$viewTitleText.html(msg);
	},		
	need2save:function(mode){
		this.NEED_TO_SAVE = mode;
		if(mode){
			this.$view.addClass('need-to-save');
		}else{
			this.$view.removeClass('need-to-save');
		}
	},
	// func in common --- /
	
	update:function(opt,viewTitle,menuTitle){

		viewTitle && this.$view.find('.view-title').html(viewTitle);
		menuTitle && this.$view.find('.menu-title').html(menuTitle);

		if(opt){

		var $actions = this.$view.find('.actions .table').html("");
		var $btn_tpl = $('<div class="tr"><span class="td button">{action}</span></div>')

			$.each(opt,function(i,param){
				console.log("param",param)
				var btn_title = param[0];
				var btn_action = param[1];			
				var $btn = $btn_tpl.clone().find('.button').html(btn_title).end()
				.on('touchend',function(e){
					btn_action();
					GLB.VIEWS.hideActionSheet();
					return false;
				});
				param[2] && $btn.addClass(param[2]);
			 	$actions.append($btn);
			});

		};
	},
	behavior:function(){		
		this.$view.find('.cancel, .view-content').on('touchend',function(e){
			GLB.VIEWS.hideActionSheet();
			return false;
		});
	}	

};