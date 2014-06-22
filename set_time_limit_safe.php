<?php
/**
 *  A PHP solution to prevent scripts running longer than specified
 *    It might be possible to bypass the protection - no guarantee of safeness
 *    It strongly affects performance of loops!
 *  
 *  Made by Jakub Mareda 2014 
 *    This script is free to any use, including modification and use for commercial purposes
 *     
 *  Usage:
 *     include "set_tile_limit_safe";  //Before any operations                    
 *     set_time_limit_safe(XXX.YYY);   //Can be float (and is compared with float)
 *  
 *  To see how it works see [register tick function](http://cz2.php.net/manual/en/function.register-tick-function.php)    
 **/    
declare(ticks=1);

function set_time_limit_safe($limit) {
  if(!is_numeric($limit))
    trigger_error("set_time_limit_safe() expects parameter 1 to be numeric value, ". gettype($limit)." given", E_USER_WARNING);
  TimeLimit::set($limit); 
}              
//I'm using class to have the possibility of private static that's shared between both
//set function and the callback
class TimeLimit {
    //Default value for limit
    private static $limit = 30;
    //When the limit is registered, this is set to current time for later comparisons
    private static $reg_time = -1;
    //Boolean to determine whether callback is already registered or not
    private static $registered = false;
    /**
     *   Sets the time limit and registers callback
     *   @param float $limit limiting time in seconds
     *   @return null     
     **/              
    public static function set($limit) {
      //echo "Setting time limit!<br />";
      self::$limit = $limit;
      //Seconds as float
      self::$reg_time = microtime(true);
      //Only register once
      if(!self::$registered) {
        register_tick_function(array('TimeLimit', 'tick_cb'));
        //echo "Registering tick function!<br />";
        self::$registered = true;  
      }
    }
    /**
     *   The callback
     *   You can disable the limit by unregistering this function          
     **/ 
    public static function tick_cb() {
      $time = microtime(true);
      //echo "Tick!!!<br />";
      if($time-self::$reg_time>=self::$limit) {
        trigger_error("User defined maximum execution time of ".self::$limit." seconds exceeded.", E_USER_ERROR);
        //In case error callback had let the error through
        exit;
      }
    }
}
/*
//Testing code
set_time_limit_safe(1.5);

while(true) {
}
*/
?>
