import {GLB} from './glb.js';
import $ from 'jquery';

export const THE_ORDER_SENDER = {

    
/**
 * ------------------------------------------------
 *
 *         ORDER FOR DELIVERY OR PICKUPSELF
 * 
 * ------------------------------------------------
 */     
    send_async:function(order, pickupself) {        
        return new Promise((res,rej)=>{

            const PATH = 'pbl/lib/';
            const url = GLB_APP_URL + PATH + 'pbl.send_order_for_delivery.php';

            const cafe_uniq_name = order.cafe_uniq_name;
            const data = {
                cafe_uniq_name,
                order,
                pickupself
            };

            console.log('start sending data async: ',data);

            const AJAX = $.ajax({
                url: url,                
                dataType: "json",
                method:"POST",
                data:data,
                xhrFields: {
                    withCredentials: true  // Для отправки cookies при CORS
                },      
                error:(result)=> {
                    console.log('err result=', result);
                    rej(result);
                }
            });
            AJAX.then((result)=>{       
                console.log('ok result=', result);                 
                if(result && !result.error){
                    res(result);
                }else{
                    rej(result);
                }                
            });            

        });
    },  

/**
 * -------------------------------
 *
 *         ORDER TO TABLE
 * 
 * -------------------------------
 */ 

    send_to_table_async: function(order, table_number ) {
         
         return new Promise((res,rej)=>{                                    
            
            const PATH = 'pbl/lib/';
            const url = GLB_APP_URL + PATH + 'pbl.send_order_to_table.php';
            const cafe_uniq_name = GLB.CAFE.get('uniq_name');
            // QR token from menu URL (/table/{token}) — chats tableId, not iiko UUID
            const table_id = $("body").data("table-uniq") || "";
            
            const data = {
                cafe_uniq_name,
                order,
                table_number,
                table_id,
            };

            console.log('data',data);

            const AJAX = $.ajax({
                url: url,                
                dataType: "json",
                method:"POST",
                data:data,
                xhrFields: {
                    withCredentials: true  // Для отправки cookies при CORS
                },                      
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
}
