import {GLB} from './glb.js';

export const VIEW_CAFE_TABLES = {
	
	init:function(options){
		
		this._init(options);
		
		this.$btnBack = this.$footer.find('.back, .close, .cancel');		
		this.$btnSave = this.$view.find('.save');		
		this.$btnGetQrCodes = this.$form.find('.btn-iiko-get-qrcodes');
		this.$inputTablesAmount = this.$form.find('.view-cafe-tables__table-counts-wrapper input');								
		
		// for superadmin only
		this.$btnResetAllQrCodes = this.$form.find('.btn-reset-tables-qrcodes');

		this.MAXIMUM_TABLES = 50;

		this.behavior();
		return this;
	},

	update:function(){
		
		this._reset();
		this._update();				
		this._page_hide();		
		this.reset();
		this._page_show();
		
	},
	reset:function() {
		this._page_to_top();			
		this._need2save(false);	
		
		this.CURRENT_NUMBERS = parseInt(GLB.THE_CAFE.get('tables_amount'),10) || 0;
		this.NEW_NUMBER_OF_TABLES = this.CURRENT_NUMBERS;		
		this.$inputTablesAmount.val(this.CURRENT_NUMBERS);	

		console.log('this.CURRENT_NUMBERS',this.CURRENT_NUMBERS)

	},

	behavior:function()	{
		var _this = this;

		this._behavior();

		this.$inputTablesAmount.on('keyup paste',(e)=>{
			this.update_number_of_tables();			
			e.originalEvent.cancelable && e.preventDefault();
		});
		
		this.$btnGetQrCodes.bind('touchend',function(e){
			_this._blur({onBlur:function(){
				if(!this.LOADING && !_this.VIEW_SCROLLED){
					if(_this.NEED_TO_SAVE){	
						_this.save({
							onReady:()=>{					
								_this.reset();			
								_this.goto_tables_qr_codes();
							}
						});
					}else{					
						_this.reset();	
						_this.goto_tables_qr_codes();
					};
				}
			}});
			e.originalEvent.cancelable && e.preventDefault();
		});			

		this.$btnSave.bind('touchend',function(e){
			_this._blur({onBlur:function(){
				if(_this.NEED_TO_SAVE && !_this.LOADING){	
					_this.save({
						onReady:()=>{								
							_this.reset();
						}
					});
				};
			}});
			e.originalEvent.cancelable && e.preventDefault();
		});			

		this.$btnResetAllQrCodes.on('touchend',function(e){
			_this._blur({onBlur:function(){
				if(!_this.LOADING && !_this.VIEW_SCROLLED){
					_this._show_confirm_async(`Подтвердите, что хотите 
						пересоздать QR-коды для всех столов!`)
						.then(()=>{
							_this._now_loading();
							_this.sa_reset_all_codes_asynq()					
							.then((vars)=>{						
								_this._show_message("Qr-коды всех столов созданы заново");
								console.log(vars);
								_this._end_loading();
							})
							.catch((vars)=>{						
								console.log(vars);
								_this._show_error(`не удалось пересоздать QR-коды`);
								_this._end_loading();
							});
						});						
				};
			}});
			e.originalEvent.cancelable && e.preventDefault();
		});	

		this.$btnBack.on('touchend',function(){
			_this._blur({onBlur:function(){
				!_this.LOADING && _this._go_back();
			}});
			return false;
		});		

	},
	goto_tables_qr_codes:function(){
		// this.reset();
		GLB.VIEW_CAFE_TABLES_QRCODE.update();
		GLB.VIEWS.setCurrent(GLB.VIEW_CAFE_TABLES_QRCODE.name);
	},
	update_number_of_tables(){
		this.NEW_NUMBER_OF_TABLES = parseInt(this.$inputTablesAmount.val(),10) || this.CURRENT_NUMBERS;
		if(this.NEW_NUMBER_OF_TABLES > this.MAXIMUM_TABLES){
			this.NEW_NUMBER_OF_TABLES = this.MAXIMUM_TABLES;
			this.$inputTablesAmount.val(this.MAXIMUM_TABLES);
		}		
		const need = this.NEW_NUMBER_OF_TABLES !== this.CURRENT_NUMBERS;
		this._need2save(need);
	},
	save:function(opt){
		
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.save_tables_amount.php';		
		
		this._now_loading();

		var data = {
			id_cafe:GLB.THE_CAFE.get().id,
			tables_amount:this.NEW_NUMBER_OF_TABLES,
		};

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            data:data,
            method:"POST",
            dataType: "jsonp",
            success: (response) => {
            	console.log("response=",response)
            	this._end_loading();
            	if(!response.error){					
					GLB.THE_CAFE.set({'tables_amount':this.NEW_NUMBER_OF_TABLES}),			
					opt && opt.onReady && opt.onReady();
            	}else{
            		opt && opt.onError && opt.onError();
            	};
            },
            error:(response)=> {
            	this._end_loading();
				opt && opt.onError && opt.onError();
		        console.log("err",response);
			}
        });	
	},
	sa_reset_all_codes_asynq:function(){
		return new Promise((res,rej)=>{

			var PATH = 'adm/lib/';
			var url = PATH + 'lib.update_all_qrcodes_for_tables.php';			

			this.AJAX = $.ajax({
				url: url+"?callback=?",
				data:{id_cafe:GLB.THE_CAFE.get().id},
				method:"POST",
				dataType: "jsonp",
				success: (response) => {										
					if(!response.error){					
						res(response);
					}else{
						rej(response.error);
					};
				},
				error:(response)=> {
					rej(response);
				}
			});	
		});
	}

};