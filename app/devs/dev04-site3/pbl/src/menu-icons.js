import {GLB} from './glb.js';

export var MENU_ICONS = {
	init:function(){		

		this.arrIcons = [
			"main_dishes","soups","salade","chicken","fish","steak",
			"seafood","bbq","kebabs","pasta","pizza","sushi_and_rolls",
			"dumpling","asian_food","sandwiches","slicing","canapes",
			"fast_food","ice_cream","fruits","pancakes","bakery","cakes",
			"childrens_menu","tea","coffee","juice_and_water","cocktails",
			"beer","alcohol"
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