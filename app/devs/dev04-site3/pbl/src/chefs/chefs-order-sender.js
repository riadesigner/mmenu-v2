import {GLB} from '../glb.js';
import $ from 'jquery';

export const CHEFS_ORDER_SENDER = {
    send_async:function(order,pickupself) {
        // ORDER FOR DELIVERY OR PICKUPSELF
        return new Promise((res,rej)=>{
            const url = GLB_APP_URL+"pbl/lib/chefs/chefs_send_order_for_delivery.php";

            const id_cafe = order.id_cafe;
            const data = {
                id_cafe,
                order,
                pickupself
            };
            
            console.log('======= ORDER =======', order)

            $.ajax({
                url: url+"?callback=?",
                jsonpCallback:GLB.CALLBACK_RANDOM.get(),                    
                dataType: "jsonp",
                method:"POST",
                data:data,
                success: function (response) {                    
                    console.log("--response1--",response)    
                    if(response.error){	       
                        rej(response.error)
                    }else{	              
                        res(response)
                    }
                },
                error:function(response) {	                    
                    console.log("--response2--",response)
                    rej(response)
                }
            });

        });
    }
};