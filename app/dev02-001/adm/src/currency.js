import {GLB} from './glb.js';

export var CURRENCY = {
	init:function(options){
		
		this.arrCurrency = {'USD':'$','RUB':'&#8381','EUR':'€'};
	},
	get_all:function(){
		return this.arrCurrency;
	},
	get_by_code:function(code){
		return this.arrCurrency[code];
	},
	get_current:function(){
		return this.get_by_code(GLB.THE_CAFE.get().cafe_currency);
	}
};