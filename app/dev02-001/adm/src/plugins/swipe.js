
export default 
$.fn.swipe = function (options) {

        var opt = $.extend({
            preventDefault: true,
            enableMouse: true,
            distance: 100,
            fastSwipe:true,
            onTouch: function (detail){},
            onMove: function (detail){},
            onSwipe: function (detail){
					
            		console.log("onSwipe!!")
            },
            onEnd: function () {}		
		}, options||{});
        
		var startTime;
        var endTime;
        var swiped;

        return this.each(function () {
		
            var $this = $(this);
            var curX, curY;
            var startX, startY;
            var mdown = false;

			var fn = {
			   touchStart:function(event){
               		if (event.targetTouches.length > 1) { return; }	
               		swiped = false;
					var et = event.targetTouches[0];
					startX = et.pageX;
                	startY = et.pageY;
                	curX = et.pageX;
                	curY = et.pageY
                	startTime = new Date();
                	opt.onTouch({
						clientX:et.clientX, 
						clientY:et.clientY,
						pageX:et.pageX,
						pageY:et.pageY,
						screenX:et.screenX,
						screenY:et.screenY							
       		        });
			   },
			   mouseDown:function(e){
			   		swiped = false;
					mdown = true;
					startX = e.pageX;
					startY = e.pageY;
					curX = e.pageX;
					curY = e.pageY;
					startTime = new Date();
					opt.onTouch({ 
						clientX:e.clientX,
						clientY:e.clientY,
						pageX:e.pageX,
						pageY:e.pageY,
						screenX:e.screenX,
						screenY:e.screenY
					});						
					opt.preventDefault && e.preventDefault();					
			   },
			   mouseMove:function(e){
					if (mdown) {
						opt.onMove({
							deltaX:e.pageX-curX,
							deltaY:e.pageY-curY,
							clientX:e.clientX,
							clientY:e.clientY,
							pageX:e.pageX,
							pageY:e.pageY,
							screenX:e.screenX,
							screenY:e.screenY
						});					
						curX = e.pageX;
						curY = e.pageY;
					};
					opt.fastSwipe && !swiped && fn.testSwipe();
					opt.preventDefault && e.preventDefault();
			   },
			   moveEnd:function(e){
					if (mdown) {
						mdown = false;
						!swiped && fn.testSwipe();
					};
					opt.preventDefault && e.preventDefault();
					opt.onEnd();                                 
			   },
			   touchEnd:function(event){
					mdown = false;
					!swiped && fn.testSwipe();
					opt.onEnd();			   
			   },			   
			   touchMove:function(e){
					if (e.targetTouches.length > 1){return;}
					var et = e.targetTouches[0];
					opt.onMove({
						deltaX:et.pageX - curX,
						deltaY:et.pageY - curY,
						clientX:et.clientX,
						clientY:et.clientY,
						pageX:et.pageX,
						pageY:et.pageY,
						screenX:et.screenX,
						screenY:et.screenY,
						evt:e					
					});
					curX = et.pageX;
					curY = et.pageY;
					opt.preventDefault && e.preventDefault();
					opt.fastSwipe && !swiped && fn.testSwipe();
			   },
			   testSwipe:function(){
					var x = curX - startX;
					var y = curY - startY;
					endTime = Math.abs(new Date() - startTime) / 1000;					
					if (Math.abs(x) >= Math.abs(y)) {
						if (Math.abs(x) >= opt.distance) {	
							swiped = true;							
							var direction = x>=0?'right':'left';
							opt.onSwipe({direction:direction, distance: Math.abs(x), speed: (Math.abs(x) / endTime), time: endTime});
						}
					}else {
						if (Math.abs(y) >= opt.distance) {
							swiped = true;
							var direction = y>=0?'down':'up';
							opt.onSwipe({direction:direction,distance: Math.abs(y), speed: (Math.abs(y) / endTime), time: endTime});
						}
					};
			   },
			   touchCancel:function(event){ }			   
			};
			
            if (opt.enableMouse) {
                $this.mousedown(function (e) {
				    var e = e||window.event;
                    var kc = e.keyCode||e.which;                     
					if (kc == 1) { fn.mouseDown(e);}					
                });
                $this.mouseup(fn.moveEnd);
                $("body").mouseup(fn.moveEnd);
                $this.mousemove(fn.mouseMove);
            };

            this.addEventListener("touchstart", fn.touchStart);
            this.addEventListener("touchmove", fn.touchMove);
            this.addEventListener("touchend", fn.touchEnd);
            this.addEventListener("touchcancel", fn.touchCancel);		
			
        });
    };




