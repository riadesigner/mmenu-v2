<?php

/*
	PUBLIC SITE: get all items of menu
*/

	header('content-type: application/json; charset=utf-8');

	define("BASEPATH",__file__);
	
	require_once getenv('WORKDIR').'/config.php';
	require_once WORK_DIR.APP_DIR.'core/common.php';

	$pilot_cafes = array_filter(array_map('trim', explode(',', (string) ($_ENV['PBL_PILOT_CAFES'] ?? '308mrr'))));

	if(!isset($_REQUEST['menu']) || empty(trim((string) $_REQUEST['menu'])))__errorjson("0. unknown menu");

	$id_menu = (int) trim((string) $_REQUEST['menu']);

	require_once WORK_DIR.APP_DIR.'core/class.sql.php';
	SQL::connect();

	$menu_row = SQL::first(
		"SELECT menu.id_external, menu.id_cafe, cafe.uniq_name
		FROM menu
		JOIN cafe ON cafe.id = menu.id_cafe
		WHERE menu.id = {$id_menu}"
	);

	if ($menu_row && in_array($menu_row['uniq_name'], $pilot_cafes, true)) {
		$proxy_url = trim((string) ($_ENV['SUPER_ADMIN_PBL_URL'] ?? ''));
		if ($proxy_url === '') {
			__errorjson('super-admin pbl url not configured');
		}

		$items_url = str_replace('pbl.get_all_menu.php', 'pbl.get_all_items.php', $proxy_url);
		if ($items_url === $proxy_url) {
			__errorjson('super-admin pbl items url not configured');
		}

		$cafe_uniq_name = trim((string) $menu_row['uniq_name']);
		$id_external = trim((string) $menu_row['id_external']);
		$id_cafe = (int) $menu_row['id_cafe'];

		$ch = curl_init($items_url);
		if ($ch === false) {
			__errorjson('super-admin pbl proxy init failed');
		}

		curl_setopt_array($ch, [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query([
				'cafe' => $cafe_uniq_name,
				'menu' => $id_external,
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

		$payload = json_decode($response_body, true);
		if (!is_array($payload)) {
			echo $response_body;
			exit;
		}

		if (isset($payload['error'])) {
			echo $response_body;
			exit;
		}

		$items = [];
		foreach ($payload as $item) {
			if (!is_array($item)) {
				continue;
			}

			$id_external_item = isset($item['id_external']) ? trim((string) $item['id_external']) : '';
			if ($id_external_item === '') {
				continue;
			}

			$id_external_sql = post_clean($id_external_item);
			$row = SQL::first(
				"SELECT id FROM items WHERE id_menu={$id_menu} AND id_external='{$id_external_sql}'"
			);
			if (!$row) {
				continue;
			}

			$item['id'] = (string) (int) $row['id'];
			$item['id_menu'] = (string) $id_menu;
			$item['id_cafe'] = (string) $id_cafe;
			$items[] = $item;
		}

		__answerjson($items);
		exit;
	}

	require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
	require_once WORK_DIR.APP_DIR.'core/class.user.php';

	$all_items = new Smart_collect("items","WHERE id_menu={$id_menu}","ORDER BY pos");
	if(!$all_items) __answerjson([]);

	$all_items_export = $all_items->export();

	__answerjson($all_items_export);


?>
