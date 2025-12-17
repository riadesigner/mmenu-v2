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
		if(orders && orders.length > 0){
			this._build_list(orders);
		}else{
			this._show_empty();
		}		 
	},
	
	_build_list:function(orders){
		const _this=this;
		const $list = $('<div class="orders-history-list"></div>');
		orders.map((order)=>{
			const order_date = '15 дек 25, 12:02';// order.date
			const to_table = order.order_target ==='table_order' ? `на стол ${order.table_number}`:''; 
			const str_order = `Заказ ${to_table} <span>${order.short_number}</span> ${order_date}`;
			const $row = $(`<div data-id-uniq='${order.id_uniq}' class="row-history-order">${str_order}</div>`);
			$row.on('touched click',function(){				
				GLB.VIEW_ORDER_FROM_HISTORY.update(order);
				GLB.UVIEWS.set_current("the-order-from-history");					
			})
			$list.append($row);
		});
		this.$listOrdersContainer.html($list);
	},
	_show_empty:function(){
		const emptyMessage = `<div class="${this.CN}orders-history-is-empty"><p>У вас нет отправленных заказов</p></div>`;
		this.$listOrdersContainer.html(emptyMessage);
	}
};



