import {GLB} from '../glb.js';
import $ from 'jquery';

/**
 * @param item_data: Object
 * 
*/
export const IIKO_ITEM_MODIFIERS = {
	init:function(item_data) {
		this.item_data = item_data;
		this.MODIFIERS = [];
		this.OBJ_MODIFIERS = {};
		this.arr_usr_chosen = [];
		this.total_modif_price = 0;
		
		this.MODIF_SCROLLED = false;		
		this.HAS_GROUPS_MODE = true;
		
		this.$m_list_all = $('<div class="all-modifs-list"></div>');

		this._init_modif_with_groups();
		this._build_list_ui_with_groups();		
		this.behavior();
		return this;
	},

	// @param id: string
	// @return object|null
	get_by_id:function(id=null){
		return id ? this.OBJ_MODIFIERS[id] : null;
	},
	
	// @return array
	get:function(id=null){
		return this.MODIFIERS;
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

	// @return void
	_init_modif_with_groups:function(){
		// the parsed modifier groups
		this.MODIFIERS = [...this.item_data.iiko_modifiers_parsed];		

		const fn = {
			make_object:(modifs)=>{
				const obj = {};
				for(let g in modifs){
					let items = modifs[g].items;
					if(items && items.length){
						for(let i in items){
							let m = items[i];
							obj[m.modifierId] = m;
						}					
					}
				}
				return obj;
			}
		};
		this.OBJ_MODIFIERS = fn.make_object(this.MODIFIERS);		

	},

	// @return { 
	//   arr_usr_chosen: array; 
	//   total_modif_price: integer;
	// }	
	_do_recalc_with_groups:function(){

		const fn = {
			// @return integer
			calc_price:()=>{
				const arr = [];
				let total_price = 0;
				if(!this.get() || !this.$MODIFIERS_ROWS.length){return};
				this.$MODIFIERS_ROWS.each((i,el)=>{
					if($(el).hasClass('chosen')){
						const id = $(el).data('modifier-id');
						const m = this.get_by_id(id);
						if(m){
							arr.push(m);
							total_price += parseInt(m.price, 10);
						}						 						
					}
				});
				return [arr, total_price]; 
			}
		}
		
		let [arr_usr_chosen, total_modif_price] = fn.calc_price();		

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
		if(!arr.length){ return; }

		const fn = {
			// @param g object
			// @return jQueryObject			
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
			const $group_el = fn.build_group(arr[i]);
			$group_el && this.$m_list_all.prepend($group_el);
		}

		this.$MODIFIERS_ROWS = this.$m_list_all.find('li');		
		fn.behaviors(this.$MODIFIERS_ROWS);
		
	}

};