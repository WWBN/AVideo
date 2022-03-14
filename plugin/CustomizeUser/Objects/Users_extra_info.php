<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Users_extra_info extends ObjectYPT {

    protected $id;
    protected $field_name;
    protected $field_type;
    protected $field_options;
    protected $field_default_value;
    protected $parameters;
    protected $status;
    protected $order;
    public static $status_INACTIVE = 'i';
    public static $status_ACTIVE_ALL_USERS = 'a';
    public static $status_ACTIVE_FORCED_ON_SIGNUP_ALL_USERS = 'b';
    public static $status_ACTIVE_OPTIONAL_ON_SIGNUP_ALL_USERS = 'c';
    public static $status_ACTIVE_COMPANIES_ONLY = 'd';
    public static $status_ACTIVE_FORCED_ON_SIGNUP_COMPANIES_ONLY = 'e';
    public static $status_ACTIVE_OPTIONAL_ON_SIGNUP_COMPANIES_ONLY = 'f';
    public static $status_options = array(
        'i' => 'Inactive',
        'a' => 'Active all users',
        'b' => 'Forced on Signup all users',
        'c' => 'Optional on signup all users',
        'd' => 'Active companies only',
        'e' => 'Forced on Signup companies only',
        'f' => 'Optional on signup companies only'
    );

    public static function getSearchFieldsNames() {
        return ['field_name', 'field_type', 'field_options', 'field_default_value', 'parameters'];
    }

    public static function getTableName() {
        return 'users_extra_info';
    }

    public function setId($id) {
        $this->id = intval($id);
    }

    public function setField_name($field_name) {
        $this->field_name = $field_name;
    }

    public function setField_type($field_type) {
        $this->field_type = $field_type;
    }

    public function setField_options($field_options) {
        $this->field_options = $field_options;
    }

    public function setField_default_value($field_default_value) {
        $this->field_default_value = $field_default_value;
    }

    public function setParameters($parameters) {
        $this->parameters = $parameters;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getId() {
        return intval($this->id);
    }

    public function getField_name() {
        return $this->field_name;
    }

    public function getField_type() {
        return $this->field_type;
    }

    public function getField_options() {
        return $this->field_options;
    }

    public function getField_default_value() {
        return $this->field_default_value;
    }

    public function getParameters() {
        return $this->parameters;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getOrder() {
        return $this->order;
    }

    public function setOrder($order) {
        $this->order = intval($order);
    }

    public static function getTypesOptionArray() {
        return ['input' => 'Text', 'number' => 'Number', 'select' => 'Predefined options (select one)', 'textarea' => 'Textarea'];
    }

    public static function typeToHTML($row, $class1 = '', $class2 = '') {
        $required = '';
        
        if(self::isRequiredField($row['status'])){
            $required = 'required';
        }
        
        $html = "";
        if (isset($row['value'])) {
            $row['field_default_value'] = $row['value'];
        }
        if ($row['field_type'] == 'input') {
            $html .= "<label for=\"usersExtraInfo_{$row['id']}\" class=\"$class1\">{$row['field_name']}:</label>";
            $html .= "<div class=\"$class2\">";
            $html .= "<input type=\"text\" id=\"usersExtraInfo_{$row['id']}\" name=\"usersExtraInfo[{$row['id']}]\" "
                    . "class=\"form-control input-sm usersExtraInfoInput\" placeholder=\"{$row['field_name']}\" value=\"{$row['field_default_value']}\" {$required}>";
            $html .= "</div>";
        } elseif ($row['field_type'] == 'number') {
            $html .= "<label for=\"usersExtraInfo_{$row['id']}\" class=\"$class1\">{$row['field_name']}:</label>";
            $html .= "<div class=\"$class2\">";
            $html .= "<input type=\"number\" id=\"usersExtraInfo_{$row['id']}\" name=\"usersExtraInfo[{$row['id']}]\" "
                    . "class=\"form-control input-sm usersExtraInfoInput\" placeholder=\"{$row['field_name']}\" value=\"{$row['field_default_value']}\" {$required}>";
            $html .= "</div>";
        } elseif ($row['field_type'] == 'select') {
            $html .= "<label for=\"usersExtraInfo_{$row['id']}\" class=\"$class1\">{$row['field_name']}:</label>";
            $html .= "<div class=\"$class2\">";
            $html .= "<select class=\"form-control input-sm usersExtraInfoInput\" name=\"usersExtraInfo[{$row['id']}]\" id=\"usersExtraInfo_{$row['id']}\" {$required}>";
            foreach (preg_split("/((\r?\n)|(\r\n?))/", $row['field_options']) as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }
                $selected = "";
                if ($line == $row['field_default_value']) {
                    $selected = "selected";
                }
                $html .= "<option {$selected}>" . htmlentities($line) . "</option>";
            }
            $html .= "</select>";
            $html .= "</div>";
        } elseif ($row['field_type'] == 'textarea') {
            $html .= "<label for=\"usersExtraInfo_{$row['id']}\" class=\"$class1\">{$row['field_name']}:</label>";
            $html .= "<div class=\"$class2\">";
            $html .= "<textarea type=\"text\" id=\"usersExtraInfo_{$row['id']}\" name=\"usersExtraInfo[{$row['id']}]\" "
                    . "class=\"form-control input-sm usersExtraInfoInput\" placeholder=\"{$row['field_name']}\" rows=\"6\" {$required}>{$row['field_default_value']}</textarea>";
            $html .= "</div>";
        }
        return $html;
    }

    public static function getActiveStatusList($includeCompany = false): array {
        $list = array(
            self::$status_ACTIVE_ALL_USERS,
            self::$status_ACTIVE_FORCED_ON_SIGNUP_ALL_USERS,
            self::$status_ACTIVE_OPTIONAL_ON_SIGNUP_ALL_USERS,
        );
        if ($includeCompany) {
            $list[] = self::$status_ACTIVE_COMPANIES_ONLY;
            $list[] = self::$status_ACTIVE_FORCED_ON_SIGNUP_COMPANIES_ONLY;
            $list[] = self::$status_ACTIVE_OPTIONAL_ON_SIGNUP_COMPANIES_ONLY;
        }
        //var_dump($includeCompany, $list);
        return $list;
    }

    public static function isRequiredField($status): bool {
        $list = array(
            self::$status_ACTIVE_FORCED_ON_SIGNUP_ALL_USERS,
            self::$status_ACTIVE_FORCED_ON_SIGNUP_COMPANIES_ONLY
        );
        return in_array($status, $list);
    }

    public static function isAllUserField($status): bool {
        if ($status == self::$status_INACTIVE) {
            return false;
        }

        $list = self::getActiveStatusList();

        return in_array($status, $list);
    }

    public static function isCompanyOnlyField($status): bool {
        if ($status == self::$status_INACTIVE) {
            return false;
        }

        return !self::isAllUserField($status);
    }

    public static function getAllActive($users_id = 0, $includeCompany = false) {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }

        if (!empty($users_id)) {
            if (User::isACompany($users_id)) {
                $includeCompany = true;
            }
        }

        $statusList = self::getActiveStatusList($includeCompany);

        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status IN ('" . implode("','", $statusList) . "') ORDER BY `order` ASC ";
        //var_dump($includeCompany, $sql);
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            if (!empty($users_id)) {
                $extraInfo = self::getFromUser($users_id);
            }
            foreach ($fullData as $row) {
                if (!empty($extraInfo) && !empty($extraInfo[$row['id']])) {
                    $row['value'] = $extraInfo[$row['id']];
                    $row['current_value'] = $row['value'];
                } else {
                    $row['current_value'] = $row['field_default_value'];
                }
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public static function getFromUser($users_id) {
        $u = new User($users_id);
        return object_to_array(_json_decode($u->getExtra_info()));
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
                $row['status_description'] = self::$status_options[$row['status']];
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

}
