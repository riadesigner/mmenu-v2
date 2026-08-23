<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Router
{
	
    private $ARR = [];  

    function __construct(){
        $str = rtrim((string) $_SERVER['REQUEST_URI'], '/'); 
        $str = preg_replace("/(\?.*)/", '', $str);
        $routeArray = explode('/', (string) $str);
        $route = [];
        foreach ($routeArray as $value) {
            if (!empty($value)) {
                $route[] = trim($value);
            }
        }   
        $this->ARR = $route;  

    }

    public function get($index=null){
        if(count($this->ARR)){
            if($index===null){
                return $this->ARR;
            }else{
                if(isset($this->ARR[$index])){
                    return $this->ARR[$index];    
                }else{
                    return false; 
                }                
            }
        }else{
            return false;
        }
    }

}

