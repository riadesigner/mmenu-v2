<?php
	require_once('tpl.site_header.php');
	$price_per_year = $CFG->contract_cost_in_rub*12;	
	$price_per_mounth = $CFG->contract_cost_in_rub;	
?>

<div class="page-main-content">
	<div class="page-breadcrumbs">
<a href="<?=SITE::get_link('home');?>"><?=LNG::get("lng_main");?></a> / <span><?=LNG::get("lng_price");?></span>
	</div>
	<div class="page-article">
		<h1>Сколько стоит меню и что входит в стоимость годового обслуживания?</h1>

		<div class="page-article__section">
			<h2>В стоимость годового обслуживания меню входит:</h2>

			<div class="page-article__price">
				<div class="page-article__price-section">
					<div class="page-article__price-section-part">
						<div class="page-article__price-section-part-image">
							<span>1.</span>
							<img class="page-article__price-section-part-image-desktop" src="./site/i/site2-exp-price-01-8.png">
							<img class="page-article__price-section-part-image-mobile" src="./site/i/site2-exp-price-mobile-01-8.png">
						</div>
						<div class="page-article__price-section-part-text">
							<i class="highlighted">Услуга хостинга</i> – размещение и хранение всей графической и текстовой информации о вашем кафе и меню, 
							включая изображения и <a href="<?=SITE::get_link('features_general');?>">описания блюд</a>.
						</div>
					</div>
					<div class="page-article__price-section-part">
						<div class="page-article__price-section-part-image">
							<span>2.</span>
							<img class="page-article__price-section-part-image-desktop" src="./site/i/site2-exp-price-02-8.png">
							<img class="page-article__price-section-part-image-mobile" src="./site/i/site2-exp-price-mobile-02-8.png">
						</div>
						<div class="page-article__price-section-part-text">
							Легкая в использовании <i class="highlighted">Панель управления,</i> которое позволит шеф-повару легко с&nbsp;телефона 
							<a href="<?=SITE::get_link('help_edit_items');?>">обновлять</a> изображения, прайс и&nbsp;описание блюд, поддерживая меню в&nbsp;актуальном состоянии.</div>
					</div>
				</div>
				<div class="page-article__price-section">
					<div class="page-article__price-section-part">
						<div class="page-article__price-section-part-image">
							<span>3.</span>
							<img class="page-article__price-section-part-image-desktop" src="./site/i/site2-exp-price-03-8.png">
							<img class="page-article__price-section-part-image-mobile" src="./site/i/site2-exp-price-mobile-03-8.png">
						</div>
						<div class="page-article__price-section-part-text">
							Предоставление <i class="highlighted">быстрого доступа</i> к&nbsp;Меню вашим посетителям через 
							<a href="<?=SITE::get_link('easy_open_modern_pwa');?>">программное обеспечение</a> сервиса, которое 
							не&nbsp;требует установки на устройства.</div>
					</div>
					<div class="page-article__price-section-part">
						<div class="page-article__price-section-part-image">
							<span>4.</span>
							<img class="page-article__price-section-part-image-desktop" src="./site/i/site2-exp-price-04-8.png">
							<img class="page-article__price-section-part-image-mobile" src="./site/i/site2-exp-price-mobile-04-8.png">
						</div>
						<div class="page-article__price-section-part-text">Обеспечение <i class="highlighted">доставки сообщений</i> (заказов  блюд) на вашу почту от&nbsp;посетителей через почтовые сервера сервиса Chef’s Menu.</div>
					</div>
				</div>
				<div class="page-article__price-section">
					<div class="page-article__price-section-part">
						<div class="page-article__price-section-part-image">
							<span>5.</span>
							<img class="page-article__price-section-part-image-desktop" src="./site/i/site2-exp-price-05-8.png">
							<img class="page-article__price-section-part-image-mobile" src="./site/i/site2-exp-price-mobile-05-8.png">
						</div>
						<div class="page-article__price-section-part-text"><i class="highlighted">Собственный адрес</i> (поддомен) в&nbsp;сети интернет для вашего меню. 
							Возможность <a href="<?=SITE::get_link('help_link_to_menu');?>">выбрать свой</a> простой адрес. </div>
					</div>
					<div class="page-article__price-section-part">
						<div class="page-article__price-section-part-image">
							<span>6.</span>
							<img class="page-article__price-section-part-image-desktop" src="./site/i/site2-exp-price-06-8.png">
							<img class="page-article__price-section-part-image-mobile" src="./site/i/site2-exp-price-mobile-06-8.png">
						</div>
						<div class="page-article__price-section-part-text"><i class="highlighted">Автоматическое обновление</i> программного обеспечения 
							до актуальной версии в&nbsp;течение всего периода контракта.
						</div>
					</div>
				</div>
				<div class="page-article__price-section">
					<div class="page-article__price-section-part">
						<div class="page-article__price-section-part-image">
							<span>7.</span>
							<img class="page-article__price-section-part-image-desktop" src="./site/i/site2-exp-price-07-8.png">
							<img class="page-article__price-section-part-image-mobile" src="./site/i/site2-exp-price-mobile-07-8.png">
						</div>
						<div class="page-article__price-section-part-text"><a href="<?=SITE::get_link('contacts');?>">
							Техническая поддержка</a> по любым вопросам, 
							связанным с работой Меню или 
							<a href="<?=SITE::get_link('help_link_control_panel');?>">Панелью Управления</a>.
						</div>							
					</div>	
					<div class="page-article__price-section-part">
						<div class="page-article__price-section-part-image">
							<span>8.</span>
							<img class="page-article__price-section-part-image-desktop" src="./site/i/site2-exp-price-08-8.png">
							<img class="page-article__price-section-part-image-mobile" src="./site/i/site2-exp-price-mobile-08-8.png">
						</div>
						<div class="page-article__price-section-part-text">Персональный <a href="<?=SITE::get_link('easy_open');?>">QR-код</a> для быстрого открытия Меню. 
							А также файлы для распечатки наклеек <nobr>с QR-кодом</nobr> на&nbsp;тэйбл-тенты.</div>					
					</div>	
				</div>
			</div>
		</div>

		<div class="page-article__section">
			<div class="page-price-information">
				<p><span class="price-description">Стоимость обслуживания Меню –</span> <nobr><span class="price-per-year"><?=$price_per_mounth;?> руб. / мес.</span></nobr></p> 
				<p>Стоимость годового контракта всего – <strong> <?=$price_per_year;?> руб.</strong></p>
				<p>Тестовый период: <span class="try-free"><nobr>14 дней – бесплатно!</nobr></span></p>
			</div>
		</div>		

	</div>
</div>

<?php
	require_once('tpl.site_footer.php');
?>