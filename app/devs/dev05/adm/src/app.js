import {GLB} from './glb.js';

import {LNG} from './lng.js';
import {MENU_ICONS} from './menu-icons.js';
import {CURRENCY} from './currency.js';
import {THE_CAFE} from './the-cafe.js';
import {TABINDEX} from './tabindex.js';
import {DEVICE} from './device.js';
import {MENU} from './menu.js';
import {ITEMS} from './items.js';
import {INPUTS_LENGTH} from './inputs_length.js';

import {VIEWS} from './views.js';
import {VIEW_STD} from './view-std.js';

import {VIEW_ALL_MENU} from './view-all-menu.js';
import {VIEW_ALL_ITEMS} from './view-all-items.js';
import {VIEW_EDIT_MENU} from './view-edit-menu.js';
import {VIEW_EDIT_ITEM} from './view-edit-item.js';
import {VIEW_MAIN_HELP} from './view-main-help.js';
import {VIEW_GET_CONTRACT} from './view-get-contract.js';
import {VIEW_ITEM_IMAGE_CHANGE} from './view-item-image-change.js';
import {VIEW_CUSTOMIZE_ALL} from './view-customize-all.js';
import {VIEW_CUSTOMIZING_CAFE} from './view-customizing-cafe.js';
import {VIEW_CUSTOMIZING_CART} from './view-customizing-cart.js';
import {VIEW_CUSTOMIZING_STAFF}	from './view-customizing-staff.js';
import {VIEW_CUSTOMIZE_INTERFACE} from './view-customize-interface.js';
import {VIEW_CAFE_DESCRIPTION} from './view-cafe-description.js';
import {VIEW_REPLACING_PARENT_SECTION} from './view-replacing-parent-section.js';
import {VIEW_CHOOSING_MODIFIERS} from './view-choosing-modifiers.js';
import {VIEW_CAFE_LINK} from './view-cafe-link.js';
import {VIEW_CAFE_TABLES} from './view-cafe-tables.js';

import {VIEW_CHANGE_PASSWORD} from './view-change-password.js';
import {VIEW_CHANGE_SUBDOMAIN} from './view-change-subdomain.js';
import {VIEW_IIKO_ADDING_API_KEY} from './view-iiko-adding-api-key.js';
import {VIEW_IIKO_CUSTOMIZATION} from './view-iiko-customization.js';
import {VIEW_IIKO_WEB_HOOKS} from './view-iiko-web-hooks.js';
import {VIEW_IIKO_MODIF_DICTIONARY} from './view-iiko-modif-dictionary.js';
import {VIEW_CAFE_TABLES_QRCODE} from './view-cafe-tables-qrcode.js';


import {VIEW_MODAL_MESSAGE} from './view-modal-message.js';
import {VIEW_MODAL_CONFIRM} from './view-modal-confirm.js';
import {VIEW_ACTION_SHEET} from './view-action-sheet.js';

import {IikoLoader} from './iiko/iiko-loader.js';
import {ExtMenuParser} from './iiko/iiko-extmenu-parser.js';
import {IikoUpdater} from './iiko/iiko-updater.js';

GLB.LNG = LNG;
GLB.CURRENCY = CURRENCY;
GLB.MENU_ICONS = MENU_ICONS;
GLB.THE_CAFE = THE_CAFE;
GLB.VIEWS = VIEWS;
GLB.TABINDEX = TABINDEX;
GLB.DEVICE = DEVICE;
GLB.MENU = MENU;
GLB.ITEMS = ITEMS;
GLB.INPUTS_LENGTH = INPUTS_LENGTH;

GLB.IIKO_LOADER = IikoLoader;
GLB.IIKO_EXT_MENU_PARSER = ExtMenuParser;
GLB.IIKO_UPDATER = IikoUpdater;


GLB.VIEW_ALL_MENU = $.extend( VIEW_ALL_MENU, VIEW_STD);
GLB.VIEW_ALL_ITEMS = $.extend( VIEW_ALL_ITEMS, VIEW_STD);
GLB.VIEW_EDIT_MENU = $.extend( VIEW_EDIT_MENU, VIEW_STD);
GLB.VIEW_EDIT_ITEM = $.extend( VIEW_EDIT_ITEM, VIEW_STD);
GLB.VIEW_GET_CONTRACT = $.extend( VIEW_GET_CONTRACT, VIEW_STD);
GLB.VIEW_CUSTOMIZE_ALL = $.extend( VIEW_CUSTOMIZE_ALL, VIEW_STD);
GLB.VIEW_ITEM_IMAGE_CHANGE = $.extend( VIEW_ITEM_IMAGE_CHANGE, VIEW_STD);
GLB.VIEW_CUSTOMIZING_CAFE = $.extend( VIEW_CUSTOMIZING_CAFE, VIEW_STD);
GLB.VIEW_CUSTOMIZING_CART = $.extend( VIEW_CUSTOMIZING_CART, VIEW_STD);
GLB.VIEW_CUSTOMIZING_STAFF = $.extend( VIEW_CUSTOMIZING_STAFF, VIEW_STD);
GLB.VIEW_CUSTOMIZE_INTERFACE = $.extend( VIEW_CUSTOMIZE_INTERFACE, VIEW_STD);
GLB.VIEW_CAFE_DESCRIPTION = $.extend( VIEW_CAFE_DESCRIPTION, VIEW_STD);
GLB.VIEW_MAIN_HELP = $.extend( VIEW_MAIN_HELP, VIEW_STD); // todo Send email to Support
GLB.VIEW_REPLACING_PARENT_SECTION = $.extend( VIEW_REPLACING_PARENT_SECTION, VIEW_STD);
GLB.VIEW_CHOOSING_MODIFIERS = $.extend( VIEW_CHOOSING_MODIFIERS, VIEW_STD);
GLB.VIEW_CAFE_LINK = $.extend( VIEW_CAFE_LINK, VIEW_STD);
GLB.VIEW_CAFE_TABLES = $.extend( VIEW_CAFE_TABLES, VIEW_STD);
GLB.VIEW_CHANGE_PASSWORD = $.extend( VIEW_CHANGE_PASSWORD, VIEW_STD);
GLB.VIEW_CHANGE_SUBDOMAIN = $.extend( VIEW_CHANGE_SUBDOMAIN, VIEW_STD);
GLB.VIEW_IIKO_ADDING_API_KEY = $.extend( VIEW_IIKO_ADDING_API_KEY, VIEW_STD);
GLB.VIEW_IIKO_CUSTOMIZATION = $.extend( VIEW_IIKO_CUSTOMIZATION, VIEW_STD);
GLB.VIEW_IIKO_WEB_HOOKS = $.extend( VIEW_IIKO_WEB_HOOKS, VIEW_STD);
GLB.VIEW_IIKO_MODIF_DICTIONARY = $.extend( VIEW_IIKO_MODIF_DICTIONARY, VIEW_STD);
GLB.VIEW_CAFE_TABLES_QRCODE = $.extend( VIEW_CAFE_TABLES_QRCODE, VIEW_STD);

GLB.VIEW_MODAL_MESSAGE = VIEW_MODAL_MESSAGE;
GLB.VIEW_MODAL_CONFIRM = VIEW_MODAL_CONFIRM;
GLB.VIEW_ACTION_SHEET = VIEW_ACTION_SHEET;

GLB.PARAMS = {};

export default function(){
		
	$(function(){

	window.onkeydown = evt => {
	    if (evt.key == 'Tab') {
	    	console.log("tab!!!!")
	        evt.preventDefault();
	    }
	};	

	LNG.init(CFG.user_lang);
	MENU_ICONS.init({LNG:LNG});
	INPUTS_LENGTH.init();
	CURRENCY.init({THE_CAFE:THE_CAFE});
	THE_CAFE.init();
	TABINDEX.init();
	GLB.DEVICE.init();
	GLB.MENU.init();
	GLB.ITEMS.init();

	VIEWS.init({
		viewsParent:"#the-app-views"
	});

	VIEWS.addView(
		VIEW_ALL_MENU.init({
		name:"all-menu-with-repos",
		template:"#templates .view-all-menu"
	}));

	VIEWS.addView(
		VIEW_EDIT_MENU.init({
		name:"view-edit-menu",
		template:"#templates .view-edit-menu",
		anim:'zoomOut'
	}));


	VIEWS.addView(
		VIEW_CUSTOMIZE_ALL.init({
		name:"view-customize-all",
		template:"#templates .view-customize-all",
		anim:'zoomOut'
	}));


	VIEWS.addView(
		VIEW_CUSTOMIZING_CAFE.init({
		name:"view-customizing-cafe",
		template:"#templates .view-customizing-cafe",
		anim:'animLeft'
	}));

	VIEWS.addView(
		VIEW_CUSTOMIZING_CART.init({
		name:"view-customizing-cart",
		template:"#templates .view-customizing-cart",
		anim:'animLeft',
	}));

	VIEWS.addView(
		VIEW_CUSTOMIZING_STAFF.init({
		name:"view-customizing-staff",
		template:"#templates .view-customizing-staff",
		anim:'animLeft',
		vars:{tgbot_link:CFG.tgbot_link}
	}));	

	VIEWS.addView(
		VIEW_CAFE_DESCRIPTION.init({
		name:"view-cafe-description",
		template:"#templates .view-cafe-description",
		anim:'animLeft'
	}));

	VIEWS.addView(
		VIEW_CHANGE_PASSWORD.init({
		name:"view-change-password",
		template:"#templates .view-change-password",
		anim:'animLeft'
	}));

	VIEWS.addView(
		VIEW_CHANGE_SUBDOMAIN.init({
		name:"view-change-subdomain",
		template:"#templates .view-change-subdomain",
		anim:'animLeft'
	}));

	VIEWS.addView(
		VIEW_IIKO_ADDING_API_KEY.init({
		name:"view-iiko-adding-api-key",
		template:"#templates .view-iiko-adding-api-key",
		anim:'animLeft'
	}));	

	VIEWS.addView(
		VIEW_IIKO_CUSTOMIZATION.init({
		name:"view-iiko-customization",
		template:"#templates .view-iiko-customization",
		anim:'animLeft'
	}));	
	

	VIEWS.addView(
		VIEW_IIKO_WEB_HOOKS.init({
		name:"view-iiko-web-hooks",
		template:"#templates .view-iiko-web-hooks",
		anim:'animLeft'
	}));		

	VIEWS.addView(
		VIEW_IIKO_MODIF_DICTIONARY.init({
		name:"view-iiko-modif-dictionary",
		template:"#templates .view-iiko-modif-dictionary",
		anim:'animLeft'
	}));

	VIEWS.addView(
		VIEW_CAFE_TABLES_QRCODE.init({
		name:"view-qrcode-with-tables",
		template:"#templates .view-qrcode-with-tables",
		anim:'animLeft'
	}));			

	VIEWS.addView(
		VIEW_CAFE_LINK.init({
		name:"view-cafe-link",
		template:"#templates .view-cafe-link",
		anim:'animLeft'
	}));

	VIEWS.addView(
		VIEW_CAFE_TABLES.init({
		name:"view-cafe-tables",
		template:"#templates .view-cafe-tables",
		anim:'animLeft'
	}));

	VIEWS.addView(
		VIEW_CUSTOMIZE_INTERFACE.init({
		name:"view-customize-interface",
		template:"#templates .view-customize-interface",
		anim:'animLeft'
	}));

	VIEWS.addView(
		VIEW_ALL_ITEMS.init({
		name:"all-items",
		template:"#templates .view-all-items",
		template_item:"#templates .item-slide-template-4 .item"
	}));

	VIEWS.addView(
		VIEW_EDIT_ITEM.init({
		name:"view-edit-item",
		template:"#templates .view-edit-item",
		anim:'zoomOut'	
	}));

	VIEWS.addView(
		VIEW_ITEM_IMAGE_CHANGE.init({
		name:"view-item-image-change",
		template:"#templates .view-item-image-change",
		anim:'zoomOut'
	}));

	VIEWS.addView(
		VIEW_REPLACING_PARENT_SECTION.init({
		name:"view-replacing-parent-section",
		template:"#templates .view-replacing-parent-section",
		anim:'animLeft'
	}));

	VIEWS.addView(
		VIEW_CHOOSING_MODIFIERS.init({
		name:"view-choosing-modifiers",
		template:"#templates .view-choosing-modifiers",
		anim:'animLeft'
	}));	

	VIEWS.addView(
		VIEW_GET_CONTRACT.init({
		name:"view-get-contract",
		template:"#templates .view-get-contract",
		anim:'animLeft'
	}));

	VIEWS.addView(
		VIEW_MAIN_HELP.init({
		name:"view-main-help",
		template:"#templates .view-main-help",
		anim:'zoomOut'
	}));	

	// default views 

	VIEWS.addView(
		VIEW_ACTION_SHEET.init({
		name:"view-action-sheet",
		template:"#templates .view-action-sheet"
	}));

	VIEWS.addView(
		VIEW_MODAL_CONFIRM.init({
		name:"view-modal-confirm",
		template:"#templates .view-modal-confirm"
	}));

	VIEWS.addView(
		VIEW_MODAL_MESSAGE.init({
		name:"view-modal-message",
		template:"#templates .view-modal-message"
	}));


	VIEW_ALL_MENU.update(CFG.id_user);
	VIEWS.setCurrent(VIEW_ALL_MENU.name);

	
	// var ADMIN_TMR_RESIZE;
	
	});

};


