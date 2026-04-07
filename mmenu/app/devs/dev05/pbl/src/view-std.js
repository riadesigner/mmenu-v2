import {GLB} from './glb.js';
import $ from 'jquery';

export var VIEW_STD = {
	
	_init:function(options){
		
		this.name = options.name;
		this.level = options.level;		
		this.$view = $(options.template).clone();
		this.chefsmenu = options.chefsmenu;
		this.noclose = options.noclose;		
		this.animName = options.anim;		

		this.CN = "mm2-";
		this._CN = "."+this.CN;
		this.$tpl = $("#mm2-templates");
		this.$tplCartRow = $("#mm2-templates .mm2-cart-row");

		this.$content = this.$view.find(".std-content");
		
		this.$modal = $('body').find(this._CN+'modal-win');
		this.$modalTitle = this.$modal.find(this._CN+'modal-win__title');
		this.$modalMessage = this.$modal.find(this._CN+'modal-win__message');
		this.$modalBtnOk = this.$modal.find(this._CN+'modal-win__button-ok');		

		this.NEED_TO_SAVE = false;
		this.VIEW_SCROLLED = false;
		this._update_lng();

	},
	_behavior:function(arrMobileButtons){
		var _this=this;

		GLB.MOBILE_BUTTONS.bhv(arrMobileButtons);
		
		this.noclose && this.$btnClose && this.$btnClose.hide();

		$(window).on('touchstart',function(){_this.VIEW_SCROLLED=false;}) 
		.on('touchmove',function(){_this.VIEW_SCROLLED=true;});
		
		this.$btnClose && this.$btnClose.on("touchend click",function() {
			_this._close_menu();
			return false;
		});

	},
	_show_modal_win:function(msg,opt) {
		this.$modal.removeClass('visible');
		this.$modal.show();		
		this.$modalMessage.html(msg);
		this.$modalBtnOk.on("click",(e)=>{
			this.$modal.removeClass('visible');
			setTimeout(()=>{this.$modal.hide();},600);
			opt&&opt.onClose&&opt.onClose();
			e.originalEvent.cancelable && e.preventDefault();
		});
		setTimeout(()=>{ this.$modal.addClass('visible'); },100);
	},
	_content_hide:function(){
		this.$view.addClass(this.CN+'content-hidden');
	},
	_content_show:function(){
		this.$view.removeClass(this.CN+'content-hidden');
	},	
	_update_tabindex:function(){
		// GLB.TABINDEX.clear();
		// this.$view.find('input, textarea').each(function(i){
		// 	$(this).attr('tabindex',i);
		// });
		// this.$view.find('input, textarea').removeAttr('tabindex');
		this.$view.find('input, textarea').attr('tabindex', '-1');
		this.$view.find('input, textarea').css({
			'-webkit-tap-highlight-color': 'transparent',
			'-webkit-user-select': 'text',
			'user-select': 'text',
			'font-size': '16px'
		});		
	},	
    _update_lng:function($el){
    	var $el = $el||this._get_view();
        $el.find('[lang]').each(function(i){
            $(this).html(GLB.LNG.get($(this).attr('lang')));
        });
        return $el;
    },
	_get_name:function() {
		return this.name;
	},
	_get_view:function() {
		return this.$view;
	},
	_get_anim:function() {
		return this.animName;
	},
	_close_menu:function(){ 
		if(this.noclose){
			GLB.UVIEWS.go_first();
		}else{
			this.chefsmenu.close_menu_win();
		}
	},
	_need2save:function(mode){
		this.NEED_TO_SAVE = mode;
		if(mode){
			this.$view && this.$view.addClass('need-to-save');
		}else{
			this.$view && this.$view.removeClass('need-to-save');
		}
	}	
}