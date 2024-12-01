export default $.extend({
	menuItems:function(opt){
		return new function(){
			var _this = this;
			var ARR = opt.arr || [];		
			var TPL = opt.tpl || "";
			
			var need2Save = opt.need2Save;
			var need2SavePos = opt.need2SavePos;
			var saveItem = opt.saveItem;
			var saveItemsPos = opt.saveItemsPos;
			var itemEdit = opt.itemEdit;
			var itemImageEdit = opt.itemImageEdit;
			var itemDelete =  opt.itemDelete;			
			var currencySign =  opt.currencySign;			
			var itemReplace =  opt.itemReplace;
			var managedByIiko = opt.itemReplace;

			if(!TPL) return false;			
			
			var ARR_NEED_TO_LOAD_IMAGE = [];

			var CURRENT_PAGE = 0;	
			var TOUCH_MOVED = 0;
			var PAGE_UP_MODE = false;
			var PAGE_DOWN_MODE = false;
			var NEED_FLAGS_TO_SAVE = false;
			var NEED_POSITIONS_TO_SAVE = false;			

			var NOW_IMG_LOADING = false;

			var $PAGES = [];

			var fn = {
				build:function(){
					$.each(ARR,function(i){
						var $p =  TPL.clone();
						$p = fn.fill_with_content($p,ARR[i]);
						$PAGES.push($p);
					});		
					fn.behaviors();					
					fn.start_load_images();
				},
				fill_with_content:function($p,item){

					var created_by_iiko = item.created_by=='iiko';
					
					if(created_by_iiko){
						var arr_price = [];
						var iiko_sizes = item.iiko_sizes ? JSON.parse(item.iiko_sizes) : [];
						for(var s in iiko_sizes){
							arr_price.push(iiko_sizes[s].price);
						};
						var price = arr_price.join('/');
					}else{
						const arr_price = [];
						const sizes = item.sizes ? JSON.parse(item.sizes) : [];
						if(sizes.length){
							for(let s in sizes){
								arr_price.push(sizes[s].price);
							}			
						}else{
							arr_price.push(0);
						};						
						var price = arr_price.join('/');
					};

					var item_title = item.title || "";
					var item_description = item.description || "";
					var item_volume = item.volume || "";
					var strPrice = price + '&nbsp;' + currencySign;

					$p.find(".item-title > div").html(item_title);
					$p.find(".item-description").html(item_description+"<div class='item-volume'>"+item_volume+"</div>");
					$p.find(".item-price > div").html(strPrice); 

					parseInt(item.mode_spicy,10) && $p.addClass('flag-spicy-enable');
					parseInt(item.mode_hit,10) && $p.addClass('flag-hit-enable');
					parseInt(item.mode_vege,10) && $p.addClass('flag-vege-enable');
					item.image_url && $p.addClass('image-available');

					if(managedByIiko && !created_by_iiko){
						$p.addClass('item-highlighted');
					};
					
					$p.addClass('item-'+item.id);
					$p.attr({'data-item-id':item.id});

					return $p;

				},
				behaviors:function(item){
					
					// console.log('item',item)

					var fn5 = {
						flag_spicy_on_touchend:function(){							
							if(!TOUCH_MOVED && PAGE_UP_MODE){								
								this.$pageParent.toggleClass("flag-spicy-enable");																
								ARR[CURRENT_PAGE].mode_spicy = this.$pageParent.hasClass("flag-spicy-enable")?1:0;
								need2Save && need2Save(ARR[CURRENT_PAGE]); 
								NEED_FLAGS_TO_SAVE = true;
								};
							return false;		
						},
						flag_hit_on_touchend:function(){
							if(!TOUCH_MOVED && PAGE_UP_MODE){								
								this.$pageParent.toggleClass("flag-hit-enable");
								ARR[CURRENT_PAGE].mode_hit = this.$pageParent.hasClass("flag-hit-enable")?1:0;
								need2Save && need2Save(ARR[CURRENT_PAGE]); 
								NEED_FLAGS_TO_SAVE = true;
							};
							return false;								
						},
						flag_vege_on_touchend:function(){
							if(!TOUCH_MOVED && PAGE_UP_MODE){	
								this.$pageParent.toggleClass("flag-vege-enable");
								ARR[CURRENT_PAGE].mode_vege = this.$pageParent.hasClass("flag-vege-enable")?1:0;
								need2Save && need2Save(ARR[CURRENT_PAGE]); 
								NEED_FLAGS_TO_SAVE = true;
							};				
							return false;
						}						
					};

					var fn4 = {
						bhvr:function($el){

							$el.on("touchstart",function(e){ TOUCH_MOVED = 0; });
							$el.on("touchmove",function(e){ 
								TOUCH_MOVED = 1;								
							});

							$el.find(".small-item-image").on("touchend",function(e){
								!TOUCH_MOVED && itemImageEdit && itemImageEdit(ARR[CURRENT_PAGE]);
								e.originalEvent.cancelable && e.preventDefault();
								e.stopPropagation();
							});

							var btn = $el.find(".btn-flag-spicy")[0];
							btn.addEventListener("touchend", fn5.flag_spicy_on_touchend,false);
							btn.$pageParent = $el;

							var btn = $el.find(".btn-flag-hit")[0];
							btn.addEventListener("touchend", fn5.flag_hit_on_touchend,false);
							btn.$pageParent = $el;							
				
							var btn = $el.find(".btn-flag-vege")[0];
							btn.addEventListener("touchend", fn5.flag_vege_on_touchend,false);
							btn.$pageParent = $el;					

							$el.find('.btns-item-repos__prev').on('touchend',function(e){								
								if(!TOUCH_MOVED && PAGE_UP_MODE){											
									itemReplace && itemReplace('prev');
								};
								return false;
							});
							$el.find('.btns-item-repos__next').on('touchend',function(e){								
								if(!TOUCH_MOVED && PAGE_UP_MODE){											
									itemReplace && itemReplace('next');
								};
								return false;
							});

							$el.find(".btn-item-delete").on("touchend",function(){
								if(!TOUCH_MOVED && PAGE_DOWN_MODE){	
									itemDelete && itemDelete(ARR[CURRENT_PAGE],CURRENT_PAGE);									
								};
								return false;
							});

						}
					};

					if(item==undefined){
						$.each($PAGES,function(){ fn4.bhvr(this); });		
					}else{
						var $p = false;
						for(var i in $PAGES){
							var itemClass = 'item-'+item.id;
							if($PAGES[i].hasClass(itemClass)){
								$p = $PAGES[i];
								break;	
							}
						};						
						$p && fn4.bhvr($p);
					};					
				},
				page_down_mode_start:function($el){
					requestAnimationFrame(function(){
						PAGE_DOWN_MODE = true;
						$el && $el.addClass('pagedownmode');
					});
				},
				page_down_mode_end:function($el){
					requestAnimationFrame(function() {
						PAGE_DOWN_MODE = false;
						$el && $el.removeClass('pagedownmode');
					});
				},				
				page_up_mode_start:function($el){
					requestAnimationFrame(function() {
						PAGE_UP_MODE = true;
						$el && $el.addClass('pageupmode');
					});
				},
				page_up_mode_end:function($el){ 
					requestAnimationFrame(function() {
						PAGE_UP_MODE = false;
						$el &&  $el.removeClass('pageupmode');
						fn.save();
					});
				},
				save:function() {
					if(NEED_FLAGS_TO_SAVE){
						saveItem && saveItem(ARR[CURRENT_PAGE]);
					}else if(NEED_POSITIONS_TO_SAVE){
						saveItemsPos && saveItemsPos();
					};
					NEED_FLAGS_TO_SAVE = false;
					NEED_POSITIONS_TO_SAVE = false;	
				},
				reset_all_pages:function(){
					NEED_FLAGS_TO_SAVE = false;
					NEED_POSITIONS_TO_SAVE = false;
					$.each($PAGES,function(i){				
						fn.page_up_mode_end($(this));
						fn.page_down_mode_end($(this));
					});
				},
				start_load_images:function() {
					
					NOW_IMG_LOADING = false;
					ARR_NEED_TO_LOAD_IMAGE = [];
					
					$.each(ARR,function(){	this.image_url && ARR_NEED_TO_LOAD_IMAGE.push(this); });

					var fn2 = {
						next_load_image:function(){
							var item = ARR_NEED_TO_LOAD_IMAGE.shift();							
							if(item){
								fn.load_image(item,function() {									
									fn2.next_load_image();									
								});								
							}else{
								NOW_IMG_LOADING = false;
							}
						}
					};
			
					setTimeout(function(){
						$.each($PAGES,function(i) {
							var $loader = $(this).find('.small-item-image__loader');							
							ARR[i] && ARR[i].image_url && $loader.removeClass('hidden');
						});
						fn2.next_load_image();
					},300);

				},
				load_image:function(item,doAfterLoad) {					
					var src = item.image_url.replace('.jpg','-s.jpg');					
					NOW_IMG_LOADING = new Image();
					NOW_IMG_LOADING.onload = function(){					
						fn.replace_image(item.id,this);
						doAfterLoad&&doAfterLoad();
					};
					NOW_IMG_LOADING.src = src;
				},
				replace_image:function(item_id,image){
					setTimeout(function(){					
					$.each($PAGES,function(){
						var $item = this;
						if($item.hasClass('item-'+item_id)){
						$item.find(".small-item-image__holder")
						.css({backgroundImage:"url('"+image.src +"')"})
						.removeClass('hidden')
						.end().find(".small-item-image__loader")
						.addClass("hidden");
						}
					});
					},500);
				}				

			};

			fn.build();

			// PUBLIC 

			$.extend(this,{
				get:function(){
					return $PAGES;
				},
				setCurrent:function(index){
					if(CURRENT_PAGE!=index){					
						CURRENT_PAGE = index;
						fn.reset_all_pages();
					}
				},				
				onTouchend:function(index){
					itemEdit && itemEdit(ARR[CURRENT_PAGE]);
				},
				stopLoadingImages:function(){
					var GIF = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
					if(NOW_IMG_LOADING){						
						NOW_IMG_LOADING.onload = function(){};
						NOW_IMG_LOADING.src = GIF;
						console.log("stop loading!");
					};
				},
				updateImageForCurrent:function() {
					var item = ARR[CURRENT_PAGE];
					var $loader = $PAGES[CURRENT_PAGE].find('.small-item-image__loader');
					item && item.image_url && $loader.removeClass('hidden');					
					fn.load_image(item,false);
				},
				updateItem:function(newItem) {					
					var itemClass = 'item-'+newItem.id;
					var $p = false;
					for(var i in $PAGES){					
						if($PAGES[i].hasClass(itemClass)){
							$p = $PAGES[i];
							break;
						}
					};

					$p && fn.fill_with_content($p,newItem);	
										
				},
				updatePosition:function(newCurrent){				

					if(newCurrent==CURRENT_PAGE) return;

					var replacedPage = $PAGES.splice(CURRENT_PAGE, 1);
						$PAGES.splice(newCurrent, 0, replacedPage[0]);

					var replacedItem = ARR.splice(CURRENT_PAGE, 1);
						ARR.splice(newCurrent, 0, replacedItem[0]);

					CURRENT_PAGE = newCurrent;
					
					NEED_POSITIONS_TO_SAVE = true;
					need2SavePos && need2SavePos();

				},
				updateAfterAdd:function(doAfter){					
					if(!ARR.length) return;					
					var newitem = ARR[ARR.length-1];
					var $p = TPL.clone();
					$p = fn.fill_with_content($p,newitem);
					$PAGES.push($p);
					fn.behaviors(newitem);
					doAfter && doAfter();
				},
				onSwipeUp:function(){					
					if (PAGE_DOWN_MODE) {					
						fn.page_down_mode_end( $PAGES[CURRENT_PAGE] );
					}else{
						fn.page_up_mode_start( $PAGES[CURRENT_PAGE] );
					}
				},
				onSwipeDown:function(){
					if (PAGE_UP_MODE) {										
						fn.page_up_mode_end( $PAGES[CURRENT_PAGE] );
						console.log("page_up_mode_end 1 in menuItems");
					}else{
						fn.page_down_mode_start( $PAGES[CURRENT_PAGE] );
					}
				},
				stateDefault:function(){
					fn.reset_all_pages();
				}
			});

		}
	}
});	
