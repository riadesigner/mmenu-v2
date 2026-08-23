<?php

/*
	PUBLIC SITE: get all menu of cafe
*/

	header('content-type: application/json; charset=utf-8');	
	define("BASEPATH",__file__);

	require_once getenv('WORKDIR').'/config.php';
	 
	require_once WORK_DIR.APP_DIR.'core/common.php';

	$pilot_cafes = array_filter(array_map('trim', explode(',', (string) ($_ENV['PBL_PILOT_CAFES'] ?? '308mrr'))));
	$cafe_uniq_name = isset($_REQUEST['cafe']) ? trim((string) $_REQUEST['cafe']) : '';

	if ($cafe_uniq_name !== '' && in_array($cafe_uniq_name, $pilot_cafes, true)) {
		$proxy_url = trim((string) ($_ENV['SUPER_ADMIN_PBL_URL'] ?? ''));
		if ($proxy_url === '') {
			__errorjson('super-admin pbl url not configured');
		}

		$ch = curl_init($proxy_url);
		if ($ch === false) {
			__errorjson('super-admin pbl proxy init failed');
		}

		curl_setopt_array($ch, [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query(['cafe' => $cafe_uniq_name]),
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

		require_once WORK_DIR.APP_DIR.'core/class.sql.php';
		SQL::connect();

		$cafe_uniq_name_clean = post_clean($cafe_uniq_name);
		$res = SQL::first("SELECT id FROM cafe WHERE uniq_name='{$cafe_uniq_name_clean}'");
		if (!$res) {
			__errorjson('1. unknown cafe');
		}

		$id_cafe = (int) $res['id'];

		if (isset($payload['cafe']) && is_array($payload['cafe'])) {
			$payload['cafe']['id'] = (string) $id_cafe;
		}

		if (!empty($payload['menu']) && is_array($payload['menu'])) {
			foreach ($payload['menu'] as &$section) {
				if (!is_array($section)) {
					continue;
				}
				$id_external = isset($section['id_external']) ? trim((string) $section['id_external']) : '';
				if ($id_external === '') {
					continue;
				}
				$id_external_sql = post_clean($id_external);
				$row = SQL::first(
					"SELECT id FROM menu WHERE id_cafe={$id_cafe} AND id_external='{$id_external_sql}'"
				);
				if ($row) {
					$section['id'] = (string) (int) $row['id'];
					$section['id_cafe'] = (string) $id_cafe;
				}
			}
			unset($section);
		}

		__answerjson($payload);
		exit;
	}
	 
	require_once WORK_DIR.APP_DIR.'core/class.sql.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
	require_once WORK_DIR.APP_DIR.'core/class.user.php';

	SQL::connect();

	if(!isset($_REQUEST['cafe']) || empty(trim((string) $_REQUEST['cafe'])))__errorjson("0. unknown cafe");

	$cafe_uniq_name = post_clean($_REQUEST['cafe']);

	$q = "SELECT * FROM cafe WHERE uniq_name='$cafe_uniq_name'";
	$res = SQL::first($q);
	
	if(!$res) __errorjson("1. unknown cafe");

	$id_cafe = (int) $res['id'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe || !$cafe->valid()) __errorjson("unknown cafe with #{$id_cafe}");	

	$all_menu = new Smart_collect("menu","WHERE id_cafe={$id_cafe}","ORDER BY pos");

	$answr = ["cafe"=>$cafe->export(), "menu"=>$all_menu->export()];
	
	// saveArrayToUniqueJson($answr); //for test only!

	__answerjson($answr);




?>