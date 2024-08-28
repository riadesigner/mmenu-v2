import {GLB} from './glb.js'; 

export var ITEMS = {
	init:function() {
		this.ARR = {};		
		return this;
	},
	update:function(id_menu,items){		
		this.ARR[id_menu] = items;		
		console.log('this.ARR',this.ARR)
	},
	add:function(id_menu, items){		
		if(!this.ARR[id_menu] || !items) return false;		
		this.ARR[id_menu].push(items);
	},
	get:function(id_menu){
		if(!id_menu) return false;
		return this.ARR[id_menu]?this.ARR[id_menu]:[];
	},
	get_arr_id:function(id_menu){
		if(!id_menu || !this.ARR[id_menu]) return false;
		// used as position
		var arr_id = [];
		$.each( this.ARR[id_menu],function(){ arr_id.push(this.id); });
		return arr_id;
	},
	replace_item:function(id_menu,item){
		if(!id_menu || !this.ARR[id_menu] || !item || !item.id) return false;

		var updated = false;
		for (var i=0;i<this.ARR[id_menu].length;i++){
			console.log('id=id',this.ARR[id_menu][i].id,item.id);
			if(parseInt(this.ARR[id_menu][i].id,10) == parseInt(item.id,10)) {
				var updated = this.ARR[id_menu][i] = item; 
				console.log('this.ARR[id_menu]['+i+']',this.ARR[id_menu][i])
				break;
			}
		};
		return updated;
	}

	// get_by_id:function(id_menu,id_item){				
	// 	if(!this.ARR[id_menu] || !this.ARR[id_menu].length || !id_item) return false;
	// 	var item = false;
	// 	for (var i=0;i<this.ARR[id_menu].length;i++){
	// 		if(parseInt(this.ARR[id_menu][i].id,10) == parseInt(id_item,10)) {
	// 			item = this.ARR[id_menu][i]; break;
	// 		}
	// 	};
	// 	return item;
	// },
	// get_index_by_id:function(id_menu,id_item){
	// 	if(!this.ARR[id_menu] || !this.ARR[id_menu].length || !id_item) return -1;
	// 	var index = -1;
	// 	for(var i=0;i<this.ARR[id_menu].length;i++){
	// 		if(parseInt(this.ARR[id_menu][i].id,10) == parseInt(id_item,10)) {
	// 			index = i; break;	
	// 		}
	// 	};
	// 	return index;
	// }

};	
		