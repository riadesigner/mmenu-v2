import {GLB} from './glb.js';

export var MENU_ICONS = {
	init:function(options){

		this.iconsData = [
			{title:"main_dishes"},
			{title:"soups"},
			{title:"salade"},
			{title:"chicken"},
			{title:"fish"},
			{title:"steak"},	
			{title:"seafood"},
			{title:"bbq"},
			{title:"kebabs"},
			{title:"pasta"},
			{title:"pizza"},
			{title:"sushi_and_rolls"},
			{title:"dumpling"},
			{title:"asian_food"},
			{title:"sandwiches"},
			{title:"slicing"},
			{title:"canapes"},
			{title:"fast_food"},
			{title:"ice_cream"},
			{title:"fruits"},
			{title:"pancakes"},
			{title:"bakery"},
			{title:"cakes"},
			{title:"childrens_menu"},
			{title:"tea"},
			{title:"coffee"},
			{title:"juice_and_water"},
			{title:"cocktails"},
			{title:"beer"},
			{title:"alcohol"},
			// new icons
			{title:"khachapuri_1"},
			{title:"khachapuri_2"},
			{title:"khinkali"},
			{title:"chebureki"},
			{title:"pita_1"},
			{title:"pita_2"},
			{title:"pita_3"},
			{title:"salade_1"},
			{title:"risotto_1"},
			{title:"risotto_2"},
			{title:"fastfood_1"},
			{title:"fastfood_2"},
			{title:"fastfood_3"},
			{title:"fastfood_4"}
			];

			this.arrData = [];
			for(var i=0;i<this.iconsData.length; i++){
				this.arrData.push({
					title:GLB.LNG.get("ico_"+this.iconsData[i].title),
					className:'ico-'+this.iconsData[i].title
				});
			}

	},
	get:function(id_icon){
		var _this = this;	
		if(id_icon!==undefined){
			return this.arrData[id_icon] ? this.arrData[id_icon]: {title:'Unknown',className:''};
		}else{
			return this.arrData;
		}
	},
	get_total:function(){
		return this.arrData.length;
	}
};