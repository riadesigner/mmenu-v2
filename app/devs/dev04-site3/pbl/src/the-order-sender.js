import {GLB} from './glb.js';

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
            
            console.log('order, pickupself  ==== ',order, pickupself)

            const PATH = 'pbl/lib/';
            const url = GLB_APP_URL + PATH + 'pbl.send_order_for_delivery.php';

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

/**
 * -------------------------------
 *
 *         ORDER TO TABLE
 * 
 * -------------------------------
 */ 

    send_to_table_async: function(order, table_number ) {
         
         return new Promise((res,rej)=>{            
            
            console.log('order, table_number  ==== ',order, table_number)

            // const fn = {
            //     dateExport:function(_date){
            //         return _date.getDate()+"-"+(_date.getMonth()+1)+"-"+_date.getFullYear()+" "+_date.getHours()+":"+_date.getMinutes();
            //     }
            // };
                            
            const PATH = 'pbl/lib/';
            const url = GLB_APP_URL + PATH + 'pbl.send_order_to_table.php';
            const id_cafe = GLB.CAFE.get().id;
            
            const data = {
                id_cafe,
                order,
                table_number,        
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
}
