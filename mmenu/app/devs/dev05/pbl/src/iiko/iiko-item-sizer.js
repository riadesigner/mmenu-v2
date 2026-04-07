import {GLB} from '../glb.js';

export const IIKO_ITEM_SIZER = {
	init:function(item,opt) {
		this.ITEM_DATA = item;
		this.onUpdate = opt.onUpdate;
		this.sizeFromModifiers = opt.sizeFromModifiers; // undefined | true
		this.arrVirtualSizes = null; // null | [{...size},]
		this.CN = "mm2-";
		this._CN = "."+this.CN;
		this.reset();
		this.build();
		return this;
	},
	reset:function() {
		this.VARS = {
			price: 0,
			originalPrice: 0, // for nomenclature (oldway menu)
			sizeId: "",
			sizeGroupId: "",			
			isVirtualSize: false, // calculated from modifiers
			sizeName: "",
			volume:"",
			units:""
		};
	},
	// @return boolean
	has_sizes:function(){		
		return (this.arrVirtualSizes && this.arrVirtualSizes.length > 1) 
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
	get_virtual:function(){
		return this.arrVirtualSizes;
	},	
	
	// private
	_calc_from_modifiers:function(){		
		// console.log('this.ITEM_DATA', this.ITEM_DATA)
		const basePrice = parseInt(this.ITEM_DATA.iiko_sizes_parsed[0].price, 10);
		const baseValue = parseInt(this.ITEM_DATA.iiko_sizes_parsed[0].portionWeightGrams, 10);

		const search_sizes = ()=>{
			const s = this.ITEM_DATA.iiko_modifiers_parsed.filter((mGroup)=>mGroup.name?.toLowerCase().includes("размер"));			
			const arrVirtualSizes = s.length ? (s[0].items).map((modif_size)=>{				
				return {
					price: parseInt(modif_size.price,10) + basePrice,
					originalPrice: basePrice,
					sizeId: modif_size.modifierId,
					sizeGroupId: s[0].modifierGroupId,					
					isVirtualSize:true,
					sizeName: modif_size.name.split(' ').join(''),
					volume: parseInt(modif_size.portionWeightGrams, 10) + baseValue,					
				}
			}) : null;

			if(arrVirtualSizes){
				const sortedAsc = [...arrVirtualSizes].sort((a, b) => a.price - b.price) ;		
				sortedAsc[0].isDefault = 'true';
				return sortedAsc;
			}else{
				return null;
			}
		}
				
		this.arrVirtualSizes = search_sizes();
		return this.arrVirtualSizes ?? this.ITEM_DATA.iiko_sizes_parsed;
		
	},

	set_current_vars:function(vars) {	
		this.VARS = vars;
	},
	build:function() {

		var _this=this;
				
		const sizes = this.sizeFromModifiers ? this._calc_from_modifiers() : this.get_all();

		const foo = {
			create_btns:($btns, sizes)=>{
				for(var i=0; i<sizes.length;i++){
					
					const s = sizes[i];										
					const currentClass = s.isDefault=='true'? " active":"";
					const $btn = $('<div></div>',{class: this.CN+"item-size-btn "+currentClass});
					const currentSize = {
						price: s.price || 0,
						originalPrice: s.originalPrice || 0,
						sizeGroupId: s.sizeGroupId || '',
						sizeName: s.sizeName || '',
						volume: s.portionWeightGrams || s.volume,
						units: foo.units_to_strings(s.measureUnitType || 'GRAM'),
						sizeId: s.sizeId || '',
						sizeCode: s.sizeCode || '',
						isVirtualSize: s.isVirtualSize,
					}

					$btn.html(`<div>${currentSize.sizeName}</div><div>${currentSize.volume} ${currentSize.units}</div>`);

					(function(vars) {
						vars.$btn.on('touchend click',function(e){														
							_this.change_current_size(vars);
							e.originalEvent.cancelable && e.preventDefault();
						});
					})({$btn, index:i, ...currentSize});
				
					s.isDefault=='true' && this.set_current_vars({$btn, index:i, ...currentSize});
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