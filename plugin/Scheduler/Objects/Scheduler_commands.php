<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Scheduler_commands extends ObjectYPT {

    public static $statusActive = 'a';
    public static $statusInactive = 'i';
    public static $statusCanceled = 'c';
    public static $statusExecuted = 'e';
    public static $statusRepeat = 'r';
    protected $id, $callbackURL, $parameters, $date_to_execute, $executed_in,
            $status, $callbackResponse, $timezone,
            $repeat_minute, $repeat_hour, $repeat_day_of_month, $repeat_month,
            $repeat_day_of_week, $type;

    static function getSearchFieldsNames() {
        return array('callbackURL', 'parameters');
    }

    static function getTableName() {
        return 'scheduler_commands';
    }

    public static function getAllScheduledTORepeat() {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='" . self::$statusRepeat . "' ";

        $minute = intval(date('i'));
        $hour = intval(date('H'));
        $day_of_month = intval(date('d'));
        $month = intval(date('m'));
        $day_of_week = intval(date('w'));

        $sql .= " AND (repeat_minute IS NULL OR repeat_minute = {$minute}) ";
        $sql .= " AND (repeat_hour IS NULL OR repeat_hour = {$hour}) ";
        $sql .= " AND (repeat_day_of_month IS NULL OR repeat_day_of_month = {$day_of_month}) ";
        $sql .= " AND (repeat_month IS NULL OR repeat_month = {$month}) ";
        $sql .= " AND (repeat_day_of_week IS NULL OR repeat_day_of_week = {$day_of_week}) ";

        //echo $sql;exit;
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

    public static function getAllActiveAndReady() {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='" . self::$statusActive . "' AND date_to_execute <= now() ";

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
        if (is_numeric($date_to_execute)) {
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
        if (!is_string($callbackResponse)) {
            $callbackResponse = json_encode($callbackResponse);
        }
        $this->setExecuted_in(date('Y-m-d H:i:s'));
        $this->setCallbackResponse($callbackResponse);

        if ($this->status !== self::$statusRepeat) {
            $this->setStatus(self::$statusExecuted);
        }
        return $this->save();
    }

    function getTimezone() {
        return $this->timezone;
    }

    private function _setTimezone($timezone) {
        $this->timezone = $timezone;
    }

    public function save() {
        if (empty($this->date_to_execute)) {
            $this->date_to_execute = 'NULL';
        }
        if (empty($this->executed_in)) {
            $this->executed_in = 'NULL';
        }
        if (empty($this->type)) {
            $this->type = 'NULL';
        }
        if (!isset($this->repeat_minute)) {
            $this->repeat_minute = 'NULL';
        }
        if (!isset($this->repeat_hour)) {
            $this->repeat_hour = 'NULL';
        }
        if (!isset($this->repeat_day_of_month)) {
            $this->repeat_day_of_month = 'NULL';
        }
        if (!isset($this->repeat_month)) {
            $this->repeat_month = 'NULL';
        }
        if (!isset($this->repeat_day_of_week)) {
            $this->repeat_day_of_week = 'NULL';
        }
        if (empty($this->status)) {
            $this->status = self::$statusActive;
        }
        if (empty($this->callbackURL)) {
            $this->callbackURL = '';
        }

        $this->_setTimeZone(date_default_timezone_get());

        return parent::save();
    }

    function getRepeat_minute() {
        return $this->repeat_minute;
    }

    function getRepeat_hour() {
        return $this->repeat_hour;
    }

    function getRepeat_day_of_month() {
        return $this->repeat_day_of_month;
    }

    function getRepeat_month() {
        return $this->repeat_month;
    }

    function setRepeat_minute($repeat_minute) {
        $this->repeat_minute = intval($repeat_minute);
    }

    function setRepeat_hour($repeat_hour) {
        $this->repeat_hour = intval($repeat_hour);
    }

    function setRepeat_day_of_month($repeat_day_of_month) {
        $this->repeat_day_of_month = intval($repeat_day_of_month);
    }

    function setRepeat_month($repeat_month) {
        $this->repeat_month = intval($repeat_month);
    }

    function getType() {
        return $this->type;
    }

    function setType($type) {
        $this->type = $type;
    }

    function getRepeat_day_of_week() {
        return $this->repeat_day_of_week;
    }

    function setRepeat_day_of_week($repeat_day_of_week) {
        $this->repeat_day_of_week = $repeat_day_of_week;
    }

    public static function deleteFromType($type) {
        global $global;
        if (!empty($type)) {
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE type = ?";
            $global['lastQuery'] = $sql;
            return sqlDAL::writeSql($sql, "s", array($type));
        }
        return false;
    }

    public static function getAllFromType($type) {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE type=? ";

        //echo $sql;
        $res = sqlDAL::readSql($sql, 's', array($type));
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

    public static function getAllActiveOrToRepeat() {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='" . (self::$statusActive) . "' OR status='" . (self::$statusRepeat) . "' ";

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
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

}
