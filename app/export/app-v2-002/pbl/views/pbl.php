<?php
define("BASEPATH",__file__);

require_once '../../../config.php';
require_once '../../core/common.php';

header('content-type: application/json; charset=utf-8');

$callback = $_REQUEST['callback'] ?? 'alert';
if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }

$filename = "pbl.html";
$file=fopen($filename,"r");
$pbl_tpl = fread($file,filesize($filename));
fclose($file);

$path_to_images = $CFG->base_url."pbl/i/";

$pbl_tpl = str_replace('%[app-version]%',$CFG->version,$pbl_tpl);
$pbl_tpl = str_replace('%[home-url]%',$CFG->http.$CFG->wwwroot,$pbl_tpl);
$pbl_tpl = str_replace("%[path-to-images]%",$path_to_images, $pbl_tpl);

$data = [['app' => $pbl_tpl, 'ver' => $CFG->version]];

echo $callback.'('.json_encode($data, JSON_UNESCAPED_UNICODE).')';


?>