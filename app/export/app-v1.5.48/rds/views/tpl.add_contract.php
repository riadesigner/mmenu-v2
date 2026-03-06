<!DOCTYPE html>
<head>
	<title>RDSAdmin / New contract </title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
<meta http-equiv="Cache-Control" content="no-cache"/>

<base href="<?=$CFG->base_rds_url;?>">

<meta name="robots" content="noindex, follow"/>

<link rel="shortcut icon" href="<?=$CFG->base_url;?>favicon.png" type="image/png">
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
<body>


<?php
	// log out and other main menu
	include 'tpl.admin_top_line.php';
?>

<div class="page-content">

<div class="page-content">

	<h2>Добавление договора</h2>

	<?php

	$r = new Router();
	if(!$r->get(1)) die("no cafe specified");
	$cafeUniqName = post_clean($r->get(1),10);	
 
	$cafes = new Smart_collect("cafe","WHERE uniq_name='$cafeUniqName'");
	if($cafes && $cafes->full()){
		
		$cafe = $cafes->get(0);
		
		$tpl = implode("",["<div class='rds-add-contract__cafe-info'>", "<p>User: {{userId}}, <a  href='mailto:{{userEmail}}'>{{userEmail}}</a><br>", "reg/upd: {{user_reg_date}} / {{user_updated_date}}, {{user_lang}}</p>", "<p>Cafe: <strong>{{cafe_title}},</strong> <a href='{{chefsmenu_link}}/cafe/{{uniq_name}}' targer='_blank'>{{uniq_name}}</a><br>", "Life Time: {{lifeTime}} дн.<br>", "Статус: {{cafeStatus}}</p>", "</div>"]);

		// Calculate life time (days)
		$created_time = strtotime((string) $cafe->created_date);
		$now_time = time();						
		$datediff= $now_time-$created_time;
		$days = floor($datediff / (60 * 60 * 24));				

		$cafeStatus = match ((int) $cafe->cafe_status) {
      0 => "Тест",
      1 => "В архиве",
      2 => "Договор",
      default => "Тест",
  };
		 
		$str = preg_replace('/{{chefsmenu_link}}/', "{$CFG->http}{$CFG->wwwroot}", $tpl);
		$str = preg_replace('/{{cafe_title}}/', (string) $cafe->cafe_title, (string) $str);
		$str = preg_replace('/{{uniq_name}}/', (string) $cafe->uniq_name, (string) $str);
		$str = preg_replace('/{{lifeTime}}/', $days, (string) $str);
		$str = preg_replace('/{{cafeStatus}}/', $cafeStatus, (string) $str);	
		

		$user = new Smart_object("users",$cafe->id_user);
		if($user && $user->valid()) {

			$regdate = new DateTime($user->regdate);
			$regdate = $regdate->format("d-m-Y");
			$updated_date = new DateTime($user->regdate);
			$updated_date = $updated_date->format("d-m-Y");			

			$str = preg_replace('/{{userId}}/', "{$user->id}", (string) $str);
			$str = preg_replace('/{{userEmail}}/', "{$user->email}", (string) $str);
			$str = preg_replace('/{{user_reg_date}}/', "{$regdate}", (string) $str);
			$str = preg_replace('/{{user_updated_date}}/', "{$updated_date}", (string) $str);
			$str = preg_replace('/{{user_lang}}/', "{$user->lang}", (string) $str);
		}

		echo "$str";

	}else{
		echo "<br>Unknown cafe $cafeUniqName";
	}

	if($user && $cafe){

		$tpl = implode("",["<div class='rds-add-contract__row'>", "<span class='rds-add-contract__row_number'>Договор <nobr>№{{contractName}}</nobr></span>", "<span class='rds-add-contract__row_regdate'>{{contractRegdate}}</span>", "<span class='rds-add-contract__row_daysleft'>{{daysLeft}}</span>", "</div>"]);


		$contract = new Contract($cafe);

		if($contract->full()){						
			$total = $contract->total();

			foreach ($contract->get() as $co) {
				
				$regdate = new DateTime($co->regdate);
				$regdate = $regdate->format("d-m-Y");
				
				$expire_on = strtotime((string) $co->expire_on);
				$now_time = time();
				$datediff= $expire_on-$now_time;
				$daysLeft = floor($datediff / (60 * 60 * 24));
				
				$str = preg_replace('/{{contractName}}/', (string) $co->contract_name, $tpl);
				$str = preg_replace('/{{contractRegdate}}/', "от ".$regdate, (string) $str);
				$str = preg_replace('/{{daysLeft}}/',  " осталось дн. ".$daysLeft, (string) $str);				
				echo "$str";
			}

		}else{
			$total = 0;			
		}

		$tpl = implode("",["<div class='rds-add-contract__new'>", "<span class='rds-add-contract__new_number'>Договор <nobr>№{{contractName}}</nobr></span>", "<span class='rds-add-contract__new_action'>{{actionButton}}</span>", "</div>"]);

	    //     _   _________       __   __________  _   ____________  ___   ____________
		//    / | / / ____/ |     / /  / ____/ __ \/ | / /_  __/ __ \/   | / ____/_  __/
		//   /  |/ / __/  | | /| / /  / /   / / / /  |/ / / / / /_/ / /| |/ /     / /
		//  / /|  / /___  | |/ |/ /  / /___/ /_/ / /|  / / / / _, _/ ___ / /___  / /
		// /_/ |_/_____/  |__/|__/   \____/\____/_/ |_/ /_/ /_/ |_/_/  |_\____/ /_/
		
		$contractName = $contract->make_new_name();

		$actionButton = implode("",["<a class='btn-activate-contract' ", "data-contract-name='{$contractName}' ", "data-id-user='{$user->id}' ", "data-id-cafe='{$cafe->id}' ", "href='#'>Активировать новый договор</a>"]);

		$str = preg_replace('/{{contractName}}/', (string) $contractName, $tpl);
		$str = preg_replace('/{{contractRegdate}}/', "&nbsp;", (string) $str);
		$str = preg_replace('/{{daysLeft}}/', "&nbsp;", (string) $str);
		$str = preg_replace('/{{actionButton}}/', $actionButton, (string) $str);
		
		echo $str;

	}

	?>	

	

</div>


</div>
</body>
</html>