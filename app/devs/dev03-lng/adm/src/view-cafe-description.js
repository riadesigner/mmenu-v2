import {GLB} from './glb.js';

export var VIEW_CAFE_DESCRIPTION = {
	
	init:function(options){

		this._init(options);

		this.$btnSave = this.$view.find('.save');
		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$cafe_description = this.$view.find('textarea[name=cafe-description]');

		this.behavior();
		
		return this;

	},

	update:function(){		
		var _this=this;
		this._update();		
		this.reset();		
		this._update_tabindex();
		var cafe = GLB.THE_CAFE.get();
		this._need2save(false);		
		this.$cafe_description.val( cafe.cafe_description );
		setTimeout(function(){ _this._page_show(); },300);
	},

	reset:function() {
		this._reset();
		this._page_to_top();
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

		this.$inputs.on('keyup',function(e){
			_this._need2save(true);
			return false;
		});

	},
	save:function(opt) {
		var _this=this;
		
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.save_cafe_description.php';		
		
		var inp_length = GLB.INPUTS_LENGTH.get('cafe-description');
		var cafe_description = $.clear_user_input( this.$cafe_description.val(), inp_length ); 

		if(cafe_description == ""){
			GLB.VIEWS.modalMessage({
				title:GLB.LNG.get("lng_attention"),
				message:"Напишите немного о вашем заведении.",
				btn_title:GLB.LNG.get('lng_close')
			});
			return false;			
		}else if(cafe_description.length<5){
			cafe_description = "Нет информации";
		};		

		var data ={
			id_cafe:GLB.THE_CAFE.get().id,
			cafe_description: cafe_description
		};

		this._now_loading();	

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType:"jsonp",
            data:data,
            method:"POST",
            success:function(response) {
            	console.log('response',response)
            	_this._end_loading();
            	if(response && !response.error){
	            	var cafe = response;
	            	var cafe_rev = cafe.rev;
	            	GLB.THE_CAFE.update(cafe);	            	
	            	GLB.VIEW_ALL_MENU._update_title(cafe.cafe_title);
	            	_this._end_loading();
	            	opt&&opt.onReady&&opt.onReady(cafe);
            	}else{					
			        console.log(response.error);
            	}
            },
            error:function(response) {
				_this._end_loading();
		        console.log("err save cafe description",response);
			}
        });
	}
};