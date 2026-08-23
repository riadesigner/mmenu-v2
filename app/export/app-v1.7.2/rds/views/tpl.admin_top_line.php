<?php

$linkAdmin = $CFG->http.$CFG->admin_sub.'.'.$CFG->wwwroot;

?>
<div class="rds-site-header">
	<h1 class="rds-site-header-title"><a href="<?=$linkAdmin;?>">Admin</a></h1>
	<a class="rds-site-header-btn-exit" href="<?=$linkAdmin;?>#">выйти</a>
</div>
<div class="rds-loader">Загрузка...</div>
<div class="rds-message">Ответ сервера</div>
<div class="rds-errmessage">Ответ сервера</div>
