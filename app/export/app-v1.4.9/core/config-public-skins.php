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
                'order-number'=>'color: #232527;background: #fff040',
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
                'btn-choosing-mode-style'=>'background: #fff040;color: #000000',
                // buttons in cart
                'cart-btn-plus-minus'=>'background: #ffffff',
                'cart-btn-plus-minus-hover'=>'background: #fff040',
                'cart-btn-plus-minus-mobile-active'=>'background: #fff040',
                'cart-btn-plus-minus-active'=>'opacity:.5',
                // add to cart button
                'btn-addtocart'=>'color: #fff040;border:2px solid #ffffff',
                'btn-addtocart-hover'=>'color: #fff040;border:2px solid #fff040;background: #3c3f42',
                'btn-addtocart-active'=>'color: #000000;background: #fff040;border:2px solid #fff040',
                'btn-addtocart-mobile-active'=>'color: #fff040;border:2px solid #fff040',
                'btn-addtocart-full'=>'color: #000000;background: #fff040;border:2px solid #fff040',
                'btn-addtocart-full-hover'=>'color: #000000;background: #fff040;',
                'btn-addtocart-full-active'=>'opacity:.5',
                // default buttons
                'regular-btn-default'=>'color: #ffffff; background:transparent',
                'regular-btn-hover'=>'border:2px solid #6f737a ',
                'regular-btn-active'=>'border:2px solid #ffffff; background: #3c3f43',
                'regular-btn-mobile-active'=>'background: #3c3f43',
                // footer buttons
                'footer-btn-default'=>'color: #fff040; background:transparent',
                'footer-btn-disabled'=>'',
                'footer-btn-hover'=>'border:2px solid #6f737a',
                'footer-btn-active'=>'border:2px solid #070303ff; background: #3c3f43',
                'footer-btn-mobile-active'=>'background: #3c3f43',
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
                'color-menu-around-from' => '#130d09ff',
                'color-menu-around-to' => '#130d09ff',
                'color-bg' => '#26160d',
                'color-front'=>'#ffffff',
                'color-bg-shadow' => '#342118',
                'color-bright'=>'#E8833C',
                'smile-bg'=>'#E8833C',
                'order-number'=>'color: #232527;background: #E8833C',
                'banner-bg-color'=>'#E8833C',
                'color-flag-text'=>'#000000',
                'color-flag-spicy'=>'#E8833C',
                'color-flag-hit'=>'#E8833C',
                'color-flag-vege'=>'#E8833C',
                'color-flag-decorator'=>'transparent',
                'color-flag-divider'=>'#a58d5f',
                'color-home-link'=>'#a58d5f',
                'color-home-link-hover'=>'#ffffff',
                'color-btn-zoom'=>'rgba(88, 49, 21, 0.6)',
                'color-options-arrow'=>'#232527',
                'border-style-1'=>'1px solid #583c34',
                'border-style-2'=>'2px dotted #583c34',
                'border-style-3'=>'1px solid #583c34',
                'fields-border-default'=>'border: 2px solid #583c34',
                'fields-border-focused'=>'border: 2px solid #ffffff',
                'attention-flashed'=>'border:2px solid #E8833C',
                // buttons in view choosing mode
                'btn-choosing-mode-style'=>'background: #bd692dff; color: #000000',
                // buttons in cart
                'cart-btn-plus-minus'=>'background: #ffffff',
                'cart-btn-plus-minus-hover'=>'background: #E8833C',
                'cart-btn-plus-minus-mobile-active'=>'background: #E8833C',
                'cart-btn-plus-minus-active'=>'opacity:.5',
                // add to cart button
                'btn-addtocart'=>'color: #E8833C;border:2px solid #ffffff',
                'btn-addtocart-hover'=>'color: #ffffff;border:2px solid #E8833C;background: #583c34',
                'btn-addtocart-active'=>'color: #000000;background: #E8833C;border:2px solid #E8833C',
                'btn-addtocart-mobile-active'=>'color: #000000;background: #E8833C;border:2px solid #E8833C',
                'btn-addtocart-full'=>'color: #000000;background: #E8833C;border:2px solid #E8833C',
                'btn-addtocart-full-hover'=>'color: #000000;background: #E8833C;',
                'btn-addtocart-full-active'=>'opacity:.5',
                // default buttons
                'regular-btn-default'=>'color: #ffffff; background: transparent',
                'regular-btn-hover'=>'border:2px solid #583c34 ',
                'regular-btn-active'=>'border:2px solid #ffffff; background: #583c34',
                'regular-btn-mobile-active'=>'background: #583c34',
                // footer buttons
                'footer-btn-default'=>'color: #E8833C; background:transparent',
                'footer-btn-disabled'=>'',
                'footer-btn-hover'=>'border:2px solid #583c34',
                'footer-btn-active'=>'border:2px solid #ffffff; background: #583c34',
                'footer-btn-mobile-active'=>'background: #583c34',
                'footer-btn-bright'=>'color: #000000; background: #E8833C;border:none',
                'footer-btn-bright-hover'=>'border:none',
                'footer-btn-bright-active'=>'opacity:.5; border:2px solid transparent;border:none',
                'footer-btn-bright-mobile-active'=>'background: #E8833C;',
                'footer-btn-cart-is-full'=>'color: #E8833C;',
                'cart-decor-color'=>'#E8833C',
                'footer-inform-color'=>'#583c34',
                'footer-background-color'=>'#26160d',
                'footer-mobile'=>'border-top:1px solid #583c34',
                'footer-decoration'=>'border-top: 1px solid #583c34',
                'footer-decoration-mobile'=>'border-top: 1px solid #ffffff',
                'path-to-skin' => $path_to_images.$path_to_skins.'dark-bronze/',
        ]]]];

?>