
export var TABINDEX = {
	init:function() {
		this.$allinputs = $('input,textarea');
		this.clear();
		return this;
	},
	clear:function(){
		this.$allinputs.attr('tabindex','-1');
		return this;
	},
	update:function($inputs){
		$inputs.each(function(i){
			$(this).attr('tabindex',i);
		});
	}
};


