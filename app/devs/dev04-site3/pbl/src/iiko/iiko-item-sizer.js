import {GLB} from '../glb.js';

export const IIKO_ITEM_SIZER = {
	init:function(item,opt) {
		this.ITEM_DATA = item;
		this.onUpdate = opt.onUpdate;
		this.sizeFromModifiers = opt.sizeFromModifiers; // undefined | true
		this.virtualSizes = null; // null | [{...size},]
		this.CN = "mm2-";
		this._CN = "."+this.CN;
		this.reset();
		this.build();
		return this;
	},
	reset:function() {
		this.VARS = {
			price:0,
			originalPrice:0, // for nomenclature (oldway menu)
			sizeGroupId:"", // for nomenclature (oldway menu)
			sizeName:""	,
			volume:"",
			units:""
		};
	},
	// @return boolean
	has_sizes:function(){		
		return (this.virtualSizes && this.virtualSizes.length > 1) 
			|| (this.ITEM_DATA.iiko_sizes_parsed && this.ITEM_DATA.iiko_sizes_parsed.length > 1);			
	},
	get_ui:function() {
		return [this.$arr_btns['mobile'],this.$arr_btns['desktop']];		
	},
	get:function() {		
		return this.VARS;
	},
	get_all:function(){
		return this.ITEM_DATA.iiko_sizes_parsed;
	},
	get_from_modifiers:function(){		
		// console.log('this.ITEM_DATA', this.ITEM_DATA)
		const basePrice = parseInt(this.ITEM_DATA.iiko_sizes_parsed[0].price, 10);
		const baseValue = parseInt(this.ITEM_DATA.iiko_sizes_parsed[0].portionWeightGrams, 10);

		const search_sizes = ()=>{
			const s = this.ITEM_DATA.iiko_modifiers_parsed.filter((mGroup)=>mGroup.name?.toLowerCase().includes("размер"));			
			const virtualSizes = s.length ? (s[0].items).map((modif_size)=>{				
				return {
					price: parseInt(modif_size.price,10) + basePrice,
					sizeId: modif_size.modifierId,
					sizeName: modif_size.name,
					volume: parseInt(modif_size.portionWeightGrams, 10) + baseValue,
					sizeGroupId: s[0].modifierGroupId,
					isVirtual:true, // calculated from Modifier
				}
			}) : null;

			if(virtualSizes){
				const sortedAsc = [...virtualSizes].sort((a, b) => a.price - b.price) ;		
				sortedAsc[0].isDefault = 'true';
				return sortedAsc;
			}else{
				return null;
			}
		}
				
		this.virtualSizes = search_sizes();
		return this.virtualSizes ?? this.ITEM_DATA.iiko_sizes_parsed;
		
	},
	// private
	set_current_vars:function(vars) {		
		this.VARS = vars;
	},
	build:function() {

		var _this=this;
				
		const sizes = this.sizeFromModifiers ? this.get_from_modifiers() : this.get_all();

		const foo = {
			create_btns:($btns, sizes)=>{
				for(var i=0; i<sizes.length;i++){
					
					const s = sizes[i];										
					const currentClass = s.isDefault=='true'? " active":"";
					const $btn = $('<div></div>',{class: this.CN+"item-size-btn "+currentClass});
					const price = s.price || 0;
					const originalPrice = s.originalPrice || 0;
					const sizeGroupId = s.sizeGroupId || "";					
					const sizeName = s.sizeName || "";
					const volume = s.portionWeightGrams || s.volume;
					const units = foo.units_to_strings(s.measureUnitType || 'GRAM');					
					const sizeId = s.sizeId || "";
					const sizeCode = s.sizeCode || "";

					$btn.html(`<div>${sizeName}</div><div>${volume} ${units}</div>`);

					(function($btn, index, price, originalPrice, sizeGroupId, sizeName) {
						$btn.on('touchend click',function(e){														
							_this.change_current_size({$btn, index, price, originalPrice, sizeGroupId, sizeName, volume, units, sizeId, sizeCode});
							e.originalEvent.cancelable && e.preventDefault();
						});
					})($btn, i, price, originalPrice, sizeGroupId, sizeName, volume, units, sizeId, sizeCode);
				
					s.isDefault=='true' && this.set_current_vars({price, originalPrice, sizeGroupId, sizeName, volume, units, sizeId, sizeCode});
					$btns.append($btn);

				};							
			},
			// @pram unit_type_name string
			// @return string
			units_to_strings:(unit_type_name)=>{
				const unitTypes = {
					'MILLILITER':'мл',
					'KILOGRAM':'кг',
					'LITER':'л',
					'GRAM':'г',
					'ПОРЦ':'г',
				};	
				return unitTypes[unit_type_name] || '';
			}			
		};

		this.$arr_btns = {
			mobile:$('<div></div>'),
			desktop:$('<div></div>')
		};		

		if(sizes){
			if(sizes.length>1){								
				foo.create_btns(this.$arr_btns['mobile'], sizes);
				foo.create_btns(this.$arr_btns['desktop'], sizes);
			}else{								
				const s = sizes[0];				
				const price = s.price;
				const originalPrice = s.originalPrice;
				const sizeGroupId = s.sizeGroupId;
				const volume = s.portionWeightGrams || s.volume;
				const units = foo.units_to_strings(s.measureUnitType || 'GRAM');				
				const sizeName = s.sizeName;				
				const sizeId = s.sizeId || "";
				const sizeCode = s.sizeCode || "";
				this.set_current_vars({price, originalPrice, sizeGroupId, sizeName, volume, units, sizeId, sizeCode});
			};
		}else{
			this.reset();
		}
	
	},
	change_current_size:function(vars){		
		this.set_current_vars(vars);				
		this.onUpdate && this.onUpdate(vars);
		vars.$btn.addClass('active').siblings().removeClass('active');
	}	

};