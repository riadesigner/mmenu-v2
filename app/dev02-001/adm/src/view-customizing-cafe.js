import {GLB} from './glb.js';

export var VIEW_CUSTOMIZING_CAFE = {
	
	init:function(options){

		this._init(options);		

		this.$btnSave = this.$footer.find('.save');
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
	
		this.$btnCafeDescription = this.$view.find('.btn-cafe-description');
		
		this._need2save(false);

		this.behavior();
		return this;

	},

	update:function(){
		var _this=this;
		this._update();
		this._page_hide();
		this.reset();
		var cafe = GLB.THE_CAFE.get();
		this.ID_CAFE = cafe.id;
		this.$view.find('input[name=cafe-title]').val(cafe.cafe_title);
		this.$view.find('textarea[name=cafe-address]').val(cafe.cafe_address);
		this.$view.find('input[name=chief-cook]').val(cafe.chief_cook);
		this.$view.find('input[name=cafe-phone]').val(cafe.cafe_phone);
		this.$view.find('input[name=work-hours]').val(cafe.work_hours);		
		setTimeout(function(){ _this._page_show(); },300);
		
	},

	behavior:function()	{
		var _this = this;

		this._behavior();

		this.$btnBack.on('touchend',function(){			
			_this._blur({onBlur:function(){
				!_this.LOADING && _this._go_back();
			}});			
			return false;
		});

		this.$btnSave.on('touchend',function(){
			_this._blur({onBlur:function(){

				if(_this.NEED_TO_SAVE){
					!_this.LOADING && _this.save({onReady:function(){					
						_this._go_back();
					}});	
				};

			}});
			return false;
		});
		
		this.$btnCafeDescription.on('touchend click',function(e){	
			_this._blur({onBlur:function(){
				if(!_this.LOADING &&!_this.VIEW_SCROLLED){
					GLB.VIEW_CAFE_DESCRIPTION.update();
					GLB.VIEWS.setCurrent(GLB.VIEW_CAFE_DESCRIPTION.name);
				};
			}});		
			return false;
		});

		this.$inputs.on('keyup',function(e){
			_this._need2save(true);
			return false;
		});		

	},

	reset:function(){
		this._reset();
		this._need2save(false);
		this._page_to_top();
	},

	save:function(opt){
		
		var _this = this;
		
		var id_cafe = this.ID_CAFE;

		var inputs = {
			cafe_title: this.$view.find('input[name=cafe-title]').val(),
			cafe_address: this.$view.find('textarea[name=cafe-address]').val(),
			chief_cook: this.$view.find('input[name=chief-cook]').val(),
			cafe_phone: this.$view.find('input[name=cafe-phone]').val(),
			work_hours: this.$view.find('input[name=work-hours]').val()
		};
		var user_input = {
			cafe_title: $.clear_user_input( inputs.cafe_title, GLB.INPUTS_LENGTH.get('cafe-title') ),
			cafe_address: $.clear_user_input( inputs.cafe_address, GLB.INPUTS_LENGTH.get('cafe-address') ),
			chief_cook: $.clear_user_input( inputs.chief_cook, GLB.INPUTS_LENGTH.get('chief-cook') ),
			cafe_phone: $.clear_user_input( inputs.cafe_phone, GLB.INPUTS_LENGTH.get('cafe-phone') ),
			work_hours: $.clear_user_input( inputs.work_hours, GLB.INPUTS_LENGTH.get('work-hours') )
		};

		if(!user_input.cafe_title){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:"Укажите название кафе",
				btn_title:GLB.LNG.get('lng_close')
			});
			return false;
		}else if(user_input.cafe_title.length<3){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:"Название кафе слишком короткое",
				btn_title:GLB.LNG.get('lng_close')
			});
			return false;
		};

		if(!user_input.cafe_address){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:"Введите адрес кафе",
				btn_title:GLB.LNG.get('lng_close')
			});
			return false;
		}else if(user_input.cafe_address.length<3){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:"Адрес кафе слишком короткий",
				btn_title:GLB.LNG.get('lng_close')
			});
			return false;
		};
		
		if(user_input.cafe_phone){
			var msk = /^[\d\s\-\(\)\+\,]+$/;	
			var phone_valid = msk.test(user_input.cafe_phone); 
			if(!phone_valid){
				GLB.VIEWS.modalMessage({
					title:GLB.LNG.get("lng_attention"),
					message:"<p>Введите корректный номер телефона кафе.</p><p>Допускаются только цифры скобки и тире</p>",
					btn_title:GLB.LNG.get('lng_close')
				});
				return false;
			}
		};

		this._now_loading();	

		var PATH = 'adm/lib/';
		var url = PATH + 'lib.save_cafe.php';		
		var data ={
			id_cafe: id_cafe,
			cafe_title: user_input.cafe_title,
			cafe_address: user_input.cafe_address,
			chief_cook: user_input.chief_cook,
			cafe_phone: user_input.cafe_phone,
			work_hours: user_input.work_hours
		};

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            data:data,
            method:"POST",
            success: function (response) {
            	console.log('response',response);
            	if(!response || response.error){	
            		console.log('response err:',response);
            	}else{
            		var cafe = response;
            		var cafe_rev = cafe.rev;
	            	GLB.THE_CAFE.update(cafe);
	            	GLB.VIEW_ALL_MENU._update_title(cafe.cafe_title);
	            	_this._end_loading();
	            	opt && opt.onReady && opt.onReady(cafe);
            	}
            },
            error:function(response) {
				_this._end_loading();
		        console.log("err save cafe info",response);
			}
        });

	}


};