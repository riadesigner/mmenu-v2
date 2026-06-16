import {GLB} from './glb.js';

export var RDSAdmin = {
	init:function() {
		this.$body = $("body");		
		this.$message = this.$body.find(".rds-message");
		this.$errmessage = this.$body.find(".rds-errmessage");
		this.$btnExit = $(".rds-site-header-btn-exit");
		this.$loader = $(".rds-loader");
		this.NOW_LOADING = false;
		console.log("hello!")
		this.behavior();
	},
	behavior:function() {
		var _this=this;
		this.$btnExit.on("touchend click",function() {
			_this.exit();	
			return false;
		});
	},
	now_loading:function() {
		this.NOW_LOADING = true;
		this.$body.addClass("now-loading");
	},
	end_loading:function() {
		this.NOW_LOADING = false;
		this.$body.removeClass("now-loading");
	},
	exit:function() {
		var url = 'rds/lib/rds.logout.php';
        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            data:{},
            method:"POST",
            success: function (answer) {
            	if(!answer.error){
            		console.log("OK!");
            		location.reload();
				}else{
					console.log(answer.error);
				}
            },
            error:function(response) {
		           console.log("err:",response);
			}
        });	
	},
	errmessage:function(msg) {
		this.$message.hide();
		this.$errmessage.hide().html(msg).fadeIn();
	},
	message:function(msg) {
		this.$errmessage.hide();
		this.$message.hide().html(msg).fadeIn();	
	}
};
