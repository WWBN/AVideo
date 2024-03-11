<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Ai_metatags_responses extends ObjectYPT {

    protected $id,$videoTitles,$keywords,$professionalDescription,$casualDescription,$shortSummary,$metaDescription,$rrating,$rratingJustification,$prompt_tokens,$completion_tokens,$price_prompt_tokens,$price_completion_tokens,$ai_responses_id;
    
    static function getSearchFieldsNames() {
        return array('videoTitles','keywords','professionalDescription','casualDescription','shortSummary','metaDescription','rrating','rratingJustification');
    }

    static function getTableName() {
        return 'ai_metatags_responses';
    }
    
    static function getAllAi_responses() {
        global $global;
        $table = "ai_responses";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } 
        return $rows;
    }
    
     
    function setId($id) {
        $this->id = intval($id);
    } 
 
    function setVideoTitles($videoTitles) {
        if(!is_string($videoTitles)){
            $videoTitles = json_encode($videoTitles);
        }
        $this->videoTitles = $videoTitles;
    } 
 
    function setKeywords($keywords) {
        if(!is_string($keywords)){
            $keywords = json_encode($keywords);
        }
        $this->keywords = $keywords;
    } 
 
    function setProfessionalDescription($professionalDescription) {
        $this->professionalDescription = $professionalDescription;
    } 
 
    function setCasualDescription($casualDescription) {
        $this->casualDescription = $casualDescription;
    } 
 
    function setShortSummary($shortSummary) {
        $this->shortSummary = $shortSummary;
    } 
 
    function setMetaDescription($metaDescription) {
        $this->metaDescription = $metaDescription;
    } 
 
    function setRrating($rrating) {
        $this->rrating = $rrating;
    } 
 
    function setRratingJustification($rratingJustification) {
        $this->rratingJustification = $rratingJustification;
    } 
 
    function setPrompt_tokens($prompt_tokens) {
        $this->prompt_tokens = intval($prompt_tokens);
    } 
 
    function setcompletion_tokens($completion_tokens) {
        $this->completion_tokens = intval($completion_tokens);
    } 
 
    function setPrice_prompt_tokens($price_prompt_tokens) {
        $this->price_prompt_tokens = $price_prompt_tokens;
    } 
 
    function setPrice_completion_tokens($price_completion_tokens) {
        $this->price_completion_tokens = $price_completion_tokens;
    } 
 
    function setAi_responses_id($ai_responses_id) {
        $this->ai_responses_id = intval($ai_responses_id);
    } 
    
     
    function getId() {
        return intval($this->id);
    }  
 
    function getVideoTitles() {
        return $this->videoTitles;
    }  
 
    function getKeywords() {
        return $this->keywords;
    }  
 
    function getProfessionalDescription() {
        return $this->professionalDescription;
    }  
 
    function getCasualDescription() {
        return $this->casualDescription;
    }  
 
    function getShortSummary() {
        return $this->shortSummary;
    }  
 
    function getMetaDescription() {
        return $this->metaDescription;
    }  
 
    function getRrating() {
        return $this->rrating;
    }  
 
    function getRratingJustification() {
        return $this->rratingJustification;
    }  
 
    function getPrompt_tokens() {
        return intval($this->prompt_tokens);
    }  
 
    function getcompletion_tokens() {
        return intval($this->completion_tokens);
    }  
 
    function getPrice_prompt_tokens() {
        return $this->price_prompt_tokens;
    }  
 
    function getPrice_completion_tokens() {
        return $this->price_completion_tokens;
    }  
 
    function getAi_responses_id() {
        return intval($this->ai_responses_id);
    }  

    static function getAllFromVideosId($videos_id){
        global $global;
        $sql = "SELECT ar.*, amr.*
        FROM ai_metatags_responses amr
        LEFT JOIN ai_responses ar ON amr.ai_responses_id = ar.id
        WHERE ar.videos_id = ?";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql, 'i', array($videos_id));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        
        return $fullData;
    }
}
