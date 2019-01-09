<?php
/**
 * Created by PhpStorm.
 * User: yangzhenyu
 * Date: 2019/1/9
 * Time: 09:55
 */

class Register
{
    protected static $three = [];
    static function set($key,$value){
        Register::$three[$key] = $value;
    }

    static function get($key){
        if (isset(  Register::$three[$key])){
            return  Register::$three[$key];
        }else{
            return null;
        }
    }
}