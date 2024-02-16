import {GLB} from './glb.js';
import $ from 'jquery';

export var VIEW_TABLE_CHANGE = {
	init:function(options) {

		this._init(options);		

		this.$btnBasket= this.$view.find(this._CN+"btn-basket");
		this.$btnClose = this.$view.find(this._CN+"btn-close-menu");
		this.$btnBack = this.$view.find(this._CN+"btn-back, "+this._CN+"std-header-btn-back, "+this._CN+"btn-close-items");		
		this.$btnSave = this.$view.find(this._CN+"btn-save");

		this.$table_list = this.$view.find(this._CN+"alltables-list");
		
		this.behavior();

		return this;
	},

	update:function(current_table) {		
		this._need2save(false);
		this.CURRENT_TABLE = parseInt(current_table,10);
		this.CHOSEN_TABLE = -1;
		let str_list = "";
		let tables_uniq_names = GLB.CAFE.get().tables_uniq_names;
		tables_uniq_names = JSON.parse(tables_uniq_names);
		let iiko_tables = GLB.CAFE.get().iiko_tables;		
		iiko_tables = JSON.parse(iiko_tables);
		if(!iiko_tables || !tables_uniq_names) return false;
		this.build(iiko_tables,tables_uniq_names,current_table);		
	},
	build:function(table_sections,names,current_table) {		
		let all_str = '';		
		for(let s in table_sections){
			if(table_sections.hasOwnProperty(s)){
				let section = table_sections[s];
				let tables = section['tables'];
				if(tables.length){
					for(let t in tables){
						if(tables.hasOwnProperty(t)){
							let tbl = tables[t];							
							let current = parseInt(tbl['number'],10)===parseInt(current_table,10);
							let current_class = current?"current":"";
							let uniq_name = names['table-'+tbl['number']];
							let disabled = !uniq_name?"disabled":"";
							let str_row = `<li class="${current_class} ${disabled}" data-table-number="${tbl['number']}">
								<span>№-${tbl['number']}<span>
								<span>${section['section_name']}: ${tbl['name']}</span>
								</li>`;
							all_str+=str_row;
						}
					}
				}
			}
		};
		all_str = `<ul>${all_str}</ul>`;		
		this.$table_list.html(all_str);
		this.$table_list.find('li').each((i,el)=>{
			$(el).on("touchstart",(e)=>{
				$(el).addClass('active');
			});
			$(el).on("touchend",(e)=>{
				$(el).removeClass('active')
				if(!this.VIEW_SCROLLED && !$(el).hasClass('current') && !$(el).hasClass('disabled')){
					$(el).siblings().removeClass('current');
					$(el).addClass('current');
					this.check_if_need2save($(el).data('table-number'));
				};
				e.originalEvent.cancelable && e.preventDefault();				
			});			
		})
	},
	check_if_need2save:function(chosen_table_number){
		this.CHOSEN_TABLE = parseInt(chosen_table_number,10);
		let need = this.CHOSEN_TABLE!==this.CURRENT_TABLE;
		this._need2save(need);
	},
	behavior:function() {
		const _this=this;	

		const arrMobileButtons = [
			this.$btnBasket,
			this.$btnBack,
			this.$btnClose
		];

		this._behavior(arrMobileButtons);

		this.$btnBack.on("touchend click",(e)=>{			
			GLB.UVIEWS.go_first();
			e.originalEvent.cancelable && e.preventDefault();
		});

		this.$btnSave.on("touchend click",(e)=>{			
			this.save();	
			e.originalEvent.cancelable && e.preventDefault();
		});

	},
	save:function() {
		console.log("new save", this.CHOSEN_TABLE);
		if(this.CHOSEN_TABLE<1) {return false;}
		let tables_uniq_names = GLB.CAFE.get().tables_uniq_names;
		if(!tables_uniq_names){return false;}
		tables_uniq_names = JSON.parse(tables_uniq_names);
		let tbl_uniq = tables_uniq_names['table-'+ this.CHOSEN_TABLE];
		if(!tbl_uniq){return false;}
		const cafe_uniq = GLB.CAFE.get('uniq_name');		
		const url =  `${SITE_CFG.home_page}cafe/${cafe_uniq}/table/${tbl_uniq}`;
		location.href= url;		
	}

};