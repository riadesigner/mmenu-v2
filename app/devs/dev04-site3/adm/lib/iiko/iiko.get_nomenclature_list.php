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
require_once WORK_DIR.APP_DIR.'core/class.iiko_nomenclature.php';
require_once WORK_DIR.APP_DIR.'core/class.iiko_parser_to_unimenu_v2.php';

session_start();
SQL::connect();


// GETTING NOMENCLATURE LIST

$user = User::from_cookie();
if(!$user || !$user->valid())__errorjsonp("Unknown user, ".__LINE__);

if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown id cafe, ".__LINE__);
$id_cafe = (int) $_POST['id_cafe'];
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);	
if($cafe->id_user !== $user->id) __errorjsonp("Not allowed, ".__LINE__);

$key = $cafe->iiko_api_key;
$IIKO_PARAMS = new Iiko_params($id_cafe, $key);
$orgId = ($IIKO_PARAMS->get())->current_organization_id;

// GETTING TOKEN FROM IIKO 
$url     = 'api/1/access_token';
$headers = ["Content-Type"=>"application/json"];
$params  = ["apiLogin" => $key];
$res = iiko_get_info($url,$headers,$params);
$token = $res['token'];

if( empty($orgId) || empty($token) ) __errorjsonp("not valid data, ".__LINE__);


// ---------------------------------------------------------------------
//  Загрузка номенклатуры из тестового файла (реальной выгрузки из iiko) 
// ---------------------------------------------------------------------
// $file_path = WORK_DIR.'/files/json-info-formated-full-new.json';
// [$error, $array] = getting_nomenc_from_test_file($file_path);
// if($error!==null ){ __errorjsonp($error); }
// $menus = get_menus($array);


// --------------------------------
//  GETTING NOMENCLATURE FROM IIKO
// --------------------------------
$NOMCL = new Iiko_nomenclature($orgId, $key);    
$NOMCL->reload();
$iiko_response = $NOMCL->get_data();

// ------------------------------------
//  Преобразуем ответ в формат UNIMENU
// ------------------------------------
// используем папки как категории
define("FOLDERS_AS_CATEGORY", false); 
$UNIMENU = new Iiko_parser_to_unimenu_v2($iiko_response);
$UNIMENU->parse(FOLDERS_AS_CATEGORY); 
$data = $UNIMENU->get_data();

$menus = [];
foreach ($data["Menus"] as $menu) {
    $menus[] = [
        "id" => $menu["menuId"],
        "name" => $menu["name"],
    ];
}

$current_oldway_menu_id = $menus[0]["id"] ?? null;
$current_oldway_menutype_id = "REAL_CATEGORY";


save_menus_to_iiko_params($id_cafe, $menus, $current_oldway_menu_id, $current_oldway_menutype_id);

__answerjsonp([
    "menus"=>$menus, 
    "current_oldway_menu_id"=>$current_oldway_menu_id,
    "current_oldway_menutype_id"=>$current_oldway_menutype_id,    
    "nomenclature_mode"=>1,
]);

// -----------------
//  LOCAL FUNCTIONS
// -----------------
// this function only for testing
function get_menus($array) {
    $menus = [];
    foreach ($array["groups"] as $group) {
        if($group["parentGroup"]===null &&
        $group["isGroupModifier"]===false){
            $menus[] = [
                'id' => $group['id'],
                'name' => $group['name'],
            ];
        }
    }
    return $menus;
}

function getting_nomenc_from_test_file($file_path): array {

    $error = null;

    // Проверяем существование файла
    if (!file_exists($file_path)) {
        $error = "Ошибка: Файл не найден.";        
    }

    // Читаем содержимое файла
    $json_data = file_get_contents($file_path);
    if ($json_data === false) {
        $error = "Ошибка: Не удалось прочитать файл.";        
    }

    // Декодируем JSON в ассоциативный массив
    $array = json_decode($json_data, true);

    // Проверяем на ошибки декодирования
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = "Ошибка в формате JSON: " . json_last_error_msg();        
    }

    // Проверяем на ошибки
    if( empty($array) || 
        !count($array) || 
        isset($array["error"]) ||
        !isset($array["groups"])
        ){
        $error = "Что-то пошло не так";
    }

    return [$error, $array];

}

function save_menus_to_iiko_params($id_cafe, $menus, $current_oldway_menu_id, $current_oldway_menutype_id): void {
    $iiko_params_collect = new Smart_collect("iiko_params", "where id_cafe='".$id_cafe."'");
    if($iiko_params_collect->full()){
        $iiko_params = $iiko_params_collect->get(0);			        
        if(count($menus)){
            $iiko_params->oldway_menus = json_encode($menus, JSON_UNESCAPED_UNICODE);                
            $iiko_params->current_oldway_menu_id = $current_oldway_menu_id;
            $iiko_params->current_oldway_menutype_id = $current_oldway_menutype_id;
            $iiko_params->nomenclature_mode = 1;
            $iiko_params->save();
        }else{            
            $iiko_params->oldway_menus = "";
            $iiko_params->current_oldway_menu_id = "";
            $iiko_params->current_oldway_menutype_id = "REAL_CATEGORY";            
            $iiko_params->nomenclature_mode = 0;
            $iiko_params->save();
        }
    }
}


?>