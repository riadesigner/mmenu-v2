import {GLB} from './glb.js';
import $ from 'jquery';

// этот модуль устарел

export var VIEW_THANK_YOU = {
	init:function(options){
		
		this._init(options);

		this.$btnBasket= this.$view.find(this._CN+"btn-basket");
		this.$btnClose = this.$view.find(this._CN+"btn-close-menu");
		this.$btnBack = this.$view.find(this._CN+"btn-back, "+this._CN+"std-header-btn-back, "+this._CN+"btn-close-items");
						
		this.$msgAmount = this.$view.find(this._CN+"thank-you-amount");
		this.$msgCartList = this.$view.find(this._CN+"thank-you-cart-list");

		this.behavior();

		return this;
	},
	behavior:function(){
		var _this=this;

		var arrMobileButtons = [
			this.$btnBasket,
			this.$btnBack,
			this.$btnClose
		];

		this._behavior(arrMobileButtons);

		this.$btnBack.on("touchend click",function() {
			GLB.UVIEWS.go_first();
			return false;
		});

	},	
	update:function(order){	


		var currency = GLB.CAFE.get('cafe_currency').symbol;

		var str_cart_list = "";

		var all_items = GLB.CART.get_all();

		if(!GLB.CART.is_empty()){
			var num = 0;
			for(var i in all_items){
				var count = all_items[i].count;
				var item = all_items[i].item;
				str_cart_list += "<li><div>"+(num+1)+". "+item.title+"</div><div>"+count+"х"+item.price+"  "+currency+"</div></li>";
				num++;
			}
			str_cart_list = "<li>"+str_cart_list+"</li>";
		}else{
			str_cart_list = "В корзине еще ничего нет";
		}
		 
		this.$msgCartList.html(str_cart_list);

		var str_amount =  "Итого: <span>"+GLB.CART.the_total()+"</span>";
		this.$msgAmount.html(str_amount);

	}
};


