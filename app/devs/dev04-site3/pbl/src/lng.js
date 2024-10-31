import {GLB} from './glb.js';

export var LNG = {init:function(lang){
		
		var lang = lang?lang:"en";		

		this.CURRENT_LANG = lang.toLowerCase();
		this.ARR_LANGS = ['en','ru','it'];
		this.LANG_CURRENT_INDEX = this.get_index(lang);		

		this.LANG_DATA = {
	
			lng_amount:['Total amount:','Сумма:'],
			lng_address:['Address:','Адрес:'],
			lng_tel:['Ph.:','Тел.:'],
			
			lng_back:['Back','Назад'],
			lng_save:['Save','Сохранить'],
			lng_choose:['Choose','Выбрать'],			
			lng_bookmarks:['Bookmarks','Закладки'],
			lng_close:['Close','Закрыть'],				
			lng_day_after_tomorrow:['Day after tomorrow','Послезавтра'],
			lng_now:['Now','Сейчас'],
			
			lng_no_items:['no items','нет товаров'],
			lng_in_an_hour:['In an hour','Через час'],
			lng_in_two_hours:['In 2 hours','Через 2 часа'],
			lng_in_three_hours:['In 3 hours','Через 3 часа'],

			lng_order_comments:['Comments to the order:','Комментарии к заказу:'],
			lng_send:['Send','Отправить'],			

			lng_more:['More...','Подробнее...'],
			lng_ordering:['Ordering','Оформление заказа'],
			lng_shopping_cart:['Your Cart','Корзина'],
			lng_sending_an_order:['Sending an order','Отправка заказа'],
			lng_rollup:['Roll-Up','Свернуть'],
			lng_today:['Today','Сегодня'],
			lng_tomorrow:['Tomorrow','Завтра'],
			lng_to_clear:['Clear','Очистить'],
			lng_to_bookmarks:['To bookmarks','В закладки'],
			lng_total:['Total:','Итого:'],

			lng_your_order:['Your order','Ваш заказ'],
			lng_you_ordered:['You ordered:','Вы заказали:'],
			lng_flag_spicy:['Spicy','Острое'],
			lng_flag_hit:['Hit','Хит'],
			lng_flag_vege:['Vege','Веге'],			

			//VIEW_ALLMENU
			lng_our_adress:['Our address:','Наш адрес:'],
			lng_chef_cook:['Chef-cook:','Шеф-повар:'],
			lng_work_hours:['Work hours:','Часы работы:'],
			lng_motto:['QR-Menu – simple!', 'QR-Меню – просто!'],
			lng_cafe_desc_title:['Welcome!', 'Добро пожаловать!'],
			
			//VIEW_CART				
			lng_cart_is_off:['The cart disabled <br>by admin', 'Корзина отключена <br>администратором'],
			lng_cart_is_empty:['The cart is empty', 'Корзина пуста'],
			lng_clear_shopping_cart:['Clear shopping cart', 'Очистить корзину'],
			lng_proceed_to_checkout:['Continue to<br>Checkout', 'Оформить<br>заказ'],	
			
			//VIEW_CHOOSING_MODE			
			lng_pick_up_the_order_myself:['I`ll pick it up<br> myself', 'Заберу сам'],
			lng_choosing_delivering:['Choose time and <br>place of delivery', 'Выбрать время<br>и место доставки'],

			//VIEW_THANK_YOU
			lng_your_order:['Your order','Ваш заказ'],
			lng_thank_you:['Thank you','Спасибо'],

			//VIEW_ORDERING
			lng_your_phone:[ 'My phone:', 'Мой телефон:'],
			lng_need_user_phone:[
				'Please provide your phone to clarify the details of the order',
				'Внимание, укажите свой телефон для уточнения деталей заказа'
			],
			lng_where_to_deliver:[ 'Where to deliver:', 'Куда доставить:'],
			lng_need_user_address:[
				'Please specify the delivery address of the order',
				'Внимание, укажите адрес доставки заказа'
			],

			lng_date_time_to:['When would you like your order:', 'Дата и время доставки:'],
			lng_pick_it_up_at:['I`ll pick up an order:', 'Заберу заказ:'],
			lng_your_shopping_cart_is_empty:[
				'There are no products in your shopping cart',
				'В вашей корзине нет товара'
			],
			lng_for_total_cost:[ 'for total cost:', 'на общую сумму:'],
			lng_in_your_shopping_cart:[ 'In your shopping cart:', 'В вашей корзине:'],

			lng_all_months:[
				"January-February-March-April-May-June-July-August-September-October-November-December",
				"Января-Февраля-Марта-Апреля-Мая-Июня-Июля-Августа-Сентября-Октября-Ноября-Декабря"
			],

			//VIEW_ORDER_OK
			lng_number_of_your_order:['Your order number:', 'Номер вашего заказа:'],
			lng_time_from:['Order created:', 'Заявка создана:'],
			lng_time_to:['Order to:', 'Заказ на:'],			
			lng_near_time:['soon', 'ближайшее время'],
			lng_order_sent:['Order sent', 'Заказ отправлен'],
			lng_thankful_msg:[
				'The manager will contact you to confirm the order as soon as posible',
				'В ближайшее время наш менеджер свяжется с вами для подтверждения заказа'
			],
			lng_thankful_waiter_msg:[
				'Great! Our waiter is already in a hurry',
				'Отлично! Официант уже спешит к вам'
			],						
			lng_menu_is_in_demo_mode_msg:[
				'The menu is in demo mode. The cafe administrator is not waiting a messages at the moment.',
				'Меню находится в демонстрационном режиме. Администратор кафе не ожидает сообщений в данный момент.'
			],			
			lng_menu_is_in_demo_mode_msg_2:[
				'If you are an Administrator, set up Telegram to receive orders.',
				'Если вы Администратор, настройте Телеграм, чтобы получать заказы.'
			],						
			//VIEW_ORDER_CANCEL
			lng_something_wrong:[
				'Something went wrong. Order can not be shipped now. <br>Try later',
				'Что-то пошло не так. Заказ не может быть отправлен сейчас. <br>Попробуйте позже'
			],
			lng_wrong_phone:[
				'Order can not be shipped. <br>Check the correctness of the phone you entered, please.',
				'Заказ не может быть отправлен. <br>Проверьте правильность введенного вами телефона.'
			]
			
		}

	},
	update:function(lang){
		this.LANG_CURRENT_INDEX = this.get_index(lang);
	},
	get_index(lang){
		var index = 0;
		var arr = this.ARR_LANGS;
		var lang = lang.toLowerCase();
		for(var i=0;i<arr.length;i++){
			if(lang===arr[i]){ index = i; }
		};
		return index;
	},
	get_current:function() {
		return this.CURRENT_LANG;
	},
	//public
	get_lang:function(){
		return this.ARR_LANGS[this.LANG_CURRENT_INDEX];
	},
	get:function(var_name){
		var var_name = var_name.toLowerCase();
		if(this.LANG_DATA[var_name]){
			return this.LANG_DATA[var_name][this.LANG_CURRENT_INDEX];
		}else{
			return "Unknown";
		}
	}
};