import {GLB} from './glb.js';
import $ from 'jquery';

export var VIEW_ORDER_OK = {
	init:function(options){
		
		this._init(options);
		
		this.$headerPhone = this.$view.find(this._CN+"header-phone");
		
		this.$btnBasket= this.$view.find(this._CN+"btn-basket");
		this.$btnClose = this.$view.find(this._CN+"btn-close-menu");
		this.$btnBack = this.$view.find(this._CN+"btn-back, "+this._CN+"std-header-btn-back, "+this._CN+"btn-close-items");

		this.$msgReport = this.$view.find(this._CN+"order-ok-report-main");
		this.$msgDemo = this.$view.find(this._CN+"order-ok-demo-message");
		this.$msgNotFoundTgUsers = this.$view.find(this._CN+"order-ok-notg-message");
		this.$msgManager = this.$view.find(this._CN+"order-ok-operator-message");		

		this.$msgOrderSentOther = this.$view.find(this._CN+"order-ok-report-other");
		this.$msgCartList = this.$view.find(this._CN+"order-ok-cart-list");
		this.$totalCost =  this.$view.find(this._CN+"order-ok-total-cost");
		
		this.$tplOrderedItem = $("#mm2-templates "+this._CN+"ordered-item");


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
	update:function(order, opt){		

		this.TABLE_MODE = opt&&opt.table_number?true:false;
		this.IIKO_MODE = GLB.CAFE.get().iiko_api_key!=="";		
		this.PICKUPSELF_MODE = opt&&opt.pickupself_mode?true:false;	
		this.order = order;

		if(this.order.demo_mode){
			this.$msgDemo.show();
			this.$msgManager.hide()
		}else if(this.order.notg_mode){
			this.$msgDemo.hide();
			this.$msgNotFoundTgUsers.show();
			this.$msgManager.hide()
		}else{
			this.$msgDemo.hide();
			this.$msgNotFoundTgUsers.hide();
			this.$msgManager.show()
		}

		var phone = GLB.CAFE.get('cafe_phone');
		phone!=="" ? this.$headerPhone.find(this._CN+"header-phone__text").html(phone) : this.$headerPhone.hide();

		if(phone){
			var ph = phone.replace(/[-() ]/g,"");
			this.$headerPhone.on("touchend click",function(){
				location.href="tel:"+ph;
				return false;
			});
		};

		var currency = GLB.CAFE.get('cafe_currency').symbol;

		

		var fn = {
			formatLngTime:function(tm,full){
				var t = tm.split(" ");
				var d = t[0].split("-");
				var time = t[1]; 				
				var arr = GLB.LNG.get("lng_all_months").split("-");
				var year = full ?" "+d[2]:"";
				return d[0]+" "+arr[parseInt(d[1],10)-1]+year+", "+t[1];
			}			
		};

		var msg = [
			"<h2>"+GLB.LNG.get("lng_number_of_your_order")+"</h2>",
			"<h3>"+this.order.short_number+"</h3>"
		].join("\n");
				
		this.$msgReport.html(msg);
		
		if(!this.TABLE_MODE){

			console.log("ORDER-ORDER",this.order);

			var need_time = this.order.order_time_need===this.order.order_time_sent;
			var need_time_str = need_time ? GLB.LNG.get('lng_near_time') : fn.formatLngTime(this.order.order_time_need);		

			let order_str = "";
			if(this.IIKO_MODE){
				const addr = this.order.order_user_iiko_address;
				const addr_entrance = addr.u_entrance?`, подъезд ${addr.u_entrance}`:"";
				const addr_floor = addr.u_floor?`, эт. ${addr.u_floor}`:"";
				const addr_flat = addr.u_flat?`, кв. ${addr.u_flat}`:"";
				order_str = `ул. ${addr.u_street}, д. ${addr.u_house}${addr_flat}${addr_floor}${addr_entrance}.`;
			}else{
				order_str = this.order.order_user_address;
			};

			let delivery_str = this.PICKUPSELF_MODE? `<span>Доставка: </span> Заберу сам.<br>`: `<span>${GLB.LNG.get("lng_address")}</span> ${order_str}<br>`;

			var msg2 = [
				"<p>",
					"<span>"+GLB.LNG.get("lng_time_from")+"</span> "+ fn.formatLngTime(order.order_time_sent)+"<br>",
					"<span>"+GLB.LNG.get("lng_time_to")+"</span> "+ need_time_str+"<br>",
					"<span>"+GLB.LNG.get("lng_amount")+"</span> "+this.order.order_total_price+" "+currency+"<br>",
					"<span>"+GLB.LNG.get("lng_tel")+"</span> "+this.order.order_user_phone+"<br>",
					delivery_str,
					"<br>"+this.order.order_user_comment,
				"</p>"
			].join("\n");

			this.$msgOrderSentOther.html(msg2);

			this.chefsmenu.end_loading();

		};

		this.build_ordered_list(this.order.order_items);

		GLB.CART.cart_clear();
		GLB.VIEW_ORDERING.update({clear:true});

	},
	build_ordered_list:function(order_items) {
		const currency = GLB.CAFE.get('cafe_currency').symbol;
		const IIKO_MODE = GLB.CAFE.get().iiko_api_key!=="";

		const orders = [...order_items];

		const fn = {
			buildArrRows:(orders)=>{
				const $list = $('<ul></ul>');
				for(let i in orders){
					if(orders.hasOwnProperty(i)){
						let item = orders[i]; 												
						if(item){		
							let $li = $('<li></li>');
							let $row = fn.buildRow(item);
							$li.append($row);
							$list.append($li);
						}						
					}
				};
				return $list;
			},
			buildRow:(row)=>{
				
				const title_str = row.item_data.title;
				const count = row.count;
				const price = row.price;				
				
				let volume_str = row.sizeName;				
				if(IIKO_MODE) {volume_str += `/ ${row.volume} ${row.units}`;}

				const modifiers = row.chosen_modifiers;
				let modifiers_str = "";
				if(IIKO_MODE && modifiers && modifiers.length){
					for (let i in modifiers){
						let mod_str = "+ "+modifiers[i].name+"<br>";
						modifiers_str += mod_str;
					}
				};				
				const price_str = count+" x "+price+" "+currency;
				const $row = this.$tplOrderedItem.clone();
				$row.find(this._CN+"ordered-title__item").html(title_str);
				$row.find(this._CN+"ordered-title__volume").html(volume_str);				
				IIKO_MODE && $row.find(this._CN+"ordered-title__modifiers").html(modifiers_str);								
				$row.find(this._CN+"ordered-quantity").html(price_str);
				return $row; 
			}
		};			

		const $list = fn.buildArrRows(order_items);			
		this.$msgCartList.html($list);

		const TOTAL_PRICE = "Итого: "+GLB.CART.get_total_price()+" "+ currency;
		this.$totalCost.html(TOTAL_PRICE);

	}
};



