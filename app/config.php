<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define("J_ENV_LOCAL",true); 
define("J_ENV_TEST",true); // write logs to file;
define("J_ENV_BETA",false); // show string 'beta version' on top site;
define("J_ENV_MAINTENANCE",false); 

#[AllowDynamicProperties]
class glb_object{};
$CFG = new glb_object();

// MULTI-LANGS VERSION
$CFG->version = "02-001.01"; // updated to php 8.1
define("APP_DIR","dev02-001"); // path to dev version

require_once APP_DIR.'/core/config-network.php';
require_once APP_DIR.'/core/config-site-links.php';
require_once APP_DIR.'/core/config-limits.php';
require_once APP_DIR.'/core/config-public-skins.php';
require_once APP_DIR.'/core/config-inputs-length.php';

