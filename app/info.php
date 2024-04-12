<?php
define("BASEPATH",__file__);

error_reporting(E_ALL);
ini_set('display_errors', 1);
              
require_once 'config.php';

$ver = "?ver=".$CFG->version;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>    
    <script type="text/javascript" src="<?=$CFG->base_url;?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?=$CFG->base_url;?>loader.js<?=$ver;?>"></script>
</head>
<body>
test
</body>
</html>

