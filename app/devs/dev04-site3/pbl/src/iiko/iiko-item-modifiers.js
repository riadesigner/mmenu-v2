import {GLB} from '../glb.js';

/**
 * @param modifiers_data: Array (iiko modifiers parsed)
 * @param virtual_sizes: String[]|null // название размеров
*/
export const IIKO_ITEM_MODIFIERS = {
	init:function(modifiers_data, virtual_sizes=null) {
		
		this.modifiers_data = modifiers_data;
		this.MODIFIERS = [];
		this.OBJ_MODIFIERS = {};
		this.arr_usr_chosen = [];
		this.total_modif_price = 0;
		
		this.MODIF_SCROLLED = false;		
		this.HAS_GROUPS_MODE = true;
		this.VIRTUAL_SIZES = virtual_sizes;		

		this.$m_list_all = $('<div class="all-modifs-list"></div>');
		this.M_GROUPS_BY_SIZES_LINKS = {}		

		this._init_modif_with_groups();
		this.VIRTUAL_SIZES && this._add_virtual_sizes(this.VIRTUAL_SIZES);				
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

	// for virtual sizes (extracted from modifiers)
	// @param sizeName: string
	switch_to_size:function(sizeName){		
		const links = this.M_GROUPS_BY_SIZES_LINKS;
		// hide all modifGroups
		for (let i in links){
			for(let n=0; n<links[i].length; n++){
				const mGroup = links[i][n];				
				mGroup.hide();
			}			
		}				
		// show only current size modifGroups
		for (let i in links){
			for(let n=0; n<links[i].length; n++){
				const mGroup = links[i][n];				
				if(sizeName==i){
					mGroup.show();
				}			
			}			
		}		
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

	_add_virtual_sizes:function(sizes){
		if(sizes && sizes.length>0){
			// updating modifiers groups
			this.MODIFIERS.map((modifGroup)=>{
				const virtualSizes = [];
				sizes.map((s)=>{
					const groupName = modifGroup.name.toLowerCase();  
					const sizeName = s.sizeName.toLowerCase();					
					if(groupName.includes(sizeName)){
						// показывать группу только для данного размера 
						virtualSizes.push(sizeName)
					}					
				});
				// показывать группу для всех размеров 
				if(virtualSizes.length === 0){
					if(!modifGroup.name.toLowerCase().includes('размер')){
						// Создаем массив со всеми названиями размеров
						modifGroup.virtualSizes = sizes.map(s => s.sizeName.toLowerCase());
					}else{
						// это модификатор 'размерный ряд'
						modifGroup.virtualSizes = virtualSizes;
					}
				} else {
					modifGroup.virtualSizes = virtualSizes;
				}				
				return modifGroup;
			})
		}
	},

	// @return void
	_init_modif_with_groups:function(){				

		this.MODIFIERS = [...this.modifiers_data];		
		
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
		// console.log('this.MODIFIERS', this.MODIFIERS)
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

				if(!this.get() || !this.$MODIFIERS_ROWS || !this.$MODIFIERS_ROWS.length){
					return [arr, 0];
				};
			
				this.$MODIFIERS_ROWS.each((i,el)=>{
					if( $(el).hasClass('chosen') && $(el).is(':visible')){
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
				let maxQuantity = parseInt(g['restrictions']['maxQuantity'],10);
				let minQuantity = parseInt(g['restrictions']['minQuantity'],10);
				let radioMode = false;
				if(g['restrictions']){					
					radioMode = maxQuantity==1 && g.items.length > 1;
				}
				let params = `data-group-id="${groupId}" 
					data-group-name="${groupName}" 
					data-max-quantity="${maxQuantity}" 
					data-radio-mode="${radioMode}"`;
				const strUpToNum = maxQuantity > 0 && g.items.length > 1 ? `( максимум ${maxQuantity} )` : ''; 	
				let strGroupName=`<div class="modifiers-group-name">${groupName} <small>${strUpToNum}</small></div>`;
				let $m_group_wrapper = $(`<div class="modif-group-wrapper" ${params}>${strGroupName}</div>`);
				
				// console.log('g', g);

				let $m_list_group = $('<ul></ul>');				
				const type_radio = radioMode?'type-radio':'';
				const mode_radio = radioMode?'mode-radio':'';
				let m_counter = 0; 
				for(let m in g.items ){					
					let modifier = g.items[m];
					let chosen = '';
					let chosenByDefault = '';
					if( parseInt(modifier['restrictions']['byDefault'],10) > 0){
						chosen = 'chosen'; 
						chosenByDefault = 'chosen-by-default';
					}
					const modif_price = modifier.price>0?'+ '+modifier.price+' руб.':'';
					$m_list_group.append([
						`<li class="btn-modifier ${mode_radio} ${chosen} ${chosenByDefault}" data-modifier-id="${modifier.modifierId}">`,
							`<div class="m-check ${type_radio}"><span></span></div>`,
							`<div class="m-title">${modifier.name}</div>`,
							`<div class="m-price">${modif_price}</div>`,
						`</li>`
						].join(''));
					m_counter++;
					if(radioMode && m_counter==g.items.length && chosen!=='chosen'){						
						$m_list_group.find('li:first').addClass('chosen').addClass('chosen-by-default');
					}
				}				
				$m_group_wrapper.append($m_list_group);
				return $m_group_wrapper;
			},
			behaviors:($rows)=>{
				const fn = {
					toggleCheckbox:($el)=>{
						if($el.hasClass('chosen')){
							$el.removeClass('chosen');
						}else{							
							fn.canEncrease($el) && $el.addClass('chosen');
						}						
					},
					toggleRadioButton:($el)=>{				
						$el.siblings().removeClass('chosen');
						$el.addClass('chosen');				
					},
					canEncrease:($el)=>{
						const countChosen = $el.siblings().filter('.chosen').length;
						const maxQuantity = parseInt($el.closest('.modif-group-wrapper').data('max-quantity'),10);
						if(countChosen >= maxQuantity){							
							return false;
						}
						return true;						
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
			if(this.VIRTUAL_SIZES && !arr[i].virtualSizes.length) break;			
			const $group_el = fn.build_group(arr[i]);
			this.VIRTUAL_SIZES && this._add_sized_group(arr[i].virtualSizes, $group_el);			
			$group_el && this.$m_list_all.prepend($group_el);
		}

		this.VIRTUAL_SIZES && this._switch_to_default_size();

		this.$MODIFIERS_ROWS = this.$m_list_all.find('li');		
		fn.behaviors(this.$MODIFIERS_ROWS);	
		
		setTimeout(() => {
			this.on_change && this.on_change();
		}, 0);

	},

	_add_sized_group:function(virtualSizes, $linkToSizedGroup){
		if(!virtualSizes.length){
			// allways hide
			$linkToSizedGroup.hide();			
			return;
		}
		// сохраняем ссылки на все группы модификаторов,
		// отсортированные по размерному ряду
		for(let i in virtualSizes){
			const sizeName = virtualSizes[i];
			if(!this.M_GROUPS_BY_SIZES_LINKS[sizeName]){
				this.M_GROUPS_BY_SIZES_LINKS[sizeName] = [];
			}
			this.M_GROUPS_BY_SIZES_LINKS[sizeName].push($linkToSizedGroup);
		}		
	},

	_switch_to_default_size:function(){
		const defaultSize = this.VIRTUAL_SIZES.filter((s)=>s.isDefault==='true');
		const sizeName = defaultSize[0]?defaultSize[0]['sizeName']:null;
		sizeName && this.switch_to_size(sizeName);
	}
		
};