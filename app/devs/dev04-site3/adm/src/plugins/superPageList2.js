export default
$.extend({
	superPageList2:function(opt){

		return new function(){
			
		var _this = this;

		var $parent = opt.$parent;
		var ALLITEMS = opt.ALLITEMS || [];
		var on_resize =  opt.on_resize;
		var Pages = opt.Pages || "";		
		var MOVE_TO_END = opt.move2end;	
		var WINDOW_WIDTH = $(window).width();	

		var CURRENT_PAGE = 0;

		var REPOS_DIRECTION_FORWARD = false;

		if(Pages) {
			// --------------------------------------------------------
			// ссылки на все собранные и заполненные контентом страницы
			// --------------------------------------------------------
			ALLITEMS = Pages.get();
		}

		console.log("ALLITEMS", ALLITEMS);
		console.log("Pages", Pages);
		
		var CLASS_NAME = "super-pagelist";
		var bhvLayerClass = CLASS_NAME+"-bhv-layer";
		var pagesLayerClass = CLASS_NAME+"-pages-layer";		
		
		var $wrapperLayer = $("<div>",{class:CLASS_NAME});
		var $protectLeftLayer = $("<div>",{class:CLASS_NAME+"-protect-left"});
		var $protectRightLayer = $("<div>",{class:CLASS_NAME+"-protect-right"});
		var $bhvLayer = $("<div>",{class:bhvLayerClass});
		var $pagesLayer;		


		$parent.html("").css({position:'relative'})
		.append($bhvLayer.append($wrapperLayer).append($protectLeftLayer).append($protectRightLayer));

		var PARENT_WIDTH;
		var PAGE_WIDTH;
		var SPACE_WIDTH;

		var EDITMODE_SPACE_BETWEEN = 40;

		var SPACE_NXT_PRV = 0;		
		var START_POS = 0;
		var TOUCH_MOVED = 0;
		var TOUCH_START_X = 0;
		var TOUCH_START_Y = 0;
		var ARR_PAGES = [];		
		var START_DRAG = false;
		var CURRENT_DELTA_X = 0;

		var MODE_UP = false; 
		var MODE_DOWN = false;

		var LOCKED_BEHAVIORS = false;

		Pages&&Pages.setCurrent(CURRENT_PAGE);

		var fn = {			
			build:function(){	

				_this.reset();
				$wrapperLayer.html("").css({transform:'translateX(15px) translateZ(0)',transition:0,opacity:0,});

				fn.update_page_param({onReady:function() {

					START_POS = Math.floor((PARENT_WIDTH-PAGE_WIDTH)/2);
					SPACE_NXT_PRV = START_POS;
					
					$pagesLayer = $("<div>",{class:pagesLayerClass});

					fn.insert_pages();

					$pagesLayer.appendTo($wrapperLayer);
					$pagesLayer.css({left:START_POS+'px'});
					$protectLeftLayer.css({width:START_POS+'px'});
					$protectRightLayer.css({width:START_POS+'px'});

					if(MOVE_TO_END) fn.to_end();
					setTimeout(function(){
						$wrapperLayer.css({transform:'translateX(0) translateZ(0)',transition:'.6s',opacity:1});
					},150)
					
					fn.behaviors();
					
				}});
			
			},
			prevent_bhvr:function(){				
				return LOCKED_BEHAVIORS;
			},
			// -------------------------------------
			// добавляем в список сразу все страницы
			// -------------------------------------
			insert_pages:function(){				
				for(var i=0;i<fn.get_total();i++){
					var current_class = i==CURRENT_PAGE ? " current ":"";
					var zIndex = i==CURRENT_PAGE ? 10000:i*10;
					var newX = fn.get_x_for_pos(i);
					var $page = $("<div>",{class:CLASS_NAME+"-page"+current_class,'data-pos':i})
					.css({width:PAGE_WIDTH,transform:"translateX("+newX+"px) translateZ(0)",zIndex:zIndex})
					.append(ALLITEMS[i]);
					$pagesLayer.append($page);						
				};
				ARR_PAGES = $pagesLayer.find("."+CLASS_NAME+"-page").toArray();								
			},			
			// ---------------------------------
			// добавляем в список новую страницу
			// ---------------------------------
			add_new_page:function(doAfter) {				
				var i = ALLITEMS.length-1;
				var zIndex = i*10;
				var newX = fn.get_x_for_pos(i-CURRENT_PAGE);
				var $page = $("<div>",{class:CLASS_NAME+"-page current",'data-pos':i})
				.css({width:PAGE_WIDTH,transform:"translateX("+newX+"px) translateZ(0)",zIndex:zIndex})
				.append(ALLITEMS[i]);
				$pagesLayer.append($page);
				
				ARR_PAGES.push($page);

				setTimeout(function(){
					fn.to_end();
					setTimeout(function(){
						doAfter && doAfter();
					},300);					
				},300);
			},			
			update_page_param:function(opt){
				PARENT_WIDTH = parseInt($parent.width(),10); 
				PAGE_WIDTH = Math.floor(PARENT_WIDTH/1.7);
				SPACE_WIDTH = 10;
				opt.onReady&&opt.onReady();
			},
			get_total:function() {
				return ALLITEMS.length;
			},
			behaviors:function(){
				var _this = this;				

					$bhvLayer.swipe({
						preventDefault: false,
						enableMouse: false,
						distance: 30,						
						onSwipe:function(opt){							
							if(fn.prevent_bhvr()) return false;
							if(opt.direction==="left"){
								if(!MODE_UP){
									fn.to_next();	
								}else{
									REPOS_DIRECTION_FORWARD ? fn.change_pos_prev() : fn.change_pos_next();
								}		
							}else if(opt.direction==="right"){	

								if(!MODE_UP){
									fn.to_prev();
								}else{
									REPOS_DIRECTION_FORWARD ? fn.change_pos_next() : fn.change_pos_prev();
								}								
							}else if(opt.direction==="up" && fn.is_touchstart_on_page()){


								if(!MODE_DOWN){
									MODE_UP = true;
									fn.mode_reposition_pages(MODE_UP); 
								}else{
									MODE_DOWN = false;
									fn.mode_pre_remove(MODE_DOWN);
								};
								
								Pages && Pages.onSwipeUp();

							}else if(opt.direction==="down" && fn.is_touchstart_on_page()){


								if(!MODE_UP){
									MODE_DOWN = true;
									fn.mode_pre_remove(MODE_DOWN);
								}else{
									MODE_UP = false;
									fn.mode_reposition_pages(MODE_UP);									
									Pages && Pages.updatePosition(CURRENT_PAGE); 
								};									
								Pages && Pages.onSwipeDown();								
							}							
						}
					});					

					$bhvLayer.on('touchstart',function(e){
						TOUCH_MOVED = 0;
						var event = e.originalEvent;										
						if (event.targetTouches.length > 1) { return; }		
						var et = event.targetTouches[0];
						TOUCH_START_X = et.pageX;
						TOUCH_START_Y = et.pageY;						
					});	
					
					$bhvLayer.on('touchend',function(e){						
						if(fn.prevent_bhvr()) return false;
						CURRENT_DELTA_X = 0;
						if(!TOUCH_MOVED){						
							if(fn.is_touchstart_on_page()){								
								if(!MODE_UP && !MODE_DOWN){									
									Pages && Pages.onTouchend(CURRENT_PAGE);
								}
							}else{
								fn.on_touch_around();	
							}
						};

						e.originalEvent.cancelable && e.preventDefault();

					});	
									
					$bhvLayer.on('touchmove',function(e){
						if(fn.prevent_bhvr()) return false;
						TOUCH_MOVED = 1;
						var event = e.originalEvent;
						if (event.targetTouches.length > 1) { return; }
						var et = event.targetTouches[0];
						// stopPropagation important 
						// because of fixIOSscroll plugin
						e.stopPropagation();
					});

					$(window).resize(function(){						
						_this.TMR_RESIZE!==null && clearTimeout(_this.TMR_RESIZE);
						_this.TMR_RESIZE = setTimeout(function(){
							if(WINDOW_WIDTH!==$(window).width()){
								on_resize&& on_resize();
							}
							_this.TMR_RESIZE = null;
						},300);
					});

			},
			mode_pre_remove:function(mode) {					
				requestAnimationFrame(function() {
					if(mode){				
						$parent.addClass("mode-pre-remove");	
					}else{
						$parent.removeClass("mode-pre-remove");	
					}
				});
			},
			mode_reposition_pages:function(mode){
				var _this=this;
				if(mode){
					$parent.addClass("mode-reposition");
				}else{					
					$parent.removeClass("mode-reposition");					
				}
			},			
				
			is_touchstart_on_page:function(){
				var pageX = Math.floor((PARENT_WIDTH-PAGE_WIDTH)/2);				
				return TOUCH_START_X>pageX && TOUCH_START_X<pageX+PAGE_WIDTH;
			},
			on_touch_around:function(){
				var touch_area = Math.floor((PARENT_WIDTH-PAGE_WIDTH)/2);
				if(TOUCH_START_X<touch_area+SPACE_WIDTH){
					if(MODE_UP){
						fn.change_pos_prev();
					}else{
						fn.to_prev();	
					}					
				}else if (TOUCH_START_X> PARENT_WIDTH - touch_area){
					if(MODE_UP){
						fn.change_pos_next();
					}else{
						fn.to_next();
					}					
				}
			},
			to_end:function(){				
				if(ALLITEMS.length>1){					
					CURRENT_PAGE = ALLITEMS.length-1;
					fn.go_to();
				}
			},
			to_prev:function(){
				if(CURRENT_PAGE>0){
					CURRENT_PAGE--;					
					fn.go_to();
				}else{
					fn.cant_prev();
				}
			},
			to_next:function(){
				if(CURRENT_PAGE<fn.get_total()-1){
					CURRENT_PAGE++;					
					fn.go_to();					
				}else{
					fn.cant_next();
				}
			},
			cant_play:function(xpos,speed){
				requestAnimationFrame(function() {
					$wrapperLayer.css({transform:'translateX('+xpos+') translateZ(0)',transition:speed});
				});				
			},
			cant_prev:function(){
				fn.cant_play('15px','0.1s');
				setTimeout(function() {	fn.cant_play('0px','1s'); },200);
			},			
			cant_next:function(){
				fn.cant_play('-15px','0.1s');
				setTimeout(function() {	fn.cant_play('0px','1s'); },200);
			},			
			go_to:function(){				
				fn.mode_pre_remove(false);
				Pages && Pages.setCurrent(CURRENT_PAGE);
				
				$.each(ARR_PAGES,function(i){	
					var newX = fn.get_x_for_pos(i-CURRENT_PAGE);
					$(this).removeClass('current').css({transform:"translateX("+newX+"px) translateZ(0)",zIndex:i*10});
				});
				$(ARR_PAGES[CURRENT_PAGE]).addClass('current').css({zIndex:1000});
				
			},
			get_x_for_pos:function(pos){
				return pos*PAGE_WIDTH + pos*SPACE_WIDTH;		
			},
			change_pos_next:function(){

				if(CURRENT_PAGE < fn.get_total()-1) {					
					requestAnimationFrame(function() {
						$.each(ARR_PAGES,function(i){						
							if(i != CURRENT_PAGE){							
								$(this).css({transform:"translateX("+fn.get_x_for_pos(i-(CURRENT_PAGE+1))+"px) translateZ(0)"});							
							};
							if(i == CURRENT_PAGE+1){
								$(this).css({transform:"translateX("+fn.get_x_for_pos(i-(CURRENT_PAGE+2))+"px) translateZ(0)"});
							};
						});

						var $el = ARR_PAGES.splice(CURRENT_PAGE,1);
						ARR_PAGES.splice(CURRENT_PAGE+1,0,$el[0]);

						CURRENT_PAGE +=1;							
						Pages&& Pages.updatePosition(CURRENT_PAGE);

					});
				}else{					
					fn.cant_next();
				}

			},
			change_pos_prev:function(){

				if(CURRENT_PAGE > 0){
					requestAnimationFrame(function() {
						$.each(ARR_PAGES,function(i){						
							if(i != CURRENT_PAGE){
								$(this).css({transform:"translateX("+fn.get_x_for_pos(i-(CURRENT_PAGE-1))+"px) translateZ(0)"});							
							};
							if(i == CURRENT_PAGE-1){
								$(this).css({transform:"translateX("+fn.get_x_for_pos(i-(CURRENT_PAGE-2))+"px) translateZ(0)"});
							};
						});

						var $el = ARR_PAGES.splice(CURRENT_PAGE,1);						
						ARR_PAGES.splice(CURRENT_PAGE-1,0,$el[0]);	

						CURRENT_PAGE -=1;
						Pages&& Pages.updatePosition(CURRENT_PAGE);						

					});
				}else{
					fn.cant_prev();
				}				

			},
			update_pos_after_deleting:function(index) {
					
				var index = parseInt(index,10);

				var fn3 = {
					endUpdate:function() {
						ARR_PAGES.splice(index,1);						
						ALLITEMS.length && ALLITEMS.splice(index,1);					
					}
				};

				if(ARR_PAGES.length === 1){
					// if last					
					fn3.endUpdate();

				}else{
					
					if(index < ARR_PAGES.length-1){
						// all except last						
						for(var i=index;i<ARR_PAGES.length;i++){
							var zIndex = i==index+1? 1000: (i-1)*10;
							var distance = (i-CURRENT_PAGE-1)*PAGE_WIDTH + (i-CURRENT_PAGE-1)*SPACE_WIDTH;	
							$(ARR_PAGES[i]).css({transform:"translateX("+distance+"px) translateZ(0)",zIndex:zIndex});
							i==index+1 && $(ARR_PAGES[i]).addClass("current");
						};

						fn3.endUpdate();

					}else if(index == ARR_PAGES.length-1){
						// if last from right						
						for(var i=index-1;i>-1;i--){
							var zIndex = i==index-1? 1000: i*10;
							var distance = (i-CURRENT_PAGE+1)*PAGE_WIDTH + (i-CURRENT_PAGE+1)*SPACE_WIDTH;
							$(ARR_PAGES[i]).css({transform:"translateX("+distance+"px) translateZ(0)",zIndex:zIndex});
							i==index-1 && $(ARR_PAGES[i]).addClass("current");
						};
						fn3.endUpdate();

						CURRENT_PAGE-=1;
						Pages&&Pages.setCurrent(CURRENT_PAGE);

					};

				};

			}			
		};		

		// public
		$.extend(this,{
			reset:function(opt){
				var pause = MODE_UP||MODE_DOWN?300:0;				
				MODE_UP = false;
				MODE_DOWN = false;				
				fn.mode_pre_remove(false);
				fn.mode_reposition_pages(false);
				Pages&&Pages.stateDefault();
				setTimeout(function(){
					opt && opt.onReady && opt.onReady();					
				},pause);
			},
			change_pos_next:function(){
				fn.change_pos_next();
			},
			change_pos_prev:function(){
				fn.change_pos_prev();
			},			
			removeCurrentPage:function(opttions) {
				var _this=this;
				this.lock_behaviors(true);
				var index = CURRENT_PAGE;							
				$(ARR_PAGES[index]).css({transform:"scale(.1)",opacity:0,transition:".6s .3s"});
				setTimeout(function() {
					fn.update_pos_after_deleting(index);
					setTimeout(function() {						
						_this.lock_behaviors(false);
						opttions.onReady && opttions.onReady();
					},300);
				},600);
			},
			lock_behaviors:function(mode){
				LOCKED_BEHAVIORS = mode?true:false;				
			},
			updateAfterAdd:function(doAfter) {			
				fn.add_new_page(doAfter);
			},
			hide:function(ms,deleteTail){
				if(deleteTail){
					// for speed animation reason
					var $pages = $pagesLayer.find("."+CLASS_NAME+"-page");
					var eq = [ CURRENT_PAGE-1, CURRENT_PAGE, CURRENT_PAGE+1 ];
					$pages = $pages.not(':eq('+eq[0]+'), :eq('+eq[1]+'), :eq('+eq[2]+')');					
					$pages.size() && $pages.remove();					
				};
				var ms = ms ? ms/1000+'s': '.6s';
				if($wrapperLayer){
					$wrapperLayer.css({transform:'translateX(15px) translateZ(0)',transition:ms,opacity:0});
				}
			}

		});

		setTimeout(function(){
			fn.build();
		},100);


		}
	}
});