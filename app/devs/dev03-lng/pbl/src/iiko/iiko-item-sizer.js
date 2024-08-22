import {GLB} from '../glb.js';
import $ from 'jquery';

export var IIKO_ITEM_SIZER = {
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
			sizeName:"",
			sizeId:"",
			sizeCode:"",
			price:0,
			volume:0
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
	
	// get_current:function() {
	// 	let v = this.VARS;
	// 	return [v.price, v.volume, v.sizeName, v.sizeId, v.sizeCode];
	// },

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
					const currentClass = s.isDefault=='true'? " active":"";
					const $btn = $('<div></div>',{class: this.CN+"item-size-btn "+currentClass});
					const price = s.price || 0;					
					const volume = s.portionWeightGrams || 0;
					const sizeName = s.sizeName || "";
					const sizeId = s.sizeId || "";
					const sizeCode = s.sizeCode || "";					
					$btn.html(sizeName);	

					(function($btn, index, price, volume, sizeName, sizeId, sizeCode) {
						$btn.on('touchend click',function(e){														
							_this.change_current_size({$btn, index, price, volume, sizeName, sizeId, sizeCode});							
							e.originalEvent.cancelable && e.preventDefault();
						});
					})($btn, i, price, volume, sizeName, sizeId, sizeCode);
				
					s.isDefault=='true' && this.set_current_vars({price, volume, sizeName, sizeId, sizeCode});							
					$btns.append($btn);

				};							
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
				var s = sizes[0];
				let price = s.price;			
				let volume = s.portionWeightGrams;		
				var sizeName = "";
				var sizeId = "";
				var sizeCode = "";
				this.set_current_vars({price, volume, sizeName, sizeId, sizeCode});
			};
		}else{
			this.reset();
			console.log("something wrong with iiko sizes",this.ITEM_DATA.id);
		}
	
	},
	change_current_size:function(vars){		
		this.set_current_vars(vars);				
		this.onUpdate && this.onUpdate(vars);
		vars.$btn.addClass('active').siblings().removeClass('active');
	}	

};