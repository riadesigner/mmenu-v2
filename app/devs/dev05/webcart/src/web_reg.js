import {GLB} from './glb.js';

export var WebReg  = {
	init:function(siteConfig){
		this.siteConfig = siteConfig;
		console.log('this.siteConfig',this.siteConfig);
		this.$webuserRole = $('.webuser-role');		
		this.check_url();
		this.behavior();
	},
	check_url:function(){

		if(this.siteConfig.register!=='waiter' 
		&& this.siteConfig.register!=='manager' 
		&& this.siteConfig.register!=='supervisor' ){
			alert('Неправильная ссылка');
			location.href=this.siteConfig.home_page+'404';
		}

		this.init_web_reg();

	},
	init_web_reg:function(){
		let role = this.siteConfig.register=='waiter'?'Официант':this.siteConfig.register=='manager'?'Менеджер':'Супервайзер';
		this.$webuserRole.html(`Роль: ${role}`);
		console.log('init_web_reg', this.siteConfig.register);
		// this.$btn = $(".web-reg-btn");

	},
	behavior:function(){
		// var _this=this;
		// this.$btn.on("touchend click",function(e) {			
		// 	var contract_name = $(this).data("contract-name");
		// 	var id_user = $(this).data("id-user");
		// 	var id_cafe = $(this).data("id-cafe");
		// 	if(confirm("Активировать контракт №"+contract_name+"?")){
		// 		!_this.NOW_LOADING && _this.activate_contract(contract_name,id_user,id_cafe);
		// 	};
		// 	return false;
		// });
	},
	activate_contract:function(contract_name,id_user,id_cafe){

		var _this=this;		
		GLB.RDSAdmin.message('Активация договора '+contract_name);
		GLB.RDSAdmin.now_loading();
		var url = 'rds/lib/rds.contract_activate.php';

		var data = {
			contract_name:contract_name,
			id_user:id_user,
			id_cafe:id_cafe
		};

	
        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            data:data,
            method:"POST",
            success: function (answer) {            	
            	GLB.RDSAdmin.end_loading();
            	if(answer && !answer.error){     
            		console.log(answer)

					location.reload();
				}else{
					GLB.RDSAdmin.errmessage(answer.error);
				}
            },
            error:function(response) {
            	console.log("!!!")
            	console.log(response)
            	GLB.RDSAdmin.errmessage(JSON.stringify(response));
            	GLB.RDSAdmin.end_loading();            	
			}
        });			
	}
};

