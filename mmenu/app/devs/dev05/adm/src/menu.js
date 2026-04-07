import {GLB} from './glb.js';

export var MENU = {
	init:function() {
		this.ARR = [];		
		return this;
	},
	update:function(menu){		
		this.ARR = menu;		
	},
	add:function(menu){
		this.ARR.push(menu);
	},
	get:function(){
		return this.ARR;
	},
	get_arr_id:function(){
		// used as position
		var arr_id = [];
		$.each( this.ARR,function(){ arr_id.push(this.id); });
		return arr_id;
	},
	get_by_id:function(id_menu){				
		if(!this.ARR.length || !id_menu) return false;
		var menu = false;
		for (var i=0;i<this.ARR.length;i++){
			if(parseInt(this.ARR[i].id,10) == parseInt(id_menu,10)) {
				menu = this.ARR[i]; break;
			}
		};
		return menu;
	},
	get_index_by_id:function(id_menu){
		if(!this.ARR.length || !id_menu) return -1;
		var index = -1;
		for(var i=0;i<this.ARR.length;i++){
			if(parseInt(this.ARR[i].id,10) == parseInt(id_menu,10)) {
				index = i; break;	
			}
		};
		return index;
	}

};	
		