<?php

header('content-type: application/json; charset=utf-8');
$callback = $_REQUEST['callback'] ?? 'alert';
if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }	

define("BASEPATH",__file__);



require_once '../../../../config.php';
require_once '../../../../vendor/autoload.php';

require_once '../../../core/common.php';	

require_once '../../../core/class.sql.php';
 
require_once '../../../core/class.smart_object.php';
require_once '../../../core/class.smart_collect.php';
require_once '../../../core/class.user.php';


session_start();
SQL::connect();

$id_cafe = (int) $_POST['id_cafe'];

if(!isset($_POST['id_cafe']) || empty($_POST['id_cafe']) ) __errorjsonp("Unknown cafe, ".__LINE__);

$id_cafe = (int) $_POST['id_cafe'];
$cafe = new Smart_object("cafe",$id_cafe);
if(!$cafe->valid())__errorjsonp("Unknown cafe, ".__LINE__);

$k = $cafe->iiko_api_key;
if(empty($k)) __errorjsonp("Cant find iiko api for the cafe, ".__LINE__);

// GETTING TOKEN FROM IIKO 
$url     = 'api/1/access_token';
$headers = ["Content-Type"=>"application/json"];
$params  = ["apiLogin" => $k];

$res = iiko_get_info($url,$headers,$params);

__answerjsonp($res);


?>