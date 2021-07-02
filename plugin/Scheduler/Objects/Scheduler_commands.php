<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Scheduler_commands extends ObjectYPT {
    
    public static $statusActive = 'a';
    public static $statusInactive = 'i';
    public static $statusCanceled = 'c';
    public static $statusExecuted = 'e';
    
    protected $id,$callbackURL,$parameters,$date_to_execute,$executed_in,$status, $callbackResponse;
    
    static function getSearchFieldsNames() {
        return array('callbackURL','parameters');
    }

    static function getTableName() {
        return 'scheduler_commands';
    }
    public static function getAllActiveAndReady() {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='a' AND date_to_execute <= now() ";

        $sql .= self::getSqlFromPost();
        //echo $sql;
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }    
     
    function setId($id) {
        $this->id = intval($id);
    } 
 
    function setCallbackURL($callbackURL) {
        $this->callbackURL = $callbackURL;
    } 
 
    function setParameters($parameters) {
        $this->parameters = $parameters;
    } 
 
    function setDate_to_execute($date_to_execute) {
        if(is_numeric($date_to_execute)){
            $date_to_execute = date('Y-m-d H:i:s', $date_to_execute);
        }
        $this->date_to_execute = $date_to_execute;
    } 
 
    function setExecuted_in($executed_in) {
        $this->executed_in = $executed_in;
    } 
 
    function setStatus($status) {
        $this->status = $status;
    } 
    
     
    function getId() {
        return intval($this->id);
    }  
 
    function getCallbackURL() {
        return $this->callbackURL;
    }  
 
    function getParameters() {
        return $this->parameters;
    }  
 
    function getDate_to_execute() {
        return $this->date_to_execute;
    }  
 
    function getExecuted_in() {
        return $this->executed_in;
    }  
 
    function getStatus() {
        return $this->status;
    }  
    
    function getCallbackResponse() {
        return $this->callbackResponse;
    }

    function setCallbackResponse($callbackResponse) {
        $this->callbackResponse = $callbackResponse;
    }

        
    function setExecuted($callbackResponse) {
        if(!is_string($callbackResponse)){
            $callbackResponse = json_encode($callbackResponse);
        }
        $this->setExecuted_in(date('Y-m-d H:i:s'));
        $this->setCallbackResponse($callbackResponse);
        $this->setStatus(self::$statusExecuted);
        return $this->save();
    } 
    
    public function save() {
        if(empty($this->date_to_execute)){
            _error_log("Scheduler_commands::save(): date_to_execute is empty ". json_encode(debug_backtrace()));
            return false;
        }
        if(empty($this->executed_in)){
            $this->executed_in = 'NULL';
        }
        if(empty($this->status)){
            $this->status = self::$statusActive;
        }
        if(empty($this->callbackURL)){
            $this->callbackURL = '';
        }
        return parent::save();
    }
        
}
