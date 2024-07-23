<?php

define("BASEPATH",__file__);

require_once 'config.php';
require_once 'vendor/autoload.php';

require_once WORK_DIR.APP_DIR.'core/class.tg_hook.php';

$data = json_decode(file_get_contents('php://input'), true);

$Tgh = new Tg_hook($data);
$Tgh->parse();

// TG BOT API HELP
// https://telegram-bot-sdk.readme.io/reference/setwebhook

// SET WEBHOOK
// https://api.telegram.org/bot5864349836:AAGi-PI_20yJy8sIrPpU-oOHnIzlYJmjIbA/setwebhook?url=https://riadesign.ru/ext/tg/index.php

// CHECK BOT 
// https://api.telegram.org/bot{token}/getWebhookInfo
// https://api.telegram.org/bot5864349836:AAGi-PI_20yJy8sIrPpU-oOHnIzlYJmjIbA/getWebhookInfo

// {
//     "ok": true,
//     "result": {
//     "url": "https://chefsmenu.ru/tg-hook.php",
//     "has_custom_certificate": false,
//     "pending_update_count": 0,
//     "max_connections": 40,
//     "ip_address": "62.113.110.82"
//     }
// }

// CHECK BOT AUTH 
// https://api.telegram.org/bot{token}/getMe
// https://api.telegram.org/bot5864349836:AAGi-PI_20yJy8sIrPpU-oOHnIzlYJmjIbA/getMe

// {
//     "ok": true,
//     "result": {
//     "id": 5864349836,
//     "is_bot": true,
//     "first_name": "Chefsmenu Cart",
//     "username": "chefsmenu_cart_bot",
//     "can_join_groups": true,
//     "can_read_all_group_messages": false,
//     "supports_inline_queries": false,
//     "can_connect_to_business": false
//     }
// }

?>