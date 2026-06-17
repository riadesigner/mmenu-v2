import {GLB} from './glb.js';


import {WebReg} from './web_reg.js';
import {WebPanel} from './web_panel.js';
import {RegisterPush} from './register_push.js';

GLB.WebReg = WebReg;
GLB.RegisterPush = RegisterPush;
GLB.WebPanel = WebPanel;

export default function(siteConfig){

	$(function(){
			
		if($('body').hasClass('page-webuser-panel')){
			GLB.WebPanel.init(siteConfig);
		}else if($('body').hasClass('page-webuser-register')){
			GLB.WebReg.init(siteConfig);
		}
		

	});
	
};