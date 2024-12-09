<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use chillerlan\QRCode\{QRCode, QROptions};

//       _______ ____  _   ______
//       / / ___// __ \/ | / / __ \
//  __  / /\__ \/ / / /  |/ / /_/ /
// / /_/ /___/ / /_/ / /|  / ____/
// \____//____/\____/_/ |_/_/

function __errorjsonp($msg='unknown'): never{
	$msg = !$msg?"unknown":$msg;
	global $callback;
	echo $callback.'('.json_encode(["error"=>$msg], JSON_UNESCAPED_UNICODE).')';
	glogError($msg);
	exit();
}	

function __answerjsonp($data): never{
	global $callback;
	echo $callback.'('.json_encode($data, JSON_UNESCAPED_UNICODE).')';
	exit();
}	

//       _______ ____  _   __
//       / / ___// __ \/ | / /
//  __  / /\__ \/ / / /  |/ /
// / /_/ /___/ / /_/ / /|  /
// \____//____/\____/_/ |_/

function __answerjson($data): never{	
	echo json_encode($data, JSON_UNESCAPED_UNICODE);
	exit();
}	
function __errorjson($msg): never{	
	echo json_encode(["error"=>$msg], JSON_UNESCAPED_UNICODE);
	exit();
}

function glog($msg, $script_file="", $pre="glog:"){	
	global $CFG;		
	if(J_ENV_TEST){			
		$name = !empty($fileName)?$fileName:$CFG->log_file_name;
		$path = $CFG->dirroot.$CFG->log_file_path.$name.'_'.date('Y_m_d').'.log';		
		if (!file_exists($path)) { touch($path); }
		$log = date('Y-m-d H:i:s') . " $pre $msg";
		if(!empty($script_file)) $log = $log.", FILE: ".$script_file;
		file_put_contents($path, $log . PHP_EOL, FILE_APPEND);
	}
}	

function glogError($msg, $script_file=""){
	glog($msg,$script_file,"gError:");
}	

/* --------------------------------------------------

	          TG FUNCTIONS 

----------------------------------------------------- */

function send_telegram($method, $data, $token, $headers = []){
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => 'https://api.telegram.org/bot' . $token . '/' . $method,
		CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
		CURLOPT_HTTPHEADER => array_merge(["Content-Type: application/json"])
	]);
	$result = curl_exec($curl);
	curl_close($curl);
	return (json_decode($result, true) ?: $result);
}

/* --------------------------------------------------

	          IIKO FUNCTIONS 

----------------------------------------------------- */

function iiko_get_info($url,$headers,$params){        
      $header = [];
      if (is_array($headers)) {
          $i_header = $headers;          
          foreach ($i_header as $param => $value) {
              $header[] = "$param: $value";
          }
      }      
      $curl = curl_init();
      curl_setopt_array($curl, [CURLOPT_URL => 'https://api-ru.iiko.services/'.$url, CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 15, CURLOPT_TIMEOUT => 10, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => 'POST', CURLOPT_POSTFIELDS => json_encode($params, JSON_UNESCAPED_UNICODE), CURLOPT_HTTPHEADER => $header]);
      $json = curl_exec($curl);        
      curl_close($curl);

      // glog($json);
            
      return json_decode($json, true);
}


function iiko_tables_res_parse($res){
	$arr = [];
	$arr_sections = $res['restaurantSections'];
	if(is_array($arr_sections) && count($arr_sections)){
		foreach($arr_sections as $section){
			array_push($arr,[
				'section_name'=>$section['name'],
				'section_id'=>$section['id'],
				'terminalGroupId'=>$section['terminalGroupId'],
				'tables'=>$section['tables']
			]);			
		}
	}
	return $arr;
}


/* --------------------------------------------------

	          QR-CODE FUNCTIONS 

----------------------------------------------------- */
// return qecode as imagedata or imagesource;
function rds_qrcode_create_from($link="http://chefsmenu.ru/unknown", $asImageData = false ){		
	$options = new QROptions([
		'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
		'returnResource'=> true,
		'scale' => 30,
		'quietzoneSize'=> 1,
	]);
	$im = (new QRCode($options))->render($link);
	ob_start();		
	imagepng($im);
	$imagedata = ob_get_contents();
	ob_end_clean();
	if($asImageData){		
		return $imagedata;
	}else{
		return 'data:image/png;base64,'.base64_encode($imagedata);		
	}
}


/* --------------------------------------------------

	          DATE / TIME FUNCTIONS 

----------------------------------------------------- */


function CHEFS__beautiful_date($date,$lang="ru"){
	if($lang=="ru"){
		$monthes = [1 => 'Января', 2 => 'Февраля', 3 => 'Марта', 4 => 'Апреля', 5 => 'Мая', 6 => 'Июня', 7 => 'Июля', 8 => 'Августа', 9 => 'Сентября', 10 => 'Октября', 11 => 'Ноября', 12 => 'Декабря'];
	}else{
		$monthes = [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];
	}
	$d = new Datetime($date);
	return $d->format('d ') . $monthes[($d->format('n'))] . $d->format(' Y');
}	

function glb_russian_datetime($str_time,$time_format=24){
	if(!$str_time) return "";		
	$date=explode(".", date("d.m",strtotime((string) $str_time)));
	$time = $time_format==24?date("H:i",strtotime((string) $str_time)):date("h:i A",strtotime((string) $str_time));
	$day = date("w",strtotime((string) $str_time));
	$arrMounth = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
	$m = $arrMounth[$date[1]-1];
	return $date[0].' '.$m.", ".$time;
}

function glb_russian_datetime_full($str_time,$time_format=24){
	if(!$str_time) return "";		
	$date=explode(".", date("d.m.Y",strtotime((string) $str_time)));
	$time = $time_format==24?date("H:i",strtotime((string) $str_time)):date("h:i A",strtotime((string) $str_time));
	$arrMounth = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
	$m = $arrMounth[$date[1]-1];
	return $date[0].' '.$m.'&nbsp;'.$date[2].", ".$time;
}

/* --------------------------------------------------

	          COMMON FUNCTIONS 

----------------------------------------------------- */

function go($href): never{
	@Header("Location: $href");exit();
}

function post_clean($var,$maxsize=0){	
	$var = trim(stripslashes(htmlspecialchars((string) $var)));
	if(empty($var)) return "";
	$var = str_replace("'","`",$var);
	return $maxsize? substr($var, 0, intval($maxsize)):$var;
}

function checkemail($email){
	return preg_match("|^[0-9a-z_\.]+@[0-9a-z_^\.]+\.[a-z]{2,6}$|i", (string) $email);
}	

function check_string($str){
	return preg_match("/^[- '\p{L}\s]+$/u", (string) $str);
}	

function get_random_string($length=16){
	$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
	return substr(str_shuffle($permitted_chars), 0, $length);
}	

function generate_password($max=6){
	$chars="1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
	$max = intval($max);
	$size=StrLen($chars)-1;
	$password=null;
	while($max--) 
	$password.=$chars[random_int(0,$size)]; 
	return $password;
}


/* SOME PUBLIC COMMON FUNCTIONS */

if(!function_exists('str_contains')){
	function str_contains(string $haystack, string $needle): bool {
	    return '' === $needle || str_contains($haystack, $needle);
	}
}


/* for remember and TODO */

// try {
//     throw new Exception("Exception message");
//     echo "That code will never been executed";
// } catch (Exception $e) {
//     echo $e->getMessage();
// }

// if (!is_dir($path)) {  mkdir($path, 0777, true); }

//$today  = date("Y-m-d");
//select * from table_name where timestamp >= CURDATE();
//select * from table_name where timestamp >= '2018-07-07';

		
?>