
import {GLB} from './glb.js';
import $ from 'jquery';

import {META_VIEWPORT} from './meta-viewport.js';
import {CALLBACK_RANDOM} from './callback-random.js';
import {MOBILE_BUTTONS} from './mobile_buttons.js';

import {UVIEWS} from './uviews.js';
import {CAFE} from './cafe.js';
import {LNG} from './lng.js';
import {MENU_ICONS} from './menu-icons.js';
import {CART} from './cart.js';
import {ITEM} from './item.js';
import {CHEFSMENU} from './chefsmenu.js';
import {TABINDEX} from './tabindex.js';
import {MENU_TABLE_MODE} from './menu-table-mode.js';

import {VIEW_STD} from './view-std.js';
import {VIEW_ALLMENU} from './view-allmenu.js';
import {VIEW_ALLITEMS} from './view-allitems.js';
import {VIEW_CART} from './view-cart.js';
import {VIEW_CHOOSING_MODE} from './view-choosing-mode.js';

import {VIEW_ORDERING} from './view-ordering.js';
import {VIEW_ORDER_OK} from './view-order-ok.js';
import {VIEW_ORDER_CANCEL} from './view-order-cancel.js';
import {VIEW_TABLE_CHANGE} from './view-table-change.js';

GLB.META_VIEWPORT = META_VIEWPORT;
GLB.CALLBACK_RANDOM = CALLBACK_RANDOM;
GLB.MOBILE_BUTTONS = MOBILE_BUTTONS;

GLB.UVIEWS = UVIEWS;
GLB.CAFE = CAFE;
GLB.LNG = LNG;
GLB.MENU_ICONS = MENU_ICONS;
GLB.CART = CART;
GLB.ITEM = ITEM;
GLB.CHEFSMENU = CHEFSMENU;
GLB.TABINDEX = TABINDEX;
GLB.MENU_TABLE_MODE = MENU_TABLE_MODE;

GLB.VIEW_ALLMENU = $.extend( VIEW_ALLMENU, VIEW_STD );
GLB.VIEW_ALLITEMS = $.extend( VIEW_ALLITEMS, VIEW_STD );
GLB.VIEW_CART = $.extend( VIEW_CART, VIEW_STD );
GLB.VIEW_ORDERING = $.extend( VIEW_ORDERING, VIEW_STD );
GLB.VIEW_CHOOSING_MODE = $.extend( VIEW_CHOOSING_MODE, VIEW_STD );
GLB.VIEW_ORDER_OK = $.extend( VIEW_ORDER_OK, VIEW_STD );
GLB.VIEW_ORDER_CANCEL = $.extend( VIEW_ORDER_CANCEL, VIEW_STD );

GLB.VIEW_TABLE_CHANGE = $.extend( VIEW_TABLE_CHANGE, VIEW_STD );


GLB.G_DATA = "CHEFSMENUGLOBALDATA";

export default (function($){    

    GLB.META_VIEWPORT.init();    
    
    /**
    * EXT_MODE – for mobile only.
    * if EXT_MODE, turn on:
    * - view-choosing-mode
    * - view-thank-you
    */
    var EXT_MODE = GLB.META_VIEWPORT.is_mobile();  

    var $menus = $('.chefsmenu-link');

       if($menus.length){

            if(!window[GLB.G_DATA]){
                window[GLB.G_DATA] = {
                    WAS_BUILT:false,
                    PRE:'mm2-',
                    SHOWED:0,
                    ALLCAFE:{},
                    ALLMENU:{},
                    ALLITEMS:{},
                    ALLSKINS:null,
                    MAINSTYLE:'',                    
                    CURRSKIN:{id:null,link:null},
                    EXT_MODE:EXT_MODE                 
                }
            };            

            var fn = {
                
                prepare_menu:function($link, onReady){
                    var CHM = $.extend({},CHEFSMENU);
                    CHM.init({
                     G_DATA:GLB.G_DATA,
                     $link:$link,
                     CHEFS_URL:CHEFS_URL,
                     onReady:onReady
                    });   
                }
            };

            // prepare first menu            
            fn.prepare_menu($menus.eq(0),function(){
                if($menus.length>1){
                    //prepare other menus
                    $menus.each(function(i){
                       i&&fn.prepare_menu($(this),false); 
                   });
                }
            });

        }; 

	
})(jQuery);