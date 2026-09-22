<!DOCTYPE html>
<html>
<head>
<title>Chef's Menu</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="QR-меню для столиков. Гость открывает меню по коду на столе, заказ принимает официант. Каталог связан с iiko.">
<base href="<?=$CFG->base_app_url;?>">
<link rel="shortcut icon" href="<?=$CFG->base_url;?>favicon.png" type="image/png">
<style>
	:root {
		--black: #000;
		--yellow: #ffe500;
		--white: #fff;
	}
	* { box-sizing: border-box; }
	html, body {
		margin: 0;
		min-height: 100%;
		background: var(--black);
		color: var(--white);
		font-family: system-ui, sans-serif;
	}
	body.shadow-mode { overflow: hidden; }
	.stub {
		min-height: 100vh;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 48px 24px;
	}
	.stub-inner { width: 100%; max-width: 36rem; }
	h1 {
		margin: 0 0 24px;
		font-size: 2.5rem;
		font-weight: 700;
		line-height: 1.1;
		color: var(--yellow);
	}
	p { margin: 0 0 20px; font-size: 1.125rem; line-height: 1.5; }
	a { color: var(--yellow); }
	.legal { margin: 32px 0 0; font-size: 0.95rem; }

	.shadow-layer {
		display: none;
		position: fixed;
		inset: 0;
		background: rgba(0, 0, 0, 0.72);
		z-index: 10;
	}
	body.shadow-mode .shadow-layer { display: block; }

	.sign-in-panel {
		display: none;
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		z-index: 20;
		padding: 0 16px;
	}
	.sign-in-panel-container {
		position: relative;
		max-width: 28rem;
		margin: 0 auto;
		padding: 28px 20px 20px;
		background: var(--black);
		color: var(--white);
		border: 2px solid var(--yellow);
		border-top: 0;
	}
	.sign-in-btn-close {
		position: absolute;
		top: 8px;
		right: 12px;
		color: var(--yellow);
		font-size: 1.5rem;
		line-height: 1;
		cursor: pointer;
	}
	.sign-in-sections .section { display: none; }
	.sign-in-loader,
	.sign-in-err-message,
	.sign-in-ok-message { display: none; margin: 12px 0 0; }
	.sign-in-err-message { color: var(--yellow); }
	.sign-in-panel input {
		display: block;
		width: 100%;
		margin: 0 0 8px;
		padding: 12px;
		border: 0;
		background: var(--white);
		color: var(--black);
		font: inherit;
	}
	.sign-in-buttons { margin-top: 16px; display: flex; justify-content: space-between; align-items: center; gap: 12px; }
	.sign-in-btn-fogot a { color: var(--white); font-size: 0.9rem; }
	.sign-in-btn-enter,
	.sign-in-btn-get,
	.sign-in-btn-close3 {
		margin-left: auto;
		padding: 10px 16px;
		background: var(--yellow);
		color: var(--black);
		cursor: pointer;
		font: inherit;
	}
	.sign-in-fogot-description { margin-bottom: 8px; }
	.section-touchonly h1 { font-size: 1.25rem; color: var(--yellow); }
</style>
<script src="<?=$CFG->base_url;?>jquery/jquery.min.js"></script>
<script src="<?=$CFG->base_url;?>loader.js<?=$ver;?>"></script>
<script src="./site/dist/app.js<?=$ver;?>"></script>
<script>
const GLB_APP_URL = '<?=Site::get_app_url();?>';
var SITE_CFG = {
	lang: '<?=SITE::get_lang();?>',
	home_page: '<?=SITE::get_link("home");?>/',
	admin_url: '<?=SITE::get_link("admin");?>',
	base_url: '<?=$CFG->base_app_url;?>'
};
App && App();
</script>
</head>
<body class="<?=htmlspecialchars(Site::get_body_classes(), ENT_QUOTES, 'UTF-8');?>">

<div class="shadow-layer"></div>

<main class="stub">
	<div class="stub-inner">
		<h1>Chef's Menu</h1>
		<p>QR-меню для столиков. Гость открывает меню по коду на столе, заказ принимает официант. Каталог связан с iiko.</p>
		<p>Вопросы по подключению: <a href="mailto:e.pogrebnyak@mail.ru">e.pogrebnyak@mail.ru</a></p>
		<p class="legal">ИП Погребняк Е.В., г. Владивосток, 2026</p>
	</div>
</main>

<div class="sign-in-panel">
	<div class="sign-in-panel-container">
		<div class="sign-in-btn-close" aria-hidden="true">×</div>
		<div class="sign-in-sections">
			<div class="section section-sign-in">
				<div class="section-container">
					<div class="section-sign-in__inputs">
						<input type="text" name="email" maxlength="50" placeholder="<?=LNG::get("lng_your_email");?>" autocomplete="username">
						<input type="password" name="pass" maxlength="50" placeholder="<?=LNG::get("lng_your_pass");?>" autocomplete="current-password">
					</div>
					<div class="sign-in-loader"><?=LNG::get("lng_checking");?></div>
					<div class="message sign-in-err-message"></div>
					<div class="sign-in-buttons">
						<div class="sign-in-btn-fogot"><a href="<?=SITE::get_link('home');?>/admin#"><?=LNG::get("lng_forgot_pass");?></a></div>
						<div class="sign-in-btn-enter"><?=LNG::get("lng_enter");?></div>
					</div>
				</div>
			</div>
			<div class="section section-get-pass">
				<div class="section-container">
					<div class="section-get-pass__inputs">
						<input name="email" type="text" maxlength="50" placeholder="<?=LNG::get("lng_your_email");?>" autocomplete="username">
					</div>
					<div class="sign-in-loader"><?=LNG::get("lng_sending");?></div>
					<div class="message sign-in-err-message"></div>
					<div class="sign-in-buttons">
						<div class="sign-in-fogot-description"><?=LNG::get("lng_get_new_pass");?></div>
						<div class="sign-in-btn-get"><?=LNG::get("lng_get");?></div>
					</div>
				</div>
			</div>
			<div class="section section-touchonly">
				<div class="section-container">
					<div>
						<h1><?=LNG::get("lng_friends");?></h1>
						<p><?=LNG::get("lng_singin_touch_only");?></p>
					</div>
				</div>
			</div>
			<div class="section section-done">
				<div class="section-container">
					<div>
						<div class="message sign-in-ok-message"></div>
						<div class="sign-in-buttons">
							<div class="sign-in-btn-close3"><?=LNG::get("lng_close");?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

</body>
</html>
