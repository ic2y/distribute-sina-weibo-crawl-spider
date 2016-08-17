<?php

/**
 * 处理处理各种等待队列
 * Created by PhpStorm.
 * User: cao
 * Date: 16-5-28
 * Time: 下午2:11
 */
class dbListClass
{
    public $redis;
    const WEIBO_LIST = "weibo_list";
    const RELATION_LIST = "relation_list";
    const PINGLUN_LIST = "pinglun_list";
    const ZHUANFA_LIST = "zhuanfa_list";
    const TAG_LIST = "tag_list";
    const HOME_LIST = "home_list";
    const UN_CRAWL_USER_LIST = "uncrawl_user_list";
    const CRAWLED_USER_SET = "crawled_user_set";


    const un_done_user = "un_done_user";
    const done_user = "done_user";
    const un_done_weibo = "un_done_weibo";
    const done_weibo = "done_weibo";

    const POP_LIMIT = 10; //每种类型的抓取,每次最多弹出10个
    const POP_LIMIT_MULTIPLE = 2; //某些种类需要进行加倍
    const INSERT_LIMIT = 50; //当种类不够的时候,每次插入最多每种插入50个

    const CONFIG_COOKIE_LIST = "config_cookie_list";

    function __construct(redisDb $r)
    {
        $this->redis = $r;
    }


    /**
     * 存储已经分配出去的任务的缓存,防止客户端 没有及时完成任务造成的丢失
     * @param $device_id 设备号
     * @param $job_arr 键是任务的url的种类,值是任务的url的数组
     */
    function add_device_job_list($device_id,$job_arr){
        if(is_array($job_arr)) {
            foreach ($job_arr as $key => $value) {
                $key_name = "device_job_".$key."_".$device_id;
                foreach($value as $item){
                    $this->redis->db->lPush($key_name, $item);
                }

            }
        }
    }

    /**
     * 在客户端非正常关闭的情况下,device_job的内容是非空的,需要处理异常情况,重新恢复到等待队列
     *
     * @param $device_id string 设备号
     */
    function restore_device_job_list($device_id){
        $it = null;
        foreach (jobClass::$crawl_job_type as $type){
            $key_name = "device_job_".$type."_".$device_id;

            //清空设备的等待队列,并插入到全局等待队列
            while($it = $this->redis->db->lPop($key_name)){
                $this->redis->db->lPush($type, $it);
            }
        }
    }

    /**
     * 从新收集来的信息,提取准备抓取的下一步内容,主要有两部分,一个是微博的relation,一个是 微博内容的uid
     * @param $data array 
     * @param $type string  
     * @param $url string 
     */
    function parse_new_relation_and_weibo($data,$type,$url){
        if($type == self::RELATION_LIST){
            foreach ($data as $value){
                $id = $value->user->id;
                if(!$this->set_exist(self::done_user, $id)){
                    $this->set_insert(self::un_done_user, $id);
                }
            }
        }
        
        if($type == self::WEIBO_LIST){
            foreach ($data as $value){
                $id = $value->mblog->id;
                if(!$this->set_exist(self::done_weibo, $id)){
                    $this->set_insert(self::un_done_weibo, $id);
                }
            }
            
        }
    }

    function delete_one_item_from_device_job_list($device_id,$type,$url){
        $key_name = "device_job_".$type."_".$device_id;
        $this->redis->delele_one($key_name, $url);
    }

    function add_home_list($list_arr){
        $this->add_list(dbListClass::HOME_LIST, $list_arr);
    }
    function add_weibo_list($list_arr){
        $this->add_list(dbListClass::WEIBO_LIST, $list_arr);
    }
    function add_relation_list($list_arr){
        $this->add_list(dbListClass::RELATION_LIST, $list_arr);
    }

    function add_pinglun_list($list_arr){
        $this->add_list(dbListClass::PINGLUN_LIST, $list_arr);
    }
    function add_zhuanfa_list($list_arr){
        $this->add_list(dbListClass::ZHUANFA_LIST, $list_arr);
    }
    function add_tag_list($list_arr){
        $this->add_list(dbListClass::TAG_LIST, $list_arr);
    }

    function add_list($list_name,$list_arr){
        if(is_string($list_arr)){
            $this->redis->db->lPush($list_name, $list_arr);
        }
        if(is_array($list_arr)) {
            foreach ($list_arr as $value) {
                $this->redis->db->lPush($list_name, $value);
            }
        }
    }

    function pop_list($list_name){
        $rs = array();
        $limit = dbListClass::POP_LIMIT;

        if($list_name == dbListClass::ZHUANFA_LIST ||
        $list_name == dbListClass::PINGLUN_LIST ){
            $limit = $limit * dbListClass::POP_LIMIT_MULTIPLE;
        }

        for($i = 0;$i < $ldimit;++$i){
            $item = $this->redis->db->lPop($list_name);
            if($item) {
                array_push($rs, $item);
            }
        }
        
        return $rs;
    }

    function set_insert($setName,$value){
        $this->redis->db->sAdd($setName,intval($value));
    }
    function set_exist($setName,$value){
        return $this->redis->db->sContains($setName, $value);
    }
    function transfer_set_item($from,$to,$value){
        $this->redis->db->sMove($from, $to, $value);
    }
    function set_pop($setName){
        return $this->redis->db->sPop($setName);
    }
    
    function get_one_cookie(){
        $len = $this->redis->db->lLen(dbListClass::CONFIG_COOKIE_LIST);
        if($len == 0){
            return "";
        }
        $cookie_arr = $this->redis->db->lRange(dbListClass::CONFIG_COOKIE_LIST,0 ,$len);
        
        return $cookie_arr[rand(0,sizeof($cookie_arr) - 1)];
    }


    function add_one_cookie($cookie){
        $this->redis->db->lPush(dbListClass::CONFIG_COOKIE_LIST, $cookie);
    }

    function delete_obj($key){
        $this->redis->db->del($key);
    }
}