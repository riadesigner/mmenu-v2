// import {GLB} from '../glb.js';
// import $ from 'jquery';

/**
 * @param item_data: Object
 * 
*/
export const IIKO_ITEM = {
	init:function(item_data) {
		this.item_data = item_data;		
		return this;
	},
	// @return boolean
	has_modifiers:function() {
		return this.item_data.iiko_modifiers!=="";
	}
};