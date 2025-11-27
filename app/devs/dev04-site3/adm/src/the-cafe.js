export var THE_CAFE = {
	init:function() {
		this.CAFE = false;
		this.MODE = "chefsmenu";
		return this;
	},
	load:function(opt) {
		var _this = this;
		var PATH = 'adm/lib/';
		var url = PATH + 'lib.get_cafe_info.php';
		
        this.AJAX = $.ajax({
            url: url+"?callback=?",
            data:{},
            dataType: "jsonp",
            method:"POST",
            success: function (res){            	
            	if(res && !res.error){            	
					_this.CAFE = res.cafe;
					_this._update_mode();
					// console.log('_this.CAFE',_this.CAFE);
					opt.onReady && opt.onReady(res.cafe);
				}else{
					console.log(res.error);
				}
            },
            error:function(res) {
		           console.log("err load cafe info",res);
			}
        });			
	},
	_update_mode:function() {
		if(this.CAFE.iiko_api_key!=""){
			this.MODE = "iiko";
		}else{
			this.MODE = "chefsmenu";
		}
	},	
	is_iiko_mode:function() {
		return this.MODE=="iiko";
	},
	update:function(cafe){
		this.CAFE = cafe;
	},
	set:function(obj_props) {
		for(var i in obj_props){
			this.CAFE[i] = obj_props[i];
		}
	},
	get:function(prop) {
		return !prop ? this.CAFE : this.CAFE[prop];
	},

	get_arr_links:function(){
		const arr = [];
		const tech_link =  {
			type:'tech',
			title:'технический адрес:',
			name:this.get_link_tech('name'), 
			url:this.get_link_tech('url')
		};
		arr.push(tech_link);
		const subdomain = this.get('subdomain');
		const subdomain_link_url = CFG.http + this.get('subdomain')+ "."+ CFG.www_url;
		const subdomain_link_name = this.get('subdomain')+ "."+ CFG.www_url;		
		const subdomain_link = subdomain ? {
			type:'subdomain',
			title:'Подтвержденный адрес:',
			name:subdomain_link_name, 
			url:subdomain_link_url
		}: null;
		subdomain_link && arr.push(subdomain_link);
		
		const external_url = this.get('external_url');
		if(external_url){
			 const external_link = {
				type:'external',
				title:'Внешний адрес:',
				name:external_url,
				url:`https://${external_url}`,
			}
		arr.push(external_link);
		}
		return arr;	
	},

	get_link:function(param){
		
		// return full link or url or name
		// if !subdomain return tech link

		var _this=this;
		var param = param?param:'full';		
		var arr_allowed_params = ['url','name','full'];
		if(arr_allowed_params.indexOf(param) == -1 ) return "";
		
		if(!this.get('subdomain')){	return this.get_link_tech(param);}

		var url = CFG.http + _this.get('subdomain')+ "."+ CFG.www_url;
		var name = _this.get('subdomain')+ "."+ CFG.www_url;
		var full = '<a href="'+url+'">'+name+'<a>';	

		switch (param){
			case 'full': return full; break; 			
			case 'url':	return url; break;
			case 'name': return name; break;
		}
	},

	get_link_tech:function(param){

		// return full tech link or tech url or tech name		
		
		var _this=this;
		var param = param?param:'full';
		var arr_allowed_params = ['url','name','full'];
		if(arr_allowed_params.indexOf(param) == -1 ) return "";		

		var tech_url = CFG.http + CFG.www_url + "/cafe/" + _this.get('uniq_name');
		var tech_name = CFG.www_url + "/cafe/" + _this.get('uniq_name');
		var tech_full = '<a href="'+tech_url+'">'+tech_name+'<a>';	

		switch (param){
			case 'url':	return tech_url; break;
			case 'name': return tech_name; break;
			case 'full': return tech_full; break;
		}
	}

};


