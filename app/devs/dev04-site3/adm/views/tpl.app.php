<!DOCTYPE html>
<head>
	<title>dev admin</title>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"> 
<meta name="mobile-web-app-capable" content="yes">

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
	'site_links'=>$CFG->site_links,
	'tgbot_link'=>"https://t.me/".$CFG->tg_cart_bot."?start=",
	];
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
						<li>Создайте Внешнее Меню в <strong><i>iikoWeb</i></strong>, если у вас его еще нет.</li>
						<li>Получите API ключ в <strong><i>iikoIcloud</i></strong>, специально для ChefsMenu.</li>
						<li>Вставьте полученный API ключ в поле ниже и нажмите кнопку «Сохранить». </li>
					</ol>
					
					<input class="std-form__input" type="input" placeholder="0000000-000" name="iiko-api-key" maxlength="100" >

					

					<p>После этого, меню перейдет под управление сервиса iiko и будет по расписанию подгружать из него последнюю версию Внешнего меню.</p>

					<p><strong>Подробности:</strong></p>

					<p>Смотрите видео-инструкцию <a href="#" name="link-iiko-help">подключения iiko</a>.</p>

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
					
					<h2>Организации</h2>
					<p>Веберите организацию:</p>

					<div class="iiko-general-information"></div>													

					<h2 class="iiko-current-org-title">Настройки <span>Pizzaiolo</span>:</h2>

					<p>При выборе (смене) организации, вся информация и настройки ниже – внешние меню, терминалы, столы – будут заново загружены для соответствующей организации и сброшены в начальное состояние.</p>

					<h2>Внешние меню</h2>
					<p>Выберите внешнее меню из имеющихся:</p>					 
					<div class="iiko-extmenu-list">
						<div label="menu-10101" class="std-form__radio-button ">Меню тест 1</div>	
						<div label="menu-10344" class="std-form__radio-button checked">Меню тест 2</div>
					</div>


					<h2>Терминалы</h2>					
					<p>Выберите текущую терминальную группу для отправки заказов </p>					
					<div class="iiko-terminals-sections">
						<p>Не найдено.</p>
					</div>

					<h2>Статус текущего терминала</h2>
					<p>(<span class="iiko-terminal-status-info-name">текущая группа</span>)</p>
					<p>Статуc: <strong class="iiko-terminal-status-info">Не определен</strong></p>
					
					<!-- TODO -->
					<!-- <button class="std-special-single-button btn-iiko-terminal-to-alive">Разбудить терминал</button> -->
					
					<h2>Столы</h2>					
					<p>У вас есть зарегистрированные столы: </p>					
					<ul class="iiko-table-sections">
						<li>Не найдено.</li>
					</ul>					

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
					
					<h2>Обновить данные iiko</h2>
					<p>Если вы поменяли / добавили внешние меню, здесь можно подгузить обновленные данные. </p>
					<button class="std-special-single-button btn-iiko-vars-update">Обновить данные iiko</button>					

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

	<div class="app-view view-qrcode-with-tables">
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
					
					<div class="iiko-mode-only">
						<h2>Всего столов в iiko</h2>
						<p class="iiko-tables-total-message"><!-- всего настроенных столов в iiko --></p>
					</div>

					<h2>Получить QR-коды:</h2>
					<p>Выберите нужные столы и нажмите кнопку <strong>Отправить</strong>. QR-коды будут сгенерированы и отправлены на почту администратора <strong><span class="adm_email"></span></strong>.</p>					
					<div class="wrapper-qrcode-list-of-table-sections">
						<!-- btns -->
					</div>

					<h2>Посмотреть Меню столика:</h2>
					<p>Чтобы открыть (протестировать) Меню определенного столика, нажмите на соответствующую кнопку ниже.</p>					
					<div class="wrapper-menulinks-for-tables-sections">
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
					<div class="std-form-switcher btn-cat-hide">
						<div class="std-form-switcher__label">Показывать на сайте</div>
						<div class="std-form-switcher__button"><span></span></div>
					</div>
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
						<div class="price-list-section">
							<div class="std-form-double">
								<div class="std-form__section-description">Объем</div>
								<div class="std-form__section-description" >Стоимость(₽):</div>
							</div>
							<div class="price-list">
								<!-- tpl -->
							</div>
							<div class="sizes-buttons">								
								<button class="btn-add-price std-special-single-button">Добавить</button>
								<button class="btn-del-price std-special-single-button">Убрать</button>
							</div>
						</div>
					</div>

					<div class="iiko-mode-only ">
						<div class="std-form__title-special-1"><span>Управляется Iiko</span></div>

						<div class="std-form__title-section item-volume-title__iiko"><!-- Volume --></div>		
						<input class="std-form__input item-volume__iiko" type="text" name="item-volume__iiko" maxlength="555">
						<div class="std-form__title-section item-price-title__iiko"><!-- Price --></div>		
						<input class="std-form__input item-price__iiko" type="text" name="item-price__iiko" maxlength="555">

						<div class="std-form__title-section iiko-modifiers-title" >Модификаторы:</div>	
						<div class="iiko-modifiers"><!-- code --></div>						
						
						<div class="std-form__title-special-1-footer"></div>
					</div>					
						
					<div class="edit-item-choose-modifiers chefsmenu-mode-only">
						<div class="std-form__section-description">
							Здесь вы можете выбрать опциональные добавки для этого блюда:
						</div> 
						<div class="std-form__wide-button edit-item-choose-modifiers__button">Добавки</div>
					</div>	

					<div class="edit-item-choose-section">
						<div class="std-form__section-description" 
						lang="lng_view_edit_item__menu_section">
							Здесь вы можете изменить раздел в котором находится это блюдо:
						</div> 
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

	<!-- - - - - - - - TPL PRICE LIST ROW - - - - - - - - - - - - -  -->
	<div class="tpl-item-edit__price-list-row sizes-list">								
		<div><input class="std-form__input item-volume" type="text" name="item-volume" maxlength="555"></div>
		<div><button name="item-units" value="г">г</button></div>
		<div><input class="std-form__input item-price" type="text" name="item-price" maxlength="555"></div>
	</div>
	
	<!-- - - - - - - - TPL EXTRA LANG - - - - - - - - - - - - - - -  -->
	<div class="tpl-item-edit__extra-lang extra-lang-section">
		<div class="std-form__section-description" >Название</div>
		<textarea class="std-form__textarea item-title" rows="3" name="item-title" maxlength="555" ></textarea>

		<div class="std-form__section-description" >Описание</div>
		<textarea class="std-form__textarea item-description" rows="5" name="item-description" maxlength="555"></textarea>
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

<!-- - - - - - - - VIEW CHOOSING MODIFIERS  - - - - - - - - - -->
	<div class="app-view view-choosing-modifiers">
		<div class="app-view-header">
			
			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text" >Выбор опций (добавок)</div>
			</div>

			<div class="view-header-buttons"></div>
		</div>
		<div class="app-view-page">			
			<div class="app-view-page-container overflow-y">
				<div class="std-form">
					<div class="std-form__section-description">
						Выберите один или несколько разделов с добавками к этому блюду (или напитку)
					</div>
					<div class="all-menu-sections-header">
						<div></div><div></div><div></div>
					</div>
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

					<div class="std-form__section-description" >Здесь вы можете настроить получение заказов</div>
					<div class="std-form__wide-button btn-cart-settings bright-btn" >Настроить Корзину</div>

					<div class="std-form__section-description" >Здесь вы можете добавить сотрудников</div>
					<div class="std-form__wide-button btn-staff-settings bright-btn" >Сотрудники кафе</div>					
					
					<div class="std-form__section-description" lang="lng_view_customize_all__menu_link"><!-- Здесь вы можете скачать QR-код и поделиться ссылкой на ваше меню --></div>					
					<div class="std-form__wide-button btn-menu-link" lang="lng_view_customize_all__menu_link_btn"><!-- Ссылка на QR-код --></div>	

					<div class="std-form__section-description" >Чтобы изменить тему, язык и другие настройки интерфейса</div>
					<div class="std-form__wide-button btn-change-language">Настроить интерфейс</div>	

					<div class="std-form__section-description" >Получить QR-коды Меню для отправки заказов на стол</div>
					<div class="std-form__wide-button btn-change-tables">Настройка столов</div>						

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
						
					<p>Выберите режим корзины и способ приема заказов:</p>					
			
					<h2>Корзина:</h2>
					<div class="customizing-cart__cart-mode"><!-- [code] --></div>

					<h2>Доставка:</h2>
					<div class="customizing-cart__delivery-mode"><!-- [code] --></div>

					<div class="iiko-mode-only">
						<h2>Отправка заказа:</h2>
						<div class="customizing-cart__order-way-mode"><!-- [code] --></div>
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
<!-- - - - - - - - VIEW CUSTOMIZING STAFF - - - - - - - - - - - - - - - -->

	<div class="app-view view-customizing-staff">
		<div class="app-view-header">
			
			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text">Настройка персонала</div>
			</div>			

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>
			</div>
		</div>
		<div class="app-view-page">
			<div class="app-view-page-container overflow-y">
				<div class="std-form">
					 
					<p>Здесь вы можете пригласить сотрудников в кафе и назначить им роли.</p>

					<p>Все заказы приходят в специальный телеграм канал <strong>@<?=$CFG->tg_cart_bot;?></strong>. 
						После подтверждения заказа, официант или менеджер могут переправить его на кассу.</p>
											

					<h2>Выберите роль для себя:</h2>

					<p>Выберите роль для себя и получите ссылку-приглашение в телеграм канал вашего кафе, чтобы работать с заказами.</p>

					<div class="customizing-cart__tg-links-section-attention std-form__highlight-field">
						<strong>Не найдено ни одного телеграм ключа!</strong><br>Нажмите ниже «Сбросить телеграм ключи».
					</div>

					<div class="customizing-cart__tg-links-section">						
						
						<div class="customizing-cart__all-keylinks">
							<a href="<?=$CFG->http.$CFG->wwwroot;?>" target="_blank" class="link-waiter"><button class="std-special-single-button">ОФИЦИАНТ</button></a>
							<a href="<?=$CFG->http.$CFG->wwwroot;?>" target="_blank" class="link-manager"><button class="std-special-single-button">МЕНЕДЖЕР</button></a>
							<a href="<?=$CFG->http.$CFG->wwwroot;?>" target="_blank" class="link-supervisor"><button class="std-special-single-button">АДМИНИСТРАТОР</button></a>
						</div>

					<h2 id="anchor_invitation">Пригласите сотрудника:</h2>

						<p>Чтобы пригласить нового сотрудника, перешлите ему ссылку соответствующеее приглашение с нужной ролью. Или покажите ему соответствующий QR-код.</p>

						<h3>Ссылка-приглашение:</h3>
						<div class="customizing-cart__all-invite-links">
							<button class="std-special-single-button invite-link-waiter">ОФИЦИАНТ</button>
							<button class="std-special-single-button invite-link-manager">МЕНЕДЖЕР</button>
							<button class="std-special-single-button invite-link-supervisor">АДМИНИСТРАТОР</button>
						</div>	

						<h3>QR-код приглашение:</h3>
						<div class="customizing-cart__all-invite-qrcodes">
							<button class="std-single-button-with-icon invite-qrcode-waiter">ОФИЦИАНТ</button>
							<button class="std-single-button-with-icon invite-qrcode-manager">МЕНЕДЖЕР</button>
							<button class="std-single-button-with-icon invite-qrcode-supervisor">АДМИНИСТРАТОР</button>
						</div>		

					</div>					
					
					<h2>Сотрудники кафе:</h2>

					<div class="customizing-cart__all-tgusers">
						<p>Сейчас в кафе зарегистрированы следующие сотрудники:</p>
						<ul>
							<li class="tgusers-role-waiter">Официанты: <span>нет</span></li>
							<li class="tgusers-role-manager">Менеджеры: <span>нет</span></li>
							<li class="tgusers-role-supervisor">Администраторы: <span>нет</span></li>
						</ul>
					</div>

					<h2>Справка:</h2>

					<p><strong><i>Официант</i></strong> – будет получать заказы на стол (в кафе).</p>
					<p><strong><i>Менеджер</i></strong> – будет получать внешние заказы на доставку или самовывоз.</p>
					<p><strong><i>Администратор</i></strong> – будет получать статистику по всем заказам за день.</p>					

					<h2>Удаление сотрудников:</h2>						
					
					<div class="super-admin-only">						
						<p>Если нужно отозвать все разрешения и заново пересоздать ключи для доступа в телеграм, выберите:</p>
						<button name="update_all_tg_keys">Сбросить телеграм ключи</button>
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

<!-- - - - - - - - VIEW CAFE TABLES - - - - - - - - - - - - - -->	
	<div class="app-view view-cafe-tables">
		<div class="app-view-header">
			
			<div class="view-header-title">
				<div class="view-header-title_icon ico-adm-customizing"></div>
				<div class="view-header-title_text">Управление столами </div>
			</div>

			<div class="view-header-buttons">
				<div class="view-header-buttons__button view-header-buttons__button_help"></div>
			</div>
		</div>
		<div class="app-view-page">
			<div class="app-view-page-container overflow-y">				
				<div class="std-form">

				<h2>Столы</h2>					
				<p>Укажите количество столов в вашем ресторане (до 50): </p>					
				<div class="view-cafe-tables__table-counts-wrapper">
					<input type="number" class="std-form__input w30">					
				</div>

				<h2>QR-коды и ссылки</h2>					
				<p>Здесь вы сможете скачать QR-коды всех столов для получения заказов на конкретный стол, а также открыть меню любого столика:</p>					
				<div class="std-form__wide-button btn-iiko-get-qrcodes">QR-коды для столов</div>
					
				<div class="superadmin-only">
				<h2>Дополнительно</h2>
					<p>Сбросить и пересоздать заново QR-коды для всех столов. Внимание, только для опытных пользователей:</p>
					<button class="btn-reset-tables-qrcodes">Сбросить QR-коды</button>
				</div>

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