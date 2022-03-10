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

        if (!empty($users_id_company)) {
            $sql .= "AND users_id_company = $users_id_company ";
        }
        if (!empty($users_id_affiliate)) {
            $sql .= "AND users_id_affiliate = $users_id_affiliate ";
        }
        if (!empty($activeOnly)) {
            $sql .= "AND status = 'a' ";
        }

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res !== false) {
            foreach ($fullData as $row) {
                if ($row['company_agree_date'] === '0000-00-00 00:00:00') {
                    $row['company_agree_date'] = null;
                }
                if ($row['affiliate_agree_date'] === '0000-00-00 00:00:00') {
                    $row['affiliate_agree_date'] = null;
                }

                $row['company'] = User::getNameIdentificationById($row['users_id_company']);
                $row['affiliate'] = User::getNameIdentificationById($row['users_id_affiliate']);
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public static function getTotal($users_id_company = 0, $users_id_affiliate = 0, $activeOnly = false) {
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        if (!static::isTableInstalled()) {
            return 0;
        }
        $sql = "SELECT id FROM  " . static::getTableName() . " WHERE 1=1  ";
        if (!empty($users_id_company)) {
            $sql .= "AND users_id_company = $users_id_company ";
        }
        if (!empty($users_id_affiliate)) {
            $sql .= "AND users_id_affiliate = $users_id_affiliate ";
        }
        if (!empty($activeOnly)) {
            $sql .= "AND status = 'a' ";
        }
        $sql .= self::getSqlSearchFromPost();
        $res = sqlDAL::readSql($sql);
        $countRow = sqlDAL::num_rows($res);
        sqlDAL::close($res);
        return $countRow;
    }

    public function save() {

        if (empty($this->id) && !empty($this->users_id_company) && !empty($this->users_id_affiliate)) {
            $row = self::getAll($this->users_id_company, $this->users_id_affiliate);
            //var_dump($row);
            if (!empty($row)) {
                foreach ($row as $value) {
                    if (!empty($value['id'])) {
                        $this->id = $value['id'];
                        break;
                    }
                }
            }
        }

        if (empty($this->company_agree_date) || $this->company_agree_date == '0000-00-00 00:00:00' || $this->company_agree_date == 'NULL') {
            $this->company_agree_date = null;
        }
        if (empty($this->affiliate_agree_date) || $this->affiliate_agree_date == '0000-00-00 00:00:00' || $this->affiliate_agree_date == 'NULL') {
            $this->affiliate_agree_date = null;
        }

        if (empty($this->users_id_affiliate)) {
            $this->users_id_affiliate = null;
        }
        if (empty($this->users_id_company)) {
            $this->users_id_company = null;
        }

        //var_dump($this);exit;
        if (!empty($this->id) && empty($this->company_agree_date) && empty($this->affiliate_agree_date)) {
            _error_log('Affiliation: both dates are empty, delete ' . $this->id);
            return self::deleteFromID($this->id);
        } else if (!empty($this->company_agree_date) && !empty($this->affiliate_agree_date)) {
            _error_log('Affiliation: both dates are NOT empty, make it active ' . $this->id);
            $this->status = 'a';
        } else {
            _error_log('Affiliation: one date is empty ' . $this->id . " company_agree_date={$this->company_agree_date} affiliate_agree_date={$this->affiliate_agree_date}");
            $this->status = 'i';
        }
        return parent::save();
    }

    static public function deleteFromID($id) {
        $id = intval($id);
        global $global;
        if (!empty($id)) {
            $sql = "DELETE FROM users_affiliations ";
            $sql .= " WHERE id = ?";
            $global['lastQuery'] = $sql;
            _error_log("Delete Query: " . $sql);
            return sqlDAL::writeSql($sql, "i", [$id]);
        }
        _error_log("Id for table " . static::getTableName() . " not defined for deletion " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)), AVideoLog::$ERROR);
        return false;
    }

    static function canEditAffiliation($Users_affiliations_id) {
        $Users_affiliations_id = intval($Users_affiliations_id);
        if (empty($Users_affiliations_id)) {
            return false;
        }

        if (User::isAdmin()) {
            return true;
        }
        if (!User::isLogged()) {
            return false;
        }

        $o = new Users_affiliations($Users_affiliations_id);

        if (empty($o->getStatus())) {
            return false;
        }

        if ($o->getUsers_id_affiliate() != User::getId() && $o->getUsers_id_company() != User::getId()) {
            return false;
        }

        return true;
    }

    static function isUserAffiliateOrCompanyToEachOther($users_id1, $users_id2) {
        $row = self::getAll($users_id1, $users_id2, true);
        if (!empty($row)) {
            foreach ($row as $value) {
                if (!empty($value['id'])) {
                    return $value;
                }
            }
        }
        $row = self::getAll($users_id2, $users_id1, true);
        //var_dump($row);
        if (!empty($row)) {
            foreach ($row as $value) {
                if (!empty($value['id'])) {
                    return $value;
                }
            }
        }
        return false;
    }

}
