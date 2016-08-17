<?php

/**
 * Created by PhpStorm.
 * User: cao
 * Date: 16-5-24
 * Time: 下午9:39
 */
class utils
{

    static function get_device_id($id){
        if($id != null && $id != ""){
            return $id;
        }
        return md5(time() + rand(1,1000000));
    }
}