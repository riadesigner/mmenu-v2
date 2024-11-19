import {GLB} from './glb.js';

export var VIEW_STD = {
	
	_init:function(options){
		
		this.name = options.name;
		this.anim = options.anim;		
		this.$view = $(options.template).clone();		

		
		this.$viewTitle = this.$view.find('.view-header-title');
		this.$viewTitleText = this.$view.find('.view-header-title_text');
		this.$viewTitleIcon = this.$view.find('.view-header-title_icon');
		this.$page = this.$view.find('.app-view-page'); 
		this.$pageContainer = this.$view.find('.app-view-page-container');
		this.$footerBts = this.$view.find('.app-view-footer .button');
		this.$footer = this.$view.find('.app-view-footer');	
		this.$form = this.$view.find('.std-form');	
		this.$inputs = this.$view.find('input, textarea'); // all inputs
		this.$textareas = this.$view.find('textarea'); 
		this.$btnMainHelp = this.$view.find('.view-header-buttons__button_help');

		
		this.LOADING = false;
		this.NEED_TO_SAVE = false;
		this.AJAX = false;
		this.VIEW_SCROLLED = false;
		
		this._update_lng();
		this._update_inputs_length();		

		$.fixIOSscroll(this.$pageContainer);		

	},
	_behavior:function(){
		
		var _this=this;
		
		$(window).on('touchstart',function(){_this.VIEW_SCROLLED=false;}) 
		.on('touchmove',function(){_this.VIEW_SCROLLED=true;});

		this.$footerBts.on("touchstart",function(){$(this).addClass("active");});
		this.$footerBts.on("touchend",function(){$(this).removeClass("active"); return false;});
		this.$footer.on('touchend',function(e) { e.preventDefault(); return false; }); // disable doubletap zoom

		this.$inputs.on('contextmenu',function(){
			console.log("input contextmenu")
		})		

		this.$textareas.on('keyup',function(){			
			_this._textareas_update($(this));
		});

		this.$btnMainHelp.on('touchend',function(){
			_this._blur({onBlur:function(){
				if(!_this.LOADING){
					GLB.VIEW_MAIN_HELP.update();
					GLB.VIEWS.setCurrent(GLB.VIEW_MAIN_HELP.name);
				};
			}});
			return false;
		});	

	},
	_textareas_update:function($el){
		var CLS_LENGTH_LIMIT_REACHED = 'length-limit-reached';
		var fn = {
			height_update:function($el){
				var maxlength = $el.attr("maxlength");				
				//calaculate symbols inside 
				var len,br,total;
			 	len = $el[0].value.match(/./g);
				br = $el[0].value.match(/\n/g);
				len = len?len.length:0;
				br = br?br.length:0;
				total = len+br;				
				//show limit reached 
				if((total)==maxlength||(total)>maxlength){
					$el.removeClass(CLS_LENGTH_LIMIT_REACHED)
					setTimeout(function() {
						$el.addClass(CLS_LENGTH_LIMIT_REACHED)
					}, 10);
				}else{
					$el.removeClass(CLS_LENGTH_LIMIT_REACHED)
				}
				//rise height if need
				var h = $el.outerHeight();
				var scroll_height = Math.ceil($el[0].scrollHeight);				
				if(h<scroll_height){			
					$el.css({height:scroll_height+20});
				}				
			}
		};

		if($el && $el.length){
			fn.height_update($el);
		}else{
			this.$textareas.each(function(){
				fn.height_update($(this));
			});
		};
	},
	_blur:function(opt){
		var focus = this.$inputs.is(":focus");		
		this.$inputs.blur();
		if(GLB.DEVICE.is_android() && focus){
			// time to remove android keyboard
			// and restore window size
			setTimeout(function(){ 
				opt&&opt.onBlur&&opt.onBlur();
			},500);
		}else{
			opt&&opt.onBlur&&opt.onBlur();
		}
	},
	_go_back:function(){
		GLB.VIEWS.goBack();
		//this._page_hide();
	},
	_update_lng:function($tpl){		
		var _this=this;
		var $tpl = $tpl || this.$view;
		$tpl.find('[lang]').each(function(i){
			$(this).html(GLB.LNG.get($(this).attr('lang')));
		});
		return $tpl;
	},
	_page_to_top:function(){
		this.$pageContainer && this.$pageContainer.scrollTop(0);
	},
	_page_hide:function(){
		this.$page && this.$page.addClass('hidden');
	},
	_page_show:function(){
		this.$page && this.$page.removeClass('hidden');
		this._textareas_update();
	},
	_now_loading:function(){		
		this.LOADING = true;
		this.$view && this.$view.addClass('now-loading');
	},
	_end_loading:function(){		
		this.LOADING = false;
		this.$view && this.$view.removeClass('now-loading');		
	},
	_reset:function(){
		this.$inputs.val("");
		this.$inputs.css({height:'auto'});
	},
	_update:function(){
		console.log("update: "+this.name);
	},
	_update_inputs_length:function(){
		var _this=this;
		if(this.$inputs.length){
			this.$inputs.each(function(){
				var name = $(this).attr('name');
				var type = $(this).attr('type');
				if(type!=='file'){
					var len = GLB.INPUTS_LENGTH.get(name);
					$(this).attr({'maxlength':len});
				}
			});			
		}		
	},
	_update_title:function(msg) {
		this.$viewTitleText && this.$viewTitleText.html(msg);
	},		
	_need2save:function(mode){
		this.NEED_TO_SAVE = mode;
		if(mode){
			this.$view && this.$view.addClass('need-to-save');
		}else{
			this.$view && this.$view.removeClass('need-to-save');
		}
	},
	_update_tabindex:function(){
		GLB.TABINDEX.clear();
		this.$view.find('input, textarea').each(function(i){
			$(this).attr('tabindex',i);
		});
	},
	_update_app:function(new_version){		
		var _this=this;
		GLB.VIEWS.modalMessage({
			title:GLB.LNG.get('lng_update'),
			message:'Панель Управления обновилась до версии <nobr>'+new_version+'.</nobr> Нажмите Ок, чтобы ее перезапустить',
			btn_title:GLB.LNG.get('lng_ok'),
			on_close: function(){
				_this._now_loading();
				location.reload();
			}
		});
	},
	_update_app_as_status_changed:function(new_status){		
		var _this=this;
		var new_status = parseInt(new_status,10);
		var msg = "";
		switch(new_status){
			case 1: 
			msg = [
				'<p>Статуc вашего Меню изменился на <nobr>«В архиве»</nobr>.</p>',
				'<p>Возможная причина – закончился годовой Контракт.</p>'
			].join(' '); 
			break;
			case 2:
			msg = [
				'<p>Статуc вашего Меню изменился на <nobr>«Договор»</nobr>.</p>',
				'<p>Вам открыты все возможности Сервиса.</p>'
			].join(' ');			
			break;
			default: 
			msg = '<p>Статуc вашего Меню изменился на <nobr>«Тестовый период»</nobr>.</p>'; 
		}

		GLB.VIEWS.modalMessage({
			title:GLB.LNG.get('lng_attention'),
			message:[
				msg,
				'<p>Нажмите Ок, чтобы перезапустить Панель Управления</p>'
			].join(' '),
			btn_title:GLB.LNG.get('lng_ok'),
			on_close: function(){
				_this._now_loading();
				location.reload();
			}
		});
	},	
	_reload_by_err:function(){		
		var _this=this;
		GLB.VIEWS.modalMessage({
			title:GLB.LNG.get('lng_update'),
			message:'Обновились настройки сервиса. Нажмите Ок для перезагрузки программы.',
			btn_title:GLB.LNG.get('lng_ok'),
			on_close: function(){
				_this._now_loading();
				location.reload();
			}
		});			
	},
	_show_message:function(okMsg){
		GLB.VIEWS.modalMessage({
			title:GLB.LNG.get("lng_awesome"),
			message:okMsg,
			btn_title:GLB.LNG.get('lng_close')
		});
	},
	_show_error:function(errMsg){
		GLB.VIEWS.modalMessage({
			title:GLB.LNG.get("lng_error"),
			message:errMsg,
			btn_title:GLB.LNG.get('lng_close')
		});
	},
	_show_confirm_async:function(askMsg){
		return new Promise((res,rej)=>{
			GLB.VIEWS.modalConfirm({
				title:GLB.LNG.get("lng_attention"),
				ask:askMsg,
				action:()=>{			
					res && res();
				},
				buttons:[GLB.LNG.get("lng_ok"),GLB.LNG.get("lng_cancel")]
			});	
		})
	}	

};