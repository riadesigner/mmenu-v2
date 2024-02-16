<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	if(J_ENV_LOCAL){

		$CFG->cafe_sample_links = [['cafe-name'=>'Легенда','cafe-uniq'=>'303cnc','subdomain'=>'ggg'], ['cafe-name'=>'Легенда','cafe-uniq'=>'303cnc','subdomain'=>''], ['cafe-name'=>'Легенда','cafe-uniq'=>'303cnc','subdomain'=>'']];	

	}else{

		$CFG->cafe_sample_links = [['cafe-name'=>'Леонардо','cafe-uniq'=>'308ggu','subdomain'=>'leonardo'], ['cafe-name'=>'Рафаэль','cafe-uniq'=>'309ond','subdomain'=>'rafael'], ['cafe-name'=>'ШокоЛэнд','cafe-uniq'=>'311gpq','subdomain'=>'shokoland']];

	}

//
// 
//		  c o n f i g   s i t e   l i n k s
//
//

	$CFG->site_links = ['home'=>$CFG->wwwroot, 'help'=>$CFG->wwwroot.'/help', 'help_edit_sections'=>$CFG->wwwroot.'/help#edit-sections', 'help_edit_items'=>$CFG->wwwroot.'/help#edit-items', 'help_edit_customize_ui'=>$CFG->wwwroot.'/help#customize-ui', 'help_quick_creating'=>$CFG->wwwroot.'/help#quick-creating', 'help_link_to_menu'=>$CFG->wwwroot.'/help#link-to-my-menu', 'help_link_control_panel'=>$CFG->wwwroot.'/help#link-control-panel', 'easy_open'=>$CFG->wwwroot.'/easy-open', 'easy_open_files'=>$CFG->wwwroot.'/easy-open#files-to-print', 'easy_open_modern_pwa'=>$CFG->wwwroot.'/easy-open#modern-pwa', 'price'=>$CFG->wwwroot.'/price', 'contacts'=>$CFG->wwwroot.'/contacts', 'privacy'=>$CFG->wwwroot.'/privacy', 'terms'=>$CFG->wwwroot.'/terms', 'features'=>$CFG->wwwroot.'/features', 'features_general'=>$CFG->wwwroot.'/features#general-features', 'to_developers'=>$CFG->wwwroot.'/to-developers', 'admin'=>$CFG->wwwroot.'/admin'];

	$CFG->site_templates = [
     // en, ru templates
     'Index'=>['title'=>['
				Chefs Menu!', 'Chefs Menu! Онлайн Меню для кафе и ресторанов за 10 секунд!'], 'descr'=>['', 'Сервис быстрого создания электронного меню для
				ресторанов и кафе, с удобным управлением, корзиной для приема заказов
				и технической поддержкой – по цене двух кружек кофе в месяц!'], 'template'=>['/site/views/tpl.site.php', '/site/views/tpl.site.php']],
     //cafe, address with subdomain
     'Cafe'=>['title'=>['Chefs Menu!', 'Chefs Menu!'], 'descr'=>['', 'Меню работает на платформе Chefs Menu!'], 'template'=>['/site/views/tpl.menu.php', '/site/views/tpl.menu.php']],
     //cafe, address without subdomain
     'Menu'=>['title'=>['Chefs Menu!', 'Chefs Menu!'], 'descr'=>['', 'Меню работает на платформе Chefs Menu!'], 'template'=>['/site/views/tpl.menu.php', '/site/views/tpl.menu.php']],
     '404'=>['title'=>['Chefs Menu! Page not found', 'Chefs Menu! Страница не найдена'], 'descr'=>['', 'Сервис быстрого создания электронного меню для
				ресторанов и кафе, с удобным управлением, корзиной для приема заказов
				и технической поддержкой – по цене двух кружек кофе в месяц!'], 'template'=>['/site/views/tpl.404.php', '/site/views/tpl.404.php']],
     'PricePage'=>['title'=>['Chefs Menu! Price. ', 'Chefs Menu! Стоимость. Онлайн Меню для кафе и ресторанов за 10 секунд!'], 'descr'=>['', 'Сервис быстрого создания электронного меню для
				ресторанов и кафе, с удобным управлением, корзиной для приема заказов
				и технической поддержкой – по цене двух кружек кофе в месяц!'], 'template'=>['/site/views/tpl.price.php', '/site/views/tpl.price.php']],
     'ContactsPage'=>['title'=>['Chefs Menu! Contacts. ', 'Chefs Menu! Контакты. Онлайн Меню для кафе и ресторанов за 10 секунд!'], 'descr'=>['', 'Сервис быстрого создания электронного меню для
				ресторанов и кафе, с удобным управлением, корзиной для приема заказов
				и технической поддержкой – по цене двух кружек кофе в месяц!'], 'template'=>['/site/views/tpl.contacts.php', '/site/views/tpl.contacts.php']],
     'ToDevelopersPage'=>['title'=>['Chefs Menu! To Developers. ', 'Chefs Menu! Разработчикам. Онлайн Меню для кафе и ресторанов за 10 секунд!'], 'descr'=>['', 'Сервис быстрого создания электронного меню для
				ресторанов и кафе, с удобным управлением, корзиной для приема заказов
				и технической поддержкой – по цене двух кружек кофе в месяц!'], 'template'=>['/site/views/tpl.to_developers.php', '/site/views/tpl.to_developers.php']],
     'FeaturesPage'=>['title'=>['Chefs Menu! Features and limits. ', 'Chefs Menu! Возможности и ограничения. Онлайн Меню для кафе и ресторанов за 10 секунд!'], 'descr'=>['', 'Сервис быстрого создания электронного меню для
				ресторанов и кафе, с удобным управлением, корзиной для приема заказов
				и технической поддержкой – по цене двух кружек кофе в месяц!'], 'template'=>['/site/views/tpl.features.php', '/site/views/tpl.features.php']],
     'HelpPage'=>['title'=>['Chefs Menu! Quick start. ', 'Chefs Menu! С чего начать. Онлайн Меню для кафе и ресторанов за 10 секунд!'], 'descr'=>['', 'Сервис быстрого создания электронного меню для
				ресторанов и кафе, с удобным управлением, корзиной для приема заказов
				и технической поддержкой – по цене двух кружек кофе в месяц!'], 'template'=>['/site/views/tpl.help.php', '/site/views/tpl.help.php']],
     'EasyOpenPage'=>['title'=>['Chefs Menu! Easy open menu', 'Chefs Menu! Легкое открытие меню. Онлайн Меню для кафе и ресторанов за 10 секунд!'], 'descr'=>['', 'Сервис быстрого создания электронного меню для
				ресторанов и кафе, с удобным управлением, корзиной для приема заказов
				и технической поддержкой – по цене двух кружек кофе в месяц!'], 'template'=>['/site/views/tpl.easy_open.php', '/site/views/tpl.easy_open.php']],
     'PrivacyPage'=>['title'=>['Chefs Menu! Privacy', 'Chefs Menu! Конфиденциальность. Онлайн Меню для кафе и ресторанов за 10 секунд!'], 'descr'=>['', 'Сервис быстрого создания электронного меню для
				ресторанов и кафе, с удобным управлением, корзиной для приема заказов
				и технической поддержкой – по цене двух кружек кофе в месяц!'], 'template'=>['/site/views/tpl.privacy.php', '/site/views/tpl.privacy.php']],
     'TermsPage'=>['title'=>['Chefs Menu! Terms and Conditions', 'Chefs Menu! Условия использования сервиса. Онлайн Меню для кафе и ресторанов за 10 секунд!'], 'descr'=>['', 'Сервис быстрого создания электронного меню для
				ресторанов и кафе, с удобным управлением, корзиной для приема заказов
				и технической поддержкой – по цене двух кружек кофе в месяц!'], 'template'=>['/site/views/tpl.terms.php', '/site/views/tpl.terms.php']],
     // no index, no follow
     'Confirmpass'=>['title'=>['Chefs Menu! Password confirmation', 'Chefs Menu! Подтверждение пароля'], 'descr'=>['', ''], 'template'=>['/site/views/tpl.confirmpass.php', '/site/views/tpl.confirmpass.php']],
     'ConfirmSubdomain'=>['title'=>['Chefs Menu! Address confirmation', 'Chefs Menu! Подтверждение адреса'], 'descr'=>['', ''], 'template'=>['/site/views/tpl.confirm_subdomain.php', '/site/views/tpl.confirm_subdomain.php']],
     'Activation'=>['title'=>['Chefs Menu! Preparing to open', 'Chefs Menu! Подготовка к открытию'], 'descr'=>['', ''], 'template'=>['/site/views/tpl.activation.php', '/site/views/tpl.activation.php']],
     'ControlPanel'=>['title'=>['Chefs Menu! Control Panel', 'Chefs Menu! Панель Управления'], 'descr'=>['', ''], 'template'=>['/adm/views/tpl.app.php', '/adm/views/tpl.app.php']],
     'RDSAdminMain'=>['title'=>['RDS-Admin / Admin', 'RDS-Admin / Admin'], 'descr'=>['', ''], 'template'=>['/rds/views/tpl.admin.php', '/rds/views/tpl.admin.php']],
     'RDSAdminEnter'=>['title'=>['RDS-Admin / Enter', 'RDS-Admin / Enter'], 'descr'=>['', ''], 'template'=>['/rds/views/tpl.enter.php', '/rds/views/tpl.enter.php']],
     'RDSAdminDelUser'=>['title'=>['RDS-Admin / Delete User', 'RDS-Admin / Add contract'], 'descr'=>['', ''], 'template'=>['/rds/views/tpl.deluser.php', '/rds/views/tpl.deluser.php']],
     'RDSAdminAddContract'=>['title'=>['RDS-Admin / Add contract', 'RDS-Admin / Add contract'], 'descr'=>['', ''], 'template'=>['/rds/views/tpl.add_contract.php', '/rds/views/tpl.add_contract.php']],
     'RDSAdmin404'=>['title'=>['Chefs Menu! Unknown page', 'Chefs Menu! Unknown page'], 'descr'=>['', ''], 'template'=>['/rds/views/tpl.404.php', '/rds/views/tpl.404.php']],
 ];


?>