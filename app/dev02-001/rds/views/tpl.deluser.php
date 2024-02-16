<?php
	
	require_once APP_DIR.'/core/class.rdsdeluser.php';

?><!DOCTYPE html>
<html>
<head>
<title>RDSAdmin</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
<meta http-equiv="Cache-Control" content="no-cache"/>

<base href="<?=$CFG->base_rds_url;?>">

<meta name="robots" content="noindex, follow"/>

<link rel="shortcut icon" href="../favicon.png" type="image/png">

<script type="text/javascript" src="../jquery/jquery.min.js"></script>
<script type="text/javascript" src="./rds/dist/app.js<?=$ver;?>"></script>


<script>

var URLSITE = {
	server:'<?=$CFG->http.$CFG->wwwroot;?>',
	base:'<?=$CFG->base_url;?>'
};

App && App();

</script>

<style>

body,html{
	width:100%;height:100%;min-height: 100%;
	position:relative;
}	
body{
    -webkit-tap-highlight-color: rgba(255, 255, 255, 0);
    -webkit-focus-ring-color: rgba(255, 255, 255, 0);
    outline: none; margin: 0;padding: 20px;
    font-family: arial;
}

* {	box-sizing: border-box; }

</style>

</head>
<body>


<?php

	$R = new Router();
	$user_email = $R->get(1);
	$delete_key = $R->get(2);

	echo "<h2>Удаление пользователя $user_email:</h2>";

	if(RDSDeluser::delete_all_about_user($user_email,$delete_key)){
		echo "<p>Ок, пользователь $user_email удален.</p>";
	}else{
		echo "<p>Err: ".RDSDeluser::get_err_message()."</p>";
	}
?>	



</body>
</html>