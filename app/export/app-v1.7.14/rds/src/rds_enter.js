import {GLB} from './glb.js';

export var RDSEnter = {
	init:function() {		
		this.$form = $(".enter");
		this.$btnEnter = this.$form.find("button");
		this.$inputLogin = this.$form.find("input[name=admin_login]");
		this.$inputPass = this.$form.find("input[name=admin_pass]");
		this.behavior();
	},
	behavior:function() {
		var _this=this;
		this.$btnEnter.on("touchend click",function() {
			_this.try_enter();
			return false;
		});
	},
	try_enter:function() {
		var _this = this;		

		console.log('!!URLSITE.base', URLSITE.base)
		if(!this.$inputLogin.val() || !this.$inputPass.val()){
			console.log("enter email and password");	
		}else{
			
			var url = URLSITE.base+'rds/lib/rds.rdsadmin.php';

			var login = this.$inputLogin.val();
			var pass = this.$inputPass.val();

			console.log('url = ',url)

	        this.AJAX = $.ajax({
	            url: url+"?callback=?",
	            dataType: "jsonp",
	            data:{login:login,md5pass:GLB.RDS_MD5(pass)},
	            method:"POST",
	            success: function (answer) {
	            	if(!answer.error){
	            		console.log("OK");
	            		location.reload();
					}else{						
						console.log("err enter 1", answer.error);
					}
	            },
	            error:function(response) {
			           console.log("err enter 2",response);
				}
	        });	

		}
	}
};
