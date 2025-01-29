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
        
        // after this time return 'timeout'; 
        // if order state will not get 'taken'
        const total_times = SITE_CFG.order_forgotten_delay * 60 * 1000;
        console.log(`total_times = ${SITE_CFG.order_forgotten_delay} min = ${total_times/1000} sec`);

        // verify every 5 second
        const check_delay = 5000;
        let counter = 0;
        
        const interval = setInterval(()=>{
            
            console.log('start checking the order status')
            counter++;
            this.check_order_status_async(short_number, cafe_uniq_name)
            .then((result)=>{
                if(result.order_status == 'taken'){
                    clearInterval(interval);
                    res(result);
                }else{
                    console.log(`order is waiting now... ${counter * check_delay/1000} s из ${SITE_CFG.order_forgotten_delay * 60} s`);
                }
            })
            .catch((err)=>{
                clearInterval(interval);
                rej(err);
            });

        },check_delay);

        setTimeout(()=>{
            clearInterval(interval);
            rej('timeout');
        },total_times);

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
