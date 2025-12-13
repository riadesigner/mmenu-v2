
export var ExtMenuParser = {
	
	parse:function(extMenu){
		
		const MENU = {
			id: extMenu.id,
			name: extMenu.name,
			categories: {},
			description: extMenu.description
		};

		var cats = extMenu.itemCategories;

		for(var i in cats){
			var cat = {				
				id: cats[i].id,
				name: cats[i].name,
				items:{}
			};

			MENU.categories[cat.id] = cat;			
			
			var items = cats[i].items;			

			for(var n in items){
				
				var item = {
					id: items[n].itemId,
					name: items[n].name,
					description: items[n].description,					
					sizes:[],
					imageUrl:"",
					modifiers:[],
					orderItemType: items[n].orderItemType,
					sku: items[n].sku
				}
				var sizes = [];
				var itemSizes = items[n].itemSizes;
				
				// collecting sizes, prices, weights
				for (var k=0;k<itemSizes.length;k++){										
					var size = itemSizes[k];
					sizes.push({
						sizeCode: size.sizeCode,
						sizeId: size.sizeId,
						sizeName: size.sizeName,						
						isDefault: size.isDefault,	
						measureUnitType:size.measureUnitType,					
						portionWeightGrams: size.portionWeightGrams,
						nutritionPerHundredGrams:size.nutritionPerHundredGrams,
						price: size.prices[0].price
					});
					// getting only first image
					if(size.buttonImageUrl && !item.imageUrl){
						item.imageUrl = size.buttonImageUrl;
					};	
					// collecting Modifiers one times only
					if(!item.modifiers.length && size.itemModifierGroups){
						if(size.itemModifierGroups.length){
							var arr = size.itemModifierGroups;
							var arr_m = [];

							for(var i=0;i<arr.length;i++){
								var	m = {
									modifierGroupId: arr[i].itemGroupId,
									name: arr[i].name,
									restrictions: arr[i].restrictions,									
									items:[]}; 								
								for(var a = 0; a < arr[i].items.length;a++ ){
									var modifier = arr[i].items[a];
									m.items.push({
										modifierId: modifier.itemId,
										modifierGroupId:arr[i].itemGroupId,
										name: modifier.name,
										description: modifier.description,
										portionWeightGrams: modifier.portionWeightGrams,
										restrictions: modifier.restrictions,
										nutritionPerHundredGrams: modifier.nutritionPerHundredGrams,
										price: modifier.prices[0].price,			
									});
								}
								arr_m.push(m);
							};														
							item.modifiers = arr_m;
						}
					}
					
				};
				
				item.sizes = sizes;				
				cat.items[item.id] = item;
			};
			
		};
		return MENU;		
	}
};
