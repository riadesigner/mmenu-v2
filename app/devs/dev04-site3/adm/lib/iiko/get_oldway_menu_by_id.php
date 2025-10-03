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
require_once WORK_DIR.APP_DIR.'core/class.iiko_nomenclature_loader.php';
require_once WORK_DIR.APP_DIR.'core/class.iiko_nomenclature_divider.php';
require_once WORK_DIR.APP_DIR.'core/class.iiko_parser_to_unimenu.php';
require_once WORK_DIR.APP_DIR.'core/class.conv_unimenu_to_chefs.php';


session_start();
SQL::connect();


// GETTING OLDWAY MENU BY ID / API 1, (FROM NOMENCLATURE) 

if(!isset($_POST['externalMenuId'])){
    __errorjsonp(["error"=>"unknown external menu id"]);
    exit();
}
if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ){
    __errorjsonp(["error"=>"--its need to know id_cafe"]);
    exit();
}

$current_menu_id = $_POST['externalMenuId'];
// $currentExtmenuHash = $_POST['currentExtmenuHash'];

$id_cafe = (int) $_POST['id_cafe'];
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe->valid())__errorjsonp("Unknown cafe");

$api_key = $cafe->iiko_api_key;
$IIKO_PARAMS = new Iiko_params($id_cafe, $api_key);
$id_org = ($IIKO_PARAMS->get())->current_organization_id;

if( empty($id_org) ) __errorjsonp("not valid data");

// getting structure type of menu: 
// REAL_CATEGORIES or GROUPS_AS_CATEGORIES
define("GROUPS_AS_CATEGORIES", ($IIKO_PARAMS->get())->current_nomenclature_type=='groups_as_categories');

[ $json_menu_data, $json_meta_info ] = load_and_parse_menu( $id_org, $api_key, $current_menu_id, GROUPS_AS_CATEGORIES );

$new_menu_hash = md5($json_menu_data);
// $need2update = $currentExtmenuHash!==$new_menu_hash;

// -----------------
// SAVING MENU TO DB
// -----------------
$m = new Smart_object("menu_imported");
$m->id_cafe = $id_cafe;
$m->id_external = $current_menu_id;
$m->source = "iiko_nomenclature";
$m->description = $json_meta_info;
$m->menu_hash = $new_menu_hash;
$m->formated = "original";
$m->data = base64_encode($json_menu_data);
$m->date_created = 'now()';
if(!$id_menu_saved = $m->save()){
    __errorjsonp("Ошибка сохранения меню в базу данных");
}

__answerjsonp([
    "id-menu-saved"=>$id_menu_saved,
    "new-menu-hash"=>$new_menu_hash,
]);


function load_and_parse_menu($id_org, $api_key, $current_menu_id, $groups_as_categories){    

    glog("IIKO. Загрузка и парсинг номенклатуры");

    // ---------------------------------
    // 1. GETTING NOMENCLATURE FROM IIKO
    // ---------------------------------
    $path_to_temp_exports = WORK_DIR.'tmp';

    // Проверяем, существует ли директория
    // if (!is_dir($path_to_temp_exports)) {
    //     // Рекурсивно создаём директорию и устанавливаем права 0755
    //     mkdir($path_to_temp_exports, 0777, true);
    // }

    $NOMCL_LOADER = new Iiko_nomenclature_loader($id_org, $api_key, $path_to_temp_exports);    
    $NOMCL_LOADER->reload(true, true);
    $json_file_path = $NOMCL_LOADER->get_file_path();        

    glog("IIKO. Загружена номенклатура, и сохранена в файл: $json_file_path");

    // ------------------------------------------------------------------
    // 2. ДЕЛИМ ФАЙЛ НОМЕНКЛАТУРЫ НА ЧАСТИ И СОХРАНЯЕМ ВО ВРЕМЕННЫЕ ФАЙЛЫ
    // ------------------------------------------------------------------
    $NOMENCL_DIVIDER = new Iiko_nomenclature_divider($json_file_path);
    $temp_file_names = $NOMENCL_DIVIDER->get();

    glog("IIKO. Номенклатура разделена на файлы: ".print_r($temp_file_names, true));

    // ---------------------------------
    // 3. ПАРСИМ ФАЙЛЫ И СОЗДАЕМ UNIMENU
    // ---------------------------------    

    $PARSER_TO_UNIMENU = new Iiko_parser_to_unimenu($temp_file_names);    
    $PARSER_TO_UNIMENU->parse($groups_as_categories);
    $data = $PARSER_TO_UNIMENU->get_data();

    glog("IIKO. Парсинг и перевод в UNIMENU выполнен, всего меню: ".$data['TotalMenus']);  
    
    $NOMENCL_DIVIDER->clean();
    $NOMCL_LOADER->clean();     

    // Получаем список всех меню из номенклатуры
    glog('IIKO. Все меню из номенклатуры: ');
    $menus = [];
    foreach ($data["Menus"] as $menu) {
        $menus[] = [
            "id" => $menu["menuId"],
            "name" => $menu["name"],
        ];
    }     
    glog(print_r($menus, 1));    
    
    // ------------------------------------
    // 4. КОНВЕРТИРУЕМ UNIMENU -> CHEFSMENU
    // ------------------------------------      
    $selected_unimenu = $data["Menus"][$current_menu_id]?? null;
    if($selected_unimenu === null) {
        $errMsg = "IIKO. Не найдено меню с id: $current_menu_id";
        glog($errMsg);
        __errorjsonp($errMsg);
    }
    $CHEFS_CONVERTER = new Conv_unimenu_to_chefs($selected_unimenu);
    $chefsdata = $CHEFS_CONVERTER->get_data();
    
    glog(sprintf("<p>Конвертирование меню <strong>%s</strong> в CHEFS выполнено</p>", $data["Menus"][$current_menu_id]['name']));
    
    // ------------------
    // 5. CONVERT TO JSON
    // ------------------
    
    $json_meta_info = json_encode([
                "iiko_loaded"=>"info 1",
                "unimenu"=>"info 2",            
                "chefs"=>"info 3",      
            ], JSON_UNESCAPED_UNICODE);
   
    $json_menu_data = json_encode($chefsdata, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    // Проверка кодирования json
    if($json_menu_data === false) {
        $error = "JSON Error: " . json_last_error_msg();
        glogError($error);        
        __errorjsonp($error);
    }

    // Проверка размера
    if(strlen($json_menu_data) > 10000000) { // >10MB
        $error = "Oversized JSON: ".strlen($json_menu_data)." bytes";
        glogError($error);
        __errorjsonp($error);
    }

    return [$json_menu_data, $json_meta_info];

}

?>