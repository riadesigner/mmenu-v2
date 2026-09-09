<?php

/*
	PUBLIC SITE: get all items of menu
*/

	header('content-type: application/json; charset=utf-8');

	define("BASEPATH",__file__);
	
	require_once getenv('WORKDIR').'/config.php';
	require_once WORK_DIR.APP_DIR.'core/common.php';

	$pilot_cafes = array_filter(array_map('trim', explode(',', (string) ($_ENV['PBL_PILOT_CAFES'] ?? '308mrr'))));
	$cafe_uniq_name = isset($_REQUEST['cafe']) ? trim((string) $_REQUEST['cafe']) : '';
	$menu_external = isset($_REQUEST['menu_external']) ? trim((string) $_REQUEST['menu_external']) : '';
	$menu_raw = isset($_REQUEST['menu']) ? trim((string) $_REQUEST['menu']) : '';

	// Pilot: proxy super-admin by cafe uniq_name + category id_external (no MySQL remap).
	if ($cafe_uniq_name !== '' && in_array($cafe_uniq_name, $pilot_cafes, true)) {
		$category_external = $menu_external !== '' ? $menu_external : $menu_raw;
		if ($category_external === '') {
			__errorjson("0. unknown menu");
		}

		$proxy_url = trim((string) ($_ENV['SUPER_ADMIN_PBL_URL'] ?? ''));
		if ($proxy_url === '') {
			__errorjson('super-admin pbl url not configured');
		}

		$items_url = str_replace('pbl.get_all_menu.php', 'pbl.get_all_items.php', $proxy_url);
		if ($items_url === $proxy_url) {
			__errorjson('super-admin pbl items url not configured');
		}

		$ch = curl_init($items_url);
		if ($ch === false) {
			__errorjson('super-admin pbl proxy init failed');
		}

		curl_setopt_array($ch, [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query([
				'cafe' => $cafe_uniq_name,
				'menu' => $category_external,
			]),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
			CURLOPT_TIMEOUT => 30,
			CURLOPT_CONNECTTIMEOUT => 10,
		]);

		$response_body = curl_exec($ch);
		$curl_error = curl_error($ch);
		$http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($response_body === false || $http_code >= 500) {
			__errorjson('super-admin unavailable' . ($curl_error ? ': '.$curl_error : ''));
		}

		if ($http_code >= 400) {
			http_response_code($http_code);
		}

		echo $response_body;
		exit;
	}

	if ($menu_raw === '') {
		__errorjson("0. unknown menu");
	}

	$id_menu = (int) $menu_raw;
	if ($id_menu <= 0) {
		__errorjson("0. unknown menu");
	}

	require_once WORK_DIR.APP_DIR.'core/class.sql.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
	require_once WORK_DIR.APP_DIR.'core/class.user.php';

	SQL::connect();

	$all_items = new Smart_collect("items","WHERE id_menu={$id_menu}","ORDER BY pos");
	if(!$all_items) __answerjson([]);

	$all_items_export = $all_items->export();

	__answerjson($all_items_export);


?>
