import {GLB} from './glb.js';

export var VIEW_ORDER_FROM_HISTORY = {
	init:function(options){
		
		this._init(options);
		
		this.$headerPhone = this.$view.find(this._CN+"header-phone");				
		this.$btnClose = this.$view.find(this._CN+"btn-close-menu");
		this.$btnBack = this.$view.find(this._CN+"btn-back, "+this._CN+"std-header-btn-back, "+this._CN+"btn-close-items");

		this.$msgReport = this.$view.find(this._CN+"order-ok-report-main");						
		this.$msgOrderSentInfo = this.$view.find(this._CN+"order-ok-report-info");		
		this.$msgOrderText = this.$view.find(this._CN+"order-from-history__text");
		this.$totalCost =  this.$view.find(this._CN+"order-ok-total-cost");		
		this.$tplOrderedItem = $("#mm2-templates "+this._CN+"ordered-item");				

		this.behavior();
		return this;
	},

	behavior:function(){

		var arrMobileButtons = [			
			this.$btnBack,
			this.$btnClose
		];

		this._behavior(arrMobileButtons);		

		this.$btnBack.on("touchend click",function() {
			GLB.UVIEWS.go_back();
			return false;
		});

	},	

	update:function(orderFromHistoryDto){		
		this._content_hide();
		this.chefsmenu.now_loading();	
		
		const {id_uniq} = orderFromHistoryDto;

		this.load_order_async(id_uniq)
		.then((loaded_order)=>{
			console.log('loaded_order', loaded_order);
			this._show_order(loaded_order);
			this._show_all();
		});

	},

	load_order_async:function(uniq_id){
		return new Promise((res,rej)=>{
			var url = GLB_APP_URL+"pbl/lib/pbl.get_order_from_history.php";	
			var data = {orderUniqId:uniq_id};			

			this.AJX_ITEMS = $.ajax({
				url: url,				
				dataType: "json",
				method:"POST",
				data:data,
                xhrFields: {
                    withCredentials: true  // Для отправки cookies при CORS
                },
				success: (order)=> {        
					res(order);
				},
				error:(response)=> {					
					console.log("err response",response);
					rej("ошибка загрузки");
				}
			});					
		})
	},

	update_template_part_common(){
		var phone = GLB.CAFE.get('cafe_phone');
		phone!=="" ? this.$headerPhone.find(this._CN+"header-phone__text").html(phone) : this.$headerPhone.hide();
		if(phone){
			var ph = phone.replace(/[-() ]/g,"");
			this.$headerPhone.on("touchend click",function(){
				location.href="tel:"+ph;
				return false;
			});
		};			
	},

	formatLngTime:function(tm,full){
		var t = tm.split(" ");
		var d = t[0].split("-");
		// var time = t[1];
		var arr = GLB.LNG.get("lng_all_months").split("-");
		var year = full ?" "+d[2]:"";
		return d[0]+" "+arr[parseInt(d[1],10)-1]+year+", "+t[1];
	},

	_show_order:function(loaded_order){

		const fn = {
			replaceUnderscores:(text)=>{
				return text.replace(/_([^_]+)_/g, '<span>$1</span>');
			}
		} 

		const {description} = loaded_order;
		const json_description = JSON.parse(description);
		const {ORDER_TEXT} = json_description;
		let parsed_order_text = ORDER_TEXT.replace(/\\n/g,'<br>');		
		parsed_order_text = fn.replaceUnderscores(parsed_order_text);
		
		this.$msgOrderText.html(parsed_order_text);		
		const currency = GLB.CAFE.get('cafe_currency').symbol;
		const TOTAL_PRICE = "Итого: "+loaded_order.total_price+" "+ currency;
		this.$totalCost.html(TOTAL_PRICE);	
		var msg = [
			"<h2>"+GLB.LNG.get("lng_number_of_your_order")+"</h2>",
			"<h3>"+loaded_order.short_number+"</h3>"
		].join("\n");				
		this.$msgReport.html(msg);				
	},
	_show_all:function(){
		setTimeout(()=>{			
			this._content_show();
			setTimeout(()=>{			
				this.chefsmenu.end_loading();			
			},500);			
		},1000);
	}
};



