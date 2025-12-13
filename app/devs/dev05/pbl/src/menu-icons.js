import {GLB} from './glb.js';

export var MENU_ICONS = {
	init:function(){		

		this.arrIcons = [
			"main_dishes","soups","salade","chicken","fish","steak",
			"seafood","bbq","kebabs","pasta","pizza","sushi_and_rolls",
			"dumpling","asian_food","sandwiches","slicing","canapes",
			"fast_food","ice_cream","fruits","pancakes","bakery","cakes",
			"childrens_menu","tea","coffee","juice_and_water","cocktails",
			"beer","alcohol", "khachapuri_1", "khachapuri_2", "khinkali", "chebureki", "pita_1", 
 			"pita_2", "pita_3", "salade_1", "risotto_1", "risotto_2", "fastfood_1", 
			"fastfood_2", "fastfood_3", "fastfood_4"
		];
	},
	get:function(index){
		if(index){
			return this.arrIcons[index]?this.arrIcons[index]:"";	
		}else{
			return this.arrIcons;
		}
		
	}
};