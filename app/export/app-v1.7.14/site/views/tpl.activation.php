<!DOCTYPE html>
<html>
<head>
<title><?=SITE::get_title();?></title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
<meta http-equiv="Cache-Control" content="no-cache"/>

<link rel="shortcut icon" href="favicon.png" type="image/png">
<base href="<?=$CFG->base_app_url;?>">

<meta name="robots" content="noindex, follow"/>

<link rel="stylesheet" href="./site/css/style_activation.css<?=$ver;?>">

<script src="<?=$CFG->base_url;?>jquery/jquery.min.js"></script>
<script>

<?php  
	$R = new Router(); 
	$email = $R->get(1) ?: "";
	$key = $R->get(2) ?: "";
	$cafe = $R->get(3) ?: "";
	$lang = $R->get(4) ?: Site::get_lang();
	echo "var email='$email';\n";
	echo "var key='$key';\n";
	echo "var cafe='".urldecode((string) $cafe)."';\n";
	echo "var lang='$lang';\n";
	echo "var home_url='".SITE::get_link('home')."';\n";

?>

var Activation = {
	init:function(){

		this.$body = $("body");		
		this.$header = $(".activation__header");
		this.$errMessage = $(".activation__err-message");
		this.$footerDots = $(".activation__slider_footer-dots>div");
		this.$slider = $(".activation__slider");
		this.$sliderWrapper = $(".activation__slider_allpages");
		this.$sliderPages = this.$slider.find('.activation__slider_page');
		this.NOW_LOADING = false;		
		
		this.URL_ADMIN = home_url+"/admin/";

		this.LOADED = false;
		this.startTime = Date.now();
		
		// waiting for activation cafe = 90 sec
		this.MAXIMUM_WAITING_TIME = 90;

		this.header_show();
		this.start();
		this.slider_start();
		
	},
	header_show:function(){
		var _this=this;
		setTimeout(function(){
			_this.$header.addClass('header-showed');
		},100);
	},
	header_hide:function(){
		this.$header.removeClass('header-showed');
	},	
	go_admin:function(){
		console.log('location.href = this.URL_ADMIN');
		location.href = this.URL_ADMIN;
	},
	slider_start:function(){
		var _this=this;
		
		var currrent = 0;
		var sliderWidth = 0;
		var total = _this.$sliderPages.size();	
		var pause = [5000,5000,3500];

		var fn = {
			prepare:function(){
				sliderWidth = _this.$slider.width();
				for(var i=0;i<total;i++){ _this.$sliderPages.eq(i).css({left:sliderWidth*i,top:0});	}
				_this.$sliderPages.eq(currrent).addClass('current');
			},
			show:function(){
				_this.$slider.addClass('showed');				
				setTimeout(function(){fn.showNext();},pause[0])
			},
			hide:function(){
				_this.$slider.removeClass('showed');
			},
			showNext:function(){
				currrent++;
				if(currrent<total){				
					_this.$sliderPages.removeClass('current');
					_this.$sliderPages.eq(currrent).addClass('current');					
					var left = currrent*sliderWidth;
					_this.$sliderWrapper.css({
						transform:"translateX(-"+left+"px)",
						transition:".6s"
					});
					_this.$footerDots.removeClass("current").eq(currrent).addClass("current");
					setTimeout(function(){fn.showNext();},pause[currrent]);
				}else{

					_this.endTime = Date.now();
					var delta = _this.endTime - _this.startTime;
					console.log('Slider end time: '+delta);
					fn.hide();
					fn.waiting_for_loading(0);

				}				
			},
			waiting_for_loading:function(counter){				
				if(_this.LOADED){ 
					fn.finish_and_go(); 
				}else{					
					if(counter<_this.MAXIMUM_WAITING_TIME){
						counter++;
						setTimeout(function(){
							fn.waiting_for_loading(counter);
						},1000);
					}else{
						_this.AJAX&&_this.AJAX.abort();
			         	_this.end_loading();
			            _this.err_message(lang=='ru'?'Что-то пошло не так.':'Something wrong.');
			            console.log('Ожидание '+counter+' сек.');
					}
				}
		
			},
			finish_and_go:function(){				
				setTimeout(function(){ 
					_this.header_hide(); 
					setTimeout(function(){
						_this.go_admin();
					},600);
				},300);
			}

		};
		fn.prepare();
		setTimeout(function(){
			fn.show();
		},600);
		
	},
	start:function() {
		var _this = this;
				
		var PATH = 'site/lib/';
		var url = PATH + 'site.account_activate.php';		
		
		this.now_loading();

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            data:{email:email,key:key,cafe:cafe,lang:lang},
            method:"POST",
            success: function (answer) { 
            	console.log('answer',answer)
            	_this.end_loading();
            	if(!answer.error){
					
					_this.LOADED = true;

					_this.endLoadingTime = Date.now();
					var deltaLoading = _this.endLoadingTime - _this.startTime;
					console.log('Loading time: '+deltaLoading);

				}else{
					_this.err_message(lang=='ru'?'Что-то пошло не так':'Something wrong');
					console.log(answer.error);
				}
            },
            error:function(response) {            	
            	_this.end_loading();
            	_this.err_message(lang=='ru'?'Что-то пошло не так':'Something wrong');
		        console.log("err activation",response);
			}
        });			

		
	},
	err_message:function(msg) {
		console.log('msg!!',msg)
		this.$errMessage.hide().html(msg).fadeIn();
	},
	now_loading:function() {
		this.NOW_LOADING = true;
		this.$body.addClass("now-loading");
	},
	end_loading:function() {
		this.NOW_LOADING = false;
		this.$body.removeClass("now-loading");
	}
};

$(function(){
	Activation.init();
});

</script>


</head>
<body class='<?=Site::get_body_classes();?>' >

<div class="activation__all-site">

<center>
	<?php
		$page_title = $lang=='ru'?'Подготовка к&nbsp;первому запуску':'Preparing for the first launch';
	?>	
	<div class="activation__header">
		<div class="activation__title"><?=$page_title;?></div>
		<div class="activation__loader"></div>
	</div>

	<div class="activation__err-message"></div>	
	<div class="activation__slider">
		<div class="activation__slider_allpages">
			<div class="activation__slider_page">
				<h2>В ваше меню добавлено три новых тестовых раздела.</h2>	
				<p>Вы можете изменять их, наполнять или удалить по своему усмотрению.</p>
			</div>
			<div class="activation__slider_page">
				<h2>В ваше меню добавлено 10 тестовых блюд с описаниями.</h2>	
				<p>В период тестирования, вы можете загрузить до 30 новых блюд.</p>
			</div>
			<div class="activation__slider_page">
				<h2>Подготовка меню завершена.</h2>	
				<p>В вашем распоряжении бесплатный тестовый период 30 дней. Приятной работы.</p>
			</div>
		</div>		
		<div class="activation__slider_footer">
			<div class="activation__slider_footer-dots">
				<div class="current"></div>
				<div></div>
				<div></div>
			</div>
		</div>
	</div>


</center>	

</div>
</body>	
</html>