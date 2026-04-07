import $ from 'jquery';

export var MOBILE_BUTTONS = {
	bhv:function(arrMobileButtons) {
		var classDisabled = 'mm2-disabled'; 
		if(arrMobileButtons && arrMobileButtons.length){			
			var arrBtns = arrMobileButtons;
			var btnDisabled = function($btn){ return $btn.hasClass(classDisabled);};
			arrBtns.forEach(function($btn){		
				$btn.on('mouseover',function(e){ !btnDisabled($(this)) && $(this).addClass('hover');});
				$btn.on('mouseout',function(e){ !btnDisabled($(this)) && $(this).removeClass(' hover');});
				$btn.on('mousedown',function(e){ !btnDisabled($(this)) && $(this).addClass('active');});
				$btn.on('mouseup',function(e){ !btnDisabled($(this)) && $(this).removeClass('active ');});
				$btn.on('touchstart',function(e){ !btnDisabled($(this)) && $(this).addClass('mobile-active');  });
				$btn.on('touchend',function(e){ !btnDisabled($(this)) && $(this).removeClass('mobile-active'); });											
			});
		}
		
	}
};
