<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Email_to_user extends ObjectYPT {

    protected $id, $sent_at, $timezone, $emails_messages_id, $users_id;

    static function getSearchFieldsNames() {
        return array('timezone');
    }

    static function getTableName() {
        return 'email_to_user';
    }

    static function getAllEmails_messages() {
        global $global;
        $table = "emails_messages";
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
        } else {
            /**
             * 
             * @var array $global
             * @var object $global['mysqli'] 
             */
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    
    static function setSent($email_to_user_id){
        global $global;
        if(!is_array($email_to_user_id)){
            $email_to_user_id = array($email_to_user_id);
        }
        $sql = "UPDATE email_to_user SET sent_at = NOW(), timezone = ? WHERE id IN (". implode(', ', $email_to_user_id).")";
        return sqlDAL::writeSql($sql, 's', [date_default_timezone_get()]);
    }
    
    static function alreadyHasMessageSet($users_id, $emails_messages_id){
        global $global;
        $sql = "SELECT * FROM email_to_user WHERE sent_at is NULL AND users_id = ? AND emails_messages_id = ?";
        $res = sqlDAL::readSql($sql, 'ii', [$users_id, $emails_messages_id], true);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        //var_dump($sql, [$users_id, $emails_messages_id], $fullData);
        return !empty($fullData);
    }
    
    static function getAllToSend(){
        global $global;
        $sql = "SELECT * FROM email_to_user WHERE sent_at is NULL ";
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $fullData;
    }
    
    static function getAllEmailsToSend(){
        global $global;
        $emails = new stdClass();
        $sql = "SELECT message, subject, GROUP_CONCAT(u.email) as emails, GROUP_CONCAT(etu.id) as ids
                FROM email_to_user etu
                LEFT JOIN users u ON users_id = u.id
                LEFT JOIN emails_messages em ON emails_messages_id = em.id 
                WHERE sent_at IS NULL 
                GROUP BY emails_messages_id";
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $fullData;
    }

    function setId($id) {
        $this->id = intval($id);
    }

    function setSent_at($sent_at) {
        $this->sent_at = $sent_at;
    }

    function setTimezone($timezone) {
        $this->timezone = $timezone;
    }

    function setEmails_messages_id($emails_messages_id) {
        $this->emails_messages_id = intval($emails_messages_id);
    }

    function setUsers_id($users_id) {
        $this->users_id = intval($users_id);
    }

    function getId() {
        return intval($this->id);
    }

    function getSent_at() {
        return $this->sent_at;
    }

    function getTimezone() {
        return $this->timezone;
    }

    function getEmails_messages_id() {
        return intval($this->emails_messages_id);
    }

    function getUsers_id() {
        return intval($this->users_id);
    }
    
    public function save() {
        
        if(self::alreadyHasMessageSet($this->users_id, $this->emails_messages_id)){
            return false;
        }
        
        if(empty($this->sent_at)){
            $this->sent_at = 'NULL';
        }
        
        return parent::save();
    }

}
