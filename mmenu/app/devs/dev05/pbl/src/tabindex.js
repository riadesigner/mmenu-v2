import $ from 'jquery';

export var TABINDEX = {
	init:function() {
		this.$allinputs = $('input,textarea');
		this.clear();
		return this;
	},
	clear:function(){
		this.$allinputs.attr('tabindex','-1');
	}
};
	 