import {GLB} from '../glb.js';
import $ from 'jquery';

/**
 * @param item_data: Object
 * 
*/
export const IIKO_ITEM_MODIFIERS = {
	init:function(item_data) {
		this.item_data = item_data;		
		this.MODIF_SCROLLED = false;		
		this._collect();
		this._build_list_ui();
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
	recalc:function() {
		let _this = this;		
		let total_modif_price = 0;
		let arr_usr_chosen = [];
			this.get().length 
			&& this.$MODIFIERS_BTNS 
			&& this.$MODIFIERS_BTNS.each(function(index){
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
		return [ arr_usr_chosen, parseInt(total_modif_price,10)];
	},

	// private	
	_build_list_ui:function(){		
		const _this=this;
		let arr = this.get();	
		if(arr.length){
			const $m_list = $('<ul></ul>');  
			for(let m in arr){
				let m_name = arr[m].name;
				let m_price = arr[m].price;
				$m_list.append([
					'<li>',
						'<div class="m-check"><span></span></div>',
						'<div class="m-title">'+m_name+'</div>',
						'<div class="m-price">+'+m_price+' руб.</div>',
					'</li>'
					].join(''));								
			}											
			const $btns = $m_list.find('li');			
			$btns.on('touchend click',function(e, index){
				if(!_this.MODIF_SCROLLED){
					$(this).toggleClass('chosen');
					_this.recalc();	
				};				
				e.originalEvent.cancelable && e.preventDefault();
			});		
			GLB.MOBILE_BUTTONS.bhv([$btns]);
			this.$MODIFIERS_BTNS = $btns;
			this.UI = $m_list;	
		}
	},		
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