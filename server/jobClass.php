<?php

/**
 * Created by PhpStorm.
 * User: cao
 * Date: 16-5-24
 * Time: 下午8:59
 */

include_once "redisDb.php";
include_once "utils.php";
include_once "dbListClass.php";
include_once "time.php";
include_once "mongoDb.php";

class jobClass
{
    public $redis;
    public $mondodb;
    public $wait_list;
    const home_url = "http://weibo.cn/%s/info";
    const tag_url = "http://weibo.cn/account/privacy/tags/?uid=%s";
    const weibo_url = "http://m.weibo.cn/page/json?containerid=100505%s_-_WEIBO_SECOND_PROFILE_WEIBO&page=%s";
    const pinglun_url = "http://m.weibo.cn/single/rcList?format=cards&id=%s&type=comment&page=%s";
    const zhuanfa_url = "http://m.weibo.cn/single/rcList?format=cards&id=%s&type=repost&page=%s";
    const relation_url = "http://m.weibo.cn/page/json?containerid=100505%s_-_FOLLOWERS&page=%s";


    public static $crawl_job_type = array(
        dbListClass::HOME_LIST,
        dbListClass::TAG_LIST,
        dbListClass::RELATION_LIST,
        dbListClass::WEIBO_LIST,
        dbListClass::PINGLUN_LIST,
        dbListClass::ZHUANFA_LIST
    );

    function __construct()
    {
        $this->redis = new redisDb();
        $this->wait_list = new dbListClass($this->redis);
        $this->mondodb = new mongoManager();
    }
    /**
     * 从一个uid初始化一个准备爬取的用户的队列,步骤如下
     * 1.初始化个人信息页的url
     * 2.初始化tag页的url
     * 3.初始化微博页的url
     * @param $uid string 用户的uid
     */
    function init_one_user_list($uid){
        $home = sprintf(jobClass::home_url,$uid);
        $tag = sprintf(jobClass::tag_url,$uid);
        $weibo = sprintf(jobClass::weibo_url,$uid,0);
        $relation = sprintf(jobClass::relation_url,$uid,1);
        $this->wait_list->add_relation_list($relation);
        $this->wait_list->add_home_list($home);
        $this->wait_list->add_tag_list($tag);
        $this->wait_list->add_weibo_list($weibo);
    }


    /**
     * 从一个微博数组生成爬取微博的评论和转发的url
     * @param $weibo_arr array 微博uid的数组
     */
    function init_weibo($weibo_uid){
        $pinglun_url = sprintf(jobClass::pinglun_url,$weibo_uid,1);
        $zhuanfa_url = sprintf(jobClass::zhuanfa_url,$weibo_uid,1);
        $this->wait_list->add_pinglun_list($pinglun_url);
        $this->wait_list->add_zhuanfa_list($zhuanfa_url);
    }

    /**
     * 当访问此接口,默认是已经完成了任务.认为访问了done_all_job接口,处理了device_wait_list.那么正常情况下,device_wait_list是空的
     * 但是有特殊情况,客户端无征兆的关闭了,device_wait_list是不空的,那么重新转移到 总体的等待队列
     *
     * 给客户端分配任务用
     * 1.分配device_id
     * 2.分配cookie
     * 3.分配任务队列
     * 4.将已分配的任务队列按照device_id为键值 存储起来
     *
     * @param $get array 传递$_GET数组
     * @return string 返回json字符串
     */
    function get_all_job(array $get){
        //获取一个设备号，不过不存在，就生成一个设备号
        $device_id = @utils::get_device_id($get["device_id"]);

        //处理客户端上次是不是异常退出的情况
        //f$this->wait_list->restore_device_job_list($device_id);

        $rs = array(
            "config" => array(
                "cookie" => $this->wait_list->get_one_cookie(),
                "device_id" => $device_id
            ),
            "url_list" => $this->create_job_list($device_id)
        );
        return json_encode($rs);
    }

    /**
     *
     * 目前的设计模式:
     * 1.客户端每成功抓取一个数据,就上报一条数据,
     * 2.负责把数据解析插入mongodb,并继续初始化redis队列
     * @param $get array get param
     * @param $post array psot param
     * @return string if ok,return ok
     */
    function done_one_job($get, $post){
        $type = $post["type"];
        $url = $post["url"];
        $device_id = $post["device_id"];
        $code = $post["code"];
        $data = $post["data"];


        //不管客户端是200 还是 302,403 都不再爬了,默认客户端任务已完成,对于302的任务,分配给别人完成
        if($code == "200"){
            //分析内容 插入数据库
            //分析链接,是否需要生成新的链接,插入到redis队列
            if($data != ""){
                $data = $this->parse_data($data, $type, $url);
                $this->mondodb->insertOneOrArray($data, $type);
                $this->wait_list->parse_new_relation_and_weibo($data, $type, $url);
            }

        }else{
            //直接从device_list 队列 删除,重新加入公共等待队列
            //$this->wait_list->delete_one_item_from_device_job_list($device_id, $type, $url);
            $this->wait_list->add_list($type, $url);
        }

        if(defined("DEBUG")){
            $file = fopen("run.log", "a+");
            fwrite($file,json_encode($post) );
            fwrite($file, "\r\n");
            fclose($file);
        }

        
        return "ok";
    }
    function done_all_job($get,$post){
        $device_id = $get["device_id"];
        $this->wait_list->restore_device_job_list($device_id);
        return "ok";
    }

    function update_cookie($post){
        $this->wait_list->delete_obj(dbListClass::CONFIG_COOKIE_LIST);
        foreach ($post["cookie"] as $key => $value){
            if($value){
                $this->wait_list->add_one_cookie($value);
            }
        }
    }


    /**
     * 根据设备id,开始准备分配任务
     * @param $device_id string 设备id
     * @return array 数组
     */
    private function create_job_list($device_id){
        $rs = array();

        foreach(jobClass::$crawl_job_type as $type){
            $rs[$type] = $this->wait_list->pop_list($type);

            //检测队列等待的东西够不够,没有东西了,就开始生成,插入
            if (sizeof($rs[$type]) < dbListClass::POP_LIMIT){
                for($i = 0;$i < dbListClass::INSERT_LIMIT;++$i){
                    $userId = $this->wait_list->set_pop(dbListClass::un_done_user);
                    $weiboId = $this->wait_list->set_pop(dbListClass::un_done_weibo);

                    if($userId){
                        $this->init_one_user_list($userId);
                        $this->wait_list->set_insert(dbListClass::done_user, $userId);
                    }
                    if($weiboId){
                        $this->init_weibo($weiboId);
                        $this->wait_list->set_insert(dbListClass::done_weibo,$weiboId);
                    }
                }
            }
        }

        //$this->wait_list->add_device_job_list($device_id,$rs);
        return $rs;
    }



    private function parse_data($data,$type,$url){
        //print_r($type);
        if($type == dbListClass::HOME_LIST){
            return json_decode($data);
        }

        if($type == dbListClass::TAG_LIST){
            return json_decode($data);
        }

        if($type == dbListClass::RELATION_LIST){
            $start_pos = strpos($url,"100505") + 6;
            $end_pos = strpos($url,"_-_");

            $obj = json_decode($data);
            $rs = $obj->cards[0]->card_group;
            foreach ($rs as $key => $value){
                $rs[$key]->from_uid = substr($url, $start_pos, $end_pos - $start_pos);
            }
            //print_r($rs);
            return $rs;
        }

        if($type == dbListClass::WEIBO_LIST){
            $obj = json_decode($data);
            $arr = $obj->cards[0]->card_group;

            foreach ($arr as $key => $value){
                $arr[$key]->mblog->created_at = time_to_unix_time($arr[$key]->mblog->created_at);
            }

            return $arr;
        }

        if($type == dbListClass::PINGLUN_LIST){
            $obj = json_decode($data);
            $arr = $obj[0]->card_group;

            foreach ($arr as $key => $value){
                $arr[$key]->created_at = time_to_unix_time($arr[$key]->created_at);
            }

            return $arr;
        }

        if($type == dbListClass::ZHUANFA_LIST){
            $obj = json_decode($data);
            $arr = $obj[0]->card_group;

            foreach ($arr as $key => $value){
                $arr[$key]->created_at = time_to_unix_time($arr[$key]->created_at);
            }
            return $arr;
        }
    }
}