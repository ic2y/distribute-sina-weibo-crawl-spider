<?php
/**
 * Created by PhpStorm.
 * User: cao
 * Date: 16-5-24
 * Time: 下午8:42
 */


define("DEBUG", "true");


if(defined("DEBUG")){
    error_reporting(E_ERROR);
}else{
    error_reporting(E_ALL);
}


include_once "jobClass.php";

/**
 * 客户端的请求，接口分为3种
 * 1.get_all_job 获取一组任务，分别含有任务的配置和任务的url
 * 2.done_one_job 单个任务完成之后的报告
 * 3.done_all_job 全部任务完成或者任务被强制终端的报告
 */
$action = $_GET["action"];
if ($action == null || $action == ""){
    die("no action");
}

$job = new jobClass();
if($action == "get_all_job"){
    echo $job->get_all_job($_GET);
}elseif ($action == "done_one_job"){
    echo $job->done_one_job($_GET,$_POST);
}elseif ($action == "done_all_job"){
    echo "ok";
    //暂时关闭，不再启用这个函数了
    //echo $job->done_all_job($_GET,$_POST);
}else if($action == "update_cookie"){
    echo $job->update_cookie($_POST);
}

