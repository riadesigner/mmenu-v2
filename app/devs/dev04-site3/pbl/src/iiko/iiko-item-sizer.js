import {GLB} from '../glb.js';

export const IIKO_ITEM_SIZER = {
	init:function(item,opt) {
		this.ITEM_DATA = item;
		this.onUpdate = opt.onUpdate;
		this.CN = "mm2-";
		this._CN = "."+this.CN;
		this.reset();
		this.build();
		return this;
	},
	reset:function() {
		this.VARS = {
			price:0,
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
	// private
	set_current_vars:function(vars) {		
		this.VARS = vars;
	},
	build:function() {

		var _this=this;
		const sizes =  this.get_all();
		const foo = {
			create_btns:($btns)=>{
				for(var i=0; i<sizes.length;i++){
					
					const s = sizes[i];					
					// console.log('s',s)
					const currentClass = s.isDefault=='true'? " active":"";
					const $btn = $('<div></div>',{class: this.CN+"item-size-btn "+currentClass});
					const price = s.price || 0;
					const sizeName = s.sizeName || "";
					const volume = s.portionWeightGrams || 0;
					const units = foo.units_to_strings(s.measureUnitType);					
					const sizeId = s.sizeId || "";
					const sizeCode = s.sizeCode || "";

					$btn.html(`${sizeName}<br>${volume} ${units}`);

					(function($btn, index, price, sizeName) {
						$btn.on('touchend click',function(e){														
							_this.change_current_size({$btn, index, price, sizeName, volume, units, sizeId, sizeCode});
							e.originalEvent.cancelable && e.preventDefault();
						});
					})($btn, i, price, sizeName, volume, units, sizeId, sizeCode);
				
					s.isDefault=='true' && this.set_current_vars({price, sizeName, volume, units, sizeId, sizeCode});							
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
				foo.create_btns(this.$arr_btns['mobile']);
				foo.create_btns(this.$arr_btns['desktop']);
			}else{								
				const s = sizes[0];				
				const price = s.price;							
				const volume = s.portionWeightGrams;
				const units = foo.units_to_strings(s.measureUnitType);				
				const sizeName = s.sizeName;				
				const sizeId = s.sizeId || "";
				const sizeCode = s.sizeCode || "";
				this.set_current_vars({price, sizeName, volume, units, sizeId, sizeCode});
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