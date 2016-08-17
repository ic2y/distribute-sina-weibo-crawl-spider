<?php
/**
 * Created by PhpStorm.
 * User: cao
 * Date: 16-5-28
 * Time: 下午4:34
 */


include_once "jobClass.php";
include_once "mongoDb.php";
include_once "time.php";
include_once "redisDb.php";
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

function init_one_user($uid)
{
    $job = new jobClass();
    $job->init_one_user_list($uid);
}
function test_mongo(){
    $mo = new mongoManager();
    $mo->insertArray( array("name" => "fuck_array"),"fuck_array");

}

//$job->init_one_user_list(123);
/*
$job->wait_list->add_one_cookie("111");
$job->wait_list->add_one_cookie("222");
$job->wait_list->add_one_cookie("333");
$job->wait_list->add_one_cookie("444");
*/


/*
$url = "http://m.weibo.cn/page/json?containerid=1005051825597840_-_FOLLOWERS&page=5";
$start_pos = strpos($url,"100505") + 6;
$end_pos = strpos($url,"_-_");
echo substr($url, $start_pos, $end_pos - $start_pos);
*/



init_one_user("5943087025");
