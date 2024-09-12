import {GLB} from './glb.js';
import $ from 'jquery';
import {IIKO_ORDER_SENDER} from './iiko/iiko-order-sender.js';

export var VIEW_CART = {
	init:function(options) {

		this._init(options);

		this.CLASS_EMPTY = this.CN+"cart-isempty";
		this.READY_TO_ORDERING = false;
					
		this.$totalValue = this.$view.find(this._CN+"cart-total .value");

		this.$btnBasket= this.$view.find(this._CN+"btn-basket");
		this.$btnClose = this.$view.find(this._CN+"btn-close-menu");
		this.$btnBack = this.$view.find(this._CN+"btn-back, "+this._CN+"std-header-btn-back, "+this._CN+"btn-close-items");

		this.$btnClearCart = this.$view.find(this._CN+"btn-clear, "+this._CN+"header-btn-clear"); 
		this.$btnOrdering = this.$view.find(this._CN+"btn-ordering"); 		
		this.$itemsContainer = this.$view.find(this._CN+"cart-allitems"); 		
		this.update_ui();		
		this.clear_cart_container();
		this.behavior();

		return this;
	},
	behavior:function() {
		var _this = this;

		var arrMobileButtons = [
			this.$btnBasket,
			this.$btnBack,			
			this.$btnClose,	
			//		
			this.$btnClearCart,
			this.$btnOrdering
			];

		this._behavior(arrMobileButtons);

		this.$btnClearCart.on("touchend click",(e)=>{
			this.clear_cart();
			e.originalEvent.cancelable && e.preventDefault();
		});
		
		this.$btnBack.on("touchend click",(e)=> {
			GLB.UVIEWS.go_first();
			e.originalEvent.cancelable && e.preventDefault();
		});				
		
		this.$btnOrdering.on("touchend click",(e)=>{
			const IIKO_MODE = GLB.CAFE.iiko_api_key!=="";
			if(this.READY_TO_ORDERING && !this.chefsmenu.is_loading_now()){				
				if(IIKO_MODE && GLB.MENU_TABLE_MODE.is_table_mode()){
					this.chefsmenu.now_loading();
					this.iiko_send_order_to_table();
				}else{
					this.send_order();
				}
			};
			e.originalEvent.cancelable && e.preventDefault();
		});		

	},
	iiko_send_order_to_table:function(){

		let orders = GLB.CART.get_all();
		const table_number = GLB.MENU_TABLE_MODE.get_table_number();		
		
		this.IIKO_TBL_SENDER = $.extend({},IIKO_ORDER_SENDER);	
		this.IIKO_TBL_SENDER.send_to_table_async(orders,table_number)
		.then((vars)=>{
			const short_number = vars['short_number'];
			const demo_mode = vars['demo_mode'];
			const notg_mode = vars['notg_mode'];			
			const order_items = orders;
			const order = { short_number,demo_mode,order_items};
			this.chefsmenu.end_loading();			
			GLB.VIEW_ORDER_OK.update(order,{table_number});
			GLB.UVIEWS.set_current("the-order-ok");
		})
		.catch((vars)=>{
            this._show_modal_win(`Заказ не получается отправить. 
            	Обратитесь к администратору кафе.`);
			console.log('err',vars);
			setTimeout(()=>{
				this.chefsmenu.end_loading();
			},300);
		});

	},
	send_order:function() {
		const has_delivery = GLB.CAFE.has_delivery();
		console.log('!! has_delivery = ', has_delivery)
		if(!GLB.CAFE.has_delivery()){
			GLB.VIEW_ORDERING.update({pickupMode:true});
			GLB.UVIEWS.set_current("the-ordering");						
		}else{
			GLB.VIEW_CHOOSING_MODE.update();
			GLB.UVIEWS.set_current("the-choosing-mode");
		}
	},
	clear_cart:function(){
		GLB.CART.cart_clear();
		GLB.VIEW_ORDERING.update({clear:true});
		this.update("clear");
	},
	clear_cart_container:function(){		
		this.$itemsContainer.html("");
		if(!GLB.CART.get_total_orders()){
			this.$content.addClass(this.CLASS_EMPTY);
		}else{
			this.$content.removeClass(this.CLASS_EMPTY);
		}	
	},
	update:function(orderId) {
		var _this=this;

		const ALL_ORDERS = GLB.CART.get_all();			
		const IIKO_MODE = GLB.CAFE.get().iiko_api_key!=="";

		let totalPrice = GLB.CART.get_total_price();

		totalPrice>0?this.$btnOrdering.removeClass(_this.CN+"btn-disabled"):this.$btnOrdering.addClass(_this.CN+"btn-disabled");
		totalPrice>0?this.$btnClearCart.removeClass(_this.CN+"btn-disabled"):this.$btnClearCart.addClass(_this.CN+"btn-disabled");
		
		this.READY_TO_ORDERING = totalPrice>0?true:false;

		const fn_cart = {
			buildAll:()=>{
				this.ALL_ROWS = {};
				for(let i in ALL_ORDERS){
					if(ALL_ORDERS.hasOwnProperty(i)){
						let order = ALL_ORDERS[i]; 												
						order && fn_cart.buildRow(order);						
					}
				};
				setTimeout(()=>{
					var counter = 0;
					for(var i in this.ALL_ROWS){
						if(this.ALL_ROWS.hasOwnProperty(i)){
							var delta = counter*.2+.5+"s";
							this.ALL_ROWS[i].css({opacity:1,transform:"translateX(0)",transition:delta});
							counter++;
						}
					}
				},300);				
			},
			buildRow:(order)=>{				
				let title = order.item_data.title;
				let count = order.count;
				let price = order.price;
				let uniq_name = order.uniq_name;				
				let volume = order.sizeName;				
				
				// FOR IIKO_MODE ONLY
				let modifiers = order.chosen_modifiers;
				let modifiers_str = "";				
				if(IIKO_MODE && modifiers.length){
					for (let i in modifiers){
						let mod_str = "+ "+modifiers[i].name+"<br>";
						modifiers_str += mod_str;
					}
				};				

				let $row = this.$tplItem.clone().css({opacity:0,transform:"translateX(50px)"});
				$row.find(this._CN+"cart-title__item").html(title);
				$row.find(this._CN+"cart-title__volume").html(volume);				
				IIKO_MODE && $row.find(this._CN+"cart-title__modifiers").html(modifiers_str);
				$row.find(this._CN+"cart-quantity").html(count+" x "+price+" "+GLB.CAFE.get('cafe_currency').symbol);
				
				(function(uniq_name){
					let $btn1 = $row.find(_this._CN+"cart-more").on("touchend click",function(){ _this.add_to_cart(uniq_name); return false; });
					let $btn2 = $row.find(_this._CN+"cart-less").on("touchend click",function(){ _this.remove_from_cart(uniq_name); return false; });					
					$btn1 && $btn2 && GLB.MOBILE_BUTTONS.bhv([$btn1,$btn2]);
				})(uniq_name);

				this.$itemsContainer.append($row);							
				this.ALL_ROWS[uniq_name] = $row;				

			},
			updateRow:(uniq_name)=>{
				const order = ALL_ORDERS[uniq_name];
				if(!order){
					if(GLB.CART.is_empty()){
						this.update("clear");
					}else{
						this.ALL_ROWS[uniq_name].css({opacity:0,transform:"translateX(-50px)",transition:"0.3s"});
						setTimeout(()=>{ this.ALL_ROWS[uniq_name].remove();	},300);
					}
				}else{
					const count = order.count;
					const price = order.price;
					const str = count+" x "+price+" "+GLB.CAFE.get_currency().symbol;
					this.ALL_ROWS[uniq_name].find(this._CN+"cart-quantity").html(str);
				}
			}						
		};	
		

		if(!orderId){

			// -----------------------------
			//       BUILD NEW ORDER
			// -----------------------------

			this.clear_cart_container();
			
			fn_cart.buildAll();

			// if(IIKO_MODE){
			// 	fn_cart.buildAll();
			// }else{
			// 	fn.buildAll();
			// }			

		}else{
			if(orderId!=='clear'){

				// ----------------------------------
				//  UPDATE ONE ROW OF THE ORDER
				// ----------------------------------				
				// the same code for CHEFSMENU & IIKO
				fn_cart.updateRow(orderId);

			}else{

				// --------------------------------
				//   CLEARING CART VIEW CONTAINER
				// --------------------------------
				
				let counter = 0;
				for(let $row in this.ALL_ROWS){
					if(this.ALL_ROWS.hasOwnProperty($row)){
					let delta = counter*.2+.5+"s";
					this.ALL_ROWS[$row] && this.ALL_ROWS[$row].css({opacity:0,transform:"translateX(-50px)",transition:delta});
					counter++;
					}
				};
				setTimeout(()=>{this.clear_cart_container();},500);

			}
		};

		this.$totalValue.html(GLB.CART.the_total());

	},	
	add_to_cart:function(orderId){		
		GLB.CART.add_one(orderId);
		this.update(orderId);
	},
	remove_from_cart:function(orderId){		
		GLB.CART.remove_one(orderId);
		this.update(orderId);
	},
	update_ui:function(){		
		const IIKO_MODE = GLB.CAFE.iiko_api_key!=="";
		const str_btn_title = IIKO_MODE && GLB.MENU_TABLE_MODE.is_table_mode() ? 'Отправить<br>заказ' : 'Оформить<br>заказ';		
		this.$btnOrdering.html(`<span>${str_btn_title}</span>`);
	}
};