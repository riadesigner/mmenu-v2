<!DOCTYPE html>
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
    outline: none; margin: 0;padding: 0;    
}

* {	box-sizing: border-box; }

.enter{
	width: 100%;
	text-align: center;
	padding:0 5%;
}
.enter-container{
	text-align: left;
	border:2px solid gold;
	border-radius:10px;
	margin:50px auto;
	display: inline-block;
	padding: 10px 5%;
	width: 90%;
	max-width:300px;
}
.enter label {font: 13px arial;margin:10px 0 2px 0;display: inline-block;}
.enter input {width: 100%; max-width:100%; font:16px arial;border:1px solid gray;padding:5px;outline: none;}
.enter button{
	margin: 15px 0 10px auto;background: gold;border:0;padding:10px 30px;
	display: inline-block;border-radius: 5px;outline: none; cursor: pointer;
}
.enter button:active{background:yellow;}

</style>

</head>
<body>


<div class="enter">	
	<div class="enter-container">
		<label for="admin_login">Login:</label><input type="text" id="admin_login" name="admin_login" >
		<label for="admin_pass">Pass:</label><input type="password" id="admin_pass" name="admin_pass" >
		<div style="text-align:right">
			<button>Войти</button>
		</div>
	</div>
</div>


</body>
</html>