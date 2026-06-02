<?php

define("BASEPATH",__FILE__);
require_once getenv('WORKDIR').'/config.php';

header('content-type: application/json; charset=utf-8');

$callback = $_REQUEST['callback'] ?? 'alert';
if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }

$path_to_images = $CFG->base_app_url."/pbl/i/";

$headStyle = file_get_contents('head-style.css');
$mainStyle = file_get_contents('main-style.css');
$mainStyleMobile = file_get_contents('main-style-mobile.css');

$headStyle = str_replace("%[path-to-images]%",$path_to_images,$headStyle);

$data = ['head-style' => $headStyle, 'main-style' => $mainStyle . "\n" . $mainStyleMobile, 'skins' => $CFG->public_skins];

echo $callback.'('.json_encode($data, JSON_UNESCAPED_UNICODE).')';


?>