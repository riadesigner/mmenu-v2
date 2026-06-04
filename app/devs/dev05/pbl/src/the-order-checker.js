import {GLB} from './glb.js';

export const THE_ORDER_CHECKER = {

/**
 * ------------------------------------------------
 *
 *         CHECK IF ORDER TAKEN BY WAITERS
 * 
 * ------------------------------------------------
 */     
check_if_order_taken_async: function(opt) {
    return new Promise((res,rej)=>{
        
        const {short_number, public_order_id, cafe_uniq_name} = opt;

        
        // after this time return 'timeout'; 
        // if order state will not get 'taken'
        const total_times = SITE_CFG.order_forgotten_delay * 60 * 1000;
        const cafe_order_way = SITE_CFG.cafe_order_way;
        
        console.log(`total_times = ${SITE_CFG.order_forgotten_delay} min = ${total_times/1000} sec`);

        // verify every 5 second
        const check_delay = 5000;
        let counter = 0;
        
        const interval = setInterval(()=>{
            
            console.log('start checking the order status')
            counter++;
            this.check_order_status_async(short_number, public_order_id, cafe_uniq_name, cafe_order_way)
            .then((result)=>{
                
                console.log('result',result);
                console.log('result.order_status',result.order_status);
                // created|new (старое название | новое название) - заказ создан, но еще не виден официанту 
                if(result.order_status == 'created' || result.order_status == 'new' ){
                    console.log(`order is waiting now... ${counter * check_delay/1000} s из ${SITE_CFG.order_forgotten_delay * 60} s`);
                }else{
                    clearInterval(interval);
                    res(result);
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

check_order_status_async:function(short_number, public_order_id, cafe_uniq_name, cafe_order_way) {
    return new Promise((res,rej)=>{
        const PATH = 'pbl/lib/';
        const url = GLB_APP_URL + PATH + 'pbl.check_order_status.php';

        const data = {
            short_number,
            public_order_id,
            cafe_uniq_name,
            cafe_order_way
        };        

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
