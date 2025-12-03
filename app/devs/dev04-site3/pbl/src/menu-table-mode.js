import {GLB} from './glb.js';
import $ from 'jquery';

export var MENU_TABLE_MODE = {
    init:function(parent){
        const _this=this;
        this.parent = parent;
        this._CN = parent._CN;
        this.$table_mode_protect = $('body').find(this._CN+"protect-table-mode");
        this.$table_mode_protect__message = this.$table_mode_protect.find(this._CN+'protect-table-mode__message');
        this.$table_mode_protect__btn_ok = this.$table_mode_protect.find(this._CN+'protect-table-mode__button-ok');
        this.ORDER_TO_TABLE_MODE = $("body").hasClass("mode-orderto-table");        
        this.TABLE_NUMBER = -1;
        this.btnTableNumber = this.parent.$view.find(this._CN+"header__table-number");         
        this.btnTableNumber_number = this.parent.$view.find(this._CN+"header__table-number_number");        
        this.parent._update_lng();        
    },
    update:function(){        
        let menu_link = `${CHEFS_URL.server}cafe/${GLB.CAFE.get().uniq_name}/`;

        if(this.ORDER_TO_TABLE_MODE){
            if(this.check_table_number()){
                this.btnTableNumber_number.html(this.TABLE_NUMBER);
                let msg = `<p>Вы открыли меню для столика №&nbsp;${this.TABLE_NUMBER}, предназначенное для работы внутри заведения</p>
                        <p>Для перехода Меню в обычный режим, с&nbsp;возможностью сделать заказ на доставку или самовывоз, нажмите ОК</p>`;
                this.$table_mode_protect__message.html(msg);
                this.$table_mode_protect__btn_ok.on("click",()=>{                    
                    location.href = menu_link;
                });
                this.behavior();
            }else{
                let msg = `<p>В адресе Меню для заказа в стол возможно ошибка!</p>
                        <p>Меню перейдет в обычный режим, с&nbsp;возможностью сделать заказ на доставку или самовывоз.</p>`;
                 this.parent._show_modal_win(msg,{onClose:()=>{
                    location.href = menu_link;
                 }});
            }
        }
    },
    check_table_number:function(){
        let data_table_uniq = $("body").data("table-uniq");
        let tables_uniq_names = GLB.CAFE.get().tables_uniq_names;
        let table_number_veryfied = false;

        // console.log('data_table_uniq = ', data_table_uniq)
        // console.log('tables_uniq_names = ', tables_uniq_names)
        // console.log('tables_uniq_names parsed = ', JSON.parse(tables_uniq_names))
                
        if(data_table_uniq && tables_uniq_names){
            tables_uniq_names = JSON.parse(tables_uniq_names);                      

            for(let i in tables_uniq_names){
                if(tables_uniq_names.hasOwnProperty(i)){                        
                    if(data_table_uniq === tables_uniq_names[i]){
                        table_number_veryfied = true;
                        let data = data_table_uniq.split('-');
                        this.TABLE_NUMBER = data[0];
                        break;
                    }
                }
            } 

        };
        return table_number_veryfied;
    },
    get_table_number:function() {
        return this.TABLE_NUMBER;
    },
    is_table_mode:function() {
        return this.ORDER_TO_TABLE_MODE;
    },
    behavior:function() {        
        // this.btnTableNumber.on("touchend",(e)=>{            
        //     GLB.VIEW_TABLE_CHANGE.update(this.TABLE_NUMBER);                                
        //     GLB.UVIEWS.set_current("view-table-change");            
        // });
    }
 
};

