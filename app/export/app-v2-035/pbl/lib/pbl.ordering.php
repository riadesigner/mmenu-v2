<?php
	
	define("BASEPATH",__file__);
	

	require_once getenv('WORKDIR').'/config.php';
	require_once WORK_DIR.APP_DIR.'core/common.php';
	

	header('content-type: application/json; charset=utf-8');
	$callback = $_REQUEST['callback'] ?? 'alert';
	if (!preg_match('/^[a-z0-9_-]+$/i', (string) $callback)) {  $callback = 'alert'; }		

	require_once WORK_DIR.APP_DIR.'core/class.sql.php';	
	 
	require_once WORK_DIR.APP_DIR.'core/class.smart_object.php';
	require_once WORK_DIR.APP_DIR.'core/class.smart_collect.php';
	require_once WORK_DIR.APP_DIR.'core/class.user.php';	
	require_once WORK_DIR.APP_DIR.'core/class.email_simple.php';
	
	require_once WORK_DIR.APP_DIR.'core/class.order_sender.php';

	session_start();
	SQL::connect();
	
	// VERIFY USER INPUTS
	if(!isset($_REQUEST["order_data"])) __errorjsonp("--wrong order data, #1");
	$order_data = $_REQUEST["order_data"];
	if(!is_array($order_data)) __errorjsonp("--wrong order data, #2");
	
	$order_items = $order_data["order_items"];
	if(!is_array($order_items)){__errorjsonp("--wrong order data, #3");}

	$user_phone  = post_clean($order_data["order_user_phone"], 50);
	$user_phone  = preg_replace("/[^+0-9 ()\-,.]/", "", (string) $user_phone);
	if(empty($user_phone)) __errorjsonp("--wrong user phone, #4");	

	if(!isset($order_data["pickupself_mode"]))__errorjsonp("--it needs to set pickupself variable");
	$PICKUPSELF_MODE = $order_data["pickupself_mode"];

	$user_address = post_clean($order_data["order_user_address"], 250);
	if(empty($user_address)) __errorjsonp("--wrong order data, #5");	

	$user_comment = post_clean($order_data["order_user_comment"], 250);

	$time_need = post_clean($order_data["order_time_need"],100);
	if(empty($time_need)) __errorjsonp("--wrong order data, #6");

	$time_sent = post_clean($order_data["order_time_sent"],100);
	if(empty($time_sent)) __errorjsonp("--wrong order data, #7");


	// CHECK IS REAL CAFE
	$id_cafe = (int) $order_data["id_cafe"];
	if(empty($id_cafe)) __errorjsonp("--unknow cafe");	
	$cafe = new Smart_object("cafe",$id_cafe);
	if(!$cafe || !$cafe->valid()) __errorjsonp("--unknow cafe");

	$DEMO_MODE = (int) $cafe->cafe_status !== 2 ;

	// -----------------------------------
	//   BUILDING ORDER ARRAY FOR SAVING
	// -----------------------------------

	$order = [
		"externalNumber"=>"",
		// "completeBefore"=>"", // null if now()
		"phone"=>$user_phone,
		// "orderServiceType"=> "DeliveryByCourier",		
		// "comment"=>"",		
		// "customer"=>"",
	    "items"=>$order_items
	];	

	// -----------------------------
	//     SAVE COPY ORDER IN DB
	// -----------------------------
	
	define("ORDER_TARGET",Order_sender::CHEFSMENU_ORDER);
	$pending_mode = false;
	$demo_mode = false;
	$short_number = Order_sender::save_order_to_db(ORDER_TARGET, $pending_mode, $cafe, $order, $demo_mode);
	if(!$short_number)__errorjsonp("--cant save order");

	$order['externalNumber'] = $short_number;

	// -------------------------------
	//     BUILDING ORDER TELEGRAM
	// -------------------------------	

	// preparing

	$cafe_title = $cafe->cafe_title;
	$time_format = 24;
	$str_currency = "₽";
	$order_total_price = (float) $order_data["order_total_price"];

	if($time_need==$time_sent){
		$order_time_to = "Ближайшее время";
	}else{
		$order_time_to = glb_russian_datetime($time_need,$time_format);	
	}

	// building
	
	#[AllowDynamicProperties]
	class Order_object{};

	$ORDER = new Order_object();
		
	$ORDER->text = "";
	$ORDER->text .= "Заказ ".$short_number."\n";
	$ORDER->text .= "       ------------\n";
	$ORDER->text .= "       Создан: ".glb_russian_datetime($time_sent,$time_format)."\n";
	$ORDER->text .= "       Заказ на: {$order_time_to}\n";
	$ORDER->text .= "       Сумма: {$order_total_price} {$str_currency}.\n";
	$ORDER->text .= "       Тел.: [{$user_phone}](tel:{$user_phone})\n";
	$ORDER->text .= "       Адрес: {$user_address}\n";
	if(!empty($user_comment)){
		$ORDER->text .= "       Комментарий: {$user_comment}\n";	
	} 	
	$ORDER->text .= "       ------------\n";
	
	$count = 0;
	foreach ($order_items as $row) {		
		$count++;
		$item_title = $count.". ".$row["title"];
		$item_price = $row["count"]."x".$row["price"]." ".$str_currency;
		$ORDER->text .= "_{$item_title}_\n";
		$ORDER->text .= "{$item_price}\n";
		$order_items_separate = count($order_items)>$count-1?"---\n":"--- .\n";
		$ORDER->text .= $order_items_separate;
	}

	$DEMO_MODE && __answerjsonp(["short_number"=>$short_number,"demo_mode"=>$DEMO_MODE]);

	if(empty(Order_sender::total_tg_users_for($cafe->uniq_name,ORDER_TARGET))) 
	__answerjsonp(["short_number"=>$short_number,"demo_mode"=>$DEMO_MODE, "notg_mode"=>true]);


	// TG sending
	$results = Order_sender::send_tg_order($cafe->uniq_name, ORDER_TARGET, $short_number, $ORDER->text);
	if(	!$results || !count($results)){
		__errorjsonp("--fail sending tg-order");
	}

	__answerjsonp(["short_number"=>$short_number,"demo_mode"=>$DEMO_MODE]);


?>