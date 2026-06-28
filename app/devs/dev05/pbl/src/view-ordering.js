import {GLB} from './glb.js';
import $ from 'jquery';
import {THE_ORDER_SENDER} from './the-order-sender.js';
import {IIKO_STREET_LOADER} from './iiko/iiko-street-loader.js';

// --------------------------------
// только для EXTERNAL ORDERING WAY
// --------------------------------

export var VIEW_ORDERING = {
	init:function(options){

		this._init(options);

		this.CLASS_DISABLED = this.CN+"ordering-disabled";
		this.CLASS_CURRENT = this.CN+"ordering-select-current";
		this.CLASS_INPUT_ERROR = this.CN+"input-error";
		
		this.$btnBasket= this.$view.find(this._CN+"btn-basket");
		this.$btnClose = this.$view.find(this._CN+"btn-close-menu");
		this.$btnBack = this.$view.find(this._CN+"btn-back, "+this._CN+"std-header-btn-back, "+this._CN+"btn-close-items");
				
		this.$btnSend = this.$view.find(this._CN+"btn-send");
		this.$inputs = this.$view.find("input, textarea");		
		
		this.errMessage1 = this.$view.find(this._CN+"ordering-message-1");
		this.errMessage2 = this.$view.find(this._CN+"ordering-message-2");

		this.$allParams = this.$view.find(this._CN+"ordering-params");
		this.$userComments = this.$view.find(this._CN+"ordering-comment");

		this.$cart_result_message =  this.$view.find(this._CN+"cart-result");
		this.$fields = this.$view.find(this._CN+'ordering-field, '+this._CN+'ordering-select-usertime');

		this.$section_user_address = this.$view.find(this._CN+"ordering-params__user_address");
		this.$section_pick_it_up_at = this.$view.find(this._CN+"pick-it-up-at").hide();

		this.$ordering_mode = this.$view.find(this._CN+"ordering-mode");

		this.PICKUPSELF_MODE = false;
		this.STREETS_LIST = [];
		
		this.behavior();		
		
		return this;
	},

	update:function(opt){

		console.log("VIEW ORDERING!",opt);

		this._update_tabindex();
		this._show_attention(false,false);

		this.IIKO_MODE = GLB.CAFE.is_iiko_mode();

		if(opt && opt.pickupMode){
			this.PICKUPSELF_MODE = true;
			this.$section_user_address.hide();
			// this.$section_date_time_to.hide();
			this.$section_pick_it_up_at.show();
			this.$ordering_mode.html('Я заберу заказ сам')
		}else{
			this.PICKUPSELF_MODE = false;
			this.$section_user_address.show();
			this.$section_pick_it_up_at.hide();			
			this.$ordering_mode.html('Заказ на доставку')
		};

		if(opt && opt.clear){

			this.$allParams.find("[name=u_phone]").val("");
			this.$allParams.find("[name=u_address]").val("");
			this.$userComments.val("");		
			var msg = "<p>"+GLB.LNG.get("lng_your_shopping_cart_is_empty")+"</p>";			

			// END ALL LOADS
			this.chefsmenu.end_loading();


		}else{
			
			var msg = [				
				"<p>"+GLB.LNG.get("lng_for_total_cost")+"<br><span>"+GLB.CART.the_total()+"</span></p>"
			].join("\n");									
			
			// LOAD CITIES FOR IIKO DELIVERY IF NEED

			const IIKO_MODE = GLB.CAFE.is_iiko_mode();	
			if(!this.STREETS_LIST.length && !this.PICKUPSELF_MODE && IIKO_MODE){
				console.log("start loading streets");
				
				this.load_iiko_cities_async()
				.then((streets)=>{					
					this.STREETS_LIST = streets;					
					const $streets_input = this.$view.find(this._CN+"streets");										
					
					console.log('streets',streets);
					
					const fn ={
						spelling:function(word, foo, sum=''){
						  let pos = 0;
						  let acc = sum;
						  this.foo=foo;
						  acc += word.substr(pos,1);
						  this.say(acc)
						  const TMR_spell = setTimeout(()=>{
							if(pos<word.length-1){
							  pos++;
							  this.spelling(word.substr(1),foo, acc);
							}
						  },10); 
						},
						say:function(letters){
						  this.foo && this.foo(letters)    
						}
					  }

					const autoCompleteJS = new autoComplete({
			        	selector:'input[name=u_street]',
			            placeHolder: "...",
			            data: {
			                src: streets,
			                cache: true,
			            },
			            resultItem: {
			                highlight: true
			            },
			            events: {
			                input: {
			                    selection: (event) => {
			                    	console.log("!!",event)
			                        const selection = event.detail.selection.value;			                        
									fn.spelling(selection,(l)=>{
										autoCompleteJS.input.value = l;
									  });
			                    }
			                }
			            }
			        });			

					setTimeout(()=>{
						this.chefsmenu.end_loading();
					},300);										
				})
				.catch((vars)=>{
		            this._show_modal_win(`Невозможно загрузить список улиц. 
		            	Обратитесь к администратору кафе.`);
					console.log('err',vars);
					setTimeout(()=>{
						this.chefsmenu.end_loading();
					},300);
				});
			}else{

				// END ALL LOADS
				this.chefsmenu.end_loading();

			};

		};

		this.$cart_result_message.html(msg);
		
	},	

	behavior:function(){
		var _this=this;

		var arrMobileButtons = [
			this.$btnBasket,
			this.$btnBack,			
			this.$btnClose,	
			//		
			this.$btnSend
			];

		this._behavior(arrMobileButtons);

		this.$fields.each(function(){
			const $el = $(this);
			$el.find('input, textarea, select').on('click',function(e){		
				e.stopPropagation();
				e.preventDefault();					
				_this.$fields.removeClass('focused');
				$el.addClass('focused');	 
			});
			
		});	

		var fn = {
			go_back:()=> {				
				if(GLB.CAFE.has_delivery()){
					GLB.UVIEWS.go_back();						
				}else{
					GLB.UVIEWS.set_current('the-showcart');
				}			
			}
		};

		this.$btnBack.on("touchend click",(e)=> {
			if(this.$inputs.is(":focus")){
				this.$inputs.blur();
				setTimeout(()=>{ fn.go_back(); },500);
			}else{
				fn.go_back();
			};
			this.$fields.removeClass('focused');	
			e.originalEvent.cancelable && e.preventDefault();
		});	

		this.$btnSend.on("touchend click",(e)=> {
			if(!this.chefsmenu.is_loading_now()){
				this.chefsmenu.now_loading();
				if(this.$inputs.is(":focus")){
					this.$inputs.blur();
					setTimeout(()=>{ 
						this.checkup_user_inputs();
					},500);
				}else{
					this.checkup_user_inputs();
				}
				this.$fields.removeClass('focused');
			};
			e.originalEvent.cancelable && e.preventDefault();
		});

	},
	_show_attention:function(mode1,mode2) {
		mode1 ? this.errMessage1.addClass(this.CLASS_INPUT_ERROR) : this.errMessage1.removeClass(this.CLASS_INPUT_ERROR);
		mode2 ? this.errMessage2.addClass(this.CLASS_INPUT_ERROR) : this.errMessage2.removeClass(this.CLASS_INPUT_ERROR);
	},
	checkup_user_inputs:function() {
		
		this._show_attention(false,false);
		
		const order_user_phone = this.$allParams.find("[name=u_phone]").val();			
		const order_user_full_address = this.get_user_address();		
		if(!this.checkup_user_address_and_phone(order_user_phone, order_user_full_address)) return;		

		const id_cafe = GLB.CAFE.get('id');
		const order_currency = GLB.CAFE.get('cafe_currency');
		const cafe_uniq_name = GLB.CAFE.get('uniq_name');
		const order_total_price = GLB.CART.get_total_price();
		const order_user_comment = this.$userComments.val();
			
		// GENERAL PART
		const order_params = {
			id_cafe,
			cafe_uniq_name,
			order_currency,
			order_total_price,
			order_user_phone,
			order_user_full_address,
			order_user_comment
		};

		this.do_order_send(order_params);

	},

	flash_inputs_errors:function() {
		this.$allParams.animate({ scrollTop: 0 }, 300);
		this.errMessage1.addClass('flashed');
		this.errMessage2.addClass('flashed');
		setTimeout(()=>{
			this.errMessage1.removeClass('flashed');
			this.errMessage2.removeClass('flashed');			
		},300);
	},	
	// @return bool 
	checkup_user_address_and_phone:function(user_phone, address){

		if(this.PICKUPSELF_MODE){
			if(!user_phone){ 
				this._show_attention(!user_phone,false);
				this.flash_inputs_errors();
				setTimeout(()=>{this.chefsmenu.end_loading();},500);
				return false;
			}					
		}else{
			// DELIVERY MODE
			let needs_address = this.IIKO_MODE ? !address.u_street || !address.u_house : !address.description;			
			if(!user_phone || needs_address){ 
				this._show_attention(!user_phone, needs_address);
				this.flash_inputs_errors();
				setTimeout(()=>{this.chefsmenu.end_loading();},500);
				return false;
			}
		}
		return true;
	},
	// @return object { description:"", u_street:"", u_house:"", u_flat:"", u_entrance:"", u_floor:"" }
	get_user_address:function(){
		if(this.IIKO_MODE){
			return {
				description:"",
				u_street:this.$allParams.find("[name=u_street]").val(),
				u_house:this.$allParams.find("[name=u_house]").val(),
				u_flat:this.$allParams.find("[name=u_flat]").val(),
				u_entrance:this.$allParams.find("[name=u_entrance]").val(),
				u_floor:this.$allParams.find("[name=u_floor]").val(),				
			}
		}else{
			return {
				description:this.$allParams.find("[name=u_address]").val(),
				u_street:"",
				u_house:"",
				u_flat:"",
				u_entrance:"",
				u_floor:"",				
			}			
		}
	},
	
	is_phone_number:function(phone_number){
		var nums = phone_number.match(/\d/g);
		var is_phone = nums && nums.length == 11? true:false;
		return is_phone;
	},
	do_order_send:function(order_params) {

		const convertSizesToModifiers = true;
		const order_items = GLB.CART.get_all(convertSizesToModifiers);			
		const order = $.extend(order_params,{order_items});		

		// ---------------------------------
		// ORDER TO DELIVERY (OR PICKUPSELF)
		// ---------------------------------		
		this.ORDER_SENDER = $.extend({},THE_ORDER_SENDER);	
		this.ORDER_SENDER.send_async(order, this.PICKUPSELF_MODE)
		.then((vars)=>{	
			
			console.log('--vars order saved--',vars)
			order.short_number = vars['short_number'];
			order.public_order_id = vars['public_order_id'];
			order.demo_mode = vars['demo_mode']?true:false;
			order.notg_mode = vars['notg_mode']?true:false;
			
			const pickupself_mode = this.PICKUPSELF_MODE;
			
			let phoneForUrl = (raw)=>{
				let d = raw.replace(/\D/g, "");
				if (d.length === 11 && d.startsWith("8")) d = "7" + d.slice(1);
				if (d.length === 10) d = "7" + d;
				return d; // "79001234567"
			}

			const publicId = vars['public_order_id'];
			const phone = order['order_user_phone'];

			const pre_url_users = SITE_CFG.users_app_url;
			const url =`${pre_url_users}/order/confirmation/${publicId}/user/${phoneForUrl(phone)}`;

			location.href=url;

			// old way
			// GLB.VIEW_ORDER_OK.update(order, {pickupself_mode:pickupself_mode} );
			// GLB.UVIEWS.set_current("the-order-ok");		
			

		})
		.catch((vars)=>{
            this._show_modal_win(`Заказ не получается отправить. 
            	Обратитесь к администратору кафе.`);
			console.log('err',vars);
			setTimeout(()=>{
				this.chefsmenu.end_loading();
			},1000);
		});		
	},
	load_iiko_cities_async:function() {
		return new Promise((res,rej)=>{
			
			const IIKO_STREETS = $.extend({},IIKO_STREET_LOADER); 			
			IIKO_STREETS.load_async_for(GLB.CAFE.get().id)
			.then((vars)=>{
				if(!vars.streets){
					rej(vars);
				}else{
					res(vars.streets);
				};				
			})
			.catch((vars)=>{
				rej(vars);
			});		

		});
	}

};