import {GLB} from '../glb.js';

export var CHEFS_ITEM_SIZER = {
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
			sizeName:"",
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
		return this.ITEM_DATA.sizes_parsed;
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
					const currentClass = i==0? " active":"";
					const $btn = $('<div></div>',{class: this.CN+"item-size-btn "+currentClass});
					const price = s.price || 0;					
					const volume = s.volume || 0;
					const units = s.units || "г";
					const sizeName = `${volume} ${units}`;
					$btn.html(sizeName);

					(function($btn, index, price, sizeName, volume, units) {
						$btn.on('touchend click',function(e){														
							_this.change_current_size({$btn, index, price, sizeName, volume, units});							
							e.originalEvent.cancelable && e.preventDefault();
						});
					})($btn, i, price, sizeName, volume, units);
				
					i==0 && this.set_current_vars({price, sizeName, volume, units});							
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
				const s = sizes[0];
				const price = s.price;
				const sizeName = `${volume} ${units}`;			
				const volume = s.volume;		
				const units = s.units;				
				this.set_current_vars({price, sizeName, volume, units});
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