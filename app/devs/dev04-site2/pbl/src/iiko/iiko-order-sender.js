import {GLB} from '../glb.js';
import $ from 'jquery';

export var IIKO_ORDER_SENDER = {
    send_async:function(order,pickupself) {
        // ORDER FOR DELIVERY OR PICKUPSELF
        return new Promise((res,rej)=>{
            
            const PATH = 'pbl/lib/iiko/';
            const url = GLB_APP_URL + PATH + 'iiko_send_order_for_delivery.php';

            const id_cafe = order.id_cafe;
            const data = {
                id_cafe,
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
    send_to_table_async: function(orders, table_number ) {
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
    }  
};