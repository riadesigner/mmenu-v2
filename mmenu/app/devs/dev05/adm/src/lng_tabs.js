
export var LNG_TABS = {
	init:function($view, $parent, cafe){
		this.$view =$view;
		this.$parent =$parent;
		this.cafe = cafe;
		this.EXTRA_LANGS = this.cafe.extra_langs?JSON.parse(this.cafe.extra_langs):{};
		this.build();
		return this;
	},
	build:function() {
		//remove old tabs
		this.$parent.find('.lang-tabs').remove();
		// build new tabs only if menu has extra languages
		if(Object.keys(this.EXTRA_LANGS).length>0){
			// adding Russian and setting it as current
			let $tabs = $("<ul></ul>").addClass("lang-tabs");			
			this.EXTRA_LANGS = $.extend({"ru":"Русский"},this.EXTRA_LANGS);			
			for(let i in this.EXTRA_LANGS){				
				let title = this.EXTRA_LANGS[i];
				let code = i;
				let current = code=='ru'?"current":"";				
				let $li = $(`<li data-lang-code="${code}" class="${current}">${code}</li>`);
				$li.on("touchend",(e)=>{
					this.$view._blur({onBlur:()=>{
						if(!this.$view.LOADING && !this.$view.VIEW_SCROLLED){
							if(!$li.hasClass('current')){								
								$li.addClass('current');
								$li.siblings().removeClass('current');
								this._set_current($li.data('lang-code'));
							};
						};
					}});
					e.originalEvent.cancelable && e.preventDefault();
				});
				$tabs.append($li);		
			};
			this.$parent.append($tabs);
		}else{
			// IF HAS NOT EXTRA LANGS
			// ADD THE ONLY RUSSIAN
			this.EXTRA_LANGS = {"ru":"Русский"};
		}
	},
	get_langs:function() {
		// return russian + extra; 
		return this.EXTRA_LANGS;
	},
	_set_current:function(code) {		
		$(this).trigger( "change",code );
	}
};