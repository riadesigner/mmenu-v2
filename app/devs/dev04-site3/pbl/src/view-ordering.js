import {GLB} from './glb.js';
import $ from 'jquery';
import {THE_ORDER_SENDER} from './the-order-sender.js';
import {IIKO_STREET_LOADER} from './iiko/iiko-street-loader.js';

export var VIEW_ORDERING = {
	init:function(options){

		this._init(options);

		this.TIME_FORMAT = 24;

		this.CLASS_24FORMAT = this.CN+"timeformat-24";
		this.CLASS_DISABLED = this.CN+"ordering-disabled";
		this.CLASS_USERTIME_MODE = this.CN+"ordering-usertime-mode";
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

		this.$btnDate = this.$view.find(this._CN+"ordering-select-date");
		this.$btnDateSelect = this.$view.find(this._CN+"ordering-select-date select");
		
		this.$btnTime = this.$view.find(this._CN+"ordering-select-time");
		this.$btnTimeSelect = this.$view.find(this._CN+"ordering-select-time select");

		this.$btnUsertime = this.$view.find(this._CN+"ordering-select-usertime");
		this.$btnUsertimeSelect = this.$view.find(this._CN+"ordering-select-usertime select");
		this.$btnTimeSwitch = this.$view.find(this._CN+"ordering-select-switch"); 
		this.$cart_result_message =  this.$view.find(this._CN+"cart-result");
		this.$fields = this.$view.find(this._CN+'ordering-field, '+this._CN+'ordering-select-usertime');

		this.$section_user_address = this.$view.find(this._CN+"ordering-params__user_address");
		this.$section_date_time_to = this.$view.find(this._CN+"date-time-to");
		this.$section_pick_it_up_at = this.$view.find(this._CN+"pick-it-up-at").hide();

		this.$ordering_mode = this.$view.find(this._CN+"ordering-mode");

		this.PICKUPSELF_MODE = false;
		this.STREETS_LIST = [];
		
		this.build_times_select();
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
			this.$section_date_time_to.hide();
			this.$section_pick_it_up_at.show();
			this.$ordering_mode.html('Я заберу заказ сам')
		}else{
			this.PICKUPSELF_MODE = false;
			this.$section_user_address.show();
			this.$section_date_time_to.show();
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

	build_times_select:function(){
		
		this.TIME_FORMAT==24 &&	this.$allParams.addClass(this.CLASS_24FORMAT);

		var fn = {
			get_options:function(ntime){
				var str="";
				var f24 = ntime==24;
				for(var i=1;i<ntime+1;i++){
					var h = f24?i-1:i;					
					str+="<option>"+h+":00</option>";
					str+="<option>"+h+":30</option>";
				}
				return str;
			}
		};
		
		this.$btnUsertimeSelect.html(fn.get_options(this.TIME_FORMAT));
	},
	get_usertime_mode:function() {
		return this.$allParams.hasClass(this.CLASS_USERTIME_MODE);
	},
	set_usertime_mode:function(mode){
		mode ? this.$allParams.addClass(this.CLASS_USERTIME_MODE) : this.$allParams.removeClass(this.CLASS_USERTIME_MODE);	
	},	
	set_date_default:function(){
		this.$btnDateSelect[0].selectedIndex = 0;
	},
	set_time_default:function(){
		this.$btnTimeSelect[0].selectedIndex = 0;	
	},	
	set_usertime_default:function(){
		var index = this.TIME_FORMAT==24?24:0;
		this.$btnUsertimeSelect[0].selectedIndex = index;	
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
			$el.find('input, textarea, select').on('click',function(){			
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

		this.$btnTimeSwitch.on("touchend click",(e)=>{
			if(this.get_usertime_mode()){
				this.set_date_default();
				this.set_usertime_default();
				this.set_usertime_mode(false);
			}else{
				this.set_time_default();
				this.set_usertime_default();
				this.set_usertime_mode(true);
			};
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$btnDateSelect.on("change",(e)=>{
			console.log('this.$btnDateSelect.val()',this.$btnDateSelect.val())
				if(this.$btnDateSelect.val()>0){
					if(!this.get_usertime_mode()){
						this.set_usertime_default();
						this.set_usertime_mode(true);							
					}
				}else{
					this.set_time_default();
					this.set_usertime_default();
					this.set_usertime_mode(false);
				}
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
		const order_total_price = GLB.CART.get_total_price();
		const [order_time_sent, order_time_need]  = this.get_user_time_info();
		const order_user_comment = this.$userComments.val();
			
		// GENERAL PART
		const order_params = {
			id_cafe,
			order_currency,
			order_total_price,
			order_user_phone,
			order_time_need,
			order_time_sent,
			order_user_full_address,
			order_user_comment
		};

		console.log('order_params = ', order_params)
		console.log('order_params cached = ', $.extend({},order_params));

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
	// @return string
	get_user_time_info:function(){
		const _this=this;

		var fn = {
			getTimeto:function() {
				var usertimeMode = _this.get_usertime_mode();		
				if(!usertimeMode){
					var hourOffset = parseInt(_this.$btnTimeSelect.val(),10);
					return {usertimemode:false,hourOffset:hourOffset};
				}else{
					var timeSelected = _this.$btnUsertimeSelect.find("option:selected").text();					
					return {usertimemode:true,timeSelected:timeSelected};
				}
			},
			getDateAndTimeto:function(tm) {			
				var now = new Date();
				var dayOffset = parseInt(_this.$btnDateSelect.val(),10);
				if(!dayOffset){
					if(!tm.usertimemode){
						var user_order_time = new Date(now.getFullYear(), now.getMonth(), now.getDate(),now.getHours()+tm.hourOffset,now.getMinutes());
					}else{	
						var time = fn.parseSelectedTime(tm);					
						var user_order_time = new Date(now.getFullYear(), now.getMonth(), now.getDate(),time.hours,time.minutes);
					}					
				}else{
					var time = fn.parseSelectedTime(tm);
					var user_order_time = new Date(now.getFullYear(), now.getMonth(), now.getDate()+dayOffset,time.hours,time.minutes);
				};				
				return user_order_time;
			},
			parseSelectedTime:function(tm) {
				var ts = tm.timeSelected.split(":");
				var time = {hours:parseInt(ts[0],10),minutes:parseInt(ts[1],10),pm:tm.pm};
				return fn.to24(time);
			},
			to24:function(t) {
				var time = {hours:0,minutes:t.minutes};
				if(t.pm){
					time.hours = t.hours < 12 ? t.hours+12 :  t.hours;
					return time;
				}else{
					time.hours = t.hours < 12 ? t.hours :  0;
					return time;
				}
			},
			dateExport:function(_date){
				return _date.getDate()+"-"+(_date.getMonth()+1)+"-"+_date.getFullYear()+" "+_date.getHours()+":"+_date.getMinutes();;
			}
		};	

		const user_time_need = fn.getDateAndTimeto(fn.getTimeto());  
		const export_time_need = fn.dateExport(user_time_need);
		const export_time_sent = fn.dateExport(new Date());		
		return [export_time_sent, export_time_need];

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

		this.ORDER_SENDER = $.extend({},THE_ORDER_SENDER);	
		this.ORDER_SENDER.send_async(order, this.PICKUPSELF_MODE)
		.then((vars)=>{	
			
			order.short_number = vars['short_number'];
			order.demo_mode = vars['demo_mode'];
			order.notg_mode = vars['notg_mode']?true:false;
			
			const pickupself_mode = this.PICKUPSELF_MODE;
			
			GLB.VIEW_ORDER_OK.update(order, {pickupself_mode:pickupself_mode} );
			GLB.UVIEWS.set_current("the-order-ok");			

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