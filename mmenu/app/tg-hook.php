<?php

define("BASEPATH",__file__);
require_once 'config.php';
require_once 'vendor/autoload.php';

require_once WORK_DIR.APP_DIR.'core/class.tg_hook.php';

$data = json_decode(file_get_contents('php://input'), true);

$Tgh = new Tg_hook($data);
$Tgh->parse();


?>