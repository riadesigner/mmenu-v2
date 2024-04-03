<?php

/*
	Change item image

*/	
	
	header('content-type: application/json; charset=utf-8');

	define("BASEPATH",__file__);
	
	require_once '../../../config.php';
	require_once '../../../vendor/autoload.php';

	require_once '../../core/common.php';	
	
	require_once '../../core/class.sql.php';
	 
	require_once '../../core/class.smart_object.php';
	require_once '../../core/class.smart_collect.php';
	require_once '../../core/class.user.php';
	require_once '../../core/class.app.php';


	use Aws\S3\S3Client;
	use Aws\S3\Exception\S3Exception;

	session_start();
	SQL::connect();

	$user = User::from_cookie();
	if(!$user || !$user->valid())__errorjson("Unknown user, ".__LINE__);

	if (!isset($_FILES['up_file']) || !count($_FILES['up_file'])) __error_json("Empty file, ".__LINE__);

	if(!isset($_POST['id_item']) && empty($_POST['id_item']) ) __errorjson("Unknown id item, ".__LINE__);
	$id_item = (int) $_POST['id_item'];	
	$item = new Smart_object('items',$id_item);
	if(!$item->valid()) __errorjson("Unknown item, ".__LINE__);

	$need_rotate = (int) $_POST['need_rotate'];

	$menu = new Smart_object('menu',$item->id_menu);
	if(!$menu || !$menu->valid()) __errorjson("Unknown menu, ".__LINE__);

	$cafe = new Smart_object("cafe",$menu->id_cafe);
	if(!$cafe->valid())__errorjson("Unknown cafe, ".__LINE__);

	if($cafe->id_user !== $user->id) __errorjson("Not allowed, ".__LINE__);

	//photobox/266ybpxc/266ybpxc-f67f0eb51f4be32ad0635816ac00ed59.jpg
	//photobox/266ybpxc/266ybpxc-f67f0eb51f4be32ad0635816ac00ed59-s.jpg
	//photobox/266ybpxc/qrcode.jpg

	$bucket = $CFG->S3['bucket'];

	// calculate new fileName
	$ext = ".jpg";	
	$uniq = mb_strtolower((string) $cafe->uniq_name);
	$name = $item->id.'-'.time().'-'.random_int(0,1000);
	$prefix = "{$uniq}/{$uniq}-";
	$newName = $prefix.md5($name);

	//Check if image file is a actual image or fake image
	$size = getimagesize($_FILES['up_file']['tmp_name']); 
	if($size===false) __error_json("allowed image only, ".__LINE__);
	if($size[0]*$size[1]>24000000) __error_json("the file too big! Required 24Mpx maximum, ".__LINE__);

	if(!App::delete_item_image($item)) __error_json("cant delete old images, ".__LINE__); 

	$image_large = get_resized_jpeg(1200,1200,$_FILES['up_file']['tmp_name'],$need_rotate);
	$image_small = get_resized_jpeg(600,600,$_FILES['up_file']['tmp_name'],$need_rotate);
	if(!$image_large || !$image_small) __error_json("Can not resized image, ".__LINE__);

	$s3Client = new S3Client([
	    'version'     => 'latest',
	    'region'      => $CFG->S3['region'],
	    'credentials' => [
	        'key'    => $CFG->S3['key'],
	        'secret' => $CFG->S3['secret'],
	    ],
	    'endpoint' => $CFG->S3['endpoint'],
	    'use_aws_shared_config_files' => false
	]);

	$result = $s3Client->putObject([
	 'Bucket' => $bucket,
	 'Key'    => $newName.$ext,
	 'ContentType' => 'image/jpeg',
	 'ACL'=>'public-read',	 
	 'Body' => $image_large
	]);

	try{
		if ($result["@metadata"]["statusCode"] == '200') {
			$url = $result["ObjectURL"];	    
		}else{
			__errorjson("Cant save image, ".__LINE__);	
		}
	} catch (S3Exception $e) {
    	__errorjson($e->getMessage() . PHP_EOL);
	}

	$result = $s3Client->putObject([
	 'Bucket' => $bucket,
	 'Key'    => $newName.'-s'.$ext,
	 'ContentType' => 'image/jpeg',
	 'ACL'=>'public-read',	 
	 'Body' => $image_small
	]);

	try{
		if ($result["@metadata"]["statusCode"] == '200') {
			// $urlSmall = $result["ObjectURL"];
		}else{
			__errorjson("Cant save image, ".__LINE__);	
		}
	} catch (S3Exception $e) {
    	__errorjson($e->getMessage() . PHP_EOL);
	}
	
	// save image name in db
	$item->image_name = "{$newName}{$ext}";
	$item->image_url = $url;
	$item->updated_date = 'now()';
	if($item->save()){

		$cafe->updated_date = 'now()';
		$cafe->rev+=1;
		$cafe->save();

		$answer= ["cafe-rev"=>$cafe->rev, "image_name"=>$newName.$ext, "image_url"=>$url];

		__answerjson($answer);

	}else{
		__errorjson("Cant save image");
	}

function get_resized_jpeg($width,$height,$src,$need_rotate){
  	
	if (!file_exists($src)) return false;
	$size = getimagesize($src);
	if ($size === false) return false;
	
  	$format = mb_strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  	$icfunc = "imagecreatefrom" . $format;
  	if (!function_exists($icfunc)) return false;
	
	$needResize = ($size[0]>$width || $size[1]>$height) ? true : false; 
	
	if($needResize) {
	  	$iw=$size[0];
	  	$ih=$size[1];
	  	$use_width = $iw>$ih;

		$k = $use_width ? $iw/$width : $ih/$height;
		$new_width = $use_width ? $width : ceil ($iw/$k);
		$new_height = $use_width ? ceil ($ih/$k) : $height;

		$source = $icfunc($src);
		$new_image=ImageCreateTrueColor ($new_width, $new_height);	
		ImageCopyResampled ($new_image, $source, 0, 0, 0, 0, $new_width, $new_height, $iw, $ih);
	  	imagedestroy($source);

	}else{
		$new_image = $icfunc($src);
	}

	$need_rotate>0 && $new_image = imagerotate($new_image, -$need_rotate, 0);

    ob_start();
    imagejpeg($new_image, null, '90');
    $data = ob_get_contents();
    ob_end_clean();
    return $data;

};


?>