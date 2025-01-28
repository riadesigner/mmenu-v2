import {GLB} from './glb.js';

export const THE_ORDER_CHECKER = {

/**
 * ------------------------------------------------
 *
 *         CHECK IF ORDER TAKEN BY WAITERS
 * 
 * ------------------------------------------------
 */     
check_if_order_taken_async: function(short_number, cafe_uniq_name) {
    return new Promise((res,rej)=>{
        
        // after "counter*1000" seconds return 'timeout'; 
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
