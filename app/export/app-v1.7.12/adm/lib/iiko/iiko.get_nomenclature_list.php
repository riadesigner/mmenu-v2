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

require_once WORK_DIR.APP_DIR.'core/class.iiko_nomenclature_divider.php';
require_once WORK_DIR.APP_DIR.'core/class.iiko_nomenclature_loader.php';
require_once WORK_DIR.APP_DIR.'core/class.iiko_parser_to_unimenu.php';

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

$api_key = $cafe->iiko_api_key;
$IIKO_PARAMS = new Iiko_params($id_cafe, $api_key);
$id_org = ($IIKO_PARAMS->get())->current_organization_id;

if( empty($id_org) ) __errorjsonp("not valid data, ".__LINE__);

// --------------------------------
//  GETTING NOMENCLATURE FROM IIKO
// --------------------------------
$path_to_temp_exports = WORK_DIR.'tmp';
$NOMCL_LOADER = new Iiko_nomenclature_loader($id_org, $api_key, $path_to_temp_exports);    
$NOMCL_LOADER->reload(true, true);
$json_file_path = $NOMCL_LOADER->get_file_path();        

glog("IIKO. Загружена номенклатура, и сохранена в файл: $json_file_path");

$NOMENCL_DIVIDER = new Iiko_nomenclature_divider($json_file_path);
$temp_file_names = $NOMENCL_DIVIDER->get();

glog("IIKO. Номенклатура разделена на файлы: ".print_r($temp_file_names, true));

// -------------------------------------------------------
// используем папки как категории (false, если PIZZAIOLO)
// -------------------------------------------------------
define("GROUPS_AS_CATEGORIES", ($IIKO_PARAMS->get())->current_nomenclature_type=='groups_as_categories'); 

$PARSER_TO_UNIMENU = new Iiko_parser_to_unimenu($temp_file_names);    
$PARSER_TO_UNIMENU->parse(GROUPS_AS_CATEGORIES);
$data = $PARSER_TO_UNIMENU->get_data();

glog("IIKO. Парсинг и перевод в UNIMENU выполнен, всего меню: ".$data['TotalMenus']);

$NOMENCL_DIVIDER->clean();
$NOMCL_LOADER->clean(); 

glog('IIKO. Все меню из номенклатуры: ');
$menus = [];
foreach ($data["Menus"] as $menu) {
    $menus[] = [
        "id" => $menu["menuId"],
        "name" => $menu["name"],
    ];
}
glog(print_r($menus, 1));

$current_oldway_menu_id = $menus[0]["id"] ?? null;

// get type menu structure: REAL_CATEGORIES or GROUPS_AS_CATEGORIES
$current_nomenclature_type = ($IIKO_PARAMS->get())->current_nomenclature_type; 

save_menus_info_to_iiko_params($id_cafe, $menus, $current_oldway_menu_id, $current_nomenclature_type);

glog('IIKO. Информция о меню сохранена в базу данных.');

$answer = [
    "menus"=>$menus, 
    "current_oldway_menu_id"=>$current_oldway_menu_id,
    "current_nomenclature_type"=>$current_nomenclature_type,    
    "nomenclature_mode"=>1,
];

glog("answer: ".print_r($answer, 1));
__answerjsonp($answer);

function save_menus_info_to_iiko_params($id_cafe, $menus, $current_oldway_menu_id, $current_nomenclature_type): void {
    $iiko_params_collect = new Smart_collect("iiko_params", "where id_cafe='".$id_cafe."'");
    if($iiko_params_collect->full()){
        $iiko_params = $iiko_params_collect->get(0);			        
        if(count($menus)){
            $iiko_params->oldway_menus = json_encode($menus, JSON_UNESCAPED_UNICODE);                
            $iiko_params->current_oldway_menu_id = $current_oldway_menu_id;
            $iiko_params->current_nomenclature_type = $current_nomenclature_type;
            $iiko_params->nomenclature_mode = 1;
            $iiko_params->save();
        }else{            
            $iiko_params->oldway_menus = "";
            $iiko_params->current_oldway_menu_id = "";
            $iiko_params->current_nomenclature_type = "real_categories";            
            $iiko_params->nomenclature_mode = 0;
            $iiko_params->save();
        }
    }
}


?>