import {GLB} from './glb.js';

export var WebReg  = {
	init:function(siteConfig){
		this.siteConfig = siteConfig;
		console.log('this.siteConfig',this.siteConfig);
		this.$webuserRole = $('.webuser-role');		
		this.$appStatus = $('.app-status');		
		this.$errMessage = $('.err-message');
		this.reset();
		this.check_url();		
		this.behavior();
	},
	reset:function(){		
		this.err_message('');		
	},
	check_url:function(){

		if(this.siteConfig.register!=='waiter' 
		&& this.siteConfig.register!=='manager' 
		&& this.siteConfig.register!=='supervisor' ){
			this.message_not_valid_link();
			return;	
		}

		this.init_web_reg();

	},
	now_loading:function(){
		console.log('now_loading');
		this.$appStatus.html('Загрузка...');
	},
	end_loading:function(){
		this.$appStatus.html('Готово');
	},
	err_message:function(errMsg=''){
		this.$errMessage.html(errMsg);
	},
	message_not_valid_link:function(){
		this.err_message('Неправильная ссылка');
		setTimeout(()=>{
			location.href=this.siteConfig.home_page+'404';
		},1000);
	},
	init_web_reg:function(){
		let role = this.siteConfig.register=='waiter'?'Официант':this.siteConfig.register=='manager'?'Менеджер':'Супервайзер';
		this.$webuserRole.html(`Роль: ${role}`);		

		this.async_register(this.siteConfig.token)
		.then(data => {
			console.log('data', data);
		})
		.catch(error => {
			this.err_message(error);
			console.log('error', error);
		});

	},
	async_register:function(token){
		return new Promise((res, rej) => {
			this.now_loading();	
			
			console.log('PAUSE')
			rej();

			var url = 'webcart/lib/web.register.php';
			var data = {
				token:token
			};
			this.AJAX = $.ajax({
				url: url+"?callback=?",
				dataType: "json",
				data:data,
				method:"POST",
                xhrFields: {
                    withCredentials: true  // Для отправки cookies при CORS
                }, 				
				success: (answer)=> {				
					this.end_loading();
					if(answer && !answer.error){
						console.log(answer)
						// location.reload();
						res();
					}else{
						this.err_message(answer.error);
						rej();
					}
				},
				error:(response)=> {					
					console.log(response)
					this.err_message(JSON.stringify(response));
					this.end_loading();		
					rej();	
				}
			});			
		});
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
	}
};

