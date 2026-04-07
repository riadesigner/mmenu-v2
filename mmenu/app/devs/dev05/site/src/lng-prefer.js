import {GLB} from './glb.js';

export var LngPrefer = {
	init:function(){		
		this.$el = $(".top-set-lngs div, .mobile-lng-menu li");
		this.behavior();
	},
	behavior:function(){
		var _this=this;
		this.$el.on("click",function(){
			if(!$(this).hasClass("current")){
				_this.set_lng_prefer($(this).data("lang"));
			}			
		});
	},
	set_lng_prefer:function(lng){	
		
		var PATH = 'site/lib/';
		var url = PATH + 'site.set_lng_prefer.php';		

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            method:"POST",
            data:{lng:lng},
            success: function (answer) {            	            	
				if(answer && !answer.error){
					// console.log("yahoo!",answer);
					location.reload(true);
				}else{					
					// console.log("err:",answer.error);
				}
            },
            error:function(response) {
		        // console.log("err set language prefer",response);
			}
        });
	}
}