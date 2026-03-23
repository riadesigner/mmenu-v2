import {GLB} from './glb.js';


import {WebReg} from './web_reg.js';
import {RegisterPush} from './register_push.js';



GLB.WebReg = WebReg;
GLB.RegisterPush = RegisterPush;


export default function(siteConfig){

	$(function(){
			
		GLB.WebReg.init(siteConfig);

	});
	
};