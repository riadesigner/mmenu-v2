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
require_once WORK_DIR.APP_DIR.'core/class.iiko_params.php';
require_once WORK_DIR.APP_DIR.('core/class.iiko_nomenclature.php');
require_once WORK_DIR.APP_DIR.('core/class.iiko_parser_to_unimenu.php');
require_once WORK_DIR.APP_DIR.('core/class.conv_unimenu_to_chefs.php');



session_start();
SQL::connect();

// нужна новая архитектура решения

// log:  ===== Размер iiko_response: ~2 MB
// log:  Переменных в iiko_response: 79978
// log:  Размер UNIMENU: ~0.91 MB
// log:  Переменных в UNIMENU: 44338
// log:  Размер chefsdata: ~1.28 MB
// log:  Переменных в chefsdata: 46101


// GETTING OLDWAY MENU BY ID / API 1, (FROM NOMENCLATURE) 

if(!isset($_POST['token'])){
	__errorjsonp(["error"=>"unknown token"]);
	exit();
}
if(!isset($_POST['externalMenuId'])){
    __errorjsonp(["error"=>"unknown external menu id"]);
    exit();
}
if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ){
    __errorjsonp(["error"=>"--its need to know id_cafe"]);
    exit();
}

$token = $_POST['token'];
$externalMenuId = $_POST['externalMenuId'];
// $currentExtmenuHash = $_POST['currentExtmenuHash'];
$id_cafe = post_clean($_POST['id_cafe']);

$id_cafe = (int) $_POST['id_cafe'];
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);

$key = $cafe->iiko_api_key;
$IIKO_PARAMS = new Iiko_params($id_cafe, $key);
$orgId = ($IIKO_PARAMS->get())->current_organization_id;

// -------------------------------------------------------
// получаем номенклатуру с сервера iiko
// -------------------------------------------------------
$NOMCL = new Iiko_nomenclature($orgId, "", $token);    
$NOMCL->reload();
$iiko_response = $NOMCL->get_data();

//-------------------------------------------------------
// размер исходных данных от iiko:
$size_iiko = strlen(serialize($iiko_response)); 
$size_iiko = round($size_iiko / 1024 / 1024 ) . " MB";
$vars_iiko = count_recursive($iiko_response);
glog("===== Размер iiko_response: ~" . $size_iiko);
glog('Переменных в iiko_response: ' . $vars_iiko);
unset($NOMCL);
//-------------------------------------------------------

// -------------------------------------------------------
// используем папки как категории (false, если PIZZAIOLO)
// -------------------------------------------------------
define("GROUPS_AS_CATEGORIES", ($IIKO_PARAMS->get())->current_nomenclature_type=='groups_as_categories'); 

// преобразуем ее в формат UNIMENU
$UNIMENU = new iiko_parser_to_unimenu("", $iiko_response);
unset($iiko_response);
$UNIMENU->parse(GROUPS_AS_CATEGORIES); 
$data = $UNIMENU->get_data();

//-------------------------------------------------------
// Размер промежуточных данных (UNIMENU):
$size_unimenu = strlen(json_encode($data, JSON_UNESCAPED_UNICODE));
$size_unimenu = round($size_unimenu / 1048576, 2) . " MB";
$vars_unimenu = count_vars($data);
glog("Размер UNIMENU: ~" . $size_unimenu);
glog('Переменных в UNIMENU: ' . $vars_unimenu);
//-------------------------------------------------------

// конвертим ее в текущий формат CHEFSMENU
$CHEFS_CONVERTER = new Conv_unimenu_to_chefs($data);
$chefsdata = $CHEFS_CONVERTER->convert()->get_data();
unset($CHEFS_CONVERTER, $data);

//-------------------------------------------------------
// Размер финальных данных:
$size_chefs = strlen(json_encode($chefsdata));
$size_chefs = round($size_chefs / 1048576, 2) . " MB";
$vars_chefs = count_vars($chefsdata);
glog("Размер chefsdata: ~" . $size_chefs);
glog('Переменных в chefsdata: ' . $vars_chefs);
//-------------------------------------------------------

$menu = $chefsdata["Menus"][$externalMenuId]??null;

$size_menu = strlen(json_encode($menu));
$size_menu = round($size_menu / 1048576, 2) . " MB";
$vars_menu = count_vars($menu);
glog("Размер menu ".$menu["name"].": ~" . $size_menu);
glog('Переменных в menu: ' . $vars_menu);

// $res = iiko_get_info($url,$headers,$params);
// $newExtmenuHash = md5(json_encode($res, JSON_UNESCAPED_UNICODE));
// $need2update = $currentExtmenuHash!==$newExtmenuHash;

$menu_description = [
        "summary_data"=>[
            "size_iiko"=>$size_iiko,
            "vars_iiko"=>$vars_iiko." переменных",            
            "size_unimenu"=>$size_unimenu,
            "vars_unimenu"=>$vars_unimenu." переменных",
            "size_chefs"=>$size_chefs,
            "vars_chefs"=>$vars_chefs." переменных",
            "size_menu"=>$size_menu,
            "vars_menu"=>$vars_menu." переменных",            
        ]    
];


// Проверка кодирования
$json_menu_data = json_encode($menu, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

if($json_menu_data === false) {
    $error = "JSON Error: " . json_last_error_msg();
    error_log($error);
    glogError($error);
    echo $callback.'('.json_encode(['error' => $error]).')';
    __errorjsonp($error);
}

// Проверка размера
if(strlen($json_menu_data) > 10000000) { // >10MB
    $error = "Oversized JSON: ".strlen($json_menu_data)." bytes";
    glogError($error);
    error_log($error);
    __errorjsonp($error);
}

// Перед сохранением в БД
// file_put_contents('before_db.json', $json_menu_data);


$menu_hash = md5($json_menu_data);

// сохраняем меню в базу данных
$m = new Smart_object("menu_imported");
$m->id_cafe = $id_cafe;
$m->id_external = $externalMenuId;
$m->source = "iiko_nomenclature";
$m->description = json_encode($menu_description, JSON_UNESCAPED_UNICODE);
$m->menu_hash = $menu_hash;
$m->formated = "original";
$m->data = base64_encode($json_menu_data);
$m->date_created = 'now()';
if(!$id_menu_saved = $m->save()){
    __errorjsonp("Ошибка сохранения меню в базу данных");
}

$answer = [
        "id-external"=>$externalMenuId, 
        "id-menu-saved"=>$id_menu_saved,
        "menu-hash"=>$menu_hash,
        "need-to-update"=>true,
        "description"=>$menu_description,
    ];

__answerjsonp($answer);

// Для глубокого подсчёта переменных в res from iiko:
function count_recursive(array $arr) {
    $count = 0;
    array_walk_recursive($arr, function() use (&$count) {
        $count++;
    });
    return $count;
}

// Подсчет переменных:
function count_vars($data) {
    $count = 0;
    foreach ($data as $key => $value) {
        $count++;
        if (is_array($value)) {
            $count += count_vars($value);
        }
    }
    return $count;
}



?>