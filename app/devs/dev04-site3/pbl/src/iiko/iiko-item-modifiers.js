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
		
		this.MODIF_SCROLLED = false;

		
		this.HAS_GROUPS_MODE = true;
		
		this.$m_list_all = $('<div class="all-modifs-list"></div>');

		this._collect_with_groups();
		this._build_list_ui_with_groups();		
		this.behavior();
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

	// REQUIRED PUBLIC METHODS
	// @return jQueryElement (list of modifiers)	
	get_ui:function(){
		return this.$m_list_all;
	},

	// @return number
	get_cost:function(){
		return this.total_modif_price;
	},

	// @return array
	get_selected:function(){
		return this.arr_usr_chosen;
	},

	reset:function(){
		const $rows = this.$MODIFIERS_ROWS;
		$rows && $rows.length && $rows.each((i, el)=>{
			if(!$(el).hasClass('chosen-by-default')){
				$(el).removeClass('chosen');
			}else{
				$(el).addClass('chosen');
			}
		})
	},

	// @return { 
	//   arr_usr_chosen: array; 
	//   total_modif_price: integer;
	// }
	recalc:function() {		
		return this._do_recalc_with_groups();
	},

	on_change:function(foo){
		this.on_change = foo;
	},

	// ----------
	//  private	
	// ----------

	behavior:function(){
		this.$m_list_all.on("touchstart",(e)=>{
			this.MODIF_SCROLLED = false;
		});
		this.$m_list_all.on("touchmove",(e)=> {
			this.MODIF_SCROLLED = true;
		});	
	},

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
		let total_modif_price = 0;
		const arr_usr_chosen = [];		
		
		console.log('-------- get = ',this.get());
		console.log('-------- this.$MODIFIERS_ROWS = ', this.$MODIFIERS_ROWS);

		const fn = {
			calc_price:()=>{
				const arr = [];
				if(!this.get() || !this.$MODIFIERS_ROWS.length){return};
				this.$MODIFIERS_ROWS.each((i,el)=>{
					if($(el).hasClass('chosen')){

					}
				})
			}
		}

		fn.calc_price();
	

		this.arr_usr_chosen = arr_usr_chosen;	
		this.total_modif_price = parseInt(total_modif_price,10);	
		return {
			arr_usr_chosen:this.arr_usr_chosen,
			total_modif_price:this.total_modif_price,
		}
	},	

	// @return void
	_build_list_ui_with_groups:function(){
		let arr = this.get();
		console.log('arr = ', arr);
		if(!arr.length){ return; }

		const fn = {
			build_group:(g)=>{			

				if(!g.items || !g.items.length) return null;							
				
				let groupId = g['modifierGroupId']??"";
				let groupName = g['name']??"–";				
				let radioMode = false;
				if(g['restrictions']){					
					radioMode = g['restrictions']['maxQuantity']==1 
					&& g['restrictions']['minQuantity'] == 1;
				}
				let params = `data-group-id="${groupId}" data-group-name="${groupName}" data-radio-mode="${radioMode}"`;
				let strGroupName=`<div class="modifiers-group-name">${groupName}</div>`;
				let $m_group_wrapper = $(`<div class="modif-group-wrapper" ${params}>${strGroupName}</div>`);
				
				let $m_list_group = $('<ul></ul>');				
				const type_radio = radioMode?'type-radio':'';
				const mode_radio = radioMode?'mode-radio':'';
				let byDefault = g['restrictions']['byDefault'] || 0;
				byDefault = parseInt(byDefault,10);				
				let m_counter = 0; 
				for(let m in g.items ){					
					let modifier = g.items[m];
					let chosen = '';
					let chosenByDefault = ''
					if(radioMode && m_counter===byDefault){  chosen = 'chosen'; chosenByDefault = 'chosen-by-default'}
					$m_list_group.append([
						`<li class="btn-modifier ${mode_radio} ${chosen} ${chosenByDefault}" data-modifier-id="${modifier.modifierId}">`,
							`<div class="m-check ${type_radio}"><span></span></div>`,
							`<div class="m-title">${modifier.name}</div>`,
							`<div class="m-price">+ ${modifier.price} руб.</div>`,
						`</li>`
						].join(''));
					m_counter++;
				}				
				$m_group_wrapper.append($m_list_group);
				return $m_group_wrapper;
			},
			behaviors:($rows)=>{
				const fn = {
					toggleCheckbox:($el)=>{
						$el.toggleClass('chosen');
					},
					toggleRadioButton:($el)=>{				
						$el.siblings().removeClass('chosen');
						$el.addClass('chosen');				
					}			
				};
						
				$rows.on('touchend click',(e)=>{
					if(!this.MODIF_SCROLLED){
						const $el = $(e.currentTarget);
						const radio  = $el.hasClass('mode-radio');
						radio ? fn.toggleRadioButton($el) : fn.toggleCheckbox($el);
						this.on_change && this.on_change();
					};				
					e.originalEvent.cancelable && e.preventDefault();
				});		
				GLB.MOBILE_BUTTONS.bhv([$rows]);
			}		
		};
		
		for(let i=0;i<arr.length;i++){
			const $group = fn.build_group(arr[i]);
			$group && this.$m_list_all.prepend($group);
		}

		this.$MODIFIERS_ROWS = this.$m_list_all.find('li');		
		fn.behaviors(this.$MODIFIERS_ROWS);
		
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