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
require_once WORK_DIR.APP_DIR.'core/class.iiko_extmenu_loader.php';
require_once WORK_DIR.APP_DIR.'core/class.iiko_extmenu_to_chefs.php';

session_start();
SQL::connect();


// GETTING EXTERNAL MENU BY ID / API 2 

if(!isset($_POST['externalMenuId'])){
    __errorjsonp(["error"=>"unknown external menu id"]);
    exit();
}
if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ){
    __errorjsonp(["error"=>"--its need to know id_cafe"]);
    exit();
}

$current_menu_id = $_POST['externalMenuId'];
$currentExtmenuHash = $_POST['currentExtmenuHash'] ?? "";

$id_cafe = (int) $_POST['id_cafe'];
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);

$api_key = $cafe->iiko_api_key;
$IIKO_PARAMS = new Iiko_params($id_cafe, $api_key);
$id_org = ($IIKO_PARAMS->get())->current_organization_id;

if( empty($id_org) ) __errorjsonp("not valid data");

[ $json_menu_data, $json_meta_info ] = load_and_parse_menu( $id_org, $api_key, $current_menu_id, $currentExtmenuHash );

$new_menu_hash = md5($json_menu_data);
$need2update = $currentExtmenuHash!==$new_menu_hash;

// -----------------
// SAVING MENU TO DB
// -----------------
$m = new Smart_object("menu_imported");
$m->id_cafe = $id_cafe;
$m->id_external = $current_menu_id;
$m->source = "iiko_external_menu";
$m->description = $json_meta_info;
$m->menu_hash = $new_menu_hash;
$m->formated = "original";
$m->data = base64_encode($json_menu_data);
$m->date_created = 'now()';
if(!$id_menu_saved = $m->save()){
    __errorjsonp("Ошибка сохранения меню в базу данных");
}

__answerjsonp([    
    "need-to-update"=>$need2update,
    "id-menu-saved"=>$id_menu_saved,
    "new-menu-hash"=>$new_menu_hash,
]);


function load_and_parse_menu($id_org, $api_key, $current_menu_id, $currentExtmenuHash){

    // ----------------------------
    // 1. GETTING EXTMENU FROM IIKO
    // ----------------------------    
    $EXTMENU_LOADER = new Iiko_extmenu_loader($id_org, $api_key, $current_menu_id);    
    $EXTMENU_LOADER->reload();
    $extmenu_data = $EXTMENU_LOADER->get_data();

    if(isset($extmenu_data['error'])){
        glog('errorDescription '. $extmenu_data['errorDescription']);
         __errorjsonp($extmenu_data['errorDescription']);
    }

    $chefs_data = Iiko_extmenu_to_chefs::parse($extmenu_data);
    $chefs_data_export = [
            "SourceMenu" => "EXT_MENU",
            "TypeMenu" => "CHEFSMENU",
            "Menu" => $chefs_data,            
        ];

    // ------------------
    // 2. CONVERT TO JSON
    // ------------------    
    $json_meta_info = json_encode([
                "iiko_loaded"=>$EXTMENU_LOADER->get_info(),
            ], JSON_UNESCAPED_UNICODE);
   
    $json_menu_data = json_encode($chefs_data_export, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

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