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

	get_all_links:function(){
		const links = {
			tech:null,
			with_subdomain:null,
			external:null,
		};
		const tech_link =  {			
			title:'технический адрес:',
			name:CFG.www_url + "/cafe/" + this.get('uniq_name'), 
			url:CFG.http + CFG.www_url + "/cafe/" + this.get('uniq_name'),
		};
		links.tech = tech_link;
		const subdomain = this.get('subdomain');
		const subdomain_link_url = CFG.http + this.get('subdomain')+ "."+ CFG.www_url;
		const subdomain_link_name = this.get('subdomain')+ "."+ CFG.www_url;		
		const subdomain_link = subdomain ? {
			title:'Подтвержденный адрес:',
			name:subdomain_link_name, 
			url:subdomain_link_url
		}: null;
		if(subdomain_link){
			links.with_subdomain = subdomain_link;
		}
		
		const external_url = this.get('external_url');
		if(external_url){
			 const external_link = {				
				title:'Внешний адрес:',
				name:external_url,
				url:`https://${external_url}`,
			}
			links.external=external_link;
		}
		return links;	
	},

	get_link:function(param){
		const links = this.get_all_links();
		if(links.external){
			return links.external;
		}else if(links.with_subdomain){
			return links.with_subdomain;
		}else{
			return links.tech;
		}
	},

};


