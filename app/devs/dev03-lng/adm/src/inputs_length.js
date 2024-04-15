
export var INPUTS_LENGTH = {
	init:function(){		
		this._input_names = {			
			// view-customizing-cafe
			'cafe-title':50,
			'chief-cook':300,
			'cafe-address':300,
			'cafe-phone':100,
			'work-hours':300,			
			// view-cafe-description
			'cafe-description':3000,
			// view-change-subdomain
			'new-subdomain':50,
			// view-iiko-adding-api-key
			'iiko-api-key':50,			
			// view-change-password
			'new-password':100,
			// view-edit-item
			'item-title':300,
			'item-description':500,
			'item-volume':100,
			'item-price':10,
			// view-edit-menu
			'menu-title':50,
			// view-main-help
			'help-user-ask':3000,
		};
		return this;		
	},
	get:function(name){
		try{
			return this._input_names[name];
		}catch{
			return 0;	
		}
	}
};