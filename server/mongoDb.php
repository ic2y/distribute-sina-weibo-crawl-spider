<?php

/**
 * Created by PhpStorm.
 * User: cao
 * Date: 16-6-4
 * Time: 下午2:44
 */
class mongoManager
{
    public $manager;
    public static $dbName = 'sina';

    /**
     * mongoDb constructor.
     */
    public function __construct()
    {
        //php 7.0
        //$this->manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

        $this->manager = new MongoClient("mongodb://huihui:huihui@127.0.0.1");
        $this->db = $this->manager->sina;
    }



    public function insertOne($data,$type){
        $tableName = self::$dbName .".".$type;
        $this->_insertOne($tableName, $data);
    }

    public function insertArray($data,$type){
        $tableName = self::$dbName .".".$type;
        if($type == dbListClass::PINGLUN_LIST ||
            $type == dbListClass::ZHUANFA_LIST ||
            $type == dbListClass::WEIBO_LIST ||
            $type == dbListClass::RELATION_LIST){
            foreach ($data as $value){
                $this->_insertArray($tableName, $value);
            }
        }else {
            $this->_insertArray($tableName, $data);
        }
    }

    public function insertOneOrArray($data,$type){
        $this->insertArray($data, $type);
    }



    public function _insertArray($table,$arr){
        /*
         * php 7.0
        $bulk = new MongoDB\Driver\BulkWrite;
        foreach ($arr as $value){
            $bulk->insert($value);
        }
        $this->manager->executeBulkWrite($table,$bulk);
        */
        $this->db->$table->insert($arr);
    }

    public function _insertOne($table,$data){

        if(!$data){
            return;
        }
        /*
         * php 7.0
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert($data);
        $this->manager->executeBulkWrite($table,$bulk);
        */
        $this->db->$table->insert($data);
    }

    /**
     * @param $table string
     * @param $key  string
     * @param $value    string
     * @return \MongoDB\Driver\Cursor array
     */
    public function _findKyKey($table,$key,$value){
        /*
         * php 7.0
        $filter = array();

        if($key){
            $filter = [$key => $value];
        }
        $query = new MongoDB\Driver\Query($filter, array());
        return $this->manager->executeQuery($table, $query);
        */
        $query = array($key => $value);
        $cursor = $this->db->$table->find($query);
        if($cursor->hasNext()){
            return $cursor->getNext();
        }
        return null;
    }

    public function _findById($table,$idValue){
        /*
         * php 7.0
        $rs = $this->_findKyKey($table,'_id' , new \MongoDB\BSON\ObjectID($idValue));
        foreach ($rs as $value){
            return $value;
        }
        return null;
        */
        $tableName = self::$dbName .".".$table;
        return $this->db->$tableName->findOne(array('_id' => new MongoId($idValue)));
    }
};
