<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Users_affiliations extends ObjectYPT {

    protected $id, $users_id_company, $users_id_affiliate, $status, $company_agree_date, $affiliate_agree_date;

    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'users_affiliations';
    }

    static function getAllUsers($companyOnly = false) {
        if ($companyOnly) {
            $isCompany = User::$is_company_status_ISACOMPANY;
        } else {
            $isCompany = User::$is_company_status_NOTCOMPANY;
        }
        $rows = User::getAllUsers(false, ['name', 'email', 'user', 'channelName', 'about'], 'a', null, $isCompany);
        return $rows;
    }

    function setId($id) {
        $this->id = intval($id);
    }

    function setUsers_id_company($users_id_company) {
        $this->users_id_company = intval($users_id_company);
    }

    function setUsers_id_affiliate($users_id_affiliate) {
        $this->users_id_affiliate = intval($users_id_affiliate);
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setCompany_agree_date($company_agree_date) {
        $this->company_agree_date = $company_agree_date;
    }

    function setAffiliate_agree_date($affiliate_agree_date) {
        $this->affiliate_agree_date = $affiliate_agree_date;
    }

    function getId() {
        return intval($this->id);
    }

    function getUsers_id_company() {
        return intval($this->users_id_company);
    }

    function getUsers_id_affiliate() {
        return intval($this->users_id_affiliate);
    }

    function getStatus() {
        return $this->status;
    }

    function getCompany_agree_date() {
        return $this->company_agree_date;
    }

    function getAffiliate_agree_date() {
        return $this->affiliate_agree_date;
    }

    public static function getAll($users_id_company = 0, $users_id_affiliate = 0, $activeOnly = false) {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        
        $users_id_company = intval($users_id_company);
        $users_id_affiliate = intval($users_id_affiliate);
        
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";
        
        if(!empty($users_id_company)){
            $sql .= "AND users_id_company = $users_id_company ";
        }
        if(!empty($users_id_affiliate)){
            $sql .= "AND users_id_affiliate = $users_id_affiliate ";
        }
        if(!empty($activeOnly)){
            $sql .= "AND status = 'a' ";
        }

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res !== false) {
            foreach ($fullData as $row) {
                $row['company'] = User::getNameIdentificationById($row['users_id_company']);
                $row['affiliate'] = User::getNameIdentificationById($row['users_id_affiliate']);
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }
    
    public static function getTotal($users_id_company = 0, $users_id_affiliate = 0, $activeOnly = false)
    {
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        if (!static::isTableInstalled()) {
            return 0;
        }
        $sql = "SELECT id FROM  " . static::getTableName() . " WHERE 1=1  ";
        if(!empty($users_id_company)){
            $sql .= "AND users_id_company = $users_id_company ";
        }
        if(!empty($users_id_affiliate)){
            $sql .= "AND users_id_affiliate = $users_id_affiliate ";
        }
        if(!empty($activeOnly)){
            $sql .= "AND status = 'a' ";
        }
        $sql .= self::getSqlSearchFromPost();
        $res = sqlDAL::readSql($sql);
        $countRow = sqlDAL::num_rows($res);
        sqlDAL::close($res);
        return $countRow;
    }
    
    public function save() {
        if(!empty($this->company_agree_date) && !empty($this->affiliate_agree_date)){
            $this->status = 'a';
        }else{
            $this->status = 'i';
        }
        return parent::save();
    }

}
