<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//
// 
//		  c o n f i g   l i m i t s
//
//


// $CFG->limits = [
// 	'test'=> ['total_items'=>25, 'total_sections'=>5, 'items_in_section'=>5], // ~ 7,5 Mb/menu
// 	'full'=> ['total_items'=>200, 'total_sections'=>20, 'items_in_section'=>20] // ~ 60 Mb/menu
// ];

$CFG->limits = [
	'test'=> ['total_items'=>100, 'total_sections'=>30, 'items_in_section'=>30], 
	'full'=> ['total_items'=>300, 'total_sections'=>40, 'items_in_section'=>40] 
];


?>