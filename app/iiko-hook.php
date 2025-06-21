<?php

define("BASEPATH",__file__);
require_once 'config.php';
require_once WORK_DIR.APP_DIR.'core/common.php';
require_once WORK_DIR.APP_DIR.'core/class.iiko_hook.php';


$data = json_decode(file_get_contents('php://input'), true);

$IikoHook = new Iiko_hook($data);
$IikoHook->parse();

echo "ok";

?>