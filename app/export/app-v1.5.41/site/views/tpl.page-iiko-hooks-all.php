<?php
// IIKO HOOK FOR PRODUCTION
require_once WORK_DIR.APP_DIR.'core/class.iiko_hook.php';

$data = file_get_contents('php://input');
$IikoHook = new Iiko_hook($data);
$IikoHook->parse();

?>