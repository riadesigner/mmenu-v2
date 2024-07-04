<!DOCTYPE html>
<head>
	<title>dev admin</title>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"> 
<meta name="apple-mobile-web-app-capable" content="yes">

<link href="https://fonts.googleapis.com/css?family=PT+Sans&subset=latin-ext" rel="stylesheet">

<base href="<?=$CFG->base_app_url;?>">

<meta name="robots" content="noindex, follow"/>

<link rel="apple-touch-icon" href="./adm/i/app-apple-icon.png">
<link rel="shortcut icon" href="./adm/favicon-adm.png" type="image/png">
<link rel="manifest" href="./adm/manifest.webmanifest<?=$ver;?>">

<link rel="stylesheet" href="./adm/css/style.css<?=$ver;?>" type="text/css">

<script src="<?=$CFG->base_url;?>jquery/jquery.min.js"></script>

<script src="./adm/dist/app-plugins.js<?=$ver;?>"></script>
<script src="./adm/dist/app.js<?=$ver;?>"></script>

<script type="text/javascript">

<?php
 
$ID_USER = Site::$User?Site::$User->id:'false';
$USER_EMAIL = Site::$User?Site::$User->email:'false';
// $USER_LANG = Site::$User?Site::$User->lang:'ru'; 
$USER_LANG = 'ru';

$APP_CFG = [
	'app_version'=>$CFG->version,
	'http'=>$CFG->http,
	'base_url'=>$CFG->base_app_url,
	'www_url'=>$CFG->wwwroot,
	'user_email'=>$USER_EMAIL,
	'id_user'=>$ID_USER,
	'user_lang'=>$USER_LANG,
	'contract'=>$CFG->contract_cost_in_rub,
	'limits'=>$CFG->limits,
	'site_links'=>$CFG->site_links
	];


$CART_BOT_NAME = $CFG->tg_cart_bot


?>

var CFG = JSON.parse('<?=json_encode($APP_CFG, JSON_UNESCAPED_UNICODE);?>');

App&&App();

</script>

</head>
<body >

<div id="the-app">
	<div class="the-app-wrapper">
		<div id="the-app-views"></div>
	</div>		
</div>

<div id="landscape-lock">
	<div class="landscape-lock-wrapper">
		<div class="landscape-lock-image"><img src="./adm/i/adm-phone.svg"></div>		
		<p>Поверните устройство в вертикальное положение</p><!-- lang? -->
	</div>
</div>


<div id="templates" style="display:none;">

<!-- - - - - - - - VIEW ALL MENU - - - - - - - -  -->

	<div class="app-view view-all-menu">
		

		<div class="app-view-header">
			<div class="view-header-title">
				<div class="view-header-title_icon"></div>
				<div class="view-header-title_text"></div>
			</div>
			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>
				<div class="view-header-buttons__button view-header-buttons__button_home"></div>
			</div>
		</div>
		
		<div class="app-view-title"><span>Панель управления</span><span>Управляется iiko</span></div>
		<div class="app-view-integration-btn">Загрузить <br>меню из iiko</div>

		<div class="app-view-page">
			<div class="app-view-page-container no-padding overflow-y">
				<!-- repos51 -->
			</div>
		</div>

		<div class="app-view-loader"><span></span></div>

		<div class="app-view-footer">
			<div class="button-section"></div>
			<div class="button-section"><div class="button customize"><span lang="lng_view_all_menu_control__settings"><!-- Настройки --></span></div></div>
			<div class="button-section">
				<div class="button add"><span lang="lng_add"><!-- Добавить --></span></div>
				<div class="button save hidden"><span lang="lng_save"><!-- Сохранить --></span></div>
			</div>
		</div>
	</div>

<!-- - - - - - - - VIEW ALL ITEMS - - - - - - - -  -->

	<div class="app-view view-all-items">

		<div class="app-view-header">
			<div class="view-header-title">
				<div class="view-header-title_icon"></div>
				<div class="view-header-title_text"></div>
			</div>
			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>
			</div>
		</div>

		<div class="app-view-page max-size">
			<div class="app-view-page-container no-padding">
				<div class="all-items-empty-message"><div lang="lng_view_all_items__is_empty"><!-- Этот раздел пока пустой.<br>Добавьте сюда блюдо. --></div></div>
				<div class="all-items-wrapper"><!-- ITEMS --></div>				
			</div>
		</div>
		
		<div class="app-view-loader"><span></span></div>

		<div class="app-view-footer">
			<div class="button-section"><div class="button back"><span lang="lng_back"><!-- Назад --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section">
				<div class="button add"><span lang="lng_add"><!-- Добавить --></span></div>
				<div class="button save hidden"><span lang="lng_save"><!-- Сохранить --></span></div>
			</div>
		</div>
	</div>

<!-- - - - - - - - VIEW GET CODE - - - - - - - - - - - - - - - -->

	<div class="app-view view-get-code">
		<div class="app-view-header">			
			
			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" lang="lng_view_get_code__code_for_embed"><!-- Код для встраивания --></div>
			</div>

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>
			</div>
		</div>
		<div class="app-view-page">		
			<div class="app-view-page-container overflow-y">
				<div class="std-form">
					<div lang="lng_view_get_code__text1">
						<!-- 
						<p>Несмотря на то, что для данного меню сайт необязателен. Вы, возможно, захотите встроить его на свой сайт.</p>
						<p>Сделать это очень просто.</p> 
						<p>Для сайтов на основе <strong>WordPress</strong> достаточно установить <strong>бесплатный плагин</strong> и в любом месте страницы ввести уникальный код вашего меню:</p>
						-->
					</div>
					<div class="std-form__bright_word view-get-code__code"><!-- [code] --></div>
					<div lang="lng_view_get_code__text2">
						<!-- 
						<p>Для всех остальных сайтов подключить меню также просто.</p>
						<p>Получите код и простую <strong>универсальную инструкцию</strong> подключения на почту, а также ссылку на плагин для WordPress.</p>
						-->
					</div>

				</div>
			</div>
		</div>
		
		<div class="app-view-loader"><span></span></div>

		<div class="app-view-footer">
			<div class="button-section"><div class="button back"><span lang="lng_back"><!-- Назад --></span></div></div>			
			<div class="button-section"></div>
			<div class="button-section"><div class="button get"><span lang="btn_get_code"><!-- Получить код --></span></div></div>
		</div>
	</div>

<!-- - - - - - - - VIEW CHANGE PASSWORD - - - - - - - - - - - - - - - -->

	<div class="app-view view-change-password">
		<div class="app-view-header">		

			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" lang="lng_view_change_password__title"><!-- Change password --></div>
			</div>

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>
			</div>
		</div>
		<div class="app-view-page">		
			<div class="app-view-page-container overflow-y">
				<div class="std-form">

					<p lang="lng_view_change_password__text1">
						<!-- Введите новый пароль и нажмите <strong>сохранить.</strong> 
						После этого перейдите на вашу почту и <strong>подтвердите</strong> смену пароля. -->
					</p>

					<div class="std-form__section-description" lang="lng_view_change_password__new_pass"><!-- New password --></div>
					<input class="std-form__input" type="input" placeholder="" name="new-password" maxlength="555" >					
				</div>						
			</div>
		</div>

		<div class="app-view-loader"><span></span></div>

		<div class="app-view-footer">
			<div class="button-section"><div class="button cancel"><span lang="lng_cancel"><!-- Cancel --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"><div class="button save"><span lang="lng_save"><!-- Save --></span></div></div>
		</div>
	</div>

<!-- - - - - - - - VIEW CHANGE SUBDOMAIN - - - - - - - - - - - - - - - -->

	<div class="app-view view-change-subdomain">
		<div class="app-view-header">		

			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" lang="lng_view_change_subdomain__title"><!-- Change subdomain --></div>
			</div>

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>				
			</div>
		</div>
		<div class="app-view-page">		
			<div class="app-view-page-container overflow-y">
				<div class="std-form">

					<div class="view-change-subdomain__best-address">
						<p>
							<span lang="lng_view_change_subdomain__text1"></span><br>
							<span class="view-change-subdomain__best-address_text"></span>
						</p>
					</div>

					<div class="view-change-subdomain__if-no-subdomain" lang="lng_view_change_subdomain__text2"></div>
					<div class="view-change-subdomain__if-has-subdomain" lang="lng_view_change_subdomain__text3"></div>					

					<div class="std-form__long_bright_word view-change-subdomain__preview"><!-- url preview --></div>
					<input class="std-form__input" type="input" placeholder="yourname" name="new-subdomain" maxlength="555" >

					<div lang="lng_view_change_subdomain__text4"></div>
					
					<div class="view-change-subdomain__if-has-subdomain">
						<div class="std-form__subtitle" lang="lng_view_change_subdomain__text5"></div>
						<div class="view-change-subdomain__allways-address">
							<p>
								<span lang="lng_view_change_subdomain__text6"></span><br>
								<span class="view-change-subdomain__allways-address_text"></span>								
							</p>
						</div>
					</div>

				</div>						
			</div>
		</div>

		<div class="app-view-loader"><span></span></div>

		<div class="app-view-footer">
			<div class="button-section"><div class="button cancel"><span lang="lng_cancel"><!-- Cancel --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"><div class="button save"><span lang="lng_save"><!-- Save --></span></div></div>
		</div>
	</div>

<!-- - - - - - - - VIEW ADDING IIKO API KEY - - - - - - - - - - - - - - - -->

	<div class="app-view view-iiko-adding-api-key">
		<div class="app-view-header">		

			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" lang="lng_view_iiko_adding_api_key__title"><!-- iiko connection --></div>
			</div>

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>				
			</div>
		</div>
		<div class="app-view-page">		
			<div class="app-view-page-container overflow-y">
				<div class="std-form">

					<p><strong>Для подключения iiko:</strong></p>	
					<ol>
						<li>Создайте Внешнее Меню в <a href="#">iikoWeb</a>, если у вас его еще нет.</li>
						<li>Получите API ключ в <a href="#">iikoIcloud</a>, специально для ChefsMenu.</li>
						<li>Вставьте полученный API ключ в поле ниже и нажмите кнопку «Сохранить». </li>
					</ol>
					
					<input class="std-form__input" type="input" placeholder="0000000-000" name="iiko-api-key" maxlength="100" >

					<p>После этого, меню перейдет под управление сервиса iiko и будет по расписанию подгружать из него последнюю версию Внешнего меню.</p>

					<p><strong>Подробности:</strong></p>

					<p>Для то, чтобы узнать как работает ChefsMenu в связке c платформой iiko, какие преимущества это дает
					 и как подключиться, посетите <a href="#">эту страницу.</a></p>

				</div>						
			</div>
		</div>

		<div class="app-view-loader"><span></span></div>

		<div class="app-view-footer">
			<div class="button-section"><div class="button cancel"><span lang="lng_cancel"><!-- Cancel --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"><div class="button save"><span lang="lng_save"><!-- Save --></span></div></div>
		</div>
	</div>

<!-- - - - - - - - VIEW IIKO CUSTOMIZATION - - - - - - - - - - - - - - - -->

	<div class="app-view view-iiko-customization">
		<div class="app-view-header">		

			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" lang="lng_view_iiko_customization__title"><!-- iiko connection --></div>
			</div>

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>				
			</div>
		</div>
		<div class="app-view-page">		
			<div class="app-view-page-container overflow-y">
				<div class="std-form">
					
					<h1>Ваш iiko API Login:</h1>
					
					<button name='iiko-api-key' class="std-special-single-button">&nbsp;</button>
					<h2>Общая информация</h2>

					<div class="iiko-general-information"></div>					
					
					<h2>Внешние меню</h2>
					<p>Выберите внешнее меню из имеющихся:</p>					 
					<div class="iiko-extmenu-list">
						<div label="menu-10101" class="std-form__radio-button ">Меню тест 1</div>	
						<div label="menu-10344" class="std-form__radio-button checked">Меню тест 2</div>
					</div>				

					<h2>Терминалы</h2>					
					<p>Терминальные группы, используемые для отправки заказов: </p>					
					<ul class="iiko-terminals-sections">
						<li>Не найдено.</li>
					</ul>
					
					<h2>Столы</h2>					
					<p>В вашем ресторане есть секции: </p>					
					<ul class="iiko-table-sections">
						<li>Не найдено. Обновите кол-во столов.</li>
					</ul>					
					<div class="std-form__wide-button btn-iiko-tables-update">Обновить столы</div>

					<h2>QR-коды</h2>					
					<p>Вы можете сгенерировать и распечатать QR-коды Меню с выбранным заранее номером столика. 
						Подробнее <a name="link-iiko-qrcode-tables" href="#">об этом здесь.</a></p>					
					<div class="std-form__wide-button btn-iiko-get-qrcodes">QR-коды для столов</div>

					<h2>Синхронизация</h2>
					<p>Здесь вы можете выбрать время автоматической синхронизации ChefsMenu c Внешним Меню iiko.</p>					
					<div>
						<ul>
							<li>03:00</li>
						</ul>
					</div>

					<h2>Отвязать iiko</h2>
					<p>Чтобы удалить API Login и отвязать сервис iiko от Chefsmenu, введите ниже слово 
						<strong>delete</strong> нажмите сохранить.</p>					
					<input class="std-form__input" type="input" placeholder="" name="iiko-del-key" maxlength="20" >

					<h2>Помощь</h2>
					<p>Узнать все подробности взаимодействия ChefsMenu и iiko и текущие 
						возможности интеграции вы можете <a href="#">по этой ссылке.</a></p>

				</div>						
			</div>
		</div>

		<div class="app-view-loader"><span></span></div>

		<div class="app-view-footer">
			<div class="button-section"><div class="button cancel"><span lang="lng_cancel"><!-- Cancel --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"><div class="button save"><span lang="lng_save"><!-- Save --></span></div></div>
		</div>
	</div>	

<!-- - - - - - - - VIEW IIKO MODIFIERS DICTIONARY - - - - - - - - - - - -->

	<div class="app-view view-iiko-modif-dictionary">
		<div class="app-view-header">		

			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text">Словарь модификаторов</div>
			</div>

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>				
			</div>
		</div>
		<div class="app-view-page">		
			<div class="app-view-page-container overflow-y">
				<div class="std-form">
										
					<div class="list-iiko-modif-dictionary__is-empty">
						<p>Список пуст.</p>
					</div>

					<div class="list-iiko-modif-dictionary">
						<ul class="std-form__structure">
							<li>
								<div class="input-title">С собой:</div>
								<input class="std-form__input" type="text" name="" placeholder="перевод">
							</li>
							<li>
								<div class="input-title">Томаты:</div>
								<input class="std-form__input" type="text" name="" placeholder="перевод">
							</li>							
						</ul>
					</div>
					
					<h2>Справка</h2>

					<p>По мере обновления Меню словарь модификаторов будет сам дополняться новыми словами. Вам нужно будет только добавлять корректный перевод этих слов.</p>
					<p>Если какой-то из модификаторов больше не нужен, введите знак минус вместо перевода и нажмите сохранить.</p>
				
				</div>						
			</div>
		</div>

		<div class="app-view-loader"><span></span></div>

		<div class="app-view-footer">
			<div class="button-section"><div class="button cancel"><span lang="lng_cancel"><!-- Cancel --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"><div class="button save"><span lang="lng_save"><!-- Save --></span></div></div>
		</div>
	</div>	

<!-- - - - - - - - VIEW IIKO QR-CODES WITH TABLES - - - - - - - - - - - - - -->

	<div class="app-view view-iiko-qrcode-with-tables">
		<div class="app-view-header">		

			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text">QR-коды для столиков</div>
			</div>

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>				
			</div>
		</div>
		<div class="app-view-page">		
			<div class="app-view-page-container overflow-y">
				<div class="std-form">					
					<h1>Заказы в стол</h1>
					<p>Здесь вы можете скачать QR-код Меню для конкретного столика. При отправке заказа из такого Меню, в заказе будет указано номер стола, с которого заказ отправлен.</p>
					<h2>Столы</h2>
					<p>Выберите нужные столы и нажмите кнопку <strong>Отправить</strong>. QR-коды будут сгенерированы и отправлены на почту администратора.</p>
					
					<div class="iiko-qrcode-list-of-table-sections">
						<!-- btns -->
					</div>

				</div>						
			</div>
		</div>

		<div class="app-view-loader"><span></span></div>

		<div class="app-view-footer">
			<div class="button-section"><div class="button cancel"><span lang="lng_cancel"><!-- Cancel --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"><div class="button send"><span lang="lng_send"><!-- Send --></span></div></div>
		</div>
	</div>		

<!-- - - - - - - - VIEW MAIN HELP - - - - - - - - - - - - - - - -->

	<div class="app-view view-main-help">
		<div class="app-view-header">

			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" lang="lng_help"><!-- Help --></div>
			</div>

			<div class="view-header-buttons"></div>
		</div>
		<div class="app-view-page">			
			<div class="app-view-page-container overflow-y">
				<div class="std-form">
					<p lang="lng_view_main_help__links"></p>
					<div class="view_main_help__links-btn"></div>
					<p class="main-help-user-mail" lang="lng_view_main_help__have_questions"></p>
					<textarea class="std-form__textarea main-help-user-ask" rows="8" name="help-user-ask" maxlength="555"></textarea>
				</div>									
			</div>
		</div>
		<div class="app-view-loader"><span></span></div>
		<div class="app-view-footer">
			<div class="button-section"><div class="button close"><span lang="lng_close"><!-- Close--></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"><div class="button send"><span lang="lng_view_main_help__send_question"><!-- Отправить вопрос --></span></div></div>
		</div>
	</div>

<!-- - - - - - - - VIEW GET CONTRACT - - - - - - - - - - - - - - - -->

	<div class="app-view view-get-contract">
		<div class="app-view-header">

			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" lang="lng_view_get_contract__remove_limits"><!-- Снять ограничения --></div>
			</div>

			<div class="view-header-buttons"></div>
		</div>
		<div class="app-view-page">			
			<div class="app-view-page-container overflow-y">
				<div class="std-form">
										
					<div class="view-get-contract__text">

						<p>Ваше меню работает в тестовом режиме. В этот период действует ограничение услуг сервиса.</p>
						<p>Для снятия всех ограничений, мы предлагаем вам годовое обслуживание.</p>
						<!-- <p>Стоимость годового обслуживания <br><strong>[contract_cost]</strong></p> -->
						<p>Чтобы снять все ограничения, нажмите:</p>

					</div>
				
					<div class="view-get-contract__btn-send std-form__wide-button bright-btn" 
						lang="lng_view_get_contract__connect"><!-- Подключиться -->
					</div>

				</div>									
			</div>
		</div>
		<div class="app-view-loader"><span></span></div>
		<div class="app-view-footer">
			<div class="button-section"><div class="button close"><span lang="lng_close"><!-- Close--></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"></div>
		</div>
	</div>

<!-- - - - - - - - VIEW ADD/EDIT MENU - - - - - - - - - - - - - - - - - - -->

	<div class="app-view view-edit-menu">
		<div class="app-view-header">
			
			<div class="view-header-title">
				<div class="view-header-title_icon"></div>
				<div class="view-header-title_text" ></div>
			</div>

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>				
			</div>
		</div>
		<div class="app-view-page">
			<div class="app-view-page-container overflow-y">

				<div class="std-form">

					<div class="std-form__section-description" >Название раздела:</div>

					<div class="menu-inputs-extra-langs">
						<!-- [code] -->	
					</div> 

					<div class="std-form__section-description" >Выберите иконку раздела:</div>					
					<div class="add-menu-icons"></div>

				</div>

			</div>
		</div>
		<div class="app-view-loader"><span></span></div>		
		<div class="app-view-footer">
			<div class="button-section"><div class="button cancel"><span lang="lng_cancel"><!-- Cancel --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"><div class="button save"><span lang="lng_save"><!-- Save --></span></div></div>
		</div>
	</div>

<!-- - - - - - - - VIEW ADD/EDIT ITEM - - - - - - - - - - - - - - - -->

	<div class="app-view view-edit-item">
		<div class="app-view-header">

			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" ></div>
			</div>

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>
			</div>
		</div>
		<div class="app-view-page">
			<div class="app-view-page-container overflow-y">
				<div class="std-form">

					<div class="item-edits-container">
						<!-- [code] -->							
					</div> 

					<div class="chefsmenu-mode-only">
						<div class="std-form__section-description">Объем</div>		
						<input class="std-form__input item-volume" type="text" name="item-volume" maxlength="555">
						<div class="std-form__section-description" >Стоимость(₽):</div>		
						<input class="std-form__input item-price" type="text" name="item-price" maxlength="555">
					</div>

					<div class="iiko-mode-only ">
						<div class="std-form__title-special-1"><span>Управляется Iiko</span></div>
						<div class="std-form__title-section iiko-modifiers-title" >Модификаторы:</div>	
						<div class="iiko-modifiers"><!-- code --></div>
						<div class="std-form__title-section item-volume-title__iiko"><!-- Volume --></div>		
						<input class="std-form__input item-volume__iiko" type="text" name="item-volume__iiko" maxlength="555">
						<div class="std-form__title-section item-price-title__iiko"><!-- Price --></div>		
						<input class="std-form__input item-price__iiko" type="text" name="item-price__iiko" maxlength="555">
						<div class="std-form__title-special-1-footer"></div>
					</div>					
							
					<div class="edit-item-choose-section">
						<div class="std-form__section-description" lang="lng_view_edit_item__menu_section"><!-- ... --></div> 
						<div class="std-form__wide-button edit-item-choose-section__button"><!-- ... --></div>
					</div>

				</div>
			</div>
		</div>
		<div class="app-view-loader"><span></span></div>
		<div class="app-view-footer">
			<div class="button-section"><div class="button cancel"><span lang="lng_cancel"><!-- Cancel --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"><div class="button save"><span lang="lng_save"><!-- Save --></span></div></div>
		</div>
	</div>

<!-- - - - - - - - VIEW REPLACING PARENT SECTION OF ITEM  - - - - - - - - - -->

	<div class="app-view view-replacing-parent-section">
		<div class="app-view-header">
			
			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" lang="lng_view_replacing_parent_section__view_title"><!-- Choosing a section --></div>
			</div>

			<div class="view-header-buttons"></div>
		</div>
		<div class="app-view-page">			
			<div class="app-view-page-container overflow-y">
				<div class="std-form">
					<div class="std-form__section-description" lang="lng_choose_parent_menu"></div>
					<div class="all-menu-sections">
						<!-- [code] -->
					</div>						
				</div>
			</div>
		</div>
		<div class="app-view-loader"><span></span></div>
		<div class="app-view-footer">
			<div class="button-section"><div class="button cancel"><span lang="lng_cancel"><!-- Cancel --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"><div class="button save"><span lang="lng_save"><!-- Save --></span></div></div>
		</div>
	</div>

<!-- - - - - - - - VIEW CUSTOMIZE ALL - - - - - - - -  -->

	<div class="app-view view-customize-all">
		<div class="app-view-header">

			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" lang="lng_view_customize_all__main_customizing"><!-- Main customizing --></div>
			</div>

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>
			</div>
		</div>		

		<div class="app-view-page">
			<div class="app-view-page-container overflow-y">
				<div class="std-form">

					<div class="std-form__section-description" lang="lng_view_customize_all__change_cafe_info"><!-- Здесь вы можете указать информацию о кафе: название, телефон и т.д. --></div>
					<div class="std-form__wide-button btn-cafe-info" lang="lng_view_customize_all__change_cafe_info_btn"><!-- Информация о кафе --></div>

			
					<div class="chefsmenu-mode-only">
						<div class="std-form__section-description" >Здесь вы можете подключить сервис iiko</div>					
						<div class="std-form__wide-button btn-add-iiko">Подключить iiko</div>							
					</div>
					
					<div class="iiko-mode-only">
						<div class="std-form__section-description">Здесь вы можете изменить настройки iiko</div>					
						<div class="std-form__wide-button bright-btn btn-iiko-customization">Управляется iiko</div>
						<div class="std-form__section-description">Здесь вы можете добавить названия модификаторов на других языках</div>
						<div class="std-form__wide-button btn-iiko-modifiers-dictionary">Словарь модификаторов</div>					
					</div>

					<div class="std-form__section-description" lang="lng_view_customize_all__change_cart_settings"><!-- Здесь вы можете настроить получение заказов от Посетителей --></div>
					<div class="std-form__wide-button btn-cart-settings" lang="lng_view_customize_all__change_cart_settings_btn"><!-- Настройка корзины --></div>
					
					<div class="std-form__section-description" lang="lng_view_customize_all__menu_link"><!-- Здесь вы можете скачать QR-код и поделиться ссылкой на ваше меню --></div>					
					<div class="std-form__wide-button btn-menu-link" lang="lng_view_customize_all__menu_link_btn"><!-- Ссылка на QR-код --></div>	

					<div class="std-form__section-description" lang="lng_view_customize_all__customize_interface"><!-- Чтобы изменить язык, валюту, тему: --></div>
					<div class="std-form__wide-button btn-change-language" lang="lng_view_customize_all__customize_interface_btn"><!-- Настроить интерфейс --></div>	

					<div class="std-form__section-description" lang="lng_view_customize_all__change_subdomain"><!-- Здесь вы можете поменять поддомен для меню: --></div>
					<div class="std-form__wide-button btn-change-subdomain" lang="lng_view_customize_all__change_subdomain_btn"><!-- Изменение поддомена --></div>		


					<div class="std-form__section-description" lang="lng_view_customize_all__change_pass"><!-- Здесь вы можете изменить пароль для входа в эту панель управления: --></div>
					<div class="std-form__wide-button btn-change-password" lang="lng_view_customize_all__change_pass_btn"><!-- Изменение пароля --></div>					

					<div class="std-form__title-section title-get-contract" lang="lng_view_customize_all__get_contract">
						<!-- Чтобы снять все ограничения тестового периода: -->							
					</div>					
					<div class="std-form__wide-button btn-get-contract" lang="lng_view_customize_all__get_contract_btn">
						<!-- Снять ограничения -->							
					</div>					

					<div class="std-form__separator"></div> 					

					<p class="view-customize-all__cafe-status" ><!-- Статус: <span> Cafe Status </span> --></p>					

					<p class="view-customize-all__user-email" lang="lng_view_customize_all__adm_email">
						<!-- Администратор: <span>[adm-email]</span> --></p>

					<p class="view-customize-all__app-version" lang="lng_view_customize_all__app_version">
						<!-- App version: <span>[app-version]</span> --></p>					

					<div class="btn-account-exit"><p><a href="#" lang="lng_view_customize_all__acc_exit"><!-- Выйти из аккаунта --></a></p></div>


				</div>
			</div>
		</div>
		<div class="app-view-loader"><span></span></div>
		<div class="app-view-footer">
			<div class="button-section"><div class="button close"><span lang="lng_close"><!-- Закрыть --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"></div>
		</div>
	</div>

<!-- - - - - - - - VIEW CUSTOMIZING CAFE - - - - - - - -  -->

	<div class="app-view view-customizing-cafe">
		<div class="app-view-header">

			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" lang="lng_view_customizing_cafe__about"><!-- About cafe --></div>
			</div>

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>
			</div>
		</div>
		<div class="app-view-page">
			<div class="app-view-page-container overflow-y">
				<div class="std-form">
					
					<div class="std-form__section-description" lang="lng_view_customizing_cafe__title"><!-- Cafe title --></div>
					<input class="std-form__input" name="cafe-title" type="text" maxlength="555" placeholder="">
					
					<div class="std-form__section-description" lang="lng_view_customizing_cafe__cook"><!-- Chief cook --></div>
					<input class="std-form__input" name="chief-cook" type="text" maxlength="555" placeholder="">

					<div class="std-form__section-description" lang="lng_view_customizing_cafe__address"><!-- Address --></div>
					<textarea class="std-form__textarea" name="cafe-address" rows="4" maxlength="555"></textarea>

					<div class="std-form__section-description" lang="lng_view_customizing_cafe__phone"><!-- Phone --></div>
					<input class="std-form__input" name="cafe-phone" type="text" maxlength="555" placeholder="">

					<div class="std-form__section-description" lang="lng_view_customizing_cafe__work_hours"><!-- Work hours --></div>
					<input class="std-form__input" name="work-hours" type="text" maxlength="555" placeholder="">

					<div class="std-form__wide-button btn-cafe-description" lang="lng_view_customizing_cafe__description"><!-- Cafe description --></div>

				</div>
			</div>
		</div>
		<div class="app-view-loader"><span></span></div>
		<div class="app-view-footer">
			<div class="button-section"><div class="button close"><span lang="lng_close"><!-- Закрыть --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"><div class="button save"><span lang="lng_save"><!-- Сохранить --></span></div></div>
		</div>
	</div>

<!-- - - - - - - - VIEW CUSTOMIZE INTERFACE - - - - - - - - - - - - - - - -->

	<div class="app-view view-customize-interface">
		<div class="app-view-header">
			
			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" lang="lng_view_customize_interface__view_title" ><!-- Customize Interface --></div>
			</div>			

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>
			</div>
		</div>
		<div class="app-view-page">
			<div class="app-view-page-container overflow-y">
				<div class="std-form">

					<div class="std-form__section-description">Выберите оформление для Меню:</div>
					<div class="customize-interface__skin"><!-- [code] --></div>
					
					<h2>Языки</h2>
					<p>Дополнительные языки (переводы) в вашем Меню:</p>
					<div class="customize-interface__extra_lang">
						<!-- [code] -->
					</div>
					
					<h2>Добавить язык</h2>
					<p>Добавьте дополнительный язык для Меню. В описании блюд появятся соответствующие поля для заполнения и редактирования.</p>
					<input class="std-form__input" type="text" name="new-lang">

					<h2>Удалить язык</h2>
					<p>Чтобы удалить перевод у Меню, введите название языка, подлежащего удалению в поле ниже.  </p>
					<input class="std-form__input" type="text" name="lang-to-delete">					


				</div>										
			</div>
		</div>
		<div class="app-view-loader"><span></span></div>
		<div class="app-view-footer">
			<div class="button-section"><div class="button cancel"><span lang="lng_cancel"><!-- Cancel --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"><div class="button save"><span lang="lng_save"><!-- Save --></span></div></div>
		</div>
	</div>	

<!-- - - - - - - - VIEW CUSTOMIZING CART - - - - - - - - - - - - - - - -->

	<div class="app-view view-customizing-cart">
		<div class="app-view-header">
			
			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" lang="lng_view_customizing_cart__view_title" ><!-- Customizing Cart --></div>
			</div>			

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>
			</div>
		</div>
		<div class="app-view-page">
			<div class="app-view-page-container overflow-y">
				<div class="std-form">
					 
					<p lang="lng_view_customizing_cart__cart_mode"></p>

					<div class="customizing-cart__cart-mode"><!-- [code] --></div>

					<h2>Доставка:</h2>
					<div class="customizing-cart__delivery-mode"><!-- [code] --></div>

					<div class="iiko-section-only">
						<h2>Варианты отправки заказов:</h2>
						<p>Выберите подходящий вариант отправки заказов. При выборе варианта №-3, заказ сначала будет отправлен в чат TG, чтобы у официанта (менеджера) была возможность перепроверить или уточнить заказ. После его подтверждения, заказ отправится дальше в iiko.</p>
						<div class="customizing-cart__order-way"><!-- [code] --></div>
					</div>

					<h2>Телеграм:</h2>

					<p>Для получения заказов через чат телеграм:</p>

					<div class="std-form__section-description">
						<ol>
							<li>скопируйте <strong>Секретный ключ,</strong></li>
							<li>перейдите в чат <a target="_blank" href="https://t.me/<?=$CART_BOT_NAME;?>">@<?=$CART_BOT_NAME;?></a></li>
							<li>и вставьте этот ключ в ответ на приветствие</li>
						</ol>						
					</div>

					<p>Для каждой роли предусмотрен соответствующий ключ. </p>
					<p><strong>Официант</strong> – будет получать заказы в стол (в кафе).</p>
					<p><strong>Менеджер</strong> – будет получать внешние заказы на доставку или самовывоз.</p>
					<p><strong>Администратор</strong> – будет получать статистику по всем заказам за день.</p>

					<p>Нажмите на нужный ключ, чтобы его скопировать:</p>

					<div class="customizing-cart__all-keys">
						<div class="customizing-cart__all-keys_key">
							<div><button class="std-special-single-button customizing-cart__tg_key key-waiter">TG_KEY:001</button></div>
							<div>официант</div>
						</div>
						<div class="customizing-cart__all-keys_key">
							<div><button class="std-special-single-button customizing-cart__tg_key key-manager">TG_KEY:002</button></div>
							<div>менеджер</div>
						</div>
						<div class="customizing-cart__all-keys_key">
							<div><button class="std-special-single-button customizing-cart__tg_key key-supervisor">TG_KEY:003</button></div>
							<div>администратор</div>
						</div>						
					</div>
					

					<div class="super-admin-only">
						<hr><button name="update_all_tg_keys">Обновить телеграм ключи</button>
					</div>
					
				</div>										
			</div>
		</div>
		<div class="app-view-loader"><span></span></div>
		<div class="app-view-footer">
			<div class="button-section"><div class="button cancel"><span lang="lng_cancel"><!-- Cancel --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"><div class="button save"><span lang="lng_save"><!-- Save --></span></div></div>
		</div>
	</div>	

<!-- - - - - - - - VIEW CAFE DESCRIPTION - - - - - - - - - - - - - - - - - - -->

	<div class="app-view view-cafe-description">
		<div class="app-view-header">

			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" lang="lng_view_cafe_description__view_title" ></div>
			</div>	

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>
			</div>
		</div>
		<div class="app-view-page">	
			<div class="app-view-page-container overflow-y">
				<div class="std-form">
					<div class="std-form__section-description" lang="lng_view_cafe_description__about"><!-- About the Cafe --></div>
					<textarea class="std-form__textarea" name="cafe-description" rows="12" maxlength="555"></textarea>
				</div>
			</div>
		</div>
		<div class="app-view-loader"><span></span></div>
		<div class="app-view-footer">
			<div class="button-section"><div class="button back"><span lang="lng_back"><!-- Back --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"><div class="button save"><span lang="lng_save"><!-- Save --></span></div></div>
		</div>
	</div>

<!-- - - - - - - - VIEW CAFE LINK - - - - - - - - - - - - - -->
	
	<div class="app-view view-cafe-link">
		<div class="app-view-header">
			
			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" lang="lng_view_cafe_link__view_title" ></div>
			</div>

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>
			</div>
		</div>
		<div class="app-view-page">
			<div class="app-view-page-container overflow-y">				
				<div class="std-form">
					<div class="std-form__section-description" lang="lng_view_cafe_link__hard_link"><!-- link --></div>
					<div class="std-form__bright-link"><a href="#" target="_blank">[menu-home-link]</a></div>

					<div class="std-form__section-description" lang="lng_view_cafe_link__qrcode"><!-- Get to email and print this QR code ... --></div>
					<div class="view-cafe-link__qr-code"></div>
						
					<div class="std-form__section-description" lang="lng_view_cafe_link__tutor"><!-- Вместе с qr-кодом вам на почту ... --></div>	
				</div>
			</div>
		</div>
		<div class="app-view-loader"><span></span></div>
		<div class="app-view-footer">
			<div class="button-section"><div class="button back"><span lang="lng_back"><!-- Back --></span></div></div>
			<div class="button-section"></div>
			<div class="button-section"><div class="button btn-get-qrcode"><span lang="lng_view_cafe_link__btn_get_qrcode"><!-- Download QR-CODE --></span></div></div>
		</div>
	</div>

<!-- - - - - - - - VIEW ITEM'S IMAGE CHANGE - - - - - - - - - - - - - - - -->

	<div class="app-view view-item-image-change">
	<div class="app-view-header">
		
		<div class="view-header-title">
			<div class="view-header-title_icon ico-adm-customizing"></div>
			<div class="view-header-title_text" lang="lng_view_item_image_change__title"><!-- Image Replacement --></div>
		</div>

		<div class="view-header-buttons">
			<div class="view-header-buttons__button view-header-buttons__button_help"></div>
		</div>
	</div>
	
	<div class="app-view-extra-loader">
		<div class="app-view-extra-loader__status">0%</div>
		<div class="app-view-extra-loader__line"></div>
	</div>

	<div class="app-view-page">
		<div class="app-view-page-container">			
			<div class="item-image-change__form" style="display:none">
				<form enctype="multipart/form-data" method="post">
			    	<input type="file" name="fileToUpload" class="fileToUpload" accept="image/*" >
				</form>
			</div>
			<div class="item-image-change__handler"></div>
		</div>
	</div>
	<div class="app-view-loader"><span></span></div>
	<div class="app-view-footer">
		<div class="button-section">
			<div class="button cancel"><span lang="lng_back"><!-- Назад --></span></div>
		</div>
		<div class="button-section">
			<div class="button button-as-image rotate-image"><span></span></div>
		</div>
		<div class="button-section">
			<div class="button change"><span lang="lng_change"><!-- Изменить  --></span></div>
			<div class="button save hidden"><span lang="lng_save"><!-- Сохранить --></span></div>
		</div>
	</div>
	</div>

<!-- - - - - - - - ITEM SLIDE - - - - - - - - - - - - - - - - - -  -->

	<div class="item-slide-template-4">
		<div class="item">
 			<div class="item__sub-layer">
				<div class="btn-item-delete"><span lang="lng_delete"><!-- Delete --></span></div>
				<div class="item-flags">
					<div class="btn-flag-spicy"><div><span></span></div></div>
					<div class="btn-flag-hit"><div><span></span></div></div>
					<div class="btn-flag-vege"><div><span></span></div></div>
				</div>
				<div class="btns-item-repos">
					<div class="btns-item-repos__button btns-item-repos__prev"><span></span></div>
					<div class="btns-item-repos__button btns-item-repos__next"><span></span></div>
				</div>
				
			</div> 
			<div class="item__front-layer">				
				<div class="small-item-image">							
					<div class="small-item-image__holder hidden"></div>
	    			<div class="small-item-image__loader hidden"><div><span></span></div></div>
				</div>
				<div class="item-title"><div></div></div>
				<div class="item-price"><div></div></div>
				<div class="front-flags-preview">
					<span class="flag-spicy-preview"></span>
					<span class="flag-hit-preview"></span>
					<span class="flag-vege-preview"></span>
				</div>
			</div> 
		</div>
	</div>

	<div class="item-slide-template-3">
		<div class="item">
 			<div class="item__sub-layer">
				<div class="btn-item-delete"><span lang="lng_delete"><!-- Delete --></span></div>
				<div class="item-flags">
					<div class="btn-flag-spicy"><span></span></div>
					<div class="btn-flag-hit"><span></span></div>
					<div class="btn-flag-vege"><span></span></div>
				</div>
				<div class="btn-item-edit"><span lang="lng_edit"><!-- Edit --></span></div>				
			</div> 
			<div class="item__front-layer">				
				<div class="small-item-image">							
					<div class="small-item-image__holder hidden"></div>
	    			<div class="small-item-image__loader hidden"><div><span></span></div></div>
				</div>
				<div class="item-title"><div></div></div>
				<div class="item-price"><div></div></div>
				<div class="front-flags-preview">
					<span class="flag-spicy-preview"></span>
					<span class="flag-hit-preview"></span>
					<span class="flag-vege-preview"></span>
				</div>
			</div> 
		</div>
	</div>

<!-- - - - - - - - ITEM EDIT FORM - - - - - - - - - - - - - - -  -->

<div class="tpl-item-edit-form extra-lang-section">
	<div class="std-form__section-description" >Название</div>
	<textarea class="std-form__textarea item-title" rows="3" name="item-title" maxlength="555" ></textarea>

	<div class="std-form__section-description" >Описание</div>
	<textarea class="std-form__textarea item-description" rows="5" name="item-description" maxlength="555"></textarea>
</div>	


<!-- - - - - - - - DEFAULT VIEW MODAL MESSAGE - - - - - - - - -->

	<div class="app-view-modal view-modal-message">
		<div class="app-view-modal-container">
			<div class="app-view-modal-container__title"><!-- Attention --></div>
			<div class="app-view-modal-container__message"><!-- message --></div>
			<div class="app-view-modal-container__actions">
				<div class="button close"><span lang="lng_close"><!-- Close --></span></div>
			</div>
		</div>		
	</div>	

<!-- - - - - - - - DEFAULT VIEW MODAL CONFIRM -->

	<div class="app-view-modal view-modal-confirm">
		<div class="app-view-confirm-container">
			<div class="app-view-confirm-container__title"><!-- Title --></div>
			<div class="app-view-confirm-container__message"><!-- Ask --></div>
			<div class="app-view-confirm-container__actions">				
				<div class="button cancel"><span lang="lng_cancel"><!-- Cancel --></span></div>
				<div class="button ok"><span>OK</span></div>
			</div>
		</div>
	</div>	

<!-- - - - - - - - DEFAULT VIEW MODAL ACTION SHEET -->

	<div class="app-view-modal view-action-sheet">
		<div class="view-header">
			<div class="view-title">Attention</div>
		</div>
		<div class="action-sheet-menu">
			<div>
				<div class="menu-title">{Menu title}</div>
			</div>
			<div >
				<div class="actions">
					<div>
						<div ><span class="button">action 1</span></div>
						<div ><span class="button">action 2</span></div>
					</div>
				</div>
			</div>
			<div >
				<div class="actions-footer">
					<div >
						<div ><span class="cancel" lang="lng_cancel"><!-- cancel --></span></div>	
					</div>
				</div>					
			</div>
		</div>
	</div>


</div>

</body>
</html>