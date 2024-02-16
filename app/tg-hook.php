<?php

define("BASEPATH",__file__);

require_once 'config.php';
require_once 'vendor/autoload.php';

require_once APP_DIR.'/core/class.tg_hook.php';

$data = json_decode(file_get_contents('php://input'), true);

$Tgh = new Tg_hook($data);
$Tgh->parse();



// set webhook
// https://api.telegram.org/bot5864349836:AAGi-PI_20yJy8sIrPpU-oOHnIzlYJmjIbA/setwebhook?url=https://riadesign.ru/ext/tg/index.php

?>