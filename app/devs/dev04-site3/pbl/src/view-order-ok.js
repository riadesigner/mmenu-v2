import {GLB} from './glb.js';
import $ from 'jquery';
import {THE_ORDER_CHECKER} from './the-order-checker.js';

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
		this.$msgOrderSentDelivery = this.$view.find(this._CN+"order-ok-report-delivery");		
		this.$msgCartList = this.$view.find(this._CN+"order-ok-cart-list");
		this.$totalCost =  this.$view.find(this._CN+"order-ok-total-cost");		
		this.$tplOrderedItem = $("#mm2-templates "+this._CN+"ordered-item");

		this.$operator_message =  this.$view.find(this._CN+'order-ok-operator-message');
		this.$progressbar = this.$view.find(this._CN+"order-wating-progress-bar");

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

		this.set_operator_message_to_start();
		this.progress_bar_restart();
		
		this.TABLE_MODE = opt&&opt.table_number?true:false;
		this.IIKO_MODE = GLB.CAFE.is_iiko_mode();		
		this.PICKUPSELF_MODE = opt&&opt.pickupself_mode?true:false;	
		this.order = order;
		this.update_template_part_common();
		!this.TABLE_MODE && this.update_template_part_delivery();		
		this.build_ordered_list(this.order.order_items);

		GLB.CART.cart_clear();
		GLB.VIEW_ORDERING.update({clear:true});		

		if(!this.order.demo_mode && !this.order.notg_mode){
			this.check_the_order_status(this.order.short_number, GLB.CAFE.get('uniq_name'));
		}
		
		this.chefsmenu.end_loading();

	},

	update_template_part_common(){
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

		var msg = [
			"<h2>"+GLB.LNG.get("lng_number_of_your_order")+"</h2>",
			"<h3>"+this.order.short_number+"</h3>"
		].join("\n");
				
		this.$msgReport.html(msg);		
		
	},
	update_template_part_delivery:function(){

		var need_time = this.order.order_time_need===this.order.order_time_sent;
		var need_time_str = need_time ? GLB.LNG.get('lng_near_time') : this.formatLngTime(this.order.order_time_need);		

		let order_str = "";
		const addr = this.order.order_user_full_address;
		if(this.IIKO_MODE){			
			// IIKO MODE ADDRESS	
			const addr_entrance = addr.u_entrance?`, подъезд ${addr.u_entrance}`:"";
			const addr_floor = addr.u_floor?`, эт. ${addr.u_floor}`:"";
			const addr_flat = addr.u_flat?`, кв. ${addr.u_flat}`:"";
			order_str = `ул. ${addr.u_street}, д. ${addr.u_house}${addr_flat}${addr_floor}${addr_entrance}.`;
		}else{
			// CHEFSMENU MODE ADDRESS
			order_str = addr.description;
		};

		let delivery_str = this.PICKUPSELF_MODE? `<span>Доставка: </span> Заберу сам.<br>`: `<span>${GLB.LNG.get("lng_address")}</span> ${order_str}<br>`;

		var currency = GLB.CAFE.get('cafe_currency').symbol;

		var msg2 = [
			"<p>",
				"<span>"+GLB.LNG.get("lng_time_from")+"</span> "+ this.formatLngTime(this.order.order_time_sent)+"<br>",
				"<span>"+GLB.LNG.get("lng_time_to")+"</span> "+ need_time_str+"<br>",
				"<span>"+GLB.LNG.get("lng_amount")+"</span> "+this.order.order_total_price+" "+currency+"<br>",
				"<span>"+GLB.LNG.get("lng_tel")+"</span> "+this.order.order_user_phone+"<br>",
				delivery_str,
				"<br>"+this.order.order_user_comment,
			"</p>"
		].join("\n");

		this.$msgOrderSentDelivery.html(msg2);
	
	},

	set_operator_message_to_start:function(){
		this.$operator_message.removeClass('bright');
		const waiting_message = 'Отлично! Ваш заказ отправлен и скоро будет принят в работу';
		this.$operator_message.find('span').html(waiting_message)
	},
	set_operator_message_to:function(msg){
		this.$operator_message.addClass('bright');
		this.$operator_message.find('span').html(msg);
	},	

	progress_bar_to_end:function(){
		this.statusbarIsStopped = true;
		const $b = this.$progressbar.find('div');
		$b.css({width:'100%',transition:'1s'});
	},
	progress_bar_restart:function(){
		this.$progressbar.show();		
		const $b = this.$progressbar.find('div');
		$b.css({width:'0%',transition:'0s'});

		const overTime = SITE_CFG.order_forgotten_delay*60;
		this.statusbarIsStopped = false;

		const fn = {
			animateProgressBar: (n, progressBar)=>{
								
				let startTime = performance.now(); // Время начала анимации
				let duration = n * 1000; // Перевод секунд в миллисекунды
			
				this.statusbarIsStopped = false; // Сбрасываем флаг остановки
				// Запускаем анимацию
				requestAnimationFrame(fn.update.bind(this, progressBar, startTime, duration)); 
			},
			update:(progressBar, startTime, duration) => {
				if (this.statusbarIsStopped) return; // Прерываем анимацию, если флаг установлен
		
				let elapsedTime = performance.now() - startTime; // Прошедшее время
				let progress = Math.min(elapsedTime / duration, 1); // От 0 до 1
		
				progressBar.style.width = (progress * 100) + "%"; // Устанавливаем ширину
		
				if (progress < 1) {
					// Запускаем следующий кадр
					requestAnimationFrame(fn.update.bind(this, progressBar, startTime, duration)); 
				}else{
					this.timeout_for_taking_the_order();
				}
			}			
		}

		console.log('overTime = ',overTime);
		fn.animateProgressBar(overTime, $b[0]);


	},
	formatLngTime:function(tm,full){
		var t = tm.split(" ");
		var d = t[0].split("-");
		// var time = t[1];
		var arr = GLB.LNG.get("lng_all_months").split("-");
		var year = full ?" "+d[2]:"";
		return d[0]+" "+arr[parseInt(d[1],10)-1]+year+", "+t[1];
	},

	build_ordered_list:function(order_items) {
		const currency = GLB.CAFE.get('cafe_currency').symbol;
		const IIKO_MODE = GLB.CAFE.is_iiko_mode();

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
				const price_with_modifiers = parseInt(row.price_with_modifiers, 10);				
				const price = parseInt(row.price, 10); 

				const weight = `${row.volume} ${row.units}`;
				let volume_str = IIKO_MODE ? `${row.sizeName} / ${weight}` : weight;
				volume_str+= `, ${price} ₽`;

				const modifiers = row.chosen_modifiers;
				let modifiers_str = "";
				if(IIKO_MODE && modifiers && modifiers.length){
					for (let i in modifiers){
						let mod_str = `+ ${modifiers[i].name}, ${modifiers[i].price} ₽<br>`;
						modifiers_str += mod_str;
					}
				};				
				const price_str = `${count} x ${price_with_modifiers}  ₽`;
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

	},
	check_the_order_status:function(short_number, cafe_uniq_name){
		this.ORDER_CHECKER = $.extend({},THE_ORDER_CHECKER);	
		this.ORDER_CHECKER.check_if_order_taken_async(short_number, cafe_uniq_name)
		.then((vars)=>{
			console.log('--vars--',vars);				
			if(vars.order_status==='taken'){
				this.show_successful_message(vars.order_manager_name);
			}
		})
		.catch((vars)=>{
			console.log('err',vars);
		});		
	},
	timeout_for_taking_the_order:function(){
		this.statusbarIsStopped = true;
		this.show_fail_message();
	},
	show_fail_message(){
		this.progress_bar_to_end();
		this.set_operator_message_to(`Внимание! <br>Заказ не взяли в работу.<br> Обратитесь к администратору!`);		
	},
	show_successful_message(order_manager_name){
		this.progress_bar_to_end();
		this.set_operator_message_to(`Заказ в работе! <br> Ваш официант – ${order_manager_name}`);		
	}
};



