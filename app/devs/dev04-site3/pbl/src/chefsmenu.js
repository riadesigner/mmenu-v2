import {GLB} from './glb.js';
import $ from 'jquery';

export var CHEFSMENU = {
    init:function(opt){
    
        var _this=this;
        
        if(!opt.G_DATA || !opt.$link || !opt.CHEFS_URL){
            return false;
        };

        this.G_DATA = opt.G_DATA;
        this.$link = opt.$link;
        this.CHEFS_URL = opt.CHEFS_URL;
        this.$body = $('body');
                
        this.CLASS_MENU_IS_SHOWED = 'chefsmenu-is-showed';
        this.CLASS_READY_TO_USE = 'ready-to-use';
        this.CLASS_IIKO_MODE = 'menu-iiko-mode';
        this.CLASS_CHEFSMENU_MODE = 'menu-chefsmenu-mode';
        this.CLASS_SHOW_PRELOAD = 'chefsmenu-show-preload';
        this.CLASS_CAFE_IS_IN_ARCHIVE = 'chefsmenu-cafe-is-in-archive';
                
        this.UNIQ_CAFE = this.$link.attr('data-cafe');
        this.NO_CLOSE = this.$link.attr("noclose");
        this.AUTOLOAD = this.$link.attr("autoload");
        this.onMenuReady = opt.onReady;

        this.load_style_asynq()
        .then((vars)=>{   
            
            this.pre_build_asynq()
            .then((vars)=>{

                this.$link.on("click",()=>{                             
                    window[this.G_DATA].SAVED_BODY_POS = window.pageYOffset;                        
                    this.show({cafe:this.UNIQ_CAFE});
                    return false;
                });
                
                this.AUTOLOAD && setTimeout(()=>{  
                    this.show({cafe:this.UNIQ_CAFE}); 
                },200);
                
                this.init_views(); 

            })
            .catch((vars)=>{
                console.log('err',vars);
            });

        })
        .catch((vars)=>{
            console.log('err',vars);
        });


    },

    init_views:function() {
        var _this=this;        

        var PATH_TO_TEMPLATES = "#mm2-templates";

        GLB.LNG.init("ru");

        GLB.MENU_ICONS.init();

        GLB.UVIEWS.init(".mm2-all-views");        

        GLB.UVIEWS.addview(
            GLB.VIEW_ALLMENU.init({
            name:"the-allmenu",            
            template:PATH_TO_TEMPLATES+" .view-allmenu",
            chefsmenu:_this,
            noclose:this.NO_CLOSE,
            anim:'animLeft'            
        }));

        GLB.UVIEWS.addview(
            GLB.VIEW_ALLITEMS.init({
            name:"the-allitems",
            template:PATH_TO_TEMPLATES+" .view-items",
            chefsmenu:_this,
            noclose:this.NO_CLOSE,
            anim:'animLeft'
        }));

        GLB.UVIEWS.addview(
            GLB.VIEW_TABLE_CHANGE.init({
            name:"view-table-change",
            template:PATH_TO_TEMPLATES+" .view-table-change",
            chefsmenu:_this,
            noclose:this.NO_CLOSE,
            anim:'animLeft'
        }));

        GLB.UVIEWS.addview(
            GLB.VIEW_CART.init({
            name:"the-showcart",
            template:PATH_TO_TEMPLATES+" .view-showcart",
            chefsmenu:_this,
            noclose:this.NO_CLOSE,
            anim:'animLeft'
        }));    

        GLB.UVIEWS.addview(
            GLB.VIEW_CHOOSING_MODE.init({
            name:"the-choosing-mode",
            template:PATH_TO_TEMPLATES+" .view-choosing-mode",
            chefsmenu:_this,
            noclose:this.NO_CLOSE,
            anim:'animLeft'
        }));

        GLB.UVIEWS.addview(
            GLB.VIEW_ORDERING.init({
            name:"the-ordering",
            template:PATH_TO_TEMPLATES+" .view-ordering",
            chefsmenu:_this,
            noclose:this.NO_CLOSE,
            anim:'animLeft'
        }));

        GLB.UVIEWS.addview(
            GLB.VIEW_ORDER_OK.init({
            name:"the-order-ok",
            template:PATH_TO_TEMPLATES+" .view-order-ok",
            chefsmenu:_this,
            noclose:this.NO_CLOSE,
            anim:'animLeft'
        }));

        GLB.UVIEWS.addview(
            GLB.VIEW_ORDER_CANCEL.init({
            name:"the-order-cancel",
            template:PATH_TO_TEMPLATES+" .view-order-cancel",
            chefsmenu:_this,
            noclose:this.NO_CLOSE,
            anim:'animLeft'
        }));        

        GLB.TABINDEX.init();

        this.onMenuReady && this.onMenuReady();

    },
    set_allitems:function(menu,allitems) {
        return window[this.G_DATA].ALLITEMS[menu.id]=allitems; 
    },
    get_allitems:function(menu) {
        return window[this.G_DATA].ALLITEMS[menu.id];
    },
    get_cafe:function() {
       return window[this.G_DATA].ALLCAFE[this.get_uniq_name()]; 
    },        
    get_allmenu:function() {
       return window[this.G_DATA].ALLMENU[this.get_uniq_name()];
    },
    get_uniq_name:function() {            
        return this.CAFE_UNIQ_NAME;
    },
    get_url_to_images:function(){
        return window[this.G_DATA].URL_TO_IMAGES;
    },
    show:function(options) {
        var _this=this;

        window[_this.G_DATA].SHOWED++;        

        // prepare to show
        this.CAFE_UNIQ_NAME = options.cafe;
        
        _this.$body.addClass(_this.CLASS_MENU_IS_SHOWED);
        _this.$body.addClass(_this.CLASS_SHOW_PRELOAD);        
        _this.$menu.show();        

        setTimeout(function() {_this.now_loading();},50);

        var fn = {
            attachSkin:function(label){
                              
                var G = window[_this.G_DATA]; 

                var default_label='light-lemon';
                var label = label?label: default_label;
                
                if(G.CURRSKIN.label&&G.CURRSKIN.label===label){                    
                    // console.log("SKIN "+label+" already was attached.");
                    return;
                }else{
                    G.CURRSKIN.link && _this.detach_style(G.CURRSKIN.link);                        

                    var skin = G.ALLSKINS[label]||G.ALLSKINS[default_label];
                    
                    var mainStyle = G.MAINSTYLE;
                    var msk;

                    for(var nm in skin){
                      if (skin.hasOwnProperty(nm)) {                            
                            msk = new RegExp("%\\["+nm+"\\]%",'gi');
                            mainStyle = mainStyle.replace(msk,skin[nm]);
                      }
                    };      
                    
                    var nm = 'skin-label';
                    var msk = new RegExp("%\\["+nm+"\\]%",'gi');
                    mainStyle = mainStyle.replace(msk,label);

                    G.CURRSKIN.label=label;
                    G.CURRSKIN.link = _this.append_style(mainStyle);                        
                }
            }
        };        

        this.load_all_menu({onReady:function(){
            
            var cafe = _this.get_cafe();            
            var label = cafe.skin_label;

            fn.attachSkin(label);
            
            const ALL_VIEWS = GLB.UVIEWS.get();
            if(cafe.lang.toLowerCase()!==GLB.LNG.get_current()){
                GLB.LNG.update(cafe.lang);   
                for(let i in ALL_VIEWS){
                    if(ALL_VIEWS.hasOwnProperty(i)){
                        let VIEW = ALL_VIEWS[i].view;                        
                        VIEW._update_lng();
                    }
                } 
            };

            _this.show_win({skin:cafe.skin ,onReady:function(){
                            
                GLB.CAFE.init(cafe);
                
                GLB.VIEW_ALLMENU.update(_this.get_allmenu());
                GLB.CART.init();
                GLB.UVIEWS.go_first("fast");
                
                const MENU_IIKO_MODE = GLB.CAFE.is_iiko_mode();
                const MENU_MODE = MENU_IIKO_MODE?_this.CLASS_IIKO_MODE:_this.CLASS_CHEFSMENU_MODE;
                _this.$menu.addClass(MENU_MODE);

                const HAS_DELIVERY = GLB.CAFE.has_delivery();
                _this.$menu.addClass('cafe-has-delivery');

                // only for menu page                
                setTimeout(function() { 
                    _this.$body.removeClass(_this.CLASS_SHOW_PRELOAD);
                    _this.$menu.addClass(_this.CLASS_READY_TO_USE);                    

                },50);

                setTimeout(function() {
                    _this.end_loading(); 
                },300);
                

            }});

        }}); 



    },         
    
    show_win:function(opt){            
        opt&&opt.onReady&&opt.onReady();
    },

    now_loading:function(){
        this.LOADING = true;
        this.$menu && this.$menu.addClass("now-loading");
    },
    end_loading:function() {
        this.LOADING = false;
        this.$menu && this.$menu.removeClass("now-loading");
    },
    is_loading_now:function() {
        return this.LOADING;
    },
    pre_build_asynq:function(){    
        return new Promise((res,rej)=>{

            const _this=this;
            const fn = {
                loadApp:function(url) {
                    // console.log('start load',url)
                    _this.AJAX = $.ajax({
                        url: url+"?callback=?",
                        jsonpCallback:GLB.CALLBACK_RANDOM.get(),
                        dataType: "jsonp",
                        method:"POST",
                        success: function (response) {                        
                            if(!window[_this.G_DATA].WAS_BUILT){
                              fn.appendToBody(response[0].app);
                            }; 
                            _this.$menu = $("."+window[_this.G_DATA].PRE+"menu");
                            res(response);
                        },
                        error:function(error){                        
                            rej(error);
                        }
                    });
                },
                appendToBody:function(appHtml) {            
                    $('body').append(appHtml);
                    window[_this.G_DATA].WAS_BUILT = true;
                }
            };

            if(!window[_this.G_DATA].WAS_BUILT){ 
                // console.log("--- loadApp ---")
                fn.loadApp(GLB_APP_URL+"pbl/views/pbl.php");
            }else{ 
                _this.$menu = $("."+window[_this.G_DATA].PRE+"menu");
                res("ok");
            }

        });    
    },  
    detach_style:function(link){
        // document.removeChild();
        link.remove();
    },
    append_style:function(style) {
        var css = document.createElement('style');
        css.type = 'text/css';
        if(css.styleSheet){
            css.styleSheet.cssText = style;
        }else{
            css.appendChild(document.createTextNode(style));
        };         
        var link = document.getElementsByTagName("head")[0].appendChild(css);           
        return link;
    },
    load_style_asynq:function() {
        return new Promise((res,rej)=>{

            const _this = this;        
            const url = GLB_APP_URL+"pbl/css/style.php";
            
            const fn = {
                parseStyle:function(response){
                    let skins = {};
                    let allSkins = response['skins']['all-skins'];
                    for(let i=0;i<allSkins.length;i++){
                        skins[allSkins[i].label] = allSkins[i].params;                    
                    };
                    window[_this.G_DATA].ALLSKINS = skins;
                    window[_this.G_DATA].MAINSTYLE = response['main-style'];
                    _this.append_style(response['head-style']); 
                    res();
                }
            };

            if(window[_this.G_DATA].ALLSKINS!==null){
                res("ok");
            }else{
                // console.log("--load style--")
                this.AJAX = $.ajax({
                    url:url+'?callback=?',            
                    jsonpCallback:GLB.CALLBACK_RANDOM.get(),
                    dataType:"jsonp",
                    data:{},
                    method:"POST",
                    success: function (response){                
                        fn.parseStyle(response);                
                    },
                    error:function(response){
                        rej(response);
                    }
                });
            }

        });
    },     
    // load_style:function(opt) {

    //     var _this = this;        

    //     var url = GLB_APP_URL+"pbl/css/style.php";

    //     var fn = {
    //         parseStyle:function(response){
    //             var skins = {};
    //             var allSkins = response['skins']['all-skins'];
    //             for(var i=0;i<allSkins.length;i++){
    //                 skins[allSkins[i].label] = allSkins[i].params;                    
    //             };
    //             window[_this.G_DATA].ALLSKINS = skins;
    //             window[_this.G_DATA].MAINSTYLE = response['main-style'];
    //             _this.append_style(response['head-style']); 
    //             opt && opt.onReady && opt.onReady();
    //         }
    //     };

    //      if(window[_this.G_DATA].ALLSKINS!==null){
    //             opt && opt.onReady && opt.onReady();
    //      }else{
    //         // console.log("--load style--")
    //         this.AJAX = $.ajax({
    //             url:url+'?callback=?',            
    //             jsonpCallback:GLB.CALLBACK_RANDOM.get(),
    //             dataType:"jsonp",
    //             data:{},
    //             method:"POST",
    //             success: function (response){                
    //                 fn.parseStyle(response);                
    //             },
    //             error:function(response){
    //                 // console.log("err",response);
    //             }
    //         });
    //      }

    // }, 
    load_all_menu:function(opt){        
        var _this=this;
    
        var url = GLB_APP_URL+"pbl/lib/pbl.get_all_menu.php";
        
        var cafe_uniq_name = this.get_uniq_name();        

        var allmenu =  window[_this.G_DATA].ALLMENU[cafe_uniq_name];            

        var fn = {
            arr2obj:function(arr){
                var obj = {};
                for(var i=0;i<arr.length;i++){
                    obj[arr[i].id] = arr[i];
                };
                obj.arr = arr;
                return obj;
            }
        };

        var data = {cafe:cafe_uniq_name};

        console.log('data', data);


        if(!allmenu){ 
            

            this.AJAX = $.ajax({
                url: url + "?callback=?",
                jsonpCallback:GLB.CALLBACK_RANDOM.get(),
                dataType: "jsonp",
                method:"POST",
                data:data,
                success: function (response) {

                    if(response.error){

                        console.log("error",response.error);
                        _this.close_menu_win();
                        _this.goto404();
                    
                    }else{

                        var cafe = response.cafe;
                        if(cafe.cafe_status == 1){
                            _this.close_menu_win();
                            _this.gotoArchive();                                    
                        }else{
                            var allmenu = fn.arr2obj(response.menu);
                            window[_this.G_DATA].ALLMENU[cafe_uniq_name] = allmenu; 
                            window[_this.G_DATA].ALLCAFE[cafe_uniq_name] = response.cafe; 
                            setTimeout(function() {
                                opt.onReady && opt.onReady();       
                            },500);
                        }
                    }
                },
                error:function(response) {
                    console.log("err response",response);
                    _this.goto404();
                }
            });


        }else{
            
            // console.log("load from cache");
            opt.onReady && opt.onReady();

        };

    },
    goto404:function(){        
        //  console.log("TEST PAUSED")
        location.href = this.CHEFS_URL.server+'cafe/';
    },
    gotoArchive:function(){
        this.$body.addClass(this.CLASS_CAFE_IS_IN_ARCHIVE);        
    },
    close_menu_win:function(){ 
        var _this=this;

        setTimeout(function(){     
            window[_this.G_DATA].SHOWED--;                 
            
            _this.$body.removeClass(_this.CLASS_MENU_IS_SHOWED);
            _this.$body.removeClass(_this.CLASS_SHOW_PRELOAD);            
            _this.$menu && _this.$menu.removeClass(_this.CLASS_READY_TO_USE);
            _this.$menu && _this.$menu.hide();

            var pos = window[_this.G_DATA].SAVED_BODY_POS;
            pos && window.scroll(0, pos);            
            window[_this.G_DATA].SAVED_BODY_POS = 0;

        },300);        
    }   
};

