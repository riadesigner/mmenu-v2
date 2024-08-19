<?php

	header('content-type: application/json; charset=utf-8');
	$callback = $_REQUEST['callback'] ?? 'alert';
	if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }	

	define("BASEPATH",__file__);

	require_once getenv('WORKDIR').'/config.php';
	 

	require_once WORK_DIR.APP_DIR.'core/common.php';	
	
	require_once WORK_DIR.APP_DIR.'core/class.sql.php';
	 
	require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
	require_once WORK_DIR.APP_DIR.'core/class.user.php';

	require_once WORK_DIR.APP_DIR.'core/class.lang_iso.php';


	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjsonp("Unknown user");

	if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown cafe");
	$id_cafe = (int) $_POST['id_cafe'];
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe->valid())__errorjsonp("Unknown cafe");
	
	if($cafe->id_user !== $user->id) __errorjsonp("Not allowed");

	$skin_label = substr((string) $_POST['skin_label'],0,30);
	$cafe->skin_label = $skin_label;	

	
	if(isset($_POST["new_lang"]) && !empty(trim((string) $_POST["new_lang"]))){		
		$new_lang = trim((string) $_POST["new_lang"]);
		$new_lang_code = Lang_iso::get_code($new_lang);
		if(!$new_lang_code) __errorjsonp("--Unknown lang");
		if(mb_strlen($new_lang)<4) __errorjsonp("--Too short lang name, ".mb_strlen($new_lang));
	}else{
		$new_lang = "";	
	}

	if(isset($_POST["lang_to_delete"]) && !empty(trim((string) $_POST["lang_to_delete"]))){	
		$lang_to_delete = mb_strtolower(trim((string) $_POST["lang_to_delete"]));
		$lang_code_to_delete = Lang_iso::get_code($lang_to_delete);
		if(!$lang_code_to_delete) __errorjsonp("--Unknown lang");
		if(mb_strlen($lang_to_delete)<4) __errorjsonp("--Too short lang name, ".mb_strlen($lang_to_delete));
	}else{
		$lang_to_delete = "";		
	}
	
	$ARR_LANG = !empty($cafe->extra_langs)?json_decode((string) $cafe->extra_langs,1):[];	

	// ADDING NEW LANGUAGE
	if($new_lang && $new_lang_code){		
		$ARR_LANG[$new_lang_code] = $new_lang;
	}

	// REMOVING LANGUAGE
	if($lang_to_delete && $lang_code_to_delete){		
		if(isset($ARR_LANG[$lang_code_to_delete])) unset($ARR_LANG[$lang_code_to_delete]);		
	}	

	
	$cafe->extra_langs = json_encode($ARR_LANG, JSON_UNESCAPED_UNICODE); 
	$cafe->updated_date = 'now()';
	$cafe->rev+=1;

	if($cafe->save()){	

		__answerjsonp($cafe->export());

	}else{
		__errorjsonp("Can not update cafe data");
	}

?>