<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//
// 
//		  c o n f i g   p u b l i c   m e n u   s k i n s 
//
//


$path_to_images = $CFG->base_app_url."pbl/i/";
$path_to_skins = "skins-2/";

$CFG->public_skins = [
    'ver' => '0.12', 
    'skin-groups'=>['dark'], 
    'all-skins'=>[
        [
            'label'=>'dark-classical', 
            'name'=>['ru'=>'Темный Классический', 'en'=>'Dark Classical'], 
            'group'=>'dark', 
            'params'=>[
                'color-menu-around-from' => 'rgba(0,0,0,0)',
                'color-menu-around-to' => 'rgba(0,0,0,1)',
                'color-bg' => '#232527',
                'color-front'=>'#ffffff',
                'color-bg-shadow' => '#2e3134',
                'color-bright'=>'#fff040',
                'smile-bg'=>'#fff040',
                'order-number'=>'color:#232527;background:#fff040',
                'banner-bg-color'=>'#fff040',
                'color-flag-text'=>'#ffffff',
                'color-flag-spicy'=>'#d73e2a',
                'color-flag-hit'=>'#e1762c',
                'color-flag-vege'=>'#547e0a',
                'color-flag-decorator'=>'transparent',
                'color-flag-divider'=>'transparent',
                'color-home-link'=>'#636363',
                'color-home-link-hover'=>'#ffffff',
                'color-btn-zoom'=>'rgba(255,255,255,.6)',
                'color-options-arrow'=>'#232527',
                'border-style-1'=>'1px solid #6f737a',
                'border-style-2'=>'2px dotted #6f737a',
                'border-style-3'=>'1px solid #676767',
                'fields-border-default'=>'border: 2px solid #6f737a',
                'fields-border-focused'=>'border: 2px solid #eeeeee',
                'attention-flashed'=>'border:2px solid #fff040',
                // buttons in view choosing mode
                'btn-choosing-mode-style'=>'background:#fff040;color:#000000',
                // buttons in cart
                'cart-btn-plus-minus'=>'background:#ffffff',
                'cart-btn-plus-minus-hover'=>'background:#fff040',
                'cart-btn-plus-minus-mobile-active'=>'background:#fff040',
                'cart-btn-plus-minus-active'=>'opacity:.5',
                // add to cart button
                'btn-addtocart'=>'color:#fff040;border:2px solid #ffffff',
                'btn-addtocart-hover'=>'color:#fff040;border:2px solid #fff040;background:#3c3f42',
                'btn-addtocart-active'=>'color:#000000;background:#fff040;border:2px solid #fff040',
                'btn-addtocart-mobile-active'=>'color:#fff040;border:2px solid #fff040',
                'btn-addtocart-full'=>'color:#000000;background:#fff040;border:2px solid #fff040',
                'btn-addtocart-full-hover'=>'color:#000000;background:#fff040;',
                'btn-addtocart-full-active'=>'opacity:.5',
                // default buttons
                'regular-btn-default'=>'color:#ffffff; background:transparent',
                'regular-btn-hover'=>'border:2px solid #6f737a ',
                'regular-btn-active'=>'border:2px solid #ffffff; background:#3c3f43',
                'regular-btn-mobile-active'=>'background:#3c3f43',
                // footer buttons
                'footer-btn-default'=>'color:#fff040; background:transparent',
                'footer-btn-disabled'=>'',
                'footer-btn-hover'=>'border:2px solid #6f737a',
                'footer-btn-active'=>'border:2px solid #ffffff; background:#3c3f43',
                'footer-btn-mobile-active'=>'background:#3c3f43',
                'footer-btn-bright'=>'color: #fff040;',
                'footer-btn-bright-hover'=>'',
                'footer-btn-bright-active'=>'',
                'footer-btn-bright-mobile-active'=>'',
                'footer-btn-cart-is-full'=>'color: #fff040;',
                'cart-decor-color'=>'#fff040',
                'footer-inform-color'=>'#6f737a',
                'footer-background-color'=>'#232527',
                'footer-mobile'=>'border-top:1px solid #6f737a',
                'footer-decoration'=>'border-top: 1px solid #6f737a',
                'footer-decoration-mobile'=>'border-top: 1px solid #ffffff',
                'path-to-skin' => $path_to_images.$path_to_skins.'dark-classical/',
        ]], [
            'label'=>'dark-bronze', 
            'name'=>['ru'=>'Темный Бронза', 'en'=>'Dark Bronze'], 
            'group'=>'dark', 
            'params'=>[
                'color-menu-around-from' => 'rgba(0,0,0,0)',
                'color-menu-around-to' => 'rgba(0,0,0,1)',
                'color-bg' => '#232527',
                'color-front'=>'#ffffff',
                'color-bg-shadow' => '#2e3134',
                'color-bright'=>'#d6b26b',
                'smile-bg'=>'#d6b26b',
                'order-number'=>'color:#232527;background:#d6b26b',
                'banner-bg-color'=>'#d6b26b',
                'color-flag-text'=>'#000000',
                'color-flag-spicy'=>'#d6b26b',
                'color-flag-hit'=>'#d6b26b',
                'color-flag-vege'=>'#d6b26b',
                'color-flag-decorator'=>'transparent',
                'color-flag-divider'=>'#a58d5f',
                'color-home-link'=>'#636363',
                'color-home-link-hover'=>'#ffffff',
                'color-btn-zoom'=>'rgba(255,255,255,.6)',
                'color-options-arrow'=>'#232527',
                'border-style-1'=>'1px solid #6f737a',
                'border-style-2'=>'2px dotted #6f737a',
                'border-style-3'=>'1px solid #676767',
                'fields-border-default'=>'border: 2px solid #6f737a',
                'fields-border-focused'=>'border: 2px solid #eeeeee',
                'attention-flashed'=>'border:2px solid #d6b26b',
                // buttons in view choosing mode
                'btn-choosing-mode-style'=>'background:#dab364;color:#000000',
                // buttons in cart
                'cart-btn-plus-minus'=>'background:#ffffff',
                'cart-btn-plus-minus-hover'=>'background:#d6b26b',
                'cart-btn-plus-minus-mobile-active'=>'background:#d6b26b',
                'cart-btn-plus-minus-active'=>'opacity:.5',
                // add to cart button
                'btn-addtocart'=>'color:#d6b26b;border:2px solid #ffffff',
                'btn-addtocart-hover'=>'color:#ffffff;border:2px solid #d6b26b;background:#3c3f42',
                'btn-addtocart-active'=>'color:#000000;background:#d6b26b;border:2px solid #d6b26b',
                'btn-addtocart-mobile-active'=>'color:#000000;background:#d6b26b;border:2px solid #d6b26b',
                'btn-addtocart-full'=>'color:#000000;background:#d6b26b;border:2px solid #d6b26b',
                'btn-addtocart-full-hover'=>'color:#000000;background:#d6b26b;',
                'btn-addtocart-full-active'=>'opacity:.5',
                // default buttons
                'regular-btn-default'=>'color:#ffffff; background:transparent',
                'regular-btn-hover'=>'border:2px solid #6f737a ',
                'regular-btn-active'=>'border:2px solid #ffffff; background:#3c3f43',
                'regular-btn-mobile-active'=>'background:#3c3f43',
                // footer buttons
                'footer-btn-default'=>'color:#d6b26b; background:transparent',
                'footer-btn-disabled'=>'',
                'footer-btn-hover'=>'border:2px solid #6f737a',
                'footer-btn-active'=>'border:2px solid #ffffff; background:#3c3f43',
                'footer-btn-mobile-active'=>'background:#3c3f43',
                'footer-btn-bright'=>'color: #000000; background:#d6b26b;border:none',
                'footer-btn-bright-hover'=>'border:none',
                'footer-btn-bright-active'=>'opacity:.5; border:2px solid transparent;border:none',
                'footer-btn-bright-mobile-active'=>'background:#bc9c64;',
                'footer-btn-cart-is-full'=>'color: #d6b26b;',
                'cart-decor-color'=>'#d6b26b',
                'footer-inform-color'=>'#6f737a',
                'footer-background-color'=>'#232527',
                'footer-mobile'=>'border-top:1px solid #6f737a',
                'footer-decoration'=>'border-top: 1px solid #6f737a',
                'footer-decoration-mobile'=>'border-top: 1px solid #ffffff',
                'path-to-skin' => $path_to_images.$path_to_skins.'dark-bronze/',
        ]]]];

?>