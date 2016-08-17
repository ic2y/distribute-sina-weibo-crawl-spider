<?php
/**
 * Created by PhpStorm.
 * User: cao
 * Date: 16-6-5
 * Time: 下午2:38
 */
/*
 * 有如下4种时间格式
49分钟前
今天 11:38
06-04 17:25
2015-04-26 23:49
 */
function time_to_unix_time($str){
    if(strpos($str, "分钟")){
        $before_minute = intval($str);
        return time() - 60 * $before_minute;
    }else if($pos = strstr($str, "今天")){
        $pos = strstr($str," ");
        $time = @date("Y-m-d") + @substr($str,$pos);
        return strtotime($time);
    }else if(strlen($str) <= 11){
        $str = date("Y")."-".$str;
    }
    return strtotime($str);
}