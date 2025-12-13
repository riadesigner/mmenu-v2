import {GLB} from './glb.js';

export var SignIn = {
	init:function() {

		this.CURRENT_SECTION = 0;
		this.admUrl = SITE_CFG.admin_url;		
		this.$body = $('body');		
		this.$signPanel = $('.sign-in-panel');

		this.$btnShowPanel = $('.mobile-btn-signin, .desktop-btn-signin');
		this.$btnClosePanel = $('.sign-in-btn-close, .sign-in-btn-close3, .shadow-layer');				
		this.$btnSignIn = $('.sign-in-btn-enter');
		this.$btnFogot = $('.sign-in-btn-fogot');
		this.$btnGetPass = $('.sign-in-btn-get');

		this.$sections = this.$signPanel.find('.section');	
		this.$allMessages = this.$signPanel.find('.message');
		this.$errMessages = this.$signPanel.find('.sign-in-err-message');
		this.$okMessage = this.$signPanel.find('.sign-in-ok-message');
		this.$loaders = this.$signPanel.find('.sign-in-loader');		
		this.$allinputs = this.$signPanel.find('input');
		this.$signInInputs = this.$signPanel.find('.section-sign-in__inputs input');
		this.$getPassInputs = this.$signPanel.find('.section-get-pass__inputs input');		

		this.$inputEmailForNewPass = this.$signPanel.find('.section-get-pass input[name=email]'); 
		this.$inputEmailForEnter = this.$signPanel.find('.section-sign-in input[name=email]');
		this.$inputPassForEnter = this.$signPanel.find('.section-sign-in input[name=pass]');

		this.behavior();
	},
	behavior:function() {
		var _this=this;
		
		this.$body.hasClass("need-sign-in") && setTimeout( this.show.bind(this), 100);	

		this.$signInInputs.on("keydown",function(e){});
		this.$getPassInputs.on("keydown",function(e){});

		//both
		this.$btnClosePanel.on("touchend click",function(e) {
			e.originalEvent.cancelable && e.preventDefault();
			e.stopPropagation();		
			_this.$allinputs.blur();
			if(!GLB.Bhv.page_scrolled()){
				_this.shadow_layer_hidden(true);
				_this.hide();
			}			
		});	
		//mobile only	
		this.$btnShowPanel.on("touchend",function(e) {
			e.originalEvent.cancelable && e.preventDefault();
			e.stopPropagation();			
			if(!GLB.Bhv.page_scrolled()){
				location.href=_this.admUrl;
			}
		});
		this.$btnSignIn.on("touchend",function(e) {			
			e.originalEvent.cancelable && e.preventDefault();
			e.stopPropagation();			
			!_this.NOW_LOADING && !GLB.Bhv.page_scrolled() && _this._sign_in();
		});
		this.$btnFogot.on("touchend",function(e) {
			e.originalEvent.cancelable && e.preventDefault();
			e.stopPropagation();
			_this.$allinputs.blur();		
			!GLB.Bhv.page_scrolled() && _this.switch_section(1);			
		});		
		this.$btnGetPass.on("touchend",function(e) {
			e.originalEvent.cancelable && e.preventDefault();
			e.stopPropagation();			
			!_this.NOW_LOADING && !GLB.Bhv.page_scrolled() && _this._get_password();
		});
		//refuse if desktop
		this.$btnGetPass.on("click",function(){
			_this.show(2);
		});
		this.$btnShowPanel.on("click",function() {
			_this.show(2);
		});
		this.$btnSignIn.on("click",function() {
			_this.show(2);
		});		
		this.$btnFogot.on("click",function(){
			_this.show(2);
		});		
		
	},
	switch_section:function(num) {
		var _this=this;
		this.reset();
		this.CURRENT_SECTION = num;
		this.$sections.eq(num).css({display:"block"})
		setTimeout(function(){
			_this.$sections.eq(num).addClass("current");	
		},100);
	},
	reset:function() {
		this.hide_messages();
		this.end_loading();
		this.$sections.removeClass("current").css({display:"none"});
		this.clear_inputs();
	},	
	clear_inputs:function() {
		this.$allinputs.val("");
	},
	
	//public
	show:function(numSection) {	
		var _this=this;
		this.$signPanel.show();
		numSection = !numSection?0:numSection;
		setTimeout(function(){
			_this.switch_section(numSection);
			_this.shadow_layer_hidden(false);
			_this.$signPanel.addClass("show");
		},100);
	},
	//public
	hide:function() {
		var _this=this;
		this.$allinputs.blur();
		this.end_loading();
		this.$signPanel.removeClass("show");
		setTimeout(function(){
			_this.$signPanel.hide();
		},300);
	},

	show_err_message:function(msg) {
		this.$allinputs.blur();
		this.$errMessages.hide().html(msg);
		this.$errMessages.eq(this.CURRENT_SECTION).html(msg).fadeIn("slow");
	},
	show_ok_message:function(msg) {
		this.$allinputs.blur();
		this.$okMessage.html(msg).fadeIn();
	},	
	hide_messages:function() {
		this.$allMessages.html("").hide();
	},
	now_loading:function(){
		this.$allinputs.blur();
		this.NOW_LOADING = true;
		this.$loaders.eq(this.CURRENT_SECTION).fadeIn("slow");
	},	
	end_loading:function(){
		this.$allinputs.blur();
		this.NOW_LOADING = false;
		this.$loaders.hide();
	},
	email_verify:function(email) {	
		return	/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
	},
	trim:function(str){
		return str.replace(/^\s+|\s+$/gm,'');
	},
	shadow_layer_hidden:function(mode) {
		if(mode){
			this.$body.removeClass('shadow-mode');
		}else{
			this.$body.addClass('shadow-mode');
		}
	},
	_sign_in:function() {
		var _this = this;				
		
		this.$allinputs.blur();

		var email = this.trim(this.$inputEmailForEnter.val());
		var pass = this.$inputPassForEnter.val();

		if(!email || !pass){
			_this.show_err_message(GLB.SiteLng.get('ph_enter_your_email'));
			return false;
		};
		
		email = email.toLowerCase();

		if(!this.email_verify(email)){
			this.show_err_message(GLB.SiteLng.get('ph_check_is_your_email_right'));
			return false;
		};

		var PATH = 'site/lib/';
		var url = PATH + 'site.sign-in.php';		

		this.hide_messages();
		this.now_loading();

		setTimeout(function(){

        _this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            method:"POST",
            data:{email:email,pass:pass},
            success: function (answer) {            	
            	_this.end_loading();            	
				if(answer && !answer.error){
					_this.clear_inputs();
					location.href = _this.admUrl;
				}else{
					_this.show_err_message(answer.error);
					// console.log("err:",answer.error);
				}
            },
            error:function(response) {
            	_this.end_loading();
            	_this.show_err_message(GLB.SiteLng.get('ph_unknown_error'));            	
		        // console.log("err sign-in",response);
			}
        });

		},500);

	},
	_get_password:function() {		
		var _this = this;

		this.$allinputs.blur();				

		var email = this.trim(this.$inputEmailForNewPass.val());

		if(!email){			
			this.show_err_message(GLB.SiteLng.get('ph_please_enter_email'));
			return false;
		};

		if(!this.email_verify(email)){
			this.show_err_message(GLB.SiteLng.get('ph_check_is_your_email_right'));
			return false;
		};

		var PATH = 'site/lib/';
		var url = PATH + 'site.password_get_new.php';		

		this.hide_messages();
		this.now_loading();

		setTimeout(function(){

        _this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            method:"POST",
            data:{email:email},
            success: function (answer) {
            	_this.end_loading();
				if(!answer.error){
					_this.clear_inputs();
					var msg = GLB.SiteLng.get('ph_pass_has_been_sent').replace('[email]', email);					
					_this.switch_section(3);
					_this.show_ok_message(msg);
				}else{
					_this.show_err_message(answer.error);
					// console.log("err:",answer.error);
				}
            },
            error:function(response) {
            	_this.end_loading();
            	_this.show_err_message(GLB.SiteLng.get('ph_unknown_error'));
		        // console.log("err getting new password",response);
			}
        });

		},500);
		
	}
};