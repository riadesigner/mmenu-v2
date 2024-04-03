<?php
define("BASEPATH",__file__);

error_reporting(E_ALL);
ini_set('display_errors', 1);
              
require_once 'config.php';

require_once APP_DIR.'/core/class.sql.php';
require_once APP_DIR.'/core/common.php';
require_once APP_DIR.'/core/class.smart_object.php';
require_once APP_DIR.'/core/class.smart_collect.php';
require_once APP_DIR.'/core/class.router.php';

require_once APP_DIR.'/core/class.app.php'; // delete users and cafe
require_once APP_DIR.'/core/class.contract.php'; // all about contracts
require_once APP_DIR.'/core/class.rdsadmin.php';

require_once APP_DIR.'/core/class.user.php';
require_once APP_DIR.'/core/class.site.php';
require_once APP_DIR.'/core/class.password.php';
require_once APP_DIR.'/core/class.subdomain.php';
require_once APP_DIR.'/core/class.account.php';

require_once APP_DIR.'/core/class.email.php';

require_once APP_DIR.'/core/class.lng.php';
require_once APP_DIR.'/core/class.lng_prefer.php';

session_set_cookie_params(0, '/', '.'.$CFG->wwwroot, $CFG->session_secure, false);

session_start();
SQL::connect();

$ver = "?ver=".$CFG->version;

Site::init();
J_ENV_MAINTENANCE && Site::add_body_classes('maintenance-mode');
J_ENV_BETA && Site::add_body_classes('beta-mode');

include (APP_DIR.Site::get_template());


?>