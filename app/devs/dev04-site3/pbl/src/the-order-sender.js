import {GLB} from './glb.js';

export const THE_ORDER_SENDER = {
    send_async:function(order,pickupself) {
        // ORDER FOR DELIVERY OR PICKUPSELF
        return new Promise((res,rej)=>{
            
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
    send_to_table_async: function(order, table_number ) {
         // ORDER TO TABLE
         return new Promise((res,rej)=>{            
            
            const fn = {
                dateExport:function(_date){
                    return _date.getDate()+"-"+(_date.getMonth()+1)+"-"+_date.getFullYear()+" "+_date.getHours()+":"+_date.getMinutes();
                }
            };
                            
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
    },
    check_if_order_taken_async: function(short_number, cafe_uniq_name) {
        return new Promise((res,rej)=>{
            
            // after 10 seconds return 'timeout'; 
            // if order status will not equal 'taken'
            const counter = 30;
            
            const interval = setInterval(()=>{
                this.check_order_status_async(short_number, cafe_uniq_name)
                .then((result)=>{
                    if(result.order_status == 'taken'){
                        clearInterval(interval);
                        res(result);
                    }
                })
                .catch((err)=>{
                    clearInterval(interval);
                    rej(err);
                });
            },3000);
            setTimeout(()=>{
                clearInterval(interval);
                rej('timeout');
            },counter*1000);
        });
    },
    check_order_status_async:function(short_number, cafe_uniq_name) {
        return new Promise((res,rej)=>{
            const PATH = 'pbl/lib/';
            const url = GLB_APP_URL + PATH + 'pbl.check_order_status.php';

            const data = {
                short_number,
                cafe_uniq_name
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
                if(result && !result.error){
                    res(result);
                }else{
                    rej(result);
                }
            });
        });
    }
}
