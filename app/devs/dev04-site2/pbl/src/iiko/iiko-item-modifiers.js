import {GLB} from '../glb.js';
import $ from 'jquery';

/**
 * @param item_data: Object
 * 
*/
export const IIKO_ITEM_MODIFIERS = {
	init:function(item_data) {
		this.item_data = item_data;
		this.ARR_MODIFIERS = [];
		this.arr_usr_chosen = [];
		this.total_modif_price = 0;
		
		//TODO ... update for chefsmenu 
		this.HAS_GROUPS_MODE = true;
		
		if(this.HAS_GROUPS_MODE){
			// for IIKO
			this._collect_with_groups();
			this._build_list_ui_with_groups();
		}else{
			// for CHEFSMENU
			this._collect();
			this._build_list_ui();			
		}		

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

	// @return jQueryElement (list of modifiers)	
	get_ui:function(){
		return this.UI;
	},

	// @return number
	get_cost:function(){
		return this.total_modif_price;
	},

	// @return array
	get_selected:function(){
		return this.arr_usr_chosen;
	},

	// @return { 
	//   arr_usr_chosen: array; 
	//   total_modif_price: integer;
	// }
	recalc:function() {	
		if(this.HAS_GROUPS_MODE){
			return this._do_recalc_with_groups();
		}else{
			return this._do_recalc();
		}
	},

	// ----------
	//  private	
	// ----------

	// @return { 
	//   arr_usr_chosen: array; 
	//   total_modif_price: integer;
	// }	
	_do_recalc:function(){
		let _this = this;		
		let total_modif_price = 0;
		let arr_usr_chosen = [];
			this.get().length 
			&& this.$MODIFIERS_ROWS 
			&& this.$MODIFIERS_ROWS.each(function(index){
			if($(this).hasClass('chosen')){
				const mod = _this.get(index);
				const id = mod.modifierId;
				const price = mod.price;
				const name = mod.name
				const modifierGroupId = mod.modifierGroupId;
				const modifierGroupName = mod.modifierGroupName;								
				arr_usr_chosen.push({id, name, price, modifierGroupId, modifierGroupName });
				total_modif_price += parseInt(price,10);
			}
		});
		this.arr_usr_chosen = arr_usr_chosen;
		this.total_modif_price = parseInt(total_modif_price,10);
		return {
			arr_usr_chosen: this.arr_usr_chosen,
			total_modif_price: this.total_modif_price 
		};		
	},

	// @return { 
	//   arr_usr_chosen: array; 
	//   total_modif_price: integer;
	// }	
	_do_recalc_with_groups:function(){
		return {
			arr_usr_chosen:[],
			total_modif_price:0,
		}		
	},	

	// @return jQueryObject
	_build_list_ui_with_groups:function(){
		let arr = this.get();
		console.log('arr = ', arr);
		if(!arr.length){ return; }

		const fn = {
			build_group:(g)=>{			

				if(!g.items || !g.items.length) return null;							

				let $m_group_wrapper = $('<div class="modif-group-wrapper"></div>')
				let $m_list_group = $('<ul></ul>');				
				let groupId = g['modifierGroupId']??"";
				let groupName = g['name']??"–";				
				let radioMode = false;
				if(g['restrictions']){					
					radioMode = g['restrictions']['maxQuantity']==1 && g['restrictions']['minQuantity'] == 1;
				}
				for(let m in g.items ){
					$m_list_group.append([
						`<li class="btn-modifier" data-group-id="${groupId}" data-group-name="${groupName}" data-radioMode="${radioMode}">`,
							`<div class="m-check"><span></span></div>`,
							`<div class="m-title">${g.items[m].name}</div>`,
							`<div class="m-price">${g.items[m].price} руб.</div>`,
						`</li>`
						].join(''));
				}
				$m_group_wrapper.append($m_list_group);
				return $m_group_wrapper;
			}
		};

		const $m_list_all = $('<div class="all-modifs-wrapper"></div>');
		for(let i=0;i<arr.length;i++){			
			for(let g in arr){				
				const $group = fn.build_group(arr[g]);				
				$group && $m_list_all.append($group);
			}
		}

		this.$MODIFIERS_ROWS = $m_list_all.find('li');
		this.UI = $m_list_all;		
	},

	// @return jQueryObject
	_build_list_ui:function(){		
		const _this=this;
		let arr = this.get();		
		if(arr.length){
			const $m_list = $('<ul></ul>');  
			for(let m in arr){
				let m_name = arr[m].name;
				let m_price = arr[m].price;
				$m_list.append([
					'<li class="btn-modifier">',
						'<div class="m-check"><span></span></div>',
						'<div class="m-title">'+m_name+'</div>',
						'<div class="m-price">+'+m_price+' руб.</div>',
					'</li>'
					].join(''));								
			}											
			this.$MODIFIERS_ROWS = $m_list.find('li');
			this.UI = $m_list;	
		}
	},	

	// @return void
	_collect_with_groups:function(){
		this.ARR_MODIFIERS = [...this.item_data.iiko_modifiers_parsed];
	},
	
	// @return void	
	_collect:function(){
		let modifiers = this.item_data.iiko_modifiers_parsed;				
		// flatting array of modifiers and their groups,
		// collecting all modifiers from groups to one list ( one level array)		
		if(modifiers && modifiers.length){
			for(let groups in modifiers){
				let group =  modifiers[groups];
				let arr_m = group.items;
				let modifierGroupId = group.modifierGroupId?group.modifierGroupId:"";
				let modifierGroupName = group.name?group.name:"";
				// console.log('modifierGroupId,modifierGroupName',modifierGroupId,modifierGroupName)
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