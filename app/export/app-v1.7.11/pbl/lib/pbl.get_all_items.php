<?php

/*
	PUBLIC SITE: get all items of menu

	Catalog JSON is served by super-admin:
	https://chefsmenu.ru/api/v1/get-all-items?cafe={uniq_name}&menu={id_external}
*/

	header('content-type: application/json; charset=utf-8');
	define("BASEPATH",__file__);

	require_once getenv('WORKDIR').'/config.php';
	require_once WORK_DIR.APP_DIR.'core/common.php';

	__errorjson('catalog moved to /api/v1/get-all-items');
