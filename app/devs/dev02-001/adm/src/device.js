
export var DEVICE = {
	init:function() {		
		this.$body = $('body');		
		console.log('navigator.userAgent',navigator.userAgent)
		this.is_Android = /(android)/i.test(navigator.userAgent);
		this.is_iOS = /iPad|iPhone|iPod|Mac OS/i.test(navigator.userAgent);
		this.add_class_to_body();
		return this;
	},
	add_class_to_body:function() {
		this.is_Android && this.$body.addClass('is_android');
		this.is_iOS && this.$body.addClass('is_ios');
	},
	is_android:function(){
		return this.is_Android;
	},
	is_ios:function(){
		return this.is_iOS;
	}	
};
