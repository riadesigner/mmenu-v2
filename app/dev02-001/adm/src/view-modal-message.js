import {GLB} from './glb.js';

export var VIEW_MODAL_MESSAGE = {
	init:function(options){

		this.name = options.name;
		this.anim = options.anim;
		this.$view = $(options.template).clone();
		
		this.CLASS_NAME = 'app-view-modal-container';
			
		this.$title = this.$view.find('.'+this.CLASS_NAME+'__title');
		this.$message = this.$view.find('.'+this.CLASS_NAME+'__message');
		this.$btn_title = this.$view.find('.close span');

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
		var message = opt.message || "The message";
		var btn_title = opt.btn_title || "Close";		
		this.$title.html(title);
		this.$message.html(message);
		this.$btn_title.html(btn_title);
		this.DO_AFTER_CLOSE = opt.on_close;
	},
	behavior:function(){
		var _this=this;
		this.$view.on("touchstart",function(e) {
			e.preventDefault();
		});
		this.$view.find('.close').on('touchend',function(e){			
			GLB.VIEWS.hideModalMessage();
			_this.DO_AFTER_CLOSE && _this.DO_AFTER_CLOSE();
			return false;
		});
		this.$view.find('.close').on("click",function(){
			_this.update({
				title:"Ой!",
				message:"Панель Управления создана для работы с телефона или планшета.",
				btn_title:"Закрыть"
			}); 
		});
	}
};