import {GLB} from './glb.js';

import {WebReg} from './web_reg.js';


GLB.WebReg = WebReg;

export default function(){

	$(function(){
			
		GLB.WebReg.init();

	});
	
};