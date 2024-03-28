<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'vendor/autoload.php';

$parent = dirname(__DIR__);
$dotenv = Dotenv\Dotenv::createImmutable($parent);
$dotenv->load();

define("J_ENV_LOCAL", $_ENV['CURRENT_ENV_LOCAL']==='true'? true : false); 
define("J_ENV_TEST", $_ENV['CURRENT_ENV_TEST']==='true'? true : false); // maximum writes logs to file;
define("J_ENV_BETA", $_ENV['CURRENT_ENV_BETA']==='true'? true : false); // show string 'beta version' on top site;
define("J_ENV_MAINTENANCE", $_ENV['CURRENT_ENV_MAINTENANCE']==='true'? true : false); 

#[AllowDynamicProperties]
class glb_object{};
$CFG = new glb_object();

// MULTI-LANGS VERSION
$CFG->version = "02-001.01"; // updated to php 8.x
define("APP_DIR","dev02-001"); // path to dev version

require_once APP_DIR.'/core/config-network.php';
require_once APP_DIR.'/core/config-site-links.php';
require_once APP_DIR.'/core/config-limits.php';
require_once APP_DIR.'/core/config-public-skins.php';
require_once APP_DIR.'/core/config-inputs-length.php';

