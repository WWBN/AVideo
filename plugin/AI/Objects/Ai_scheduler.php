<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Ai_scheduler extends ObjectYPT {

    static $statusActive = 'a';
    static $statusProcessingTranscription = 't';
    static $statusProcessingTranslation = 'r';
    static $statusProcessingShort = 's';
    static $statusProcessingBasic = 'b';

    static $statusInactive = 'i';
    static $statusExecuted = 'e';
    static $statusError = 'x';

    static $typeCutVideo = 'cutVideo';
    static $typeProcessAll = 'processAll';


    protected $id,$json,$status,$ai_scheduler_type,$created_php_time,$modified_php_time;
    
    static function getSearchFieldsNames() {
        return array('ai_scheduler_type');
    }

    static function getTableName() {
        return 'ai_scheduler';
    }
         
    function setId($id) {
        $this->id = intval($id);
    } 
 
    function setJson($json) {
        if(!is_string($json)){
            $json = _json_encode($json);
        }
        $this->json = $json;
    } 
 
    function setStatus($status) {
        _error_log('AI::setStatus '._json_encode(debug_backtrace()));
        $this->status = $status;
    } 
 
    function setAi_scheduler_type($ai_scheduler_type) {
        $this->ai_scheduler_type = $ai_scheduler_type;
    } 
 
    function setCreated_php_time($created_php_time) {
        $this->created_php_time = $created_php_time;
    } 
 
    function setModified_php_time($modified_php_time) {
        $this->modified_php_time = $modified_php_time;
    } 
    
     
    function getId() {
        return intval($this->id);
    }  
 
    function getJson() {
        return $this->json;
    }  
 
    function getStatus() {
        return $this->status;
    }  
 
    function getAi_scheduler_type() {
        return $this->ai_scheduler_type;
    }  
 
    function getCreated_php_time() {
        return $this->created_php_time;
    }  
 
    function getModified_php_time() {
        return $this->modified_php_time;
    }  

        
    public static function getAllToExecute()
    {
        global $global;
        $sql = "SELECT * FROM  ai_scheduler WHERE status NOT IN ('".Ai_scheduler::$statusInactive."','".Ai_scheduler::$statusExecuted."','".Ai_scheduler::$statusError."') ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $fullData;
    }
    
    public static function isAlreadyScheduled($videos_id, $type, $status)
    {
        $row = self::getFromVideoAndStatus($videos_id, $type, $status);
        return !empty($row);
    }

    public static function getFromVideoAndStatus($videos_id, $type, $status)
    {
        global $global;
        $sql = "SELECT * FROM  ai_scheduler WHERE ai_scheduler_type = '$type' AND status = '{$status}' ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        foreach ($fullData as $key => $value) {
            $obj = _json_decode($value['json']);
            if($obj->videos_id == $videos_id){
                return $value;
            }
        }
        return false;
    }

    public static function saveToProcessAll($videos_id, $users_id)
    {
        $obj2 = new stdClass();
        $obj2->videos_id = $videos_id;
        $obj2->users_id = $users_id;
        $obj2->error = true;
        $obj2->msg = '';
        if(!Ai_scheduler::isAlreadyScheduled($videos_id, Ai_scheduler::$typeProcessAll, Ai_scheduler::$statusActive)){
            $ai = new Ai_scheduler(0);
            $ai->setAi_scheduler_type(Ai_scheduler::$typeProcessAll);
            $ai->setJson($obj2);
            $ai->setStatus(Ai_scheduler::$statusActive);
            $obj2->schedulerSaved = $ai->save();
            $obj2->error = empty($obj2->schedulerSaved);
            if($obj2->error){
                $obj2->msg = _('Error');
            }else{
                $obj2->msg = _('Your video has been successfully scheduled for AI processing! You will receive notifications regarding the progress and completion.');
            }
        }else{
            $obj2->msg = _('This video is already scheduled to process');
        }
        return $obj2;
    }

}
