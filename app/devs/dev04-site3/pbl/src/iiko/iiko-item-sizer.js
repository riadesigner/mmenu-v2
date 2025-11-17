import {GLB} from '../glb.js';

export const IIKO_ITEM_SIZER = {
	init:function(item,opt) {
		this.ITEM_DATA = item;
		this.onUpdate = opt.onUpdate;
		this.sizeMode = opt.sizeMode; // undefined | 'FROM_MODIFIERS'
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
		const has_size_from_modifiers = true;
		if(has_size_from_modifiers){
			return null;
		}else{
			this.get_all();
		}		
	},
	// private
	set_current_vars:function(vars) {		
		this.VARS = vars;
	},
	build:function() {

		var _this=this;
				
		const sizes = this.sizeMode==='FROM_MODIFIERS' ? this.get_from_modifiers() : this.get_all();		

		const foo = {
			create_btns:($btns, sizes)=>{
				for(var i=0; i<sizes.length;i++){
					
					const s = sizes[i];					
					// console.log('s',s)
					const currentClass = s.isDefault=='true'? " active":"";
					const $btn = $('<div></div>',{class: this.CN+"item-size-btn "+currentClass});
					const price = s.price || 0;
					const originalPrice = s.originalPrice || 0;
					const sizeGroupId = s.sizeGroupId || "";					
					const sizeName = s.sizeName || "";
					const volume = s.portionWeightGrams || 0;
					const units = foo.units_to_strings(s.measureUnitType);					
					const sizeId = s.sizeId || "";
					const sizeCode = s.sizeCode || "";

					$btn.html(`${sizeName}<br>${volume} ${units}`);

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
				const volume = s.portionWeightGrams;
				const units = foo.units_to_strings(s.measureUnitType);				
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