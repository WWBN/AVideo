<?php

interface ObjectInterface {

    static function getTableName();

    static function getSearchFieldsNames();
}

$tableExists = array();

abstract class ObjectYPT implements ObjectInterface {

    protected $fieldsName = array();

    function __construct($id = "") {
        if (!empty($id)) {
            // get data from id
            $this->load($id);
        }
    }

    protected function load($id) {
        $row = self::getFromDb($id);
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    static protected function getFromDb($id) {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/YouPHPTube/about
        $res = sqlDAL::readSql($sql, "i", array($id));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    static function getAll() {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";

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

    static function getTotal() {
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT id FROM  " . static::getTableName() . " WHERE 1=1  ";
        $sql .= self::getSqlSearchFromPost();
        $res = sqlDAL::readSql($sql);
        $countRow = sqlDAL::num_rows($res);
        sqlDAL::close($res);
        return $countRow;
    }

    static function getSqlFromPost($keyPrefix = "") {
        global $global;
        $sql = self::getSqlSearchFromPost();

        if (empty($_POST['sort']) && !empty($_GET['order'][0]['dir'])) {
            $index = intval($_GET['order'][0]['column']);
            $_GET['columns'][$index]['data'];
            $_POST['sort'][$_GET['columns'][$index]['data']] = $_GET['order'][0]['dir'];
        }

        // add a security here 
        if (!empty($_POST['sort'])) {
            foreach ($_POST['sort'] as $key => $value) {
                $_POST['sort'][xss_esc($key)] = xss_esc($value);
            }
        }

        if (!empty($_POST['sort'])) {
            $orderBy = array();
            foreach ($_POST['sort'] as $key => $value) {
                $key = $global['mysqli']->real_escape_string($key);
                //$value = $global['mysqli']->real_escape_string($value);
                $direction = "ASC";
                if(strtoupper($value)==="DESC"){
                    $direction = "DESC";
                }
                $key = preg_replace("/[^A-Za-z0-9._ ]/", '', $key);
                $orderBy[] = " {$keyPrefix}{$key} {$value} ";
            }
            $sql .= " ORDER BY " . implode(",", $orderBy);
        }

        if (empty($_POST['rowCount']) && !empty($_GET['length'])) {
            $_POST['rowCount'] = intval($_GET['length']);
        }

        if (empty($_POST['current']) && !empty($_GET['start'])) {
            $_POST['current'] = ($_GET['start'] / $_GET['length']) + 1;
        } else if (empty($_POST['current']) && isset($_GET['start'])) {
            $_POST['current'] = 1;
        }

        $_POST['current'] = intval(@$_POST['current']);
        $_POST['rowCount'] = intval(@$_POST['rowCount']);

        if (!empty($_POST['rowCount']) && !empty($_POST['current']) && $_POST['rowCount'] > 0) {
            $_POST['rowCount'] = intval($_POST['rowCount']);
            $_POST['current'] = intval($_POST['current']);
            $current = ($_POST['current'] - 1) * $_POST['rowCount'];
            $current = $current < 0 ? 0 : $current;
            $sql .= " LIMIT $current, {$_POST['rowCount']} ";
        } else {
            $_POST['current'] = 0;
            $_POST['rowCount'] = 0;
            $sql .= " LIMIT 1000 ";
        }
        return $sql;
    }

    static function getSqlSearchFromPost() {
        $sql = "";
        if (!empty($_POST['searchPhrase'])) {
            $_GET['q'] = $_POST['searchPhrase'];
        } else if (!empty($_GET['search']['value'])) {
            $_GET['q'] = $_GET['search']['value'];
        }
        if (!empty($_GET['q'])) {
            global $global;
            $search = $global['mysqli']->real_escape_string(xss_esc($_GET['q']));

            $like = array();
            $searchFields = static::getSearchFieldsNames();
            foreach ($searchFields as $value) {
                $like[] = " {$value} LIKE '%{$search}%' ";
                // for accent insensitive
                $like[] = " CONVERT(CAST({$value} as BINARY) USING utf8) LIKE '%{$search}%' ";
            }
            if (!empty($like)) {
                $sql .= " AND (" . implode(" OR ", $like) . ")";
            } else {
                $sql .= " AND 1=1 ";
            }
        }

        return $sql;
    }

    function save() {
        if (!$this->tableExists()) {
            error_log("Save error, table " . static::getTableName() . " does not exists");
            return false;
        }
        global $global;
        $fieldsName = $this->getAllFields();
        if (!empty($this->id)) {
            $sql = "UPDATE " . static::getTableName() . " SET ";
            $fields = array();
            foreach ($fieldsName as $value) {
                if (strtolower($value) == 'created') {
                    // do nothing
                } elseif (strtolower($value) == 'modified') {
                    $fields[] = " {$value} = now() ";
                } else if (is_numeric($this->$value)) {
                    $fields[] = " `{$value}` = {$this->$value} ";
                } else if (strtolower($this->$value) == 'null') {
                    $fields[] = " `{$value}` = NULL ";
                } else {
                    $fields[] = " `{$value}` = '{$this->$value}' ";
                }
            }
            $sql .= implode(", ", $fields);
            $sql .= " WHERE id = {$this->id}";
        } else {
            $sql = "INSERT INTO " . static::getTableName() . " ( ";
            $sql .= "`" . implode("`,`", $fieldsName) . "` )";
            $fields = array();
            foreach ($fieldsName as $value) {
                if (strtolower($value) == 'created' || strtolower($value) == 'modified') {
                    $fields[] = " now() ";
                } elseif (!isset($this->$value) || strtolower($this->$value) == 'null') {
                    $fields[] = " NULL ";
                } else {
                    $fields[] = " '{$this->$value}' ";
                }
            }
            $sql .= " VALUES (" . implode(", ", $fields) . ")";
        }
        //echo $sql;
        $insert_row = sqlDAL::writeSql($sql);

        if ($insert_row) {
            if (empty($this->id)) {
                $id = $global['mysqli']->insert_id;
            } else {
                $id = $this->id;
            }
            return $id;
        } else {
            die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }

    private function getAllFields() {
        global $global, $mysqlDatabase;
        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = '" . static::getTableName() . "'";
        $res = sqlDAL::readSql($sql, "s", array($mysqlDatabase));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row["COLUMN_NAME"];
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    function delete() {
        global $global;
        if (!empty($this->id)) {
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE id = ?";
            $global['lastQuery'] = $sql;
            //error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql, "i", array($this->id));
        }
        error_log("Id for table " . static::getTableName() . " not defined for deletion");
        return false;
    }

    static function setCache($name, $value) {
        $tmpDir = sys_get_temp_dir();
        $uniqueHash = md5(__FILE__);

        $cachefile = $tmpDir . DIRECTORY_SEPARATOR . $name . $uniqueHash; // e.g. cache/index.php.
        file_put_contents($cachefile, json_encode($value));
    }

    static function getCache($name, $lifetime = 60) {
        $tmpDir = sys_get_temp_dir();
        $uniqueHash = md5(__FILE__);

        $cachefile = $tmpDir . DIRECTORY_SEPARATOR . $name . $uniqueHash; // e.g. cache/index.php.
        if (!empty($_GET['lifetime'])) {
            $lifetime = intval($_GET['lifetime']);
        }
        if (file_exists($cachefile) && time() - $lifetime <= filemtime($cachefile)) {
            $c = @url_get_contents($cachefile);
            return json_decode($c);
        } else if (file_exists($cachefile)) {
            unlink($cachefile);
        }
    }

    function tableExists() {
        return self::isTableInstalled();
    }

    static function isTableInstalled($tableName="") {
        global $global, $tableExists;
        if(empty($tableName)){
           $tableName = static::getTableName();
        }
        if (!isset($tableExists[$tableName])) {
            $res = sqlDAL::readSql("SHOW TABLES LIKE '" . $tableName . "'");
            $result = sqlDal::num_rows($res);
            sqlDAL::close($res);
            $tableExists[$tableName] = !empty($result);
        }
        return $tableExists[$tableName];
    }

}

//abstract class Object extends ObjectYPT{};
