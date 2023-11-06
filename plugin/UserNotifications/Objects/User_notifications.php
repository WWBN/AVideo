<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class User_notifications extends ObjectYPT {

    protected $id, $msg, $title, $type, $status, $time_readed, $users_id, $image, $icon, $href, $onclick, $element_class, $element_id, $priority;

    static function getSearchFieldsNames() {
        return array('msg', 'type', 'image', 'icon', 'href', 'onclick', 'element_class', 'element_id');
    }

    static function getTableName() {
        return 'user_notifications';
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title): void {
        $this->title = safeString($title);
    }

    function setId($id) {
        $this->id = intval($id);
    }

    function setMsg($msg) {
        $this->msg = safeString($msg);
    }

    function setType($type) {
        $this->type = $type;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setTime_readed($time_readed) {
        $this->time_readed = $time_readed;
    }

    function setUsers_id($users_id) {
        $this->users_id = intval($users_id);
    }

    function setImage($image) {
        $this->image = safeString($image);
    }

    function setIcon($icon) {
        $this->icon = safeString($icon);
    }

    function setHref($href) {
        $this->href = safeString($href);
    }

    function setOnclick($onclick) {
        $this->onclick = $onclick;
    }

    function setElement_class($element_class) {
        $this->element_class = safeString($element_class);
    }

    function setElement_Id($element_id) {
        $this->element_id = substr(safeString($element_id), -250);;
    }

    function setPriority($priority) {
        $this->priority = intval($priority);
    }

    function getElement_Id() {
        return $this->element_id;
    }

    function getMsg() {
        return $this->msg;
    }

    function getType() {
        return $this->type;
    }

    function getStatus() {
        return $this->status;
    }

    function getTime_readed() {
        return $this->time_readed;
    }

    function getUsers_id() {
        return intval($this->users_id);
    }

    function getImage() {
        return $this->image;
    }

    function getIcon() {
        return $this->icon;
    }

    function getHref() {
        return $this->href;
    }

    function getOnclick() {
        return $this->onclick;
    }

    function getElement_class() {
        return $this->element_class;
    }

    function getId() {
        return $this->id;
    }

    function getPriority() {
        return intval($this->priority);
    }

    public static function getAll() {
        setDefaultSort('id', 'DESC');
        return parent::getAll();
    }

    public static function getAllForUsers_id($users_id, $limit = 10, $sort = true) {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE users_id = ? ORDER BY id DESC LIMIT ?";
        $res = sqlDAL::readSql($sql, 'ii', [$users_id, $limit]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res !== false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        }

        if($sort){
            usort($rows, function($a, $b) {
                return $a['id']-$b['id'];
            });
        }      
        return $rows;
    }

    public static function deleteForUsers_id($users_id) {
        global $global;
        if (!empty($users_id)) {

            if (!self::ignoreTableSecurityCheck() && isUntrustedRequest("DELETE " . static::getTableName())) {
                return false;
            }
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE users_id = ?";
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql, "i", [$users_id]);
        }
        _error_log("Id for table " . static::getTableName() . " not defined for deletion " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)), AVideoLog::$ERROR);
        return false;
    }

    public function save() {
        if (empty($this->element_id)) {
            $this->element_id = 'automatic_id_' . uniqid();
        }else{
            if(self::elementIdExists($this->element_id)){
                return false;
            }
        }
        return parent::save();
    }
    
    
    static function elementIdExists($element_id){
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  element_id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "s", [$element_id]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

}
