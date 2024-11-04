import {GLB} from '../glb.js';

export const CHEFS_ORDER_SENDER = {
    send_async:function(order,pickupself) {
        // ORDER FOR DELIVERY OR PICKUPSELF
        return new Promise((res,rej)=>{
            
            const PATH = 'pbl/lib/chefs/';
            const url = GLB_APP_URL + PATH + 'chefs_send_order_for_delivery.php';

            const id_cafe = order.id_cafe;
            const data = {
                id_cafe,
                order,
                pickupself
            };

            const AJAX = $.ajax({
                url: url+"?callback=?",
                jsonpCallback:GLB.CALLBACK_RANDOM.get(),                
                dataType: "jsonp",
                method:"POST",
                data:data,
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
    send_to_table_async: function(order, table_number ) {
        return new Promise((res,rej)=>{
            rej("Администратор не включил данную функцию");
        });
    }
};