import {GLB} from '../glb.js';
import $ from 'jquery';

export var IIKO_STREET_LOADER = {
    load_async_for:function(id_cafe) {        
        return new Promise((res,rej)=>{            
            this._get_token_async()
            .then((token)=>{
                this._load_with_token_async(id_cafe,token)
                .then((result)=>{
                    res(result);
                })
                .catch((vars)=>{
                    rej(vars);    
                });                
            })
            .catch((vars)=>{
                rej(vars);
            });        
        });
    },
    // private
    _load_with_token_async:function(id_cafe, token){
        // LOAD STREETS FOR DELIVERY
        return new Promise((res,rej)=>{
            
            const PATH = 'pbl/lib/iiko/';
            const url = GLB_APP_URL + PATH + 'iiko_get_streets_for_delivery.php';

            const data = {id_cafe,token};

            const AJAX = $.ajax({
                url: url+"?callback=?",
                data:data,
                dataType: "jsonp",
                method:"POST",
                error:(result)=> {
                    rej(result);
                }
            });
            AJAX.then((result)=>{                        
                if(result && !result.error){
                    res(result);
                }else{
                    rej(result);
                }                
            });            

        });
    },
    _get_token_async: function(){
    	return new Promise((res,rej)=>{    
        
            const PATH = 'pbl/lib/iiko/';
            const url = GLB_APP_URL + PATH + 'get_token_for_cafe_pbl.php';            

            // try get token localy first
            let token = this._get_token_localy();
            if(token){ res(token); return; };

            const data = {
                id_cafe:GLB.CAFE.get().id
            };

            console.log('data----,url',data, url)

            // load new token otherwise
            const AJAX = $.ajax({
                url: url+"?callback=?",
                data:data,
                dataType: "jsonp",
                method:"POST",
                error:(err)=> {
                    rej(err)
                }
            });

            AJAX.then((result)=>{
                if(result&&result["token"]){
                    token = result["token"];
                    this._save_token_localy(token);                
                    res(token);
                }else{
                    rej(result);
                }
            });

    	}); 
    },
    _get_token_localy:function(){                
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
    _save_token_localy:function(token) {        
        GLB._IIKO_TOKEN = token;
        GLB._IIKO_TOKEN_DATE = new Date().getTime();
    } 
};