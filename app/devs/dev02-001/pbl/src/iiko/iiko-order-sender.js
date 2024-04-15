import {GLB} from '../glb.js';
import $ from 'jquery';

export var IIKO_ORDER_SENDER = {
    send_async:function(order,pickupself) {
        // ORDER FOR DELIVERY OR PICKUPSELF
        return new Promise((res,rej)=>{
            this._get_token_async()
            .then((token)=>{                
                this._send_with_token_async(order,pickupself,token)
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
    send_to_table_async: function(orders, table_number ) {
        // ORDER TO TABLE
        return new Promise((res,rej)=>{
            this._get_token_async()
            .then((token)=>{                
                this._send_with_token_to_table_async(orders,table_number,token)
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
    _send_with_token_async:function(order,pickupself,token){
        // ORDER FOR DELIVERY OR PICKUPSELF
        return new Promise((res,rej)=>{
            
            const PATH = 'pbl/lib/iiko/';
            const url = GLB_APP_URL + PATH + 'iiko_send_order_for_delivery.php';

            const id_cafe = order.id_cafe;
            const data = {
                id_cafe,
                token,
                order,
                pickupself
            };

            console.log("data",data)

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
    _send_with_token_to_table_async:function(orders, table_number, token) {
        // ORDER TO TABLE
        return new Promise((res,rej)=>{            
            
            const fn = {
                dateExport:function(_date){
                    return _date.getDate()+"-"+(_date.getMonth()+1)+"-"+_date.getFullYear()+" "+_date.getHours()+":"+_date.getMinutes();
                }
            };
                            
            const PATH = 'pbl/lib/iiko/';
            const url = GLB_APP_URL + PATH + 'iiko_send_order_to_table.php';

            const id_cafe = GLB.CAFE.get().id;
            const total_price = GLB.CART.get_total_price();                
            const order_time_sent = fn.dateExport(new Date());

            const data = {
                id_cafe,
                token,
                orders,
                table_number,
                total_price,
                order_time_sent                
            };

            console.log('data',data);            
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