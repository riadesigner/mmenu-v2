import {GLB} from '../glb.js';

export var IikoLoader = {

    init:function() {

        this.EXTERNALMENU_MODE = true; 
        this.TOKEN = false;
        this.ORGANIZATION_ID = null;
        this.TERMINAL_GROUP_ID = null;
        this.ARR_MENU_IDS = [];
        this.PRICE_CATEGORIES = null;   
        this.CURRENT_MENU = 0;        
        this.NOW_LOADING = false;
        return this;

    },
    // @param extmenu_id: string
    // @param externalmenu_mode: boolean
    // @return Promise
    load_extmenu_asynq: function(extmenu_id, externalmenu_mode = true) {
        return new Promise((res,rej)=>{        
            
            if(this.NOW_LOADING){
                rej("Идет загрузка меню, попробуйте позже");
                return;
            };
            
            this.NOW_LOADING = true;        
            this.EXTERNALMENU_MODE = externalmenu_mode;
            this.EXTMENU_ID = extmenu_id;

            // GETTING EXTMENU ID
            this.get_menu_by_id_asynq(this.EXTMENU_ID)
            .then((vars)=>{                            
                this.NOW_LOADING = false;

                res({
                    idMenuSaved:vars['id-menu-saved'],
                    newMenuHash:vars['new-menu-hash'],
                    needToUpdate:vars['need-to-update'],
                    menu:vars['menu'],
                });

            })
            .catch((err)=>{
                this.NOW_LOADING = false;
                rej(err);
            }); 

        });
    },

    get_menu_by_id_asynq:function(menu_id){
        return new Promise((res,rej)=>{
 
            const PATH = "./adm/lib/iiko/";                        

            const url = this.EXTERNALMENU_MODE ? PATH + "get_menu_v2_by_id.php" : PATH + "get_oldway_menu_by_id.php";

            const cafe = GLB.THE_CAFE.get();
            const iiko_params = GLB.THE_CAFE.get('iiko_params');

            const data = {
                id_cafe:cafe.id,
                externalMenuId: menu_id,
                currentExtmenuHash: this.EXTERNALMENU_MODE ? iiko_params['current_extmenu_hash']: "",
            };

            const AJAX = $.ajax({
                url: url+"?callback=?",
                data:data,
                dataType: "jsonp",
                method:"POST",
                error:(err)=> {                    
                    rej(err);                    
                }
            });

            AJAX.then((result)=>{                            
                if(result && !result['error']){
                    console.log('result ok after loading nomencl', result);
                    res(result);
                }else{
                    console.log('err 2 after loading nomencl', result);
                    rej(result);
                }
            });

        })        
    }

};
