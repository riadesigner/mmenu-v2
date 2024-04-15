import {GLB} from './glb.js';

export var CAFE = {
	init:function(cafe){
		this.OBJ_CAFE = cafe;
	},
	get:function(prop){
		if(prop){
			switch (prop) {
				case 'cafe_currency': return this.get_currency(); break;
				default: return this.OBJ_CAFE[prop];
			}			
		}else{
			return this.OBJ_CAFE;
		}
	},
	get_status:function(){		
		return this.OBJ_CAFE.cafe_status;
	},
	get_currency:function(){		
		var cur = this.OBJ_CAFE.cafe_currency;
		var arr = {"RUB":"руб.","USD":"$","EUR":"€","JPY":"¥","GBP":"£","KRW":"₩"};
		var symbol = arr[cur.toUpperCase()];
		var currency = !symbol ? {code:"USD",symbol:"$"} :  {code:cur,symbol:symbol};
		return currency;
	}
};