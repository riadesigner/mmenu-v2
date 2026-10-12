<?php

function pbl_load_cafe_by_uniq_name(): Smart_object {
	$uniq = isset($_POST['cafe_uniq_name']) ? trim((string) $_POST['cafe_uniq_name']) : '';
	if ($uniq === '') {
		__errorjson("--it needs to know cafe_uniq_name");
	}

	$uniq = post_clean($uniq);
	$res = SQL::first("SELECT id FROM cafe WHERE uniq_name='{$uniq}'");
	if (!$res) {
		__errorjson("Unknown cafe");
	}

	$cafe = new Smart_object("cafe", (int) $res['id']);
	if (!$cafe->valid()) {
		__errorjson("Unknown cafe");
	}

	return $cafe;
}
