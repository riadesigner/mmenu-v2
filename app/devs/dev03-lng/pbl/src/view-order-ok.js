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

		console.log('order, short_number',order,order.short_number)

		this.TABLE_MODE = opt&&opt.table_number?true:false;
		this.IIKO_MODE = GLB.CAFE.get().iiko_api_key!=="";		
		this.PICKUPSELF_MODE = opt&&opt.pickupself_mode?true:false;	

		if(order.demo_mode){
			this.$msgDemo.show();
			this.$msgManager.hide()
		}else if(order.notg_mode){
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
			"<h3>"+order.short_number+"</h3>"
		].join("\n");
				
		this.$msgReport.html(msg);

		
		if(!this.TABLE_MODE){

			console.log("ORDER-ORDER",order);

			var need_time = order.order_time_need==order.order_time_sent;
			var need_time_str = need_time ? GLB.LNG.get('lng_near_time') : fn.formatLngTime(order.order_time_need);		

			let order_str = "";
			if(this.IIKO_MODE){
				const addr = order.order_user_iiko_address;
				const addr_entrance = addr.u_entrance?`, подъезд ${addr.u_entrance}`:"";
				const addr_floor = addr.u_floor?`, эт. ${addr.u_floor}`:"";
				const addr_flat = addr.u_flat?`, кв. ${addr.u_flat}`:"";
				order_str = `ул. ${addr.u_street}, д. ${addr.u_house}${addr_flat}${addr_floor}${addr_entrance}.`;
			}else{
				order_str = order.order_user_address;
			};

			let delivery_str = this.PICKUPSELF_MODE? `<span>Доставка: </span> Заберу сам.<br>`: `<span>${GLB.LNG.get("lng_address")}</span> ${order_str}<br>`;

			var msg2 = [
				"<p>",
					"<span>"+GLB.LNG.get("lng_time_from")+"</span> "+ fn.formatLngTime(order.order_time_sent)+"<br>",
					"<span>"+GLB.LNG.get("lng_time_to")+"</span> "+ need_time_str+"<br>",
					"<span>"+GLB.LNG.get("lng_amount")+"</span> "+order.order_total_price+" "+currency+"<br>",
					"<span>"+GLB.LNG.get("lng_tel")+"</span> "+order.order_user_phone+"<br>",
					delivery_str,
					"<br>"+order.order_user_comment,
				"</p>"
			].join("\n");

			this.$msgOrderSentOther.html(msg2);

			this.chefsmenu.end_loading();

		};

		this.build_ordered_list(order.order_items);

		GLB.CART.cart_clear();
		GLB.VIEW_ORDERING.update({clear:true});

	},
	build_ordered_list:function(order_items) {
		const currency = GLB.CAFE.get('cafe_currency').symbol;
		const IIKO_MODE = GLB.CAFE.get().iiko_api_key!=="";

		// IIKO_MODE
		const fn = {
			iiko_buildArrRows:(order_items)=>{
				const $list = $('<ul></ul>');
				for(let i in order_items){
					if(order_items.hasOwnProperty(i)){
						let order = order_items[i]; 												
						if(order){		
							let $li = $('<li></li>');
							$li.append(fn.iiko_buildRow(order));
							$list.append($li);
						}						
					}
				};
				return $list;
			},
			iiko_buildRow:(order)=>{				
				let title = order.item_data.title;
				let count = order.count;
				let price = order.price;
				let uniq_name = order.uniq_name;
				let modifiers = order.chosen_modifiers;
				let volume = order.volume;
				if(order.sizeName) {volume = order.sizeName+" / "+volume;}
				let modifiers_str = "";
				if(modifiers.length){
					for (let i in modifiers){
						let mod_str = "+ "+modifiers[i].name+"<br>";
						modifiers_str += mod_str;
					}
				};				
				let $row = this.$tplOrderedItem.clone();
				$row.find(this._CN+"ordered-title__item").html(title);
				$row.find(this._CN+"ordered-title__volume").html(volume);				
				$row.find(this._CN+"ordered-title__modifiers").html(modifiers_str);								
				$row.find(this._CN+"ordered-quantity").html(count+" x "+price+" "+currency);
				return $row; 
			}
		};			

		// CHEFS_MODE
		if(!IIKO_MODE){
			let $list = $('<ul></ul>');			
			for(let i=0;i<order_items.length;i++){			
				const $li = $('<li></li>');
				const $row = this.$tplOrderedItem.clone();	
				const count = order_items[i].count;
				const price = order_items[i].price;				
				$row.find(this._CN+"ordered-title__item").html(`${i+1}.${order_items[i].title}`);
				$row.find(this._CN+"ordered-title__volume").html("").hide();				
				$row.find(this._CN+"ordered-title__modifiers").html("").hide();								
				$row.find(this._CN+"ordered-quantity").html(count+" x "+price+" "+currency);
				$li.append($row);
				$list.append($li);	
			};			
			this.$msgCartList.html($list);
		}else{
			const $list = fn.iiko_buildArrRows(order_items);			
			this.$msgCartList.html($list);
		};

		const TOTAL_PRICE = "Итого: "+GLB.CART.get_total_price()+" "+ currency;
		this.$totalCost.html(TOTAL_PRICE);

	}
};



