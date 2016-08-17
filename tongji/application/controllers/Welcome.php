<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {
    public $show_key = [
        "home",
        "relation",
        "pinglun",
        "zhuanfa",
        "weibo",
        "tag"
        ];

    private function create_time_sting($time,$val){
        $time = $time * 1000;
        $str = " [".$time.", ".$val."]";
        return $str;
    }
	public function index()
	{
	    $time = time();
        $start_time = $time - 1 * 24 * 3600;

        $sql = "SELECT * FROM tongji WHERE time > ?";

        $rs = $this->db->query($sql,array($start_time))->result();
        //print_r($rs);
		$buf = array();
        foreach ($this->show_key as $val){
            $buf[$val] = array();
        }
        foreach ($rs as $obj){
            foreach ($this->show_key as $key){
                array_push($buf[$key],[
                    $obj->time * 1000,
                    intval($obj->$key)
                ]);
            }

        }

        $json = [];
        foreach($buf as $key => $value){
            $json[] = array(
                "name" => $key,
                "data" => $value
            );
        }

        $data["json"] = json_encode($json);
        $this->load->view('welcome_message',$data);
	}

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

	public function count(){
        $this->load->library("mongo_db");
        $home_count = $this->mongo_db->count("sina.home_list");
        $pinglun_count = $this->mongo_db->count("sina.pinglun_list");
        $relation_count = $this->mongo_db->count("sina.relation_list");
        $tag_count = $this->mongo_db->count("sina.tag_list");
        $weibo_count = $this->mongo_db->count("sina.weibo_list");
        $zhuanfa_count = $this->mongo_db->count("sina.zhuanfa_list");

        $data = [
            "time" => time(),
            "home" => $home_count,
            "pinglun" => $pinglun_count,
            "relation" => $relation_count,
            "tag" => $tag_count,
            "weibo" => $weibo_count,
            "zhuanfa" => $zhuanfa_count
        ];
        $this->db->insert("tongji",$data);

        echo "ok";
    }
}
