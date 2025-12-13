import {GLB} from './glb.js';

export var VIEW_ORDERS_HISTORY = {
	init:function(options){		
		this._init(options);				
		this.$btnBack = this.$view.find(this._CN+"btn-back, "+this._CN+"std-header-btn-back, "+this._CN+"btn-close-items");
		this.$listOrdersContainer = this.$view.find(this._CN+"orders-history-list__container");		
		this.behavior();
		return this;
	},

	behavior:function(){		

		var arrMobileButtons = [			
			this.$btnBack			
		];		
		
		this._behavior(arrMobileButtons);
				
		this.$btnBack.on("touchend click",function() {
			GLB.UVIEWS.go_first();
			return false;
		});

	},	

	update:function(){
		const orders = GLB.ORDERS_HISTORY.get();
		orders && orders.length > 0 && this.build_list(orders);
	},
	build_list:function(orders){
		const $list = $('<div class="orders-history-list"></div>');
		orders.map((order)=>{
			const order_date = '15 дек 25, 12:02';
			const str_order = `Заказ <span>${order}</span> ${order_date}`;
			const $row = $(`<div class="row-history-order">${str_order}</div>`);
			$row.on('touched click',function(){
				console.log('=',order);
				return false;
			})
			$list.append($row);
		});
		this.$listOrdersContainer.html($list);
	}
};



