import {GLB} from '../glb.js';

export var IikoLoader = {

    init:function() {

        this.EXTERNALMENU_MODE = true; 
        this.TOKEN = false;
        this.ORGANIZATION_ID = false;
        this.TERMINAL_GROUP_ID = false;
        this.ARR_MENU_IDS = [];
        this.PRICE_CATEGORIES = null;   
        this.CURRENT_MENU = 0;        
        this.NOW_LOADING = false;
        return this;

    },
    load_extmenu_asynq: function(extmenu_id, externalmenu_mode = true) {
        return new Promise((res,rej)=>{        
            

            if(this.NOW_LOADING){
                rej("Идет загрузка меню, попробуйте позже");
                return;
            };
            
            this.NOW_LOADING = true;        
            this.EXTERNALMENU_MODE = externalmenu_mode;
            this.EXTMENU_ID = extmenu_id;

            console.log('EXTERNALMENU_MODE = ', this.EXTERNALMENU_MODE);
		    console.log('id_menu_for_loading = ', this.EXTMENU_ID);

            // GETTING TOKEN
            this.get_token_asynq()
            .then((token)=>{

                this.TOKEN = token;

                // GETTING EXTMENU ID
                this.get_menu_by_id_asynq(this.EXTMENU_ID)
                .then((vars)=>{                            
                    this.NOW_LOADING = false;

                    console.log('vars and summary = ', vars);

                    res([
                        vars['menu'],
                        vars['menu-hash'],
                        vars['need-to-update'],
                        vars['summary_data'],
                    ]);

                })
                .catch((vars)=>{
                    this.NOW_LOADING = false;
                    rej(vars);
                });           

            })
            .catch((vars)=>{
                console.log('err get token',vars);
                rej(vars);
            });

        });
    },

    get_token_asynq:function() {
        return new Promise((res,rej)=>{

            let cafe = GLB.THE_CAFE.get();

            // try get token localy first
            let token = this.get_token_localy();
            if(token){ res(token); return; };

            const _this = this;
            const PATH = './adm/lib/iiko/';
            const url = PATH + 'get_token_for_cafe.php';
            const data = { id_cafe:cafe.id };
            
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
                if(result&&result["token"]){
                    token = result["token"];
                    this.save_token_localy(token);                
                    res(token);
                }else{
                    rej(result);
                }
            });

        });
    },

    get_token_localy:function(){        
        var token = GLB._IIKO_TOKEN;        
        var token_date = GLB._IIKO_TOKEN_DATE;
        if(!token || !token_date){
            console.log("unknown token, will be get new");            
            return false;
        }else{                        
            var duration = (new Date().getTime()-token_date)/3600000;
            console.log("duration",duration);
            if(!duration || duration>.5){
                // if more than 30 minutes
                console.log("token too old, and will be get new");                
                return false;                
            }else{
                return token;
            }     
        }
    },
    save_token_localy:function(token) {        
        GLB._IIKO_TOKEN = token;
        GLB._IIKO_TOKEN_DATE = new Date().getTime();
    },
    get_menu_by_id_asynq:function(menu_id){
        return new Promise((res,rej)=>{
 
            const PATH = "./adm/lib/iiko/";
            
            const url = this.EXTERNALMENU_MODE ? PATH + "get_menu_v2_by_id.php" : PATH + "get_oldway_menu_by_id.php";

            const cafe = GLB.THE_CAFE.get();
            const iiko_params = GLB.THE_CAFE.get('iiko_params');

            const data = {
                token:this.TOKEN,
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
                if(res(result) && !result['error']){
                    res[result];
                }else{
                    rej[result];
                }
            });

        })        
    }

};
