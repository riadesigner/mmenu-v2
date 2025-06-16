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
require_once WORK_DIR.APP_DIR.('core/class.Iiko_nomenclature.php');
require_once WORK_DIR.APP_DIR.('core/class.iiko_parser_to_unimenu_v2.php');
require_once WORK_DIR.APP_DIR.('core/class.conv_unimenu_to_chefs.php');

session_start();
SQL::connect();


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

// $iiko_params_collect = new Smart_collect("iiko_params", "where id_cafe='".$cafe->id."'");
// if(!$iiko_params_collect || !$iiko_params_collect->full()) __errorjsonp("--cant find iiko params for cafe ".$cafe->id);
// $iiko_params = $iiko_params_collect->get(0);
// $organization_id = $iiko_params->current_organization_id;
$key = $cafe->iiko_api_key;
$IIKO_PARAMS = new Iiko_params($id_cafe, $key);
$orgId = ($IIKO_PARAMS->get())->current_organization_id;

// -------------------------------------------------------
// загружаем номенклатуру из тестового файла
// (реальный ответ от iiko, API 1, получение номенклатуры)
// -------------------------------------------------------
// $file_path = WORK_DIR.'/files/json-info-formated-full-new.json';
// $iiko_response = get_response_from_test_file($file_path);
// $menu_id = "9da77ff8-862d-45e4-a7f2-a5117910fa66";

// -------------------------------------------------------
// получаем номенклатуру с сервера iiko
// -------------------------------------------------------
$NOMCL = new Iiko_nomenclature($orgId, "", $token);    
$NOMCL->reload();
$iiko_response = $NOMCL->get_data();

glog("IIKO_RESPONSE  ========== ".print_r($iiko_response,1));


// -------------------------------------------------------
// используем папки как категории (false, если PIZZAIOLO)
// -------------------------------------------------------
define("GROUPS_AS_CATEGORIES", ($IIKO_PARAMS->get())->current_nomenclature_type=='groups_as_categories'); 

// преобразуем ее в формат UNIMENU
$UNIMENU = new Iiko_parser_to_unimenu_v2($iiko_response);
$UNIMENU->parse(GROUPS_AS_CATEGORIES); 
$data = $UNIMENU->get_data();

glog("UNIMENU  ========== ".print_r($data,1));

// конвертим ее в текущий формат CHEFSMENU
$CHEFS_CONVERTER = new Conv_unimenu_to_chefs($data);
$chefsdata = $CHEFS_CONVERTER->convert()->get_data();

$menu = $chefsdata["Menus"][$externalMenuId]??null;

glog("CHEFSDATA ========== ".print_r($chefsdata,1));

// $res = iiko_get_info($url,$headers,$params);
// $newExtmenuHash = md5(json_encode($res, JSON_UNESCAPED_UNICODE));
// $need2update = $currentExtmenuHash!==$newExtmenuHash;

$answer = [
        "menu"=>$menu,
        "menu-hash"=>"1",
        "need-to-update"=>true
    ];

__answerjsonp($answer);



function get_response_from_test_file($file_path): array{
    // Проверяем существование файла
    if (!file_exists($file_path)) {
        __errorjsonp("Ошибка: Файл не найден.");
    }
    // Читаем содержимое файла
    $json_data = file_get_contents($file_path);
    if ($json_data === false) {
        __errorjsonp("Ошибка: Не удалось прочитать файл.");
    }
    // Декодируем JSON в ассоциативный массив
    $iiko_response = json_decode($json_data, true);

    return $iiko_response;
}

?>