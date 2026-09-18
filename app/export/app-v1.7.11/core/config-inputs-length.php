<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//
// 
//   config user inputs limits
//   [en, cyr, jp needs 1-3 bytes respectively]
//   
//
// in db varchar length +100

$CFG->inputs_length = [
	// view-customizing-cafe
	'cafe-title'=>150,
	'chief-cook'=>900,
	'cafe-address'=>900,
	'cafe-phone'=>300,
	'work-hours'=>900,			
	// view-cafe-description
	'cafe-description'=>9000, // need to do the same in js
	// view-iiko-adding-api-key
	'iiko-api-key'=>50, // need to do the same in js
	// view-change-subdomain
	'new-subdomain'=>150,
	// view-change-password
	'new-password'=>300,
	// view-edit-item
	'item-title'=>900,
	'item-description'=>1500,
	'item-volume'=>300,
	'item-price'=>10,
	// view-edit-menu
	'menu-title'=>150,
	// view-main-help
	'user_question'=>9000, // need to do the same in js	
];


?>