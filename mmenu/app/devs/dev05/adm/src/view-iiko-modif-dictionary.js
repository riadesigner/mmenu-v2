import {GLB} from './glb.js';

export var VIEW_IIKO_MODIF_DICTIONARY = {
	
	init:function(options){
		
		this._init(options);
									
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$btnSave = this.$view.find('.save');
		
		this.$msgListEmpty =  this.$form.find('.list-iiko-modif-dictionary__is-empty'); 
        this.$msgListContainer = this.$form.find('.list-iiko-modif-dictionary');
                				
		// this.SITE_URL = CFG.base_url;
		// this.USER_EMAIL = CFG.user_email;
		// this.IIKO_HELP_URL = "#link-iiko-help"; 
		
		this.reset();        
		this.behavior();				        

		return this;

	},	

	update:function(){
				
		this._reset();
		this._update();
		this._page_hide();
		this._update_tabindex();

		this.update_content_async()
        .then((vars)=>{
            console.log('vars',vars)
            this._page_show();
            this._end_loading();
        });		
	},
	reset:function(){		
		this._reset();        
		this._need2save(false);
		this._page_to_top();
	},
	update_content_async:function(){
        return new Promise((res,rej)=>{
            this._load_iiko_modif_async()
            .then((ARR_MODIF)=>{
                console.log('ARR_MODIF',ARR_MODIF)
                if(!ARR_MODIF.length){
                    this.$msgListEmpty.show();
                    this.$msgListContainer.hide();
                    res("ok");
                }else{
                    this.$msgListEmpty.hide();
                    this.$msgListContainer.show();
                    this.build_modif_list();
                    setTimeout(()=>{
                        res("ok");
                    },300);                
                };
            })
            .catch((vars)=>{
                rej(vars);
            });           
        });
	},
	behavior:function()	{
		
		this._behavior();

		this.$btnBack.bind('touchend',(e)=>{
			this._blur({onBlur:()=>{
				!this.LOADING && this._go_back();
			}});			
			return false;
		});

		// this.$inputIikoKey.on('keyup',function(e){			
		// 	_this._need2save(_this.$inputIikoKey.val()!=="");
		// });
	},
    build_modif_list:function(){

    },
    _load_iiko_modif_async:function() {
        return new Promise((res,rej)=>{            

            const PATH = 'adm/lib/iiko/';
            const url = PATH + 'iiko.get_modif_dictionary.php';
            const data = {
                id_cafe:GLB.THE_CAFE.get().id
            };

            this._now_loading();

            this.AJAX = $.ajax({
                url: url+"?callback=?",
                data:data,
                method:"POST",
                dataType: "jsonp",
                success: (result)=> {                    
                    console.log("result",result)
                    if(result && !result.error){
                        res(result);
                    }else{                        
                        rej(result);
                    }
                },
                error:(result)=> {                    
                    rej(result);
                }
            }); 
            
        });
    }

};