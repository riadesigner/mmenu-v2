<?php
/**
 *
 * @filesource   html.php
 * @created      21.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

// namespace chillerlan\QRCodeExamples;
require_once 'vendor/autoload.php';
use chillerlan\QRCode\{QRCode, QROptions};


// $qr = QRCode::format("png")->size(250)->generate("https://ya.ru");	
// $qr = QRCode::generate("https://ya.ru");

$data = "https://ya.ru";
// $qr = (new QRCode)->render($data);
// $options->outputInterface     = QRGdImageWEBP::class;

$options = new QROptions([
    'outputType'   => QRCode::OUTPUT_IMAGE_PNG,    
    'returnResource'=> true,
    'scale' => 30,
    'quietzoneSize'=> 1,
]);

$im = (new QRCode($options))->render($data);

// ob_start();
// imagepng($im);
// $imageQrcode = ob_get_contents();
// ob_end_clean();

// var_dump($imageQrcode);
// echo "<img src='{$imageQrcode}'>";

header('Content-type: image/png');
imagepng($im);