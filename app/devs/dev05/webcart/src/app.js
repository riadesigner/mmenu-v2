import {GLB} from './glb.js';

import {WebReg} from './web_reg.js';


GLB.WebReg = WebReg;

export default function(siteConfig){

	$(function(){
			
		GLB.WebReg.init(siteConfig);

	});
	
};