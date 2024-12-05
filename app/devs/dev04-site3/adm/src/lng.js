export var LNG = {
	init:function(lang){
		
		this.LANG_CURRENT = this.get_index(lang);

		this.AUTO_LNG_COUNT = 0;

		this.LANG_DATA = {
			
			lng_change:['-','Изменить'],
			lng_choose:['-','Выбрать'],
			lng_add:['-','Добавить'],			
			lng_back:['-','Назад'],
			lng_rotate:['-','Повернуть'],
			lng_title:['-','Название'],
			lng_thank:['-','Спасибо'],
			lng_description:['-','Описание'],
			lng_price:['-','Стоимость'],
			lng_volume:['-','Объем'],
			lng_update:['-','Обновление!'],
			lng_save:['-','Сохранить'],
			lng_send:['-','Отправить'],
			lng_error:['-','Ошибка!'],	
			lng_currency:['-','Валюта'],			
			lng_symbol:['-','Символ'],		
			lng_attention:['-','Внимание!'],
			lng_close:['-','Закрыть'],
			lng_ok:['-','ОК'],
			lng_yes:['-','Да'],
			lng_no:['-','нет'],
			lng_awesome:['-','Отлично'],
			lng_cancel:['-','Отмена'],
			lng_edit:['-','Редактировать'],
			lng_hello:['-','Привет'],
			lng_delete:['-','Удалить'],
			lng_customize:['-','Настройки'],
			lng_help:['-','Помощь'],
			lng_flag_new:['-','Новинка'],
			lng_flag_hit:['-','Хит'],
			lng_flag_vege:['-','Веге'],
			lng_public_skin:['-','Оформление меню'],
			lng_public_skin_light:['-','Светлое оформление'],
			lng_public_skin_dark:['-','Темное оформление'],
			
			lng_untitled:['-','Без названия'],
			lng_something_wrong:['-','Неожиданная ошибка'],

			// MENU ICONS
			ico_main_dishes:['-','Основные блюда'],
			ico_soups:['Soup','Супы'],
			ico_salade:['Salade','Салаты'],
			ico_chicken:['Chicken','Птица'],
			ico_fish:['Fish','Рыба'],
			ico_steak:['Steak','Стейк'],
			ico_seafood:['Seafood','Морепродукты'],
			ico_bbq:['BBQ','Блюда на гриле'],
			ico_kebabs:['Kebabs','Шашлыки'],
			ico_pasta:['Pasta','Паста'],
			ico_pizza:['Pizza','Пицца'],
			ico_sushi_and_rolls:['Sushi and Rolls','Суши и Роллы'],
			ico_dumpling:['Dumpling','Дамплинги'],
			ico_asian_food:['Asian food','Азиатская кухня'],
			ico_sandwiches:['Sandwiches','Бутерброды'],
			ico_slicing:['Slicing','Нарезки'],
			ico_canapes:['Canape','Фуршет, канапе','Canape'],
			ico_fast_food:['Fast food','Бургеры, хот-доги'],			
			ico_ice_cream:['Ice cream','Мороженое'],
			ico_fruits:['Fruits','Фрукты'],
			ico_pancakes:['Pancakes','Блины'],
			ico_bakery:['Bakery','Выпечка'],
			ico_cakes:['Cakes','Торты'],
			ico_childrens_menu:['Children`s menu','Детское меню'],
			ico_tea:['Tea','Чай'],
			ico_coffee:['Coffee','Кофе'],
			ico_juice_and_water:['Juice and water','Сок и вода'],
			ico_cocktails:['Cocktails','Коктейли'],
			ico_beer:['Beer','Пиво'],
			ico_alcohol:['Alcohol','Алкоголь'],
			//new icons
			ico_khachapuri_1:['Khachapuri-1','Хачапури-1'],			
			ico_khachapuri_2:['Khachapuri-2','Хачапури-2'],			
			ico_khinkali:['Khinkali','Хинкали'],
			ico_chebureki:['Chebureki','Чебуреки'],
			ico_pita_1:['Pita-1','Пита-1'],
			ico_pita_2:['Pita-2','Пита-1'],
			ico_pita_3:['Pita-3','Пита-1'],
			ico_salade_1:['Salade-1','Салат-1'],
			ico_risotto_1:['Risotto-1','Ризотто-1'],
			ico_risotto_2:['Risotto-2','Ризотто-2'],
			ico_fastfood_1:['Fastfood-1','Фастфуд-1'],
			ico_fastfood_2:['Fastfood-2','Фастфуд-2'],
			ico_fastfood_3:['Fastfood-3','Фастфуд-3'],
			ico_fastfood_4:['Fastfood-4','Фастфуд-4'],

			//VIEW USER CUSTOMIZE PAGE

			lng_view_customize_all__change_cafe_info: [
				'-',
				'Здесь вы можете указать информацию о кафе: название, телефон и т.д.'
				],
			lng_view_customize_all__change_cafe_info_btn: ['-','Информация о кафе'],

			lng_view_customize_all__menu_link: [
				'-',
				'Здесь вы можете скачать QR-код и поделиться ссылкой на ваше меню'
				],
			lng_view_customize_all__menu_link_btn: ['-','Ссылка и QR-code'],

			lng_view_customize_all__change_pass:[
				'-',
				'Здесь вы можете изменить пароль для входа в эту панель управления:'
				],
			lng_view_customize_all__change_pass_btn:['-','Изменить пароль'],

			lng_view_customize_all__change_subdomain:['-','Выберите для вашего меню запоминающийся адрес:'],
			lng_view_customize_all__change_subdomain_btn:['-','Выбрать адрес'],

			lng_view_customize_all__get_contract:['-','Чтобы снять все ограничения тестового периода:'],
			lng_view_customize_all__get_contract_btn:['-','Снять ограничения'],

			lng_view_customizing_cart__view_title:['-','Настройка Корзины'],

			lng_view_customize_all__code_for_embed:['-','Здесь вы можете получить код для вставки меню на ваш собственный сайт'],
			lng_view_customize_all__code_for_embed_btn:['-','Код для встраивания'],	
			
			lng_view_customize_all__app_version:['-','Версия: <span>[app-version]</span>'],
			lng_view_customize_all__acc_exit:['-','Выйти из аккаунта'],
			lng_view_customize_all__main_customizing:['-','Главные настройки'],

			//VIEW CHANGE PASSWORD			
			lng_view_change_password__title:['-','Изменение пароля'],
			lng_view_change_password__new_pass:['-','Новый пароль'],
			lng_view_change_password__text1:[
				'-',
				[
					'-',
					'После этого перейдите на вашу почту и <strong>подтвердите</strong> смену пароля.'
				].join('')
			],

			//VIEW CHANGE SUBDOMAIN			
			lng_view_change_subdomain__title:['-','Изменение поддомена'],
			lng_view_change_subdomain__text1:['-','Адрес вашего меню:'],		
			lng_view_change_subdomain__text2:[
				'-',
				[
					'<p>Чтобы выбрать дополнительный, более запоминающийся адрес. ',
					'Напишите вместо <strong>yourname</strong> любое слово:</p>'
				].join('')
			],
			lng_view_change_subdomain__text3:[
				'-',
				'<p>Чтобы изменить его, напишите вместо <strong>yourname</strong> любое слово:</p>'
			],			
			lng_view_change_subdomain__text4:[
				'-',
				[
					'<p>Соблюдайте простые условия:</p><p>',
					'1. Минимальное количество символов – 3.<br>',
					'2. Максимальное количество символов – 10.<br>',
					'3. Разрешенные символы: все символы латинского алфавита, тире и цифры.<br>',
					'4. Имя должно начинаться с буквы или цифры.</p>'
				].join('')
			],
			lng_view_change_subdomain__text5:['-','На заметку'],
			lng_view_change_subdomain__text6:[
				'-',
				'У вашего меню есть также постоянный неизменный адрес, полученный при регистрации:'
			],

			//VIEW ADDING IIKO API KEY
			lng_view_iiko_customization__title:['-','Настройка iiko'],			
			lng_view_iiko_adding_api_key__title:['-','Подключение iiko'],	
						
			//VIEW CAFE LINK			
			lng_view_cafe_link__view_title:['-','Ссылка и QR-code'],
			lng_view_cafe_link__hard_link:['-','Адрес вашего меню:'],
			lng_view_cafe_link__qrcode:[
				'-',
				'Получите на почту и распечатайте этот QR-код вашего меню. Ваши посетители смогут за пару секунд открыть ваше меню у себя на телефоне.'
			],
			lng_view_cafe_link__tutor:[
				'-',
				'Вместе с qr-кодом вам на почту придет простая инструкция по применению.'
			],						
			lng_view_cafe_link__btn_get_qrcode:['-','Получить <nobr>QR-code</nobr>'],

			//VIEW CUSTOMIZING CAFE			
			lng_view_customizing_cafe__about:['-','<nobr>О кафе</nobr>'],
			lng_view_customizing_cafe__title:['-','Название кафе:'],
			lng_view_customizing_cafe__address:['-','Адрес:'],
			lng_view_customizing_cafe__cook:['-','Шеф повар:'],
			lng_view_customizing_cafe__phone: ['-','Телефон:'],
			lng_view_customizing_cafe__work_hours: ['-','Часы работы:'],
			lng_view_customizing_cafe__description:['-','Краткое описание'],			

			// VIEW GET CONTRACT
			lng_view_get_contract__remove_limits:['-','Снять ограничения:'],		
			lng_view_get_contract__connect:['-','Подключиться'],

			// VIEW GET CODE
			lng_view_get_code__code_for_embed:['-','Код для встраивания'],
			lng_view_get_code__text1:[
				'-',
				[
				'<p>У вашего меню есть свой постоянный адрес. Однако, вы возможно захотите встроить это меню на свой собственный сайт.</p>',
				'<p>Сделать это очень просто.</p>',
				'<p>Для сайтов на основе <strong>WordPress</strong> достаточно установить <strong>бесплатный плагин</strong> и в любом месте страницы ввести уникальный код вашего меню:</p>'
				].join('')				
			],
			lng_view_get_code__text2:[
				'-',
				[
				'<p>Для всех остальных сайтов подключить меню также просто.</p>',
				'<p>Получите код и простую <strong>универсальную инструкцию</strong> подключения на почту, а также ссылку на плагин для WordPress.</p>'
				].join('')
			],

			btn_get_code:['-','Получить код'],
			
			//VIEW CUSTOMIZE INTERFACE
			lng_view_customize_interface__view_title:['-','Настройка интерфейса'],
			lng_view_customize_interface__select_lng:['-','Выберите язык интерфейса'],
			lng_customize:[
				'-',
				'Эти настройки повлияют только на интерфейс админки'
			],
			
			lng_view_customize_interface__cart_mode:['-','Выберите режим корзины на сайте'],
			
			lng_light_skin:['-','Светлое оформление'],
			lng_dark_skin:['-','Темное оформление'],
			
			//VIEW ADD/EDIT MENU
			// lng_view_edit_menu__edit_title:['-','Название раздела'],
			// lng_view_edit_menu__select_menu_icon:['-',''],
			lng_view_edit_menu__edit:['-','Редактирование раздела'],
			lng_view_edit_menu__add:['-','Добавление раздела'],
			lng_required_field:['-','Введите название раздела'],
			// lng_view_edit_menu__error_add:[
			// 	'-',
			// 	''
			// 	],			
			// lng_view_edit_menu__error_limit:[
			// 	'-',
			// 	''],

			//VIEW ADD/EDIT ITEMS
			// lng_view_edit_item__modifiers:['-','Модификаторы:'],
			lng_view_edit_item__menu_section:['-','Здесь вы можете изменить раздел в котором находится это блюдо:'],
			lng_item_edit:['-','Редактирование блюда'],			
			lng_item_add:['-','Добавление блюда'],						
			lng_tochoose_image:['-','Выберите изображение:'],
			lng_tochange_image:['-','Заменить изображение:'],
			lng_required_item_title:['-','Введите название блюда'],
			lng_view_edit_item__error_add:[
				'-',
				'<p>Невозможно добавить блюдо сейчас.</p><p>Попробуйте позже или сообщите в поддержку.</p>'],
			lng_view_edit_item__error_edit:[
				'-',
				'<p>Невозможно сохранить блюдо сейчас.</p><p>Попробуйте позже или сообщите в поддержку.</p>'],

			//VIEW ITEM'S IMAGE CHANGE
			lng_view_item_image_change__title:['-','Замена изображения'],
			
			// VIEW ALL ITEMS
			lng_no_title:['-','Без названия'],
			lng_no_description:['-','Нет описания'],
			lng_view_all_items__is_empty:['-','Этот раздел пока пустой.<br>Добавьте сюда блюдо.'],

			// VIEW REPLACING PARENT SECTION
			lng_replacing_parent_section:['-','Выбор раздела меню'],
			lng_choose_parent_menu:['-','Выберите раздел, в котором будет находиться это блюдо'],

			// VIEW ALL MENU			
			lng_view_all_menu_control__settings:['-','Настройки'],
			lng_limits_total_section__test:[
				'-',
				'<p>Количество разделов ограничено тестовым режимом.</p><p>Откройте «Помощь», чтобы ознакомиться со всеми возможностями и ограничениями сервиса.</p>'
			],
			lng_limits_total_section__full:[
				'-',
				'<p>Добавление нового раздела ограничено.</p><p>Откройте «Помощь», чтобы ознакомиться со всеми возможностями и ограничениями сервиса.</p>'
			],
			lng_limits_items_in_section__test:[
				'-',
				'<p>Количество блюд в разделе ограничено тестовым режимом.</p><p>Откройте «Помощь», чтобы ознакомиться со всеми возможностями и ограничениями сервиса.</p>'
			],
			lng_limits_items_in_section__full:[
				'-',
				'<p>Количество блюд в разделе ограничено.</p><p>Откройте «Помощь», чтобы ознакомиться со всеми возможностями и ограничениями сервиса.</p>'
			],			

			// MISC
			lng_view_replacing_parent_section__view_title:['-','Выбор раздела'],
			
			lng_view_cafe_description__view_title:['-','Краткое описание'],
			lng_view_cafe_description__about:['-','О кафе:'],

			lng_view_main_help__send_question:['-','Отправить вопрос'],
			lng_view_main_help__links:[
				'-',
				'Здесь собраны ссылки на видео с ответами на различные вопросы по управлению вашим меню.'
				],
			lng_view_main_help__have_questions:	[
				'-',
				'Остались вопросы? Напишите нам прямо здесь. Мы ответим на вашу почту [email]:'
				],

			lng_confirm_delete:['-','Подтвердите удаление'],
			lng_select_action:['-','Выберите действие для'],
			lng_del_menu:['-','Удаление меню'],
			lng_del_cafe:['-','Удаление кафе'],
			lng_no_one_cafe:['-','Для начала добавьте кафе'],
			lng_limit_adding_cafe:[
				'-','На данный момент для одного аккаунта возможно добавить только три кафе'
			],
			lng_err_add_menu:['-','Ошибка добавления меню'],
			lng_min_cafe:[
				'-','Необходимо оставить как минимум одно кафе. Вы всегда можете изменить его.'
			],
			lng_min_menu:[
				'-','Необходимо оставить как минимум  один раздел. Вы всегда можете изменить его'
			],
			lng_load_large_image:[
				'-','Загрузка большого изображения'
			],
			lng_min_pass_length:[
				'-','Минимальная длина пароля - 6 символов'
			],
			lng_passwords_donot_match:[
				'-','Новый пароль не должен совпадать с текущим паролем'
			],

			lng_some_err:['-','Ой, что-то не так'],
			lng_unknown_user:['-','Неизвестный пользователь'],
			lng_unknown_data:['-','Неизвестное значение'],
			lng_err_password:['-','Неправильно введен текущий пароль'],
			lng_pass_too_short:['-','Пароль слишком короткий'],
			lng_cannot_save:['-','Невозможно сохранить сейчас'],
			lng_not_specified_cafe:['-','Не указано кафе'],
			lng_unknown_cafe:['-','Неизвестное кафе'],
			
			lng_external:['-','Открыть сайт кафе'],


			//ERRORS

			err_code_106:[
				'-',
				'Изображение слишком мало. Рекомендуется не менее 2Mpx (1600x1200px)'
			],

			err_code_107:[
				'-',
				'Изображение слишком велико. Рекомендуется не больше 16Mpx'
			]


		}

	},
	update:function(lang){
		this.LANG_CURRENT = this.get_index(lang);
	},
	get_index:function(lang){
		var index = 0;
		var arr = ['en','ru'];
		var lang = lang.toLowerCase();
		for(var i=0;i<arr.length;i++){
			if(lang===arr[i]){
				index = i; 
			}
		};
		return index;
	},
	//public
	get:function(var_name){
		if(typeof var_name == 'object' && var_name.length > 0 ){			
			return var_name[this.LANG_CURRENT];
		}else{
			var var_name = var_name.toLowerCase();
			if(this.LANG_DATA[var_name]){
				return this.LANG_DATA[var_name][this.LANG_CURRENT];
			}else{
				return "Unknown";
			}			
		}
	},
	// get_current:function() {
	// 	return this.LANG_CURRENT;
	// },
	add:function(arr_msg){
		this.AUTO_LNG_COUNT++;
		var uniq = 'auto_lang_'+this.AUTO_LNG_COUNT;
		this.LANG_DATA[uniq] = arr_msg;
		return '<div lang="'+uniq+'">' + arr_msg[this.LANG_CURRENT] + '</div>';
	}

};