import {GLB} from './glb.js';

export const VIEW_CAFE_TABLES = {
	
	init:function(options){
		
		this._init(options);
		
		this.$btnBack = this.$footer.find('.back, .close, .cancel');		
		this.$btnIikoGetQrcodes = this.$form.find('.btn-iiko-get-qrcodes');	
		this.$inputTablesAmount = this.$form.find('.view-cafe-tables__table-counts-wrapper input');						

		this.USER_EMAIL = CFG.user_email; 		
		this.behavior();
		return this;

	},

	update:function(){
		var _this=this;
				
		this._update();		

		this._page_hide();

		this.NEW_NUMBER_OF_TABLES = parseInt(GLB.THE_CAFE.tables_amount,10) || 0;
		this.$inputTablesAmount.val(this.NEW_NUMBER_OF_TABLES);

		// console.log('this.NEW_NUMBER_OF_TABLES',this.NEW_NUMBER_OF_TABLES)

		// this._now_loading();
		// this.reset();
		// this._update_tabindex();
		
		// var cafe = GLB.THE_CAFE.get();		
		// this._end_loading();		
		this._page_show();

		return;

		this.$home_menu_link.html(GLB.THE_CAFE.get_link('name'))
		.attr({ href : GLB.THE_CAFE.get_link('url') });

		this.$qrCode.html("<img src='"+cafe.qrcode+"'>");			

		var img = new Image();
		img.onload = function(){
			_this._end_loading();
			_this._page_show();
		};
		img.src = cafe.qrcode;
		
	},
	reset:function() {		
		this._reset();
		this._page_to_top();		
	},

	behavior:function()	{
		var _this = this;

		this._behavior();

		this.$btnIikoGetQrcodes.bind('touchend',(e)=>{
			this._blur({onBlur:()=>{
				if(!this.LOADING && !_this.VIEW_SCROLLED){		
					GLB.VIEW_CAFE_TABLES_QRCODE.update();
					GLB.VIEWS.setCurrent(GLB.VIEW_CAFE_TABLES_QRCODE.name);
				}
			}});			
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$inputTablesAmount.on('keyup paste',(e)=>{
			this.update_number_of_tables();			
			e.originalEvent.cancelable && e.preventDefault();
		});				

		this.$btnBack.on('touchend',function(){
			_this._blur({onBlur:function(){
				!_this.LOADING && _this._go_back();
			}});
			return false;
		});



	},
	update_number_of_tables(){
		console.log("need to save", this.$inputTablesAmount.val());
	},
	send:function(opt){

		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.send_qr_code.php';
		
		this._now_loading();

		var data = {
			id_cafe:GLB.THE_CAFE.get().id
		};

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            data:data,
            method:"POST",
            dataType: "jsonp",
            success: function (response) {
            	console.log("response=",response)
            	_this._end_loading();
            	if(!response.error){
					opt.onReady && opt.onReady();
            	}else if(response.error && response.error.indexOf('limit message per day') !== -1){
					opt.onLimit && opt.onLimit();
            	}else{
            		opt.onError && opt.onError();
            	};
            },
            error:function(response) {
            	_this._end_loading();
				opt.onError && opt.onError();
		        console.log("err",response);
			}
        });
			
	}

};