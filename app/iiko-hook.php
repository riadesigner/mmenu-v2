<?php

define("BASEPATH",__file__);
require_once 'config.php';
require_once 'vendor/autoload.php';

require_once WORK_DIR.APP_DIR.'core/class.iiko_hook.php';

$data = file_get_contents('php://input');
if(!$data){
    echo "no data!";
    exit();
}

$decoded = json_decode($data, true);
$IikoHook = new Iiko_hook($decoded);
$IikoHook->parse();



?>