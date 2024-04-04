<?php

define("BASEPATH",__file__);
require_once getenv('WORKDIR').'/config.php';

header('content-type: application/json; charset=utf-8');

$callback = $_REQUEST['callback'] ?? 'alert';
if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }

$path_to_images = $CFG->base_url."/pbl/i/";

$filename = "head-style.css";
$file=fopen($filename,"r");
$headStyle = fread($file,filesize($filename));
fclose($file);

$filename = "main-style.css";
$file=fopen($filename,"r");
$mainStyle = fread($file,filesize($filename));
fclose($file);


$headStyle = str_replace("%[path-to-images]%",$path_to_images,$headStyle);

$data = ['head-style' => $headStyle, 'main-style' => $mainStyle, 'skins' => $CFG->public_skins];

echo $callback.'('.json_encode($data, JSON_UNESCAPED_UNICODE).')';

// var temp = "Строка<br>Строка<br>Строка";
// temp = temp.replace(/<br>/g, "\n");

// function htmlEncode(str) {
//     return String(str)
//             .replace(/&/g, '&amp;')
//             .replace(/"/g, '&quot;')
//             .replace(/'/g, '&#39;')
//             .replace(/</g, '&lt;')
//             .replace(/>/g, '&gt;');
// }

?>