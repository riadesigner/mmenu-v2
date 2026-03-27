import {GLB} from './glb.js';

export var WebReg  = {
	init:function(siteConfig){
		this.siteConfig = siteConfig;
		console.log('this.siteConfig',this.siteConfig);
		this.$webuserRole = $('.webuser-role');		
		this.$appStatus = $('.app-status');		
		this.$errMessage = $('.err-message');
		this.$okMessage = $('.ok-message');
		this.vapidPublicKey = this.siteConfig.vapidPublicKey;
		this.reset();
		this.check_url();
		this.check_notif_enabled();		
		this.behavior();
	},
	reset:function(){		
		this.$errMessage.html('');	
		this.$okMessage.html('');	
	},
	check_notif_enabled:function(){
		// Проверяем доступность уведомлений
		if (!('Notification' in window)) {
			alert('Ваш браузер не поддерживает уведомления');
		}		
		
		if(Notification.permission === 'denied'){
			const msg =`Уведомления не разрешены.<br>
			Чтобы их включить нажпите на замок в адресной строке,
			выберите Настройка сайта->Уведомления->разрешить`;
			this.err_message(msg);
		}
	
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
		this.$errMessage.append(`<p>${errMsg}</p>`);
	},
	ok_message:function(okMsg=''){
		this.$okMessage.append(`<p>${okMsg}</p>`);
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

		this.async_check_token(this.siteConfig.token)
		.then(data => {
			this.err_message('');
			this.async_webuser_register(data);			
		})
		.catch(error => {
			this.err_message(error);
			console.log('error', error);			
		});

	},
	async_check_token:function(token){
		return new Promise((res, rej) => {
			this.now_loading();									

			var url = 'webcart/lib/web.check_token.php';
			var data = {
				token:token
			};
			this.AJAX = $.ajax({
				url: url,
				dataType: "json",
				data:data,
				method:"POST",
                xhrFields: {
                    withCredentials: true  // Для отправки cookies при CORS
                }, 				
				success: (answer)=> {					
					this.end_loading();					
					if(answer && !answer.error){						
						res(answer);
					}else{						
						rej(answer.error);
					}
				},
				error:(response)=> {					
					console.log('err!', response)
					this.end_loading();	
					rej(JSON.stringify(response));	
				}
			});			
		});
	},
	async_webuser_register:async function(data){
				
		console.log('======= VAPIDPUBLICKEY ======= ', this.vapidPublicKey);
		console.log('async_webuser_register', data);
		const Push = GLB.RegisterPush;
		const {error, subscription, isNew, message} = await Push.init(this.vapidPublicKey); 		
		if(error){
			this.err_message(error);
		}else{
			message && this.ok_message(message);
			console.log( 'subscription = ',subscription, 'isNew = ', isNew);
			await this.save_to_db_async(subscription,isNew)
			.then((vars)=>{
				console.log('vars', vars);
			},
			(error)=>{
				console.log('error', error);
			})
		}		
		
	},
    save_to_db_async: function(subscription, isNew){
		return new Promise((res, rej) => {
			this.now_loading();

			var url = 'webcart/lib/web.reg_to_db.php';

			const subscriptionData = JSON.parse(JSON.stringify(subscription));

			const data = {
				isNew,
				...subscriptionData
			};			

			console.log('data', data);

			$.ajax({
				url: url,
				method: "POST",
                xhrFields: {
                    withCredentials: true  // Для отправки cookies при CORS
                },
				contentType: "application/json",
				data: JSON.stringify(subscription), // ← сохраняем полную структуру
				success: (answer)=> {					
					this.end_loading();					
					if(answer && !answer.error){						
						res(answer);
					}else{						
						rej(answer.error);
					}
				},
				error:(response)=> {					
					console.log('err!', response)
					this.end_loading();	
					rej(JSON.stringify(response));	
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

