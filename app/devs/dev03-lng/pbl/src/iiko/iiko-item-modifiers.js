// import {GLB} from '../glb.js';
// import $ from 'jquery';

/**
 * @param item_data: Object
 * 
*/
export const IIKO_ITEM_MODIFIERS = {
	init:function(item_data) {
		this.item_data = item_data;		
		this._collect();
		return this;
	},
	// @param index: number
	// @return array
	get:function(index=null){
		if(index!==null){
			return this.ARR_MODIFIERS[index];	
		}else{
			return this.ARR_MODIFIERS;
		}
	},
	// private
	_collect:function(){
		let modifiers = this.item_data.iiko_modifiers_parsed;		
		// flatting array of modifiers and their groups,
		// collecting all modifiers from groups to one list ( one level array)
		this.ARR_MODIFIERS = [];
		if(modifiers && modifiers.length){
			for(let groups in modifiers){
				let group =  modifiers[groups];
				let arr_m = group.items;
				let modifierGroupId = group.modifierGroupId?group.modifierGroupId:"";
				let modifierGroupName = group.name?group.name:"";
				console.log('modifierGroupId,modifierGroupName',modifierGroupId,modifierGroupName)
				if(arr_m && arr_m.length){
					for(let m in arr_m){
						let mod = arr_m[m];
						// copy modifiersGroup properties 
						// to every modifiers (if it enables)
						mod.modifierGroupId = modifierGroupId;
						mod.modifierGroupName = modifierGroupName;
						this.ARR_MODIFIERS.push(mod);
					}
				}
			}			
		};
	}	
};