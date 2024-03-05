<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Ai_responses_json extends ObjectYPT {

    protected $id,$response,$ai_type,$ai_responses_id;
    
    static function getSearchFieldsNames() {
        return array('response','ai_type');
    }

    static function getTableName() {
        return 'ai_responses_json';
    }
     
    function setId($id) {
        $this->id = intval($id);
    } 
 
    function setResponse($response) {
        if(!is_string($response)){
            $response = json_encode($response);
        }
        $this->response = $response;
    } 
 
    function setAi_type($ai_type) {
        $this->ai_type = $ai_type;
    } 
 
    function setAi_responses_id($ai_responses_id) {
        $this->ai_responses_id = intval($ai_responses_id);
    }
     
    function getId() {
        return intval($this->id);
    }  
 
    function getResponse() {
        return $this->response;
    }  
 
    function getAi_type() {
        return $this->ai_type;
    }  
 
    function getAi_responses_id() {
        return intval($this->ai_responses_id);
    }  

    static function getAllFromAIType($type, $videos_id = 0) {
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  ai_responses_json arj LEFT JOIN ai_responses ar ON ar.id = arj.ai_responses_id WHERE ai_type = ? ";
        $format = 's';
        $values = array($type);
        if(!empty($videos_id)){
            $sql .= " AND videos_id = ? ";
            $format .= 'i';
            $values[] = $videos_id;
        }

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql, $format, $values);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $fullData;
    }  
        
}
