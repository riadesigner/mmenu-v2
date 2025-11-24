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
     'Index'=>[
		'title'=>'Chefs Menu! QR-Меню для кафе и ресторанов за 10 минут!', 		
		'descr'=>'Сервис быстрого создания электронного меню для ресторанов и кафе, с удобным управлением, корзиной для приема заказов!', 
		'template'=>'site/views/tpl.site.php'],
     // MENU, ADDRESS WITHOUT SUBDOMAIN
     'Menu'=>[
		'title'=>'Menu',		
		'descr'=>'Меню работает на платформе Chefs Menu!',
		'template'=>'site/views/tpl.menu.php',],
     '404'=>[
		'title'=>'Chefs Menu! Page not found', 	 	
		'descr'=>'Сервис быстрого создания электронного меню для ресторанов и кафе, с удобным управлением, корзиной для приема заказов!', 
		'template'=>'site/views/tpl.404.php',],
     'PricePage'=>[
		'title'=>'Chefs Menu! Стоимость. QR-Меню для кафе и ресторанов за 10 минут!',
		'descr'=>'Сервис быстрого создания электронного меню для ресторанов и кафе, с удобным управлением, корзиной для приема заказов!', 
		'template'=>'site/views/tpl.price.php',],
     'ContactsPage'=>[
		'title'=>'Chefs Menu! Контакты. QR-Меню для кафе и ресторанов за 10 минут!',
		'descr'=>'Сервис быстрого создания электронного меню для ресторанов и кафе, с удобным управлением, корзиной для приема заказов!', 
		'template'=>'site/views/tpl.contacts.php',],
     'FeaturesPage'=>[
		'title'=>'Chefs Menu! Возможности и ограничения. QR-Меню для кафе и ресторанов за 10 минут!', 
		'descr'=>'Сервис быстрого создания электронного меню для ресторанов и кафе, с удобным управлением, корзиной для приема заказов!', 
		'template'=>'site/views/tpl.features.php',],
     'HelpPage'=>[
		'title'=>'Chefs Menu! Возможности и ограничения. QR-Меню для кафе и ресторанов за 10 минут!', 
		'descr'=>'Сервис быстрого создания электронного меню для ресторанов и кафе, с удобным управлением, корзиной для приема заказов!', 
		'template'=>'site/views/tpl.help.php',],
     'EasyOpenPage'=>[
		'title'=>'Chefs Menu! Возможности и ограничения. QR-Меню для кафе и ресторанов за 10 минут!', 
		'descr'=>'Сервис быстрого создания электронного меню для ресторанов и кафе, с удобным управлением, корзиной для приема заказов!', 
		'template'=>'site/views/tpl.easy_open.php',],
     'PrivacyPage'=>[
		'title'=>'Chefs Menu! Возможности и ограничения. QR-Меню для кафе и ресторанов за 10 минут!', 
		'descr'=>'Сервис быстрого создания электронного меню для ресторанов и кафе, с удобным управлением, корзиной для приема заказов!', 
		'template'=>'site/views/tpl.privacy.php',],
     'TermsPage'=>[
		'title'=>'Chefs Menu! Возможности и ограничения. QR-Меню для кафе и ресторанов за 10 минут!', 
		'descr'=>'Сервис быстрого создания электронного меню для ресторанов и кафе, с удобным управлением, корзиной для приема заказов!', 
		'template'=>'site/views/tpl.terms.php',],
	'IikoConnectionPage'=>[
		'title'=>'Chefs Menu! Подключение Iiko. QR-Меню для кафе и ресторанов за 10 минут!', 
		'descr'=>'Сервис быстрого создания электронного меню для ресторанов и кафе, с удобным управлением, корзиной для приема заказов!', 
		'template'=>'site/views/tpl.page-iiko-connection.php',],		
     // no index, no follow
     'Confirmpass'=>[
		'title'=> 'Chefs Menu! Подтверждение пароля', 
	 	'descr'=>'', 
		'template'=>'site/views/tpl.confirmpass.php',],
     'ConfirmSubdomain'=>[
		'title'=> 'Chefs Menu! Подтверждение адреса', 
	 	'descr'=>'', 
		'template'=>'site/views/tpl.confirm_subdomain.php',],
     'Activation'=>[
		'title'=>'Chefs Menu! Подготовка к открытию', 
	 	'descr'=>'', 
		'template'=>'site/views/tpl.activation.php',],
     'ControlPanel'=>[
		'title'=> 'Chefs Menu! Панель Управления', 
	 	'descr'=>'', 
		'template'=>'/adm/views/tpl.app.php',],
     'RDSAdminMain'=>[
		'title'=> 'RDS-Admin / Admin', 
	 	'descr'=>'', 
		'template'=>'/rds/views/tpl.admin.php',],
     'RDSAdminEnter'=>[
		'title'=> 'RDS-Admin / Enter', 
	 	'descr'=>'', 
		'template'=>'/rds/views/tpl.enter.php',],
     'RDSAdminDelUser'=>[
		'title'=>'RDS-Admin / Add contract', 
	 	'descr'=>'', 
		'template'=>'/rds/views/tpl.deluser.php',],
     'RDSAdminAddContract'=>[
		'title'=>'RDS-Admin / Add contract', 
	 	'descr'=>'', 
		'template'=>'/rds/views/tpl.add_contract.php',],
     'RDSAdmin404'=>[
		'title'=> 'Chefs Menu! Unknown page', 
	 	'descr'=>'', 
		'template'=>'/rds/views/tpl.404.php',],
 ];


?>