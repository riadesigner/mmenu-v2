<?php

/*
	PUBLIC SITE: get all menu of cafe

	Catalog JSON is served by super-admin:
	https://chefsmenu.ru/api/v1/get-all-menu?cafe={uniq_name}
*/

	header('content-type: application/json; charset=utf-8');
	define("BASEPATH",__file__);

	require_once getenv('WORKDIR').'/config.php';
	require_once WORK_DIR.APP_DIR.'core/common.php';

	__errorjson('catalog moved to /api/v1/get-all-menu');
