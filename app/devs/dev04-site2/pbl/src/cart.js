import {GLB} from './glb.js';
import $ from 'jquery';

export var CART = {
	init:function() {		

		this.CN = "mm2-";
		this._CN = "."+this.CN;				

		this.CLASS_CART_SWITCHED_OFF = 'mm2-cart-switched-off';

		this.$menu = $(this._CN+'menu');
		this.$btnCartFooter = $(this._CN+"btn-basket__text");
		this.cart_clear();		
		this.update_mode();
	},
	update_mode:function(){		
		if(this.is_off()){
			this.$menu.addClass(this.CLASS_CART_SWITCHED_OFF);	
		}else{
			this.$menu.removeClass(this.CLASS_CART_SWITCHED_OFF);	
		}		 
	},
	
	// new 
	// create_uniq_name:function(prefix) {
	// 	let uniq_by_time = Math.floor((Date.now() * Math.random()) / 1000).toString();			
	// 	return prefix+uniq_by_time;
	// },

	/**
	 * @param preorder: preorderObject
	 * preorderObject = { 
	 *   itemId:string;
	 *   uniq_name: string;
	 *   price: number;
	 *   count:number;
	 *   sizeName:string;
	 *   item_data:object;
	 *	 sizeName: string; / optional (IIKO)
	 *	 volume: number
	 *	 units: string (г|мл|л|кг)
	 *	 sizeId: string; / optional (IIKO)
	 *	 sizeCode: string; / optional (IIKO)			
	 *	 chosen_modifiers: string; / optional (IIKO)
	 * }
	*/
	add_preorder:function(preorder){						
		let total_in_cart = this.get_total_orders(preorder.itemId);
		const order = this.ALL_ORDERS[preorder.uniq_name];
		if(order){
			order.count+=preorder.count;
		}else{
			this.ALL_ORDERS[preorder.uniq_name] = preorder;
		}
		this.update_btn_cart();
		return total_in_cart + preorder.count;
	},
	get:function(orderId) {		
		let order = this.ALL_ORDERS[orderId];
		return order;
	},
	get_all:function(){
		return this.ALL_ORDERS;
	},
	get_total_orders:function(itemId) {
		// total positions in cart
		let total = 0;
		if(!itemId){
			// total in cart
			for(let i in this.ALL_ORDERS){
				if(this.ALL_ORDERS.hasOwnProperty(i)){
					let order = this.ALL_ORDERS[i];
					if(order){
						total+=order.count;
					}					
				}
			}
		}else{
			// total for only current item
			for(let i in this.ALL_ORDERS){
				if(this.ALL_ORDERS.hasOwnProperty(i)){
					let order = this.ALL_ORDERS[i];
					if(order && order.itemId==itemId){
						total+=order.count;
					}					
				}
			}
		};
		return total;
	},
	get_total_price:function() {		
		let total_price = 0;		
		for(let i in this.ALL_ORDERS){
			if(this.ALL_ORDERS.hasOwnProperty(i)){
				let order = this.ALL_ORDERS[i];
				if(order){
					total_price += (parseInt(order.price,10) * order.count);
				}
			}
		};				
		return total_price;
	},
	add_one:function(orderId){		
		let order = this.ALL_ORDERS[orderId];
		if(order){
			order.count++;
			this.update_btn_cart();
		}
	},
	remove_one:function(orderId){
		let order = this.ALL_ORDERS[orderId];
		if(order){
			order.count--;
			if(!order.count){ 				
				delete this.ALL_ORDERS[orderId];
			};
			this.update_btn_cart();				
		}
	},
	update_btn_cart:function() {
		let total = this.get_total_orders();		
		if(total>0){
			this.$menu.addClass('cart-is-full');	
		}else{			
			this.$menu.removeClass('cart-is-full');
		};		
		this.$btnCartFooter.html(this.the_total());		
	},
	the_total:function() {
		return `${this.get_total_price()} ${GLB.CAFE.get('cafe_currency').symbol}`;
	},
	cart_clear:function(argument) {
		this.ALL_ORDERS = {};		
		this.update_btn_cart();
	},
	is_off:function(){
		var IS_OFF = parseInt(GLB.CAFE.get('cart_mode'),10)!==1;
		return IS_OFF;
	},
	is_empty:function(){
		var t=0;
		var all_orders = this.get_all();
		for(var i in all_orders){ 
			if(all_orders.hasOwnProperty(i)){ t++; } 
		};
		return t==0;
	},
	the_items:function(){
		var items_total = this.get_total_orders();
		var lng = GLB.LNG.get_lang();
		
		var fn = {
			ru_parse:function(count){				
				var n = parseInt(count.toString().substr(-1),10);
				if(n==1){
					return count+" товар";
				}else if(n>1 && n<5){
					return count+" товара";
				}else{
					return count+" товаров";
				}
			},
			en_parse:function(count){
				var n = parseInt(count.toString().substr(-1),10);
				if(n==1){
					return count+" item";
				}else{
					return count+" items";
				}
			},
			it_parse:function(count){
				var n = parseInt(count.toString().substr(-1),10);
				if(n==1){
					return count+" item";
				}else{
					return count+" items";
				}
			}			
		};

		if(items_total){
			return fn[lng+"_parse"](items_total);	
		}else{
			return GLB.LNG.get("lng_no_items");
		}		
	}
};