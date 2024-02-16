import {GLB} from './glb.js';

export var VIEW_ITEM_IMAGE_CHANGE = {
	
	init:function(options){

		this._init(options);		

		this.$btnBack = this.$footer.find('.back, .close, .cancel');
		this.$btnSave = this.$view.find('.save');
		

		this.$imageHandler = this.$view.find('.item-image-change__handler');		
		this.$btnChange = this.$view.find('.change, .item-image-change__handler');
		this.$btnRotateRight = this.$footer.find('.rotate-image');
		
		this.$fileInput = this.$view.find('input.fileToUpload');
		
		// show progress
		this.$extraLoader = this.$view.find('.app-view-extra-loader');
		this.$extraLoaderLine = this.$extraLoader.find('.app-view-extra-loader__line');
		this.$extraLoaderStatus = this.$extraLoader.find('.app-view-extra-loader__status');

		this.IMAGE_MAX_WIDTH = 1500;

		this.reset();
		this.behavior();
		
		return this;

	},
	
	reset:function(){
		this._reset();
		this._need2save(false);		
		this.ID_ITEM = 0;
		this.AJAX = false;
		this.IMAGE = null;
		this.$elImg = null;
		this.$elCanvas = null;
		this.onReady = false;		
		this.CURRENT_ROTATE = 0;
		this.REAL_ROTATE = 0;		
		this.handler_no_image(false);
		this.btn_rotate_hide(true);
		this.handlers_clear();
		this.extra_loader_hide();		
	},
	btn_rotate_hide:function(mode){
		mode? this.$btnRotateRight.addClass('hidden'):this.$btnRotateRight.removeClass('hidden');
	},
	handlers_clear:function(){
		this.$imageHandler.html("");		
	},
	handler_no_image:function(mode){
		mode ? this.$imageHandler.addClass("no-image") : this.$imageHandler.removeClass("no-image");
	},	
	update_button_title:function(image){
		var title = image ? 'Заменить':'Выбрать';
		this.$btnChange.find('span').html(title);
	},

	update:function(options){
		var _this=this;
		this.reset();
		this._update();		
		this._page_hide();

		this.ITEM = options.item;
		this.ID_ITEM = this.ITEM.id;		
		this.onReady = options.onReady;		

		this.update_button_title(this.ITEM.image_url);
		// GLB.CURRENCY.get_current()

		if(!this.ITEM.image_url){
			this.handler_no_image(true);
			setTimeout(function(){ _this._page_show(); },300);
		}else{						
			this._now_loading();			
			this.load_images(this.ITEM.image_url);
		}
	},
	
	rotate_image_toright:function(deg){
		if(!this.LOADING && this.IMAGE && this.$elImg){
					

			if(deg!==undefined){
				this.CURRENT_ROTATE = deg;
			}else{				
				this.CURRENT_ROTATE+=90;
			};
			
			this.REAL_ROTATE = this.CURRENT_ROTATE%360;

			this._need2save(true);

			var vert = (this.CURRENT_ROTATE/90)%2 > 0?false:true;

			var hdlSize = {w:this.$imageHandler.width(),h:this.$imageHandler.height()}						
			var imgVert = this.IMAGE.width<this.IMAGE.height;			

			if(imgVert){
				var k = hdlSize.w/hdlSize.h;				
			}else{				
				var k = hdlSize.h/hdlSize.w;								
			};

			var scale = vert?1:k;

			if(!vert){
				var a = this.$elImg.height();
				var b = this.$elImg.width();					
				var d = (b-a)/2*scale;
				var d1 = (a*scale-a)/2;
				var top = d+d1;
			}else{
				var top = 0;
			};

			this.$elImg.css({
				top:top,
				transform:'translate(-50%,0%) rotateZ('+this.CURRENT_ROTATE+'deg) scale('+scale+')',
				transition:'.5s'
			});
			
		};
		return false;
	},		
	image_to_start_pos:function(){
		this.CURRENT_ROTATE = 0;
		this.REAL_ROTATE = 0;
		this.rotate_image_toright(0);
	},
	load_images:function(src){

		//xxx
		var _this=this;

		var fn = {
			load:function(src,options){				
				_this.NOW_IMG_LOADING = new Image();
				if(options.crossOrigin){
					_this.NOW_IMG_LOADING.crossOrigin = 'Anonymous';
				}
				_this.NOW_IMG_LOADING.onload = function(){						
					options.onReady&& options.onReady(this);
				}
				_this.NOW_IMG_LOADING.onerror = function(err){											
					
					var ask = [
							"<p>Не удалось открыть изображение, ",
							"хотите его заменить?</p>",
						].join(' ');
					GLB.VIEWS.modalConfirm({
						title:GLB.LNG.get("lng_attention"),
						ask:ask,
						action:function(){
							options.onReady&& options.onReady(null);
						},
						cancel:function(){ _this._go_back(); },
						buttons:[GLB.LNG.get("lng_ok"),GLB.LNG.get("lng_cancel")]
					});				



					_this._end_loading();
				}				
				_this.NOW_IMG_LOADING.src = src;
			}
		};

		var urlLarge = src+'?adm_rnd='+Math.random();

		fn.load(urlLarge,{onReady:function(img){

			!img && _this.handler_no_image(true);
			img&&_this.show_image(img);		
			_this.update_button_title(img);
			setTimeout(function(){
				_this._page_show();
			},100);
			setTimeout(function(){				
				img && _this.btn_rotate_hide(false);								
				_this._end_loading();
			},300);
		},crossOrigin:true});

	},

	get_image_from_file:function(file){
		var _this=this;
		var fn = {
			readPreview:function(file){							
				var oFReader = new FileReader();
				oFReader.readAsDataURL(file);
				oFReader.onload = function(oFREvent) {
					var src = oFREvent.target.result;
					fn.preloadImage(src)
				};
			},
			preloadImage:function(src){
				var img = new Image();
				img.onload = function(){					
					_this.show_image(this);					
					setTimeout(function(){
						_this.btn_rotate_hide(false);
						_this.handler_no_image(false);
						_this._end_loading();					
						_this.image_to_start_pos();						
					},300);
				};
				img.src = src;
			}
		};
		fn.readPreview(file);
	},

	show_image:function(img){
		var _this=this;
		var v = img.height>img.width;		
		this.IMAGE = img;		
		v ? this.$imageHandler.addClass('image-vert'):this.$imageHandler.removeClass('image-vert');

		if(!this.$elImg){
			this.$elImg = $('<img>',{src:img.src});
			this.$imageHandler.html(this.$elImg);
		}else{
			this.$elImg.attr({src:img.src})
		}

	},

	stop_load_image:function(){
		var GIF = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
		if(this.NOW_IMG_LOADING){
			this.NOW_IMG_LOADING.onload = function(){};
			this.NOW_IMG_LOADING.src = GIF;
		};	
	},

	behavior:function()	{
		var _this = this;

		this._behavior();
		
		this.$btnBack.on('touchend',function(){				

			_this.stop_load_image();
			_this.AJAX && _this.AJAX.abort();

			_this._blur({onBlur:function(){
				_this._end_loading();
				_this._go_back();
			}});

			return false;
		});

		this.$btnRotateRight.on('touchend',function(e){			
			!_this.LOADING && _this.rotate_image_toright();
		});
			
		this.$fileInput.change(function(){
			var arrFiles = $(this)[0].files;
			if(arrFiles.length){
				_this.stop_load_image();
				_this._need2save(true);
				_this.FILE_TO_UPLOAD = arrFiles[0];
				_this._now_loading();
				_this.get_image_from_file(arrFiles[0]);
			};
		});

 		this.$btnChange.on('touchend',function(){ 			
 			_this._blur({onBlur:function(){
	 			if(!_this.LOADING){
					_this.$fileInput.val("").trigger('click');
	 			};
 			}});
 			return false;
		});

		// this.$imageHandler.on('touchstart',function(){			
		// 	$(this).addClass('.active');
		// });
		// this.$imageHandler.on('touchend',function(){			
		// 	$(this).removeClass('.active');
		// });

		this.$btnSave.on('touchend',function(){
			_this._blur({onBlur:function(){
				!_this.LOADING && _this.IMAGE && _this.save();
			}});			
			return false;
		});				

	},
	extra_loader_show:function(){
		this.$extraLoader.addClass('app-view-extra-loader-visible');
	},
	extra_loader_hide:function(){
		this.$extraLoader.removeClass('app-view-extra-loader-visible');
		this.extra_loader_reset();
	},
	extra_loader_update:function(pr){
		this.$extraLoaderLine.css({width:pr+'%'})
		this.$extraLoaderStatus.html(pr+"%");
	},
	extra_loader_reset:function(){
		this.$extraLoaderLine.css({width:0})
		this.$extraLoaderStatus.html("0%");
	},

	show_err_loading:function(){
		GLB.VIEWS.modalMessage({
			title:GLB.LNG.get("lng_attention"),
			message:"Не удалось сохранить изображение",
			btn_title:GLB.LNG.get('lng_ok')
		});
	},
	canvas_prepare:function(){
		if(!this.IMAGE || !this.$elImg) return false;


		var img = this.IMAGE;
		
		var is_vert = img.height>img.width;		
		var max_size = this.IMAGE_MAX_WIDTH;

		var fn = {
			calc_size:function(img,max_size){
				var v = img.height>img.width;
				var w,h;

				if(v){
					if(img.height<max_size){
						var size = {w:img.width, h:img.height}
					}else{						
						h = max_size;
						var size = {w:img.width/(img.height/max_size),h:h}
					}					
				}else{
					if(img.width<max_size){
						var size = {w:img.width, h:img.height}
					}else{						
						w = max_size;
						var size = {w:w,h:img.height/(img.width/max_size)}
					}														
				};
				return size;	
			}
		};	
		
		var size = fn.calc_size(img,max_size);		

		var t0 = performance.now();
		
        this.cvs = document.createElement('canvas');
		this.cvs.setAttribute("id", "image-canvas");
		this.cvs.width = size.w;
		this.cvs.height = size.h;
		this.ctx = this.cvs.getContext('2d');		
		this.ctx.drawImage(img, 0,0,size.w,size.h);

		var t1 = performance.now();
		console.log("image to canvas takes " + (t1 - t0) + " milliseconds.");

	},

	save:function(){

		var _this=this;
		var PATH = 'adm/lib/';
		var lib_save_to_file = PATH + 'lib.save_to_file.php';

		this.$inputs.blur();

		this.canvas_prepare();

		if(!this.cvs){return false};
		
		this._now_loading();
		this.extra_loader_show();
		this.btn_rotate_hide(true);

		var fn = {
			uploadComplete:function(evt){

				_this._need2save(false);				
				_this._end_loading();
				_this.extra_loader_hide();
				_this.btn_rotate_hide(false);

				var response = JSON.parse(evt.target.responseText);

				if(!response || response.error){					
					_this.show_err_loading();
				}else{					
					
					var newImageName = {image_name:response['image_name'], image_url:response['image_url']};
					_this.onReady && _this.onReady(newImageName)
					_this._go_back();
				}

			},
			uploadProgress:function(evt){
			   if (evt.lengthComputable){
			        var percentComplete = parseInt((evt.loaded / evt.total) * 100);
			        percentComplete>10 && _this.extra_loader_update(percentComplete-10);
			    }
			},
			uploadFailed:function(evt){
				_this.extra_loader_hide();
				_this._end_loading();
				_this.show_err_loading();
				// console.log('upload faled',evt.target.responseText);
			},
			uploadCanceled:function(evt){
				_this.extra_loader_hide();
				_this._end_loading();
				_this.show_err_loading();
				// console.log('upload canceled', evt.target.responseText);
			}
		};

		var send = {
			image:function(blob){
				var fd = new FormData();
				fd.append('up_file', blob, 'new_image');	
				fd.append('id_item', _this.ID_ITEM);
				fd.append('need_rotate', _this.REAL_ROTATE);
				_this.AJAX = new XMLHttpRequest();
				_this.AJAX.upload.addEventListener("progress", fn.uploadProgress, false);
				_this.AJAX.addEventListener("load", fn.uploadComplete, false);
				_this.AJAX.addEventListener("error", fn.uploadFailed, false);
				_this.AJAX.addEventListener("abort", fn.uploadCanceled, false);
				_this.AJAX.open("POST", lib_save_to_file,true); 
				_this.AJAX.send(fd);			
			}			
		};

		this.cvs.toBlob(function(blob){			
				console.log("SAVE!", (blob.size/1000000).toFixed(2)+'Mb');				
			    send.image(blob);
		}, 'image/jpeg', 0.95); 

	}
};
