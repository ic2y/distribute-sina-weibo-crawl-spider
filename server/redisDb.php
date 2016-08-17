<?php

/**
 * Created by PhpStorm.
 * User: cao
 * Date: 16-5-24
 * Time: 下午9:11
 */
class redisDb
{
    public $db;
    public function __construct()
    {
        $this->db = new Redis();
        $this->db->connect('127.0.0.1', 6379);
    }

    public function ping(){
        echo "Connection to server sucessfully";
        //查看服务是否运行
        echo "Server is running: " . $this->db->ping();
    }

    public function insert(){
        $this->db->lpush("weibo", "Mysql");
        $this->db->lpush("weibo", "Mysql");
        $this->db->lpush("weibo", "Mysql");
        $this->db->lpush("weibo", "Mysql");
        $this->db->lpush("weibo", "Mysql2");

    }
    public function show(){
        $a = $this->db->lrange("weibo", 0 ,5);
        print_r($a);
    }

    public function delele_one($key,$value){
        return $this->db->lRem($key, $value, 0);
    }
    
}
