<?php

define("BASEPATH",__file__);
require_once 'config.php';

header('Content-Type: text/javascript');

echo "console.log('test 555');";

// $app_server = $CFG->base_url;
// $app_base = $CFG->base_app_url;
// $app_version = $CFG->version;

// $filename = "loader-tpl.js";
// $file=fopen($filename,"r");
// $loader = fread($file,filesize($filename));
// fclose($file);

// $loader = str_replace("%[app_server]%",$app_server,$loader);
// $loader = str_replace("%[app_base]%",$app_base,$loader);
// $loader = str_replace("%[app_version]%",$app_version,$loader);

// echo $loader;

?>