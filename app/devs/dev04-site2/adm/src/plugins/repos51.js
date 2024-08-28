export default
$.extend({
	repos51:function(options){

	return new function(){
			
		var repos = {
			init:function(){
				var _this=this;

				this.ALLROWS = options.ALLROWS;
				this.BUTTONS = options.BUTTONS || {};
				this.need2save = options.need2save||function(){};
				this.onTouchend = options.onTouchend||function(){};
				this.onRepose = options.onRepose||function(){};
				this.getRowIcon = options.getRowIcon;
				this.onReady = options.onReady;				
				this.$parent_old = options.$parent;
				this.CLASS_NAME = "repos-51";				

				this.BUTTON_WIDTH = 80;
				this.BUTTONS.left && $.each(this.BUTTONS.left,function(){
					this.btnClass = _this.CLASS_NAME+'__'+this.btnClass;			
				});
				this.BUTTONS.right && $.each(this.BUTTONS.right,function(){
					this.btnClass = _this.CLASS_NAME+'__'+this.btnClass;			
				});
				this.BUTTONS_HAVE_OPENED = false;
				this.reset();				
				this.build();
				this.behavior();
			},
			reset:function(){
				this.CURRENT_DRAG = 0;
				this.OVER_DRAG = 0;
				this.START_DRAG = false;
				this.START_Y = 0;
				this.CURR_Y = 0;
				this.BEHAVIORS_MODE = true;	
				this.TOUCH_MOVED = false;				
				this.ROW_HEIGHT = null;		
				this.PARENT_OFFSET_TOP = null;	
				this.CURRENT_DELTA = 0;
			},			
			get_row_height:function(){				
				this.ROW_HEIGHT = this.ROW_HEIGHT?this.ROW_HEIGHT:this.$rowSample.height();
				return this.ROW_HEIGHT;
			},
			get_index_by_id:function(id_menu){
				var index = -1;
				for(var i=0;i<this.ALLROWS.length;i++){
					if(parseInt(this.ALLROWS[i].id,10)==parseInt(id_menu,10)) {
						index = i; break;
					}
				};
				return index;
			},
			get_index_row:function(coordY){	
				this.PARENT_OFFSET_TOP = this.PARENT_OFFSET_TOP!==null? this.PARENT_OFFSET_TOP : this.$parent.offset().top;				
				var delta = (this.PARENT_OFFSET_TOP - coordY- this.$parent.scrollTop()) / this.get_row_height();
				var index = Math.floor(Math.abs(delta)); 
				index = delta>0?0:index;				
				index = index<this.ALLROWS.length?index : this.ALLROWS.length-1;				
				return index;
			},		
			build:function(){
				var _this=this;
								
				this.$parent_old.html("");				
				this.$parent = $('<div></div>').addClass(this.$parent_old.attr('class'));
				var $p = this.$parent_old.parent();
				$p.append(this.$parent);
				this.$parent_old.html("").remove();

				this.$rowSample = $("<div>",{class:this.CLASS_NAME+"__row-sample"});				
				this.$wrapper = $("<div>",{class:this.CLASS_NAME+"-wrapper"});				

				this.$parent.append(this.$rowSample);
				this.$wrapper.html('');
				this.update_height_content();
				$.each(_this.ALLROWS,function(index){	
					var $row = _this.build_new_row(index,this);
					_this.$wrapper.append($row);
				});
				this.ROWS_LINKS = this.$wrapper.find('.'+_this.CLASS_NAME+'__row').toArray();
				this.$parent.append(this.$wrapper);				
			},			
			build_new_row:function(index, menu){
				var _this=this;				
				var bld = {
					bnt_left:function(id_menu){
						if(!_this.BUTTONS.left) return false;
						var width = _this.BUTTONS.left.length*_this.BUTTON_WIDTH;	

						var $btnLeft = $("<div>",{class:_this.CLASS_NAME+"__row-buttons-left"})
						.css({width:width,left:-width});
						
						$.each(_this.BUTTONS.left,function(i){
							var btnAction = this.btnAction;
							var btnClass = this.btnClass||"";							

							var btnStructure = [
								"<div class='ico-adm-delete'></div>",
								"<div></div>"
							].join('');
		
							var $btn = $("<div>",{class:_this.CLASS_NAME+"__row_button " + btnClass})
							.html(btnStructure)
							.on("touchstart",function(e){
								if(!$(this).hasClass(_this.CLASS_NAME+'__btn-delete')){
									_this.rowCloseAll();
								};
								btnAction && btnAction(id_menu, _this);
								e.originalEvent.cancelable && e.preventDefault();
								return false;
							}).appendTo($btnLeft);
						});	

						return $btnLeft;
					},
					bnt_right:function(id_menu){						
						if(!_this.BUTTONS.right) return false;
						var width = _this.BUTTONS.right.length*_this.BUTTON_WIDTH;	
						
						var $btnRight = $("<div>",{class:_this.CLASS_NAME+"__row-buttons-right"})
						.css({width:width,right:-width});
						
						$.each(_this.BUTTONS.right,function(i){
							var btnAction = this.btnAction;
							var btnClass = this.btnClass||"";
														
							var btnStructure = [
								"<div class='ico-adm-edit'></div>",
								"<div></div>"
							].join('');

							var $btn = $("<div>",{class:_this.CLASS_NAME+"__row_button " + btnClass})
							.html(btnStructure)
							.on("touchstart",function(e){		
								if(!$(this).hasClass(_this.CLASS_NAME+'__btn-edit')){										
									_this.rowCloseAll();
								};
								btnAction && btnAction(id_menu, _this);
								e.originalEvent.cancelable && e.preventDefault();
								return false;
							}).appendTo($btnRight);
						});	
						
						return $btnRight;
					}					
				};

				var title = menu.title?menu.title:menu;
				var id_menu = menu.id?menu.id:0;
				var id_icon = parseInt(menu.id_icon,10);					
				var iconClassName = _this.getRowIcon ? _this.getRowIcon(id_icon).className:'';
				var top = _this.get_row_height()*index;
				var style = "height:"+_this.get_row_height()+"px;top:"+top+"px"; 
				var $row = $([
					"<div class='"+_this.CLASS_NAME+"__row row-has-icon' row-id='"+id_menu+"' style='"+style+"'>",
						"<div class='"+_this.CLASS_NAME+"__title'>",
						"<div class='"+_this.CLASS_NAME+"__title_icon "+iconClassName+"'></div>",
						"<div class='"+_this.CLASS_NAME+"__title_text'>"+title+"</div></div>",
					"</div>"].join(""));
				_this.BUTTONS.left && $row.append(bld.bnt_left(id_menu)); 
				_this.BUTTONS.right && $row.append(bld.bnt_right(id_menu));
				return $row;
			},
			update_height_content:function(opt){
				var height = this.ALLROWS.length*this.get_row_height();
				if(opt&&opt.slow){
					this.$wrapper.css({height:height,transition:'.3s'}); 
					setTimeout(function(){
						opt && opt.onReady && opt.onReady();
					},300);
				}else{
					this.$wrapper.css({height:height}); 	
					opt && opt.onReady && opt.onReady();
				}				
			},			
			update_menu:function(menu){
				var _this=this;
				var id_icon = parseInt(menu.id_icon,10);
				var index = this.get_index_by_id(menu.id);				
				var $row = $(this.ROWS_LINKS[index]).find('.'+this.CLASS_NAME+'__title');
				$row.find('.'+this.CLASS_NAME+'__title_text').html(menu.title);
				if(this.getRowIcon){
					var $rowIcon = $row.find('.'+this.CLASS_NAME+'__title_icon');
					var iconClassName = this.getRowIcon(id_icon).className;
					var arrClasses = this.getRowIcon();
					$.each(arrClasses,function(){ $rowIcon.removeClass(this.className); });
					$rowIcon.addClass(iconClassName);
				}
			},
			add_menu:function(menu){
				var _this=this;
				var id_icon = parseInt(menu.id_icon,10);				
				var index = this.ALLROWS.length-1;				
				var $row = this.build_new_row(index,menu)
				this.$wrapper.append($row);	
				this.ROWS_LINKS.push($row[0]);
				this.update_height_content()
			},
			ios_scroll_safely:function(){	
				//TODO performance
				var height = this.$parent.height();
				var $rows = this.$parent.find('.repos-51__row');			
				var contentHeight = $rows.size()*$rows.eq(0).height();
				var topScroll = this.$parent.scrollTop();
				var abs = Math.abs(contentHeight-height);				
				if((topScroll>-1) && (topScroll < abs+1) ){
					return true;					
				}else{					
					return false;
				}
			},
			behavior:function(){
				var _this=this;

				var fn = {
					touchStart:function(event) {
						if(!_this.BEHAVIORS_MODE) return false;
						if(event.targetTouches.length>1) return false;
						
						_this.TOUCH_MOVED = false;
						
						var et = event.targetTouches[0];
						var index = _this.get_index_row(et.pageY);
						
						_this.ROW_HAVE_TOCHED = index;
						if(index===null) return false;

						_this.TMR_LONG_PRESS && clearTimeout(_this.TMR_LONG_PRESS);
						_this.TMR_LONG_PRESS = setTimeout(function() {
							if(!_this.ios_scroll_safely()) return false;							
							_this.BUTTONS_HAVE_OPENED && _this.rowCloseAll();
							_this.START_SCROLLTOP = _this.$parent.scrollTop();					
							_this.START_DRAG = true;
							_this.CURRENT_DRAG = index;	
							_this.OVER_DRAG = index;					
		                	_this.START_Y = et.pageY;
		                	_this.CURR_Y = et.pageY;		                	
		                	$(_this.ROWS_LINKS[_this.CURRENT_DRAG])
		                	.addClass(_this.CLASS_NAME+'__row-now-dragging');							
		                	// event.originalEvent.cancelable && event.preventDefault();
						},400);
					},
					touchMove:function(event) {

						if(!_this.BEHAVIORS_MODE) return false;
						
						_this.TOUCH_MOVED = true;

						if(event.targetTouches.length>1 || !_this.START_DRAG) {
							_this.TMR_LONG_PRESS && clearTimeout(_this.TMR_LONG_PRESS);
							return false;	
						}
						var et = event.targetTouches[0];
						var index = _this.get_index_row(et.pageY);						
						_this.OVER_DRAG = index;
	     				_this.CURR_Y = et.pageY;

	     				_this.drag_row();
	     				_this.quick_repos();

	     				// event.originalEvent.cancelable && event.preventDefault();
					},
					touchEnd:function() {
						_this.TMR_LONG_PRESS && clearTimeout(_this.TMR_LONG_PRESS);	
						if(_this.ROW_HAVE_TOCHED===null) return false;
						if(_this.START_DRAG){
							_this.parking_current();
						}else{	
							 if(!_this.TOUCH_MOVED){
							 	_this.rowCloseAll();
							 	var menu = _this.ALLROWS[_this.ROW_HAVE_TOCHED];
							 	_this.onTouchend && _this.onTouchend(menu);
							}
						}
					}
				};

				this.$parent[0].addEventListener("touchstart", fn.touchStart,false);
				this.$parent[0].addEventListener("touchmove", fn.touchMove,false);
				this.$parent[0].addEventListener("touchend", fn.touchEnd,false);
				this.$parent[0].addEventListener("touchcancel", fn.touchEnd,false);

				this.$parent.on("contextmenu", false);

				this.$parent.swipe({
					preventDefault: false,
					enableMouse: false,
					distance: 30,						
					onSwipe:function(opt){	
						if(!_this.BEHAVIORS_MODE) return false;						
						var $row = $(_this.ROWS_LINKS[_this.ROW_HAVE_TOCHED]); 
						if(opt.direction==="left"){
							_this.rowCloseSilblings($row);
							_this.rowOpenRight($row);						
						}else if(opt.direction==="right"){
							_this.rowCloseSilblings($row);
							_this.rowOpenLeft($row);
						}
					}
				});
				this.onReady&&this.onReady();
			},
			drag_row:function() {
				var _this=this;

				setTimeout(function(){
					var err_ios_prevent = _this.$parent.scrollTop() - _this.START_SCROLLTOP;		
					_this.CURRENT_DELTA = _this.CURR_Y-_this.START_Y + err_ios_prevent;
					var max_top = _this.get_row_height()*(_this.CURRENT_DRAG+1)*-1 - 10 ;
					var max_bottom = _this.get_row_height()*(_this.ROWS_LINKS.length-_this.CURRENT_DRAG) + 10 ;					
					if(_this.CURRENT_DELTA > max_top && _this.CURRENT_DELTA < max_bottom){

		 				var $row = $(_this.ROWS_LINKS[_this.CURRENT_DRAG]);
		 				$row.css({transform:'translate3d(0,'+_this.CURRENT_DELTA+'px, 0)',transition:'0s'});
					}
				},0);				
			},
			quick_repos:function() {
				var _this = this;
				
				var fn = {
					move_down:function(index) {
						if(index!==_this.CURRENT_DRAG){
							$(_this.ROWS_LINKS[index]).css({transform:'translate3d(0, '+_this.get_row_height()+'px, 0 )',transition:'.3s'});
						}						
					},
					move_zero:function(index) {
						if(index!==_this.CURRENT_DRAG){
							$(_this.ROWS_LINKS[index]).css({transform:'translate3d(0,0,0)',transition:'.3s'});	
						}
					},
					move_up:function(index) {
						$(_this.ROWS_LINKS[index]).css({transform:'translate3d(0,-'+_this.get_row_height()+'px,0)',transition:'.3s'});
					}
				};
				requestAnimationFrame(function() {
					$(_this.ROWS_LINKS).each(function(index) {					
						if(_this.CURRENT_DRAG>_this.OVER_DRAG){
							if(index>_this.OVER_DRAG-1 && index<_this.CURRENT_DRAG){
								fn.move_down(index);
							}else{
								fn.move_zero(index);
							}
						}else if(_this.CURRENT_DRAG==_this.OVER_DRAG){
							fn.move_zero(index);
						}else if(_this.CURRENT_DRAG<_this.OVER_DRAG){
							if(index<_this.OVER_DRAG+1 && index>_this.CURRENT_DRAG){
								fn.move_up(index);
							}else{
								fn.move_zero(index);
							}
						}					
					});
				});
			},		
			stop_behaviors:function() {
				var _this=this;
				this.$wrapper.addClass('protected');
				this.BEHAVIORS_MODE = false;				
			},
			start_behaviors:function() {
				this.$wrapper.removeClass('protected');
				this.BEHAVIORS_MODE = true;
			},

			startRemove:function(id,opt){
				var _this=this;
				var  index = this.get_index_by_id(id);
				if(index<0) { console.log('unknown row index'); return; }
				this.stop_behaviors();
				var $a = $(_this.ROWS_LINKS[index]);
				$a.addClass('prepare-to-remove');				
				opt.onReady && opt.onReady();
			},
			cancelRemoving:function(id) {
				var _this=this;
				var  index = this.get_index_by_id(id);
				if(index<0) { console.log('unknown row index');return;}
				var $a = $(_this.ROWS_LINKS[index]);
				$a.removeClass('prepare-to-remove');
				this.rowCloseAll();
				this.start_behaviors();
			},
			endRemove:function(id_menu){
				var _this=this;
				var  index = this.get_index_by_id(id_menu);
				
				var fn = {
					closeLeftButton:function(){
						if(index===undefined) { console.log('unknown row index');return;}
						var $a = $(_this.ROWS_LINKS[index]);
						$a.css({transform:'translate3d(0,0,0)',transition:'.3s'});
						setTimeout(function(){fn.removeFromArr($a);},300);
					},
					removeFromArr:function($a){
						$a.remove();
						setTimeout(function(){
							_this.ROWS_LINKS.splice(index,1);
							_this.ALLROWS.splice(index,1);
							console.log('after removed',_this.ALLROWS)
							for(var i=index;i<_this.ROWS_LINKS.length;i++){
								var $a = $(_this.ROWS_LINKS[i]);
								$a.css({transform:'translate3d(0,-'+_this.get_row_height()+'px,0)', transition:'.6s cubic-bezier(0.075, 0.82, 0.165, 1)'});
							};
							setTimeout(function(){_this.redraw_list();},600);
						},0);
					}
				};

				fn.closeLeftButton();

			},
			parking_current:function() {
				var _this=this;	
				this.stop_behaviors();	

				console.log("START PARKING")

				var $currentRow = $(this.ROWS_LINKS[this.CURRENT_DRAG]);							
				var top = - (_this.CURRENT_DRAG-_this.OVER_DRAG)*_this.get_row_height();				
				
				var s = 300
				var d =(this.CURRENT_DELTA-top);
				var v = s/d;

				var animate = function (draw, duration) {
				  var start = performance.now();
				  requestAnimationFrame(function animate(time) {
				    var timePassed = time - start;
				    if (timePassed > duration) timePassed = duration;
				    draw(timePassed);
				    if (timePassed < duration) {
				      requestAnimationFrame(animate);
				    }
				  });
				};

				animate(function(timePassed){
					var ypos =  _this.CURRENT_DELTA - timePassed/v;
					if(ypos>top) ypos=top;						
					$currentRow.css({transform:'translate3d(0,'+ypos+'px,0)', transition:'0s'});
				},s);
				 

				_this.START_DRAG = false;
				setTimeout(function() {
					$currentRow.removeClass(_this.CLASS_NAME+'__row-now-dragging');
					if(_this.CURRENT_DRAG==_this.OVER_DRAG){												
						_this.start_behaviors();
					}else{

						console.log("reorder")
						_this.reorder_all();
					}
				},s);
			},
			reorder_all:function() {		
				var _this=this;						
				var el = this.ALLROWS.splice(this.CURRENT_DRAG,1);
				this.ALLROWS.splice(this.OVER_DRAG,0,el[0]);
				var el = this.ROWS_LINKS.splice(this.CURRENT_DRAG,1);
				this.ROWS_LINKS.splice(this.OVER_DRAG,0,el[0]);				
				this.need2save && this.need2save(true);
				this.redraw_list();				
			},
			redraw_list:function(){
				var _this=this;
				
				var $new_wrapper = $("<div>",{class:this.CLASS_NAME+"-wrapper"});
				$.each(_this.ALLROWS,function(index){	
					var $row = _this.build_new_row(index,this);					
					$new_wrapper.append($row);
				});				
				this.$parent.append($new_wrapper);				
				
				setTimeout(function(){
					_this.$wrapper.remove();
					_this.$wrapper = $new_wrapper;
					_this.ROWS_LINKS = _this.$wrapper.find('.'+_this.CLASS_NAME+'__row').toArray();
					_this.update_height_content({
						onReady:function(){
							console.log("!!!")
							_this.start_behaviors();
						}
					});
				},300)
						
			},			
			rowOpenRight:function($el){
				var _this=this;
				if($el.hasClass('leftOpened')){
					_this.rowCloseLeft($el);
				}else if(!$el.hasClass('rightOpened') && this.BUTTONS.right){
					this.BUTTONS_HAVE_OPENED = true;
					$el.addClass('rightOpened');
					$el.css({transform:'translate3d(-'+this.BUTTON_WIDTH*this.BUTTONS.right.length+'px,0,0)',transition:'.3s'});
				}
			},
			rowOpenLeft:function($el){
				var _this=this;
				if($el.hasClass('rightOpened')){
					_this.rowCloseRight($el);
				}else if(!$el.hasClass('leftOpened') && this.BUTTONS.left){
					this.BUTTONS_HAVE_OPENED = true;							
					$el.addClass('leftOpened');
					$el.css({transform:'translate3d('+this.BUTTON_WIDTH*this.BUTTONS.left.length+'px,0,0)',transition:'.3s'});
				}
			},
			rowCloseAll:function(){
				var _this=this;
				this.BUTTONS_HAVE_OPENED = false;
				$(this.ROWS_LINKS).each(function(){
					_this.rowCloseLeft($(this));
					_this.rowCloseRight($(this));
				});
			},						
			rowCloseSilblings:function($el){	
				var _this=this;					
				$el.siblings().each(function(){
					$(this).hasClass('rightOpened') && _this.rowCloseRight($(this));
					$(this).hasClass('leftOpened') && _this.rowCloseLeft($(this));
				});
			},
			rowCloseRight:function($el){
				var _this=this;
				if($el.hasClass('rightOpened')){
					this.BUTTONS_HAVE_OPENED = false;
					$el.css({transform:'translate3d(0,0,0)',transition:'.3s'});
					$el.removeClass('rightOpened');
				}
			},
			rowCloseLeft:function($el){
				var _this=this;
				if($el.hasClass('leftOpened')){	
					this.BUTTONS_HAVE_OPENED = false;
					$el.css({transform:'translate3d(0,0,0)',transition:'.3s'});
					$el.removeClass('leftOpened');
				}
			}					

		};

		// public
		this.stopBehaviors = function(){ repos.stop_behaviors();};
		this.startBehaviors = function(){ repos.start_behaviors();};
		this.updateMenu = function(menu){repos.update_menu(menu);};
		this.addMenu = function(menu){repos.add_menu(menu);};
		this.closeAll = function(){repos.rowCloseAll();};

		repos.init();

		}
	}

});