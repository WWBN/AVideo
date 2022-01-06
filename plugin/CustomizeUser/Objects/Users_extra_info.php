<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Users_extra_info extends ObjectYPT
{
    protected $id;
    protected $field_name;
    protected $field_type;
    protected $field_options;
    protected $field_default_value;
    protected $parameters;
    protected $status;
    protected $order;

    public static function getSearchFieldsNames()
    {
        return ['field_name', 'field_type', 'field_options', 'field_default_value', 'parameters'];
    }

    public static function getTableName()
    {
        return 'users_extra_info';
    }

    public function setId($id)
    {
        $this->id = intval($id);
    }

    public function setField_name($field_name)
    {
        $this->field_name = $field_name;
    }

    public function setField_type($field_type)
    {
        $this->field_type = $field_type;
    }

    public function setField_options($field_options)
    {
        $this->field_options = $field_options;
    }

    public function setField_default_value($field_default_value)
    {
        $this->field_default_value = $field_default_value;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getId()
    {
        return intval($this->id);
    }

    public function getField_name()
    {
        return $this->field_name;
    }

    public function getField_type()
    {
        return $this->field_type;
    }

    public function getField_options()
    {
        return $this->field_options;
    }

    public function getField_default_value()
    {
        return $this->field_default_value;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder($order)
    {
        $this->order = intval($order);
    }

    public static function getTypesOptionArray()
    {
        return ['input' => 'Text', 'number' => 'Number', 'select' => 'Predefined options (select one)', 'textarea' => 'Textarea'];
    }

    public static function typeToHTML($row)
    {
        $html = "";
        if (isset($row['value'])) {
            $row['field_default_value'] = $row['value'];
        }
        if ($row['field_type'] == 'input') {
            $html .= "<label for=\"usersExtraInfo_{$row['id']}\">{$row['field_name']}:</label>";
            $html .= "<input type=\"text\" id=\"usersExtraInfo_{$row['id']}\" name=\"usersExtraInfo[{$row['id']}]\" "
                    . "class=\"form-control input-sm usersExtraInfoInput\" placeholder=\"{$row['field_name']}\" value=\"{$row['field_default_value']}\">";
        } elseif ($row['field_type'] == 'number') {
            $html .= "<label for=\"usersExtraInfo_{$row['id']}\">{$row['field_name']}:</label>";
            $html .= "<input type=\"number\" id=\"usersExtraInfo_{$row['id']}\" name=\"usersExtraInfo[{$row['id']}]\" "
                    . "class=\"form-control input-sm usersExtraInfoInput\" placeholder=\"{$row['field_name']}\" value=\"{$row['field_default_value']}\">";
        } elseif ($row['field_type'] == 'select') {
            $html = "<label for=\"usersExtraInfo_{$row['id']}\">{$row['field_name']}:</label>
                <select class=\"form-control input-sm usersExtraInfoInput\" name=\"usersExtraInfo[{$row['id']}]\" id=\"usersExtraInfo_{$row['id']}\">";
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
        } elseif ($row['field_type'] == 'textarea') {
            $html .= "<label for=\"usersExtraInfo_{$row['id']}\">{$row['field_name']}:</label>";
            $html .= "<textarea type=\"text\" id=\"usersExtraInfo_{$row['id']}\" name=\"usersExtraInfo[{$row['id']}]\" "
                    . "class=\"form-control input-sm usersExtraInfoInput\" placeholder=\"{$row['field_name']}\" rows=\"6\">{$row['field_default_value']}</textarea>";
        }
        return $html;
    }

    public static function getAllActive($users_id=0)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='a' ORDER BY `order` ASC ";
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

    public static function getFromUser($users_id)
    {
        $u = new User($users_id);
        return object_to_array(_json_decode($u->getExtra_info()));
    }
}
