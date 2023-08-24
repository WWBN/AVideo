<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Emails_messages extends ObjectYPT {

    protected $id,$message,$subject;
    
    static function getSearchFieldsNames() {
        return array('message','subject');
    }

    static function getTableName() {
        return 'emails_messages';
    }    
     
    function setId($id) {
        $this->id = intval($id);
    } 
 
    function setMessage($message) {
        $this->message = $message;
    } 
 
    function setSubject($subject) {
        $this->subject = $subject;
    } 
    
     
    function getId() {
        return intval($this->id);
    }  
 
    function getMessage() {
        return $this->message;
    }  
 
    function getSubject() {
        return $this->subject;
    }  

    static function setOrCreate($message, $subject): Emails_messages {
        global $global;
        $sql = "SELECT id FROM emails_messages WHERE message = ? ";
        $res = sqlDAL::readSql($sql, 's', [$message], true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if(empty($data)){
            $obj = new Emails_messages(0);
            $obj->setMessage($message);
            $obj->setSubject($subject);
            $id = $obj->save();
            $obj->setId($id);
        }else{
            $obj = new Emails_messages($data['id']);
        }
        return $obj;
    }    
        
}
