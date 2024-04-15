import {GLB} from './glb.js';

export var CreateMenu = {
	init:function(){
		this.CURRENT = 0;
		this.URL_HASH_CREATE = '#create-menu';
		this.$createSection = $('.site-section-create');
		this.$inputsWrapper = $('.site-section-create-forms-inputs'); 

		this.$btnCreate = $('.site-section-create-invite .btn-create');
		this.$bntClose = $('.site-section-create-forms-close, .site-section-create-succeed-close');
		this.$btnSend = $('.site-section-create-forms-button');
		this.$btnCreateFromFooter = $('.site-new-footer__invite-button');
		
		this.$okMessage = $('.userinput-ok-message');
		this.$errMessage = $('.userinput-err-message');
		this.$doneMessage = $('.done-message');	
		this.$allInputs = $('.site-section-create-forms input');
		this.$newUserInputEmail =  $("input[name='new-invite-user-email']");
		this.$newUserInputCafe =  $("input[name='new-invite-cafe-title']");
		this.reset();
		this.behavior();
		this.check_url_hash();
	},
	check_url_hash:function(){
		var h = window.location.hash;
		h==this.URL_HASH_CREATE && this.show_create_form();
	},
	behavior:function(){
		var _this=this;
		
		this.$allInputs.on('keydown',function(ev){			
			if(ev.key.toLowerCase()=='enter'){
				!_this.NOW_LOADING && _this._send();
			}
		});

		this.$btnCreate.on("touchend click",function(ev){			
			ev.originalEvent.cancelable && ev.preventDefault();
			ev.stopPropagation();
			GLB.TABINDEX.clear().update(_this.$allInputs);
			!GLB.Bhv.page_scrolled() && _this.show_create_form();			
		});
		
		this.$bntClose.on("touchend click",function(ev){
			ev.originalEvent.cancelable && ev.preventDefault();
			ev.stopPropagation();
			if(!GLB.Bhv.page_scrolled()){
				_this.$allInputs.blur();
				setTimeout(function(){
					_this.reset();	
				},50);				
			}
		});		
		this.$btnSend.on("touchend click",function(ev){

			ev.originalEvent.cancelable && ev.preventDefault();
			ev.stopPropagation();			

			if(!GLB.Bhv.page_scrolled() && !_this.NOW_LOADING){
				_this._send()
			};

		});

		this.$btnCreateFromFooter.on("touchend click",function(ev){

			console.log("btnCreateFromFooter touched");
			
			ev.originalEvent.cancelable && ev.preventDefault();
			ev.stopPropagation();
			
			if(GLB.Bhv.page_scrolled()){ return; }
			
			_this.$allInputs.blur();

			setTimeout(function(){
				if($('body').hasClass('page-home')){
		 			$("html").animate({ scrollTop: 0 }, 300, _this.show_create_form.bind(_this));
				}else{
					location.href = SITE_CFG.home_page+_this.URL_HASH_CREATE;
				};				
			},50);
			
		});

	},
	create:function(opt) {
		var _this = this;
		var PATH = 'site/lib/';
		var url = PATH + 'site.account_create.php';
		var email = this.get_useinput_email();
		var cafe = this.get_useinput_cafe();
		this.now_loading();
		
		setTimeout(function() {
	        _this.AJAX = $.ajax({
	            url: url+"?callback=?",
	            data:{email:email,cafe:cafe},
	            method:"POST",
	            dataType: "jsonp",
	            success: function (response) {
	            	_this.end_loading();
	            	if(!response.error){					
						opt.onReady && opt.onReady(response);
					}else{
						_this.err_message(response.error);
						// console.log(response.error);
					}
	            },
	            error:function(response) {
	            	_this.end_loading();
	            	_this.err_message(GLB.SiteLng.get('ph_err_try_later'));
			        // console.log("err create account",response);
				}
	        });	
		},500);

	},	
	_send:function(){
		var _this=this;
		this.$allInputs.blur();
		setTimeout(function(){
			if(!_this.is_userinput_empty()){
				_this.ok_message();
				_this.create({onReady:function() {
					_this.done_message(_this.get_useinput_email());
					setTimeout(function(){
						_this.show_create_succeed();
						_this.end_loading();						
						_this.clear_inputs();
					},50);
				}});
			}else{
				_this.err_message(GLB.SiteLng.get('ph_all_fields_need_to_fill'));
			}
		},50);
	},
	reset:function(){
		this.$inputsWrapper.hide();
		this.hide_create_form();
		this.hide_create_succeed();
		this.end_loading();
		this.TMR && clearTimeout(this.TMR);
		this.TMR2 && clearTimeout(this.TMR2);
		this.clear_inputs();
	},
	clear_inputs:function() {
		this.$allInputs.val("");
	},	
	now_loading:function(){
		this.NOW_LOADING = true;
		this.$createSection.addClass('now-loading');
	},
	end_loading:function(){
		this.NOW_LOADING = false;
		this.$createSection.removeClass('now-loading');
	},
	done_message:function(user_email){		
		var msg = GLB.SiteLng.get('ph_the_message_has_been_sent');
		msg = msg.replace(/\[email\]/i, user_email);
		this.$doneMessage.html(msg);		
	},	
	ok_message:function() {
		var msg = GLB.SiteLng.get('ph_a_link_will_be_sent');
		this.$errMessage.hide();
		this.$okMessage.html(msg).fadeIn();
	},
	err_message:function(msg) {
		this.$okMessage.hide();
		this.$errMessage.hide().html(msg).fadeIn();
	},
	is_userinput_empty:function(){
		return !this.get_useinput_email() || !this.get_useinput_cafe();
	},	
	get_useinput_email:function(){
		return this.trim(this.$newUserInputEmail.val()).toLowerCase();
	},	
	get_useinput_cafe:function(){
		return this.trim(this.$newUserInputCafe.val());	
	},
	trim:function(str){
		return str.replace(/^\s+|\s+$/gm,'');
	},	
	is_open:function(){
		return this.$createSection.hasClass('expanded');		 
	},
	show_create_form:function(){
		var _this=this;
		this.$createSection.addClass('expanded');
		setTimeout(function(){ 
			_this.$inputsWrapper.show();
			setTimeout(function(){
				_this.$createSection.addClass('show-user-inputs');
			},100);			
		},600)
		$(this).triggerHandler('open');
	},
	hide_create_form:function(){
		var _this=this;
		this.$createSection.removeClass('show-user-inputs');
		setTimeout(function(){ _this.$inputsWrapper.hide(); },500)
		this.$createSection.removeClass('expanded');		
		$(this).triggerHandler('close');
	},
	show_create_succeed:function(){
		var _this=this;
		setTimeout(function(){			
			_this.$createSection.removeClass('show-user-inputs');
			_this.$inputsWrapper.hide();
		},500);
		this.$createSection.addClass('succeed');		
	},
	hide_create_succeed:function(){
		this.$createSection.removeClass('succeed');		
	}
};