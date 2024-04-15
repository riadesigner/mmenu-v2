import {GLB} from './glb.js';

import {TABINDEX} from './tabindex.js';
import {Mobilemenu} from './mobile-menu.js';
import {LngPrefer} from './lng-prefer.js';
import {SiteLng} from './site-lang.js';
import {MaintenanceMode} from './maintenance-mode.js';
import {SignIn} from './sign-in.js';
import {CreateMenu} from './create-menu.js';

import {SiteVideo} from './site-video.js';
import {Bhv} from './bhv.js';
import {SiteSlider} from './site-slider.js';

GLB.TABINDEX = TABINDEX;
GLB.Mobilemenu = Mobilemenu;
GLB.LngPrefer = LngPrefer;
GLB.SiteLng = SiteLng;
GLB.MaintenanceMode = MaintenanceMode;
GLB.SignIn = SignIn;
GLB.CreateMenu = CreateMenu;

GLB.SiteVideo = SiteVideo;
GLB.Bhv = Bhv;
GLB.SiteSlider = SiteSlider;



export default function(){

	$(function(){

		var sitePath2Images = SITE_CFG.base_url+ 'site/i/';
		SiteSlider.init(sitePath2Images);

		TABINDEX.init();
		Bhv.init();
		Mobilemenu.init();
		LngPrefer.init();
		SiteLng.init(SITE_CFG.lang);
		SignIn.init();
		MaintenanceMode.init();
		CreateMenu.init();
		
		GLB.SiteVideo.init("480p");



	});
	
};