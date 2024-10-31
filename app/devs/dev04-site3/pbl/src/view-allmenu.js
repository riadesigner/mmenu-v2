import {GLB} from './glb.js';
import $ from 'jquery';

export var VIEW_ALLMENU = {
	init:function(options) {

		this._init(options);		
				
		this.$headerTitle = this.$view.find(this._CN+"allmenu-header-title");
		this.$headerPhone = this.$view.find(this._CN+"header-phone");

		this.$btnBasket = this.$view.find(this._CN+"btn-basket");
		this.$btnClose = this.$view.find(this._CN+"btn-close-menu");
		this.$btnBack = this.$view.find(this._CN+"btn-back, "+this._CN+"std-header-btn-back, "+this._CN+"btn-close-items");
				
		this.$menuListContainer = this.$view.find(this._CN+"allmenu-list2");		
		this.$tplMenuItem  = this.$tpl.find(this._CN+"allmenu-row");
		this.$cafeDescription = this.$view.find(this._CN+"cafe-description");		
		this.$btnSlideUpAbout = this.$view.find(this._CN+"btn-slideup");		
		
		// mobile version ui
		this.$btnsLangContainer = this.$view.find(this._CN+"langs__buttons");		

		this.CLASS_SHOWED_ABOUT = this.CN+"allmenu-about-showed";
		this.LIST_SCROLLED = false;
		
		GLB.MENU_TABLE_MODE.init(this);
		
		this.behavior();

		return this;
	},

	update:function(allmenu) {

		var _this=this;		

		GLB.MENU_TABLE_MODE.update();		
		console.log('allmenu',allmenu)			

		// -----------------------
		// MULTILANG BUILDING UI
		// -----------------------
		this.build_langs_ui();

		this.all_menu = allmenu;	
			
		var fn = {
			rebuild:function() {
				var $ul = $("<ul></ul>");				
				$ul.on('touchstart',function(){
					_this.LIST_SCROLLED = false					
				});
				$ul.on('touchmove',function(){
					_this.LIST_SCROLLED = true;					
				});
				for (var i=0; i<_this.all_menu.arr.length;i++){
					var m = _this.all_menu.arr[i];
					
						m.visible > 0 && (function(m_id){
						var $tpl = _this.$tplMenuItem.clone();
						$tpl.find(_this._CN+"allmenu-row__title span").html(m.title).end();
						$tpl.find(_this._CN+'allmenu-row__icon').addClass(_this.CN+"icon-"+ GLB.MENU_ICONS.get(m.id_icon));

						$tpl.find('a').attr({"data-id-menu":m_id})
						.on("touchend click",function(e){
							if(!_this.LIST_SCROLLED){
								var menu = _this.get_menu_by_id(m_id);
								GLB.VIEW_ALLITEMS.update(menu);
								_this.chefsmenu.now_loading();
								GLB.UVIEWS.set_current("the-allitems");
							};
														
							e.originalEvent.cancelable && e.preventDefault();
							e.stopPropagation();
							
						});	
						$ul.append($tpl);
					})(m.id);

				};

				$ul.find("li").each(function(i) {
					$(this).css({transition:".3s "+(i*.1)+"s transform"});
				});

				_this.$menuListContainer.html($ul);
				setTimeout(function() {
					_this.$menuListContainer.addClass(_this.CN+"allmenu-list-showed");	
				},10);
			}
		};
		
		const cafeTitleStr = GLB.CAFE.get('cafe_title');
		this.$headerTitle.find(this._CN+"allmenu-header-title__text").html(cafeTitleStr);

		var phone = GLB.CAFE.get('cafe_phone');
		phone!=="" ? this.$headerPhone.find(this._CN+"header-phone__text").html(phone) : this.$headerPhone.hide();

		if(phone){
			var ph = phone.replace(/[-() ]/g,"");
			this.$headerPhone.on("touchend click",function(){
				location.href="tel:"+ph;
				return false;
			});
		};

		var $descr = this.$cafeDescription;

		var cafe_address = GLB.CAFE.get('cafe_address');
		var class_br = this.CN+'cafe-description__br';
		var cafe_descr_nl2br = GLB.CAFE.get('cafe_description').replace(/\n/g, "<div class='"+class_br+"'></div>");
		var chief_cook = GLB.CAFE.get('chief_cook');
		var work_hours = GLB.CAFE.get('work_hours')!==""?GLB.CAFE.get('work_hours'):"Не указаны";		

		$descr = this._update_lng($descr);
		$descr.find(this._CN+"cafe-description-address").html(GLB.LNG.get("lng_our_adress")+" <strong>"+cafe_address+"</strong>");
		$descr.find(this._CN+"cafe-description-chief").html(GLB.LNG.get("lng_chef_cook")+" <strong>"+chief_cook+"</strong>");
		$descr.find(this._CN+"cafe-description-workhours").html(GLB.LNG.get("lng_work_hours")+" <strong>"+work_hours+"</strong>");
		$descr.find(this._CN+"cafe-description-about").html(cafe_descr_nl2br);
		
		chief_cook==="" && $descr.find(this._CN+"cafe-description-chief").hide();

		this.$menuListContainer.html("");
		fn.rebuild();
		
	},
	get_menu_by_id:function(id_menu) {	
		return this.all_menu[id_menu];		
	},
	build_langs_ui:function(){
		const _this=this;
		this.$view.removeClass("multilang-mode");				
		const CAFE = GLB.CAFE.get();
		const extra_langs = CAFE.extra_langs?JSON.parse(CAFE.extra_langs):false;
		if(!extra_langs) return;
		
		this.$view.addClass("multilang-mode");

		this.$btnsLangContainer.html("");
		const current = "ru";
		const btns = [];
		const arr_langs = [];		
		for (let i in extra_langs) {arr_langs.push(i)}
		arr_langs.unshift('ru');

		for (let ln in arr_langs){		
			let nm = arr_langs[ln];
			let currentClass = current===nm?'class="current"':'';
			let $btn = $(`<div data-user-lang="${nm}" ${currentClass}><span>${nm}</span></div>`);
			this.$btnsLangContainer.append($btn);					
			btns.push($btn);				
		};		

		this.$btnsLangContainer.find('div').on("touchend click",function(e){
			const lng = $(this).data("user-lang");			
			_this.set_user_language(lng);
			e.cancelable && e.preventDefault();
		});

		this._behavior(btns);

	},
	set_user_language:function(lng){
		console.log('lng',lng)
	},
	behavior:function() {
		var _this=this;	

		var arrMobileButtons = [
			this.$btnBasket,
			this.$btnBack,
			this.$btnClose,
			//
			this.$headerTitle,
			this.$btnSlideUpAbout
		];

		this._behavior(arrMobileButtons);		

		this.$headerTitle.on("touchend click",function(e){
			_this.$view.toggleClass(_this.CLASS_SHOWED_ABOUT);
			e.originalEvent.cancelable && e.preventDefault();
		});
		
		this.$btnSlideUpAbout.on("touchend click",function(e){
			_this.$view.removeClass(_this.CLASS_SHOWED_ABOUT);
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$btnBasket.on("touchend click",function(e) {
			GLB.VIEW_CART.update();
			GLB.UVIEWS.set_current("the-showcart");
			e.originalEvent.cancelable && e.preventDefault();
		});

	}
};