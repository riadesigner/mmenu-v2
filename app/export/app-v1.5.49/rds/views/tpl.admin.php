<?php

define("ADMIN_URL","{$CFG->http}{$CFG->admin_sub}.{$CFG->wwwroot}"); 

?><!DOCTYPE html>
<html>
<head>
<title>RDSAdmin</title>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
<meta http-equiv="Cache-Control" content="no-cache"/>

<link rel="shortcut icon" href="<?=$CFG->base_url;?>favicon.png" type="image/png">

<base href="<?=$CFG->base_rds_url;?>">

<meta name="robots" content="noindex, follow"/>

<link rel="stylesheet" href="./rds/css/style.css<?=$ver;?>" type="text/css">


<script type="text/javascript" src="<?=$CFG->base_url;?>jquery/jquery.min.js"></script>
<script type="text/javascript" src="./rds/dist/app.js<?=$ver;?>"></script>

<script>

var URLSITE = {
	server:'<?=$CFG->http.$CFG->wwwroot;?>',
	base:'<?=$CFG->base_app_url;?>'
};

App && App();

</script>


</head>
<body >


<?php
	// log out and other main menu
	include 'tpl.admin_top_line.php';
?>

<?php

	$q = "SELECT count(id) as total_users FROM users";	
	$res = SQL::query($q);
	if($res && $res->num_rows){
		$fetch = $res->fetch_object();
		$total_users = $fetch->total_users;
	}else{
		$total_users = 0;
	}
    

	$q = "SELECT count(id) as total_cafe FROM cafe";	
	$res = SQL::query($q);
	if($res && $res->num_rows){
		$fetch = $res->fetch_object();    	
		$total_cafe = $fetch->total_cafe;
	}else{
		$total_cafe = 0;
	}

?>

<div class="page-content">

<div class="top-menu">
	Пользователи (<?=$total_users;?>) | <a href="<?=ADMIN_URL;?>/#">Cafe (<?=$total_cafe;?>)</a>;
</div>

<?php


	$users = new Smart_collect("users","","ORDER BY regdate");


	
	if($users&&$users->full()){

		echo "<ul class='rds-user-list'>";

		foreach ($users->get() as $user) {

			$str = implode("",["<li data-id-user='{userId}'>", "<div class='rds-user-list__user-info'>{{user}}</div>", "<div class='rds-user-list__lifetime'>{{lifeTime}}</div>", "<div class=''>{{statusTestArchive}}</div>", "<div class='rds-user-list__active'>{{linkToNewContract}}</div>", "<div class='rds-user-list__cafe-info'>{{cafeTitle}}</div>", "<div class='rds-user-list__buttons'>{{linkToDelete}}</div>", "</li>"]);

			$regdate = new DateTime($user->regdate);
			$regdate = $regdate->format("d-m-Y");
			$updated_date = new DateTime($user->regdate);
			$updated_date = $updated_date->format("d-m-Y");
			
			$str=preg_replace('/{{userId}}/', (string) $user->id, $str);			

			$strUser = implode("",["ID:{$user->id}, ", "<a href='mailto:{$user->email}'>{$user->email},</a> <br>", "reg/upd: {$regdate}/{$updated_date}, {$user->lang}"]);			
			
			$str=preg_replace('/{{user}}/', $strUser, (string) $str);

			$cafes = new Smart_collect("cafe","WHERE id_user={$user->id}");	
			
			if($cafes&&$cafes->full()){
				
				$cafe = $cafes->get(0);
				$cafeLink = $CFG->http.$CFG->wwwroot."/cafe/".$cafe->uniq_name;

				// Calculate life time (days)
				$created_time = strtotime((string) $cafe->created_date);
				$now_time = time();
				$datediff= $now_time-$created_time;
				$days = floor($datediff / (60 * 60 * 24));				
				$str=preg_replace('/{{lifeTime}}/', "{$days} дн.", (string) $str);

				if(!$cafe->sample){					

					
					// $contract = new Contract($cafe);
					// if($contract->full()){						
					// 	$total_contracts = $contract->total();

					// 	foreach ($contract->get() as $co) {
							
					// 		$regdate = new DateTime($co->regdate);
					// 		$regdate = $regdate->format("d-m-Y");
							
					// 		$expire_on = strtotime($co->expire_on);
					// 		$now_time = time();
					// 		$datediff= $expire_on-$now_time;
					// 		$daysLeft = floor($datediff / (60 * 60 * 24));
							
					// 		$str = preg_replace('/{{contractName}}/', $co->contract_name, $tpl);
					// 		$str = preg_replace('/{{contractRegdate}}/', "от ".$regdate, $str);
					// 		$str = preg_replace('/{{daysLeft}}/',  " осталось дн. ".$daysLeft, $str);				
					// 		echo "$str";
					// 	}

					// }else{
					// 	$total_contracts = 0;			
					// }					

					$link_param = "data-id-cafe='{$cafe->id}' href='".ADMIN_URL."/add-contract/{$cafe->uniq_name}'";
					$CAFE_STATUS = (int) $cafe->cafe_status;
					
					switch ( (int) $cafe->cafe_status) {
						case 0:							
							$linkToNewContract = "<a {$link_param} >Создать договор</a>"; 
							$statusTestArchive ="тест<br><a class='to-archive' data-id-cafe='{$cafe->id}' href='".ADMIN_URL."/#'><nobr>в архив</nobr></a>";
							break;
						case 1:
							$linkToNewContract = "-"; 
							$statusTestArchive ="в архиве<br><a class='from-archive' data-id-cafe='{$cafe->id}' href='".ADMIN_URL."/#'><nobr>из архива</nobr></a>";
							break;						
						case 2:
							$linkToNewContract = "<a {$link_param} ><span class='rds-user-list__flag-contract'>Есть договора</span></a>";
							$statusTestArchive = "–";
							break;
					}

					$linktoDelete = "<a class='del-user' href='#' data-id-user='{$user->id}'>
					<span>X</span></a>";

				}else{
					$linktoDelete = "–";
					$linkToNewContract = "–"; 
					$statusTestArchive = "–";
				}
								
				$str=preg_replace('/{{linkToDelete}}/', $linktoDelete, (string) $str);
				$str=preg_replace('/{{linkToNewContract}}/', (string) $linkToNewContract, (string) $str);
				$str=preg_replace('/{{statusTestArchive}}/', (string) $statusTestArchive, (string) $str);
				$sample = !empty(trim((string) $cafe->sample))? ", <span style='background:green;color:white;'>[образец, $cafe->sample]</span>":"";				
				$str=preg_replace('/{{cafeTitle}}/', $cafe->cafe_title.", <a target='_blank' href='{$cafeLink}'>".$cafe->uniq_name."</a>".$sample, (string) $str);

			}
			
			echo $str;
		}
		echo "</ul>";
	}else{
		echo "Пользователей нет";
	}

?>

</div>
</body>
</html>