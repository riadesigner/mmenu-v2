import {GLB} from './glb.js';

export var VIEW_CUSTOMIZING_CART = {
	
	init:function(options){
		
		this._init(options);

		this.$btnSave = this.$view.find('.save');
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		
		this.$buttonsCartMode = this.$view.find('.customizing-cart__cart-mode');
		this.$buttonsDeliveryMode = this.$view.find('.customizing-cart__delivery-mode');		
		this.$buttonsOrderWayMode = this.$view.find('.customizing-cart__order-way-mode');				
		
		this.behavior();

		return this;

	},
	reset:function(){
		this._reset();
		this._need2save(false);
		this._page_to_top();
	},

	update:function(USER){		

		this._update();
		this._page_hide();
		
		var cafe = GLB.THE_CAFE.get();
		
		this.ID_CAFE = cafe.id;
		
		this.NEW_CART_MODE = parseInt(cafe.cart_mode,10);
		this.NEW_DELIVERY_MODE = parseInt(cafe.has_delivery,10);		
		this.NEW_ORDER_WAY = parseInt(cafe.order_way,10);				
	
		this.reset();		
		this.rebuild();	
		this._page_show();
	},

	end_updating_with_error(error_message, foo){
		if(error_message){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:error_message,
				btn_title:GLB.LNG.get('lng_ok')
			});
		};		
		foo ? foo() : this.end_updating();
	},

	end_updating:function(){
		setTimeout(()=>{ 
			this._end_loading();
			this._page_show(); 
		},300);		
	},

	rebuild:function(){
		
		var _this=this;		
		const cafe = GLB.THE_CAFE.get();		

		this.$buttonsCartMode.html("");
		var arrBtns = ["Корзина выключена","Корзина включена"];
		for(let i=0;i<arrBtns.length;i++){
			let checked = parseInt(cafe.cart_mode,10) == i?" checked":"";
			let $btn = $("<div class='std-form__radio-button "+checked+"' mode='"+i+"'>"+arrBtns[i]+"</div>\n");
			$btn.on("touchend",function(e){
				if(!_this.VIEW_SCROLLED){
					if(!$(this).hasClass('checked')){
						_this.NEW_CART_MODE = parseInt($(this).attr('mode'),10);
						$(this).addClass('checked');	
						$(this).siblings().removeClass('checked');
						_this.check_need_to_save();
					}
				};
				e.originalEvent.cancelable && e.preventDefault();
			});
			this.$buttonsCartMode.append($btn);
		};

		this.$buttonsDeliveryMode.html("");
		var arrBtns = ["Нет доставки","Есть доставка"];
		for(let i=0;i<arrBtns.length;i++){
			let checked = parseInt(cafe.has_delivery,10) == i?" checked":"";
			let $btn = $("<div class='std-form__radio-button "+checked+"' mode='"+i+"'>"+arrBtns[i]+"</div>\n");
			$btn.on("touchend",function(e){
				if(!_this.VIEW_SCROLLED){
					if(!$(this).hasClass('checked')){
						_this.NEW_DELIVERY_MODE = parseInt($(this).attr('mode'),10);
						$(this).addClass('checked');	
						$(this).siblings().removeClass('checked');
						_this.check_need_to_save();
					}
				};
				e.originalEvent.cancelable && e.preventDefault();
			});
			this.$buttonsDeliveryMode.append($btn);
		};
		
		this.$buttonsOrderWayMode.html("");	
		var arrBtns = ["1. Только в TG","2. В ТG, затем в IIKO"];
		for(let i=0;i<arrBtns.length;i++){
			let checked = parseInt(cafe.order_way,10) == i?" checked":"";
			let $btn = $("<div class='std-form__radio-button "+checked+"' mode='"+i+"'>"+arrBtns[i]+"</div>\n");
			$btn.on("touchend",function(e){
				if(!_this.VIEW_SCROLLED){
					if(!$(this).hasClass('checked')){
						_this.NEW_ORDER_WAY = parseInt($(this).attr('mode'),10);
						$(this).addClass('checked');	
						$(this).siblings().removeClass('checked');
						_this.check_need_to_save();
					}
				};
				e.originalEvent.cancelable && e.preventDefault();
			});
			this.$buttonsOrderWayMode.append($btn);
		};

		this.check_need_to_save();											

	},

	behavior:function()	{
		var _this = this;

		this._behavior();
		
		this.$btnBack.on('touchend',function(){
			_this._blur({onBlur:function(){
				!_this.LOADING && GLB.VIEWS.goBack();
			}});			
			return false;
		});

		this.$btnSave.on('touchend',function(){
			_this._blur({onBlur:function(){
				!_this.LOADING && _this.save({onReady:function(){				
					_this._go_back();
				}});
			}});
			return false;
		});		

	},

	check_need_to_save:function(){
		this._need2save(false);
		if(GLB.THE_CAFE.is_iiko_mode()){
			if( parseInt(GLB.THE_CAFE.get().cart_mode,10)!==this.NEW_CART_MODE ||
			parseInt(GLB.THE_CAFE.get().order_way,10)!==this.NEW_ORDER_WAY ||
			parseInt(GLB.THE_CAFE.get().has_delivery,10)!==this.NEW_DELIVERY_MODE){
				this._need2save(true);
			}

		}else{
			if(parseInt(GLB.THE_CAFE.get().cart_mode,10)!==this.NEW_CART_MODE ||
				parseInt(GLB.THE_CAFE.get().has_delivery,10)!==this.NEW_DELIVERY_MODE){
				this._need2save(true);	
			}
		}
	},

	save:function(opt){

		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.save_cart_settings.php';			
		
		this._now_loading();

		var data = {
			id_cafe:GLB.THE_CAFE.get().id,			
			cart_mode:this.NEW_CART_MODE,
			order_way:this.NEW_ORDER_WAY,
			has_delivery:this.NEW_DELIVERY_MODE
		};

		var onSuccess = function(cafe){
        	_this._end_loading();				            	
        	if(!cafe.error){					
				GLB.THE_CAFE.update(cafe);
				opt.onReady && opt.onReady();
			}else{
				_this._end_loading();
				console.log(cafe.error);
			}
		};

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            data:data,
            method:"POST",
            dataType: "jsonp",
            success: function (response) {
            	
            	if(response && !response.error){
	            	var cafe = response;
	            	var cafe_rev = cafe.rev;            	
	            	setTimeout(function(){onSuccess(cafe);},300);
            	}else{
            		console.log('response err:',response)
            	}
            },
            error:function(response) {
            	_this._end_loading();
		        console.log("err load cafe info",response);
			}
        });
			
	}

};