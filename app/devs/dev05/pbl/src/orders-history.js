import {GLB} from './glb.js';

export var ORDERS_HISTORY = {
	init:function() {	
		this.STORAGE_KEY = 'chfs_orders';	
		this.$btnOrdersHistoryFooter = $(this._CN+"btn-orders-history");			
		return this;
	},	
	_test_clear:function(){
		// localStorage.setItem(this.STORAGE_KEY, JSON.stringify([]));
	},
	add_order:function(order){
		const orders = JSON.parse(localStorage.getItem(this.STORAGE_KEY) || '[]');
			orders.push({ ...order, timestamp: new Date().toISOString() });
			
			// Проверка на переполнение (≈5MB)
			const dataSize = JSON.stringify(orders).length * 2; // Байты
			if (dataSize > 4 * 1024 * 1024) { // 4MB с запасом
				console.warn('LocalStorage почти заполнен!');
				orders.shift(); // Удаляем самый старый заказ
			}			
			localStorage.setItem(this.STORAGE_KEY, JSON.stringify(orders));
			GLB.VIEW_ALLMENU.update_orders_history_button(orders.length>0);			
	},
	get:function(){		
		return JSON.parse(localStorage.getItem(this.STORAGE_KEY) || '[]');
	}
};