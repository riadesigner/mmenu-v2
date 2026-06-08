import {GLB} from './glb.js';

export var RDSUserList = {
	init:function() {
		this.$form = $(".rds-user-list");
		this.$btnDel = this.$form.find("a.del-user");
		this.$btnToArchive = this.$form.find("a.to-archive");
		this.$btnFromArchive = this.$form.find("a.from-archive");
		this.behavior();
	},
	behavior:function() {
		var _this=this;


		var fn={
			del_if_confirmed:function(id_user){
				if(confirm("Удалить пользователя ID:"+id_user+"?")){					
					!_this.NOW_LOADING && _this.del(id_user);					
				}
			},
			to_arch_if_confirmed:function(id_cafe){
				if(confirm("Поместить кафе "+id_cafe+" в архив?")){					
					!_this.NOW_LOADING && _this.to_archive(id_cafe);
				}				
			},
			from_arch_if_confirmed:function(id_cafe){
				if(confirm("Достать кафе "+id_cafe+" из архива?")){					
					!_this.NOW_LOADING && _this.from_archive(id_cafe);
				}				
			}			
		};

		this.$btnDel.each(function(i) {			
			$(this).on('click',function(){
				fn.del_if_confirmed($(this).data('id-user'));
				return false;
			});
		});

		this.$btnToArchive.each(function(i) {
			$(this).on("click",function() {
				fn.to_arch_if_confirmed($(this).data('id-cafe'));
				return false;
			});		
		});

		this.$btnFromArchive.each(function(i) {
			$(this).on("click",function() {
				fn.from_arch_if_confirmed($(this).data('id-cafe'));
				return false;
			});		
		});


	},

	to_archive:function(id_cafe){
		var _this=this;		
		GLB.RDSAdmin.message('Отправка кафе '+id_cafe+' в архив');
		GLB.RDSAdmin.now_loading();
		var url = 'rds/lib/rds.cafe_to_archive.php';
        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            data:{id_cafe:id_cafe},
            method:"POST",
            success: function (answer) {
				console.log(answer);
            	GLB.RDSAdmin.end_loading();
            	if(answer && !answer.error){            		
            		location.reload();
				}else{
					GLB.RDSAdmin.errmessage(answer.error);
				}
            },
            error:function(response) {
            	console.log(response)
            	GLB.RDSAdmin.end_loading();
            	GLB.RDSAdmin.errmessage(response);
			}
        });			
	},

	from_archive:function(id_cafe){
		var _this=this;		
		GLB.RDSAdmin.message('Возвращение кафе '+id_cafe+' из архива');
		GLB.RDSAdmin.now_loading();
		var url = 'rds/lib/rds.cafe_from_archive.php';
        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            data:{id_cafe:id_cafe},
            method:"POST",
            success: function (answer) {
				console.log(answer);
            	GLB.RDSAdmin.end_loading();
            	if(answer && !answer.error){            		
            		location.reload();
				}else{
					GLB.RDSAdmin.errmessage(answer.error);
				}
            },
            error:function(response) {
            	console.log(response)
            	GLB.RDSAdmin.end_loading();
            	GLB.RDSAdmin.errmessage(response);
			}
        });			
	},	

	del:function(id_user) {
		var _this=this;		
		GLB.RDSAdmin.message('Отправка запроса на удаление пользователя с ID:'+id_user);
		GLB.RDSAdmin.now_loading();
		var url = 'rds/lib/rds.order_del_user.php';
        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            data:{id_user:id_user},
            method:"POST",
            success: function (answer) {    
            	console.log(answer);        	
            	GLB.RDSAdmin.end_loading();
            	if(answer && !answer.error){            		
            		var msg = "Отправлен запрос на удаление пользователя c ID:"+id_user;            		
            		GLB.RDSAdmin.message(msg);
				}else{
					GLB.RDSAdmin.errmessage(answer.error);
				}
            },
            error:function(response) {
            	console.log(response);
            	GLB.RDSAdmin.end_loading();
            	GLB.RDSAdmin.errmessage(response);
			}
        });	
	}
};