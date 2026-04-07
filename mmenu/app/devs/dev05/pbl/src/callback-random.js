

export var CALLBACK_RANDOM = {
	get:function() {
		var rnd = 'Rand_'+Math.random().toString(36).substring(2, 15) + '_' + Math.random().toString(36).substring(2, 15);
		return rnd;
	}
};
