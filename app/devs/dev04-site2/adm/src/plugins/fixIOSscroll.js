
export default
$.fixIOSscroll = function($el){
	
		var lastY = 0; // Needed in order to determine direction of scroll.
		
		var fn = {
			touchStart:function(event){				
				lastY = event.targetTouches[0].clientY;     			
			},
			touchMove:function(event){
				var top = event.targetTouches[0].clientY;

			    // Determine scroll position and direction.
			    var scrollTop = $(event.currentTarget).scrollTop();
			    var direction = (lastY - top) < 0 ? "up" : "down";			    

			    // FIX IT!
			    if (scrollTop == 0 && direction == "up") {
			      // Prevent scrolling up when already at top as this introduces a freeze.
			      event.cancelable && event.preventDefault();

			    } else if (scrollTop >= (event.currentTarget.scrollHeight - $(event.currentTarget).outerHeight()) && direction == "down") {
			      // Prevent scrolling down when already at bottom as this also introduces a freeze.			      			      
			      event.cancelable && event.preventDefault();
			    }

			    lastY = top;
			}
		};
		
        $el[0].addEventListener("touchstart", fn.touchStart,false);
        $el[0].addEventListener("touchmove", fn.touchMove,false);

        return this;

};