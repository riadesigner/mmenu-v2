<?php
	require_once('tpl.site_header.php');
?>

<div class="page-main-content">
	<div class="page-breadcrumbs">
		<a href="<?=SITE::get_link('home');?>"><?=LNG::get("lng_main");?></a>
		 / <span><?=LNG::get("lng_wrong_page");?></span>
	</div>
	<div class="page-article">
		<h1>Ой, такой страницы нет</h1>	
		<p>Перейдите, пожалуйста, на <a href="<?=SITE::get_link('home');?>">главную страницу</a></p>
	</div>
</div>

<?php
	require_once('tpl.site_footer.php');
?>