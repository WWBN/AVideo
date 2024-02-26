<?php

class WWBNIndexModel
{

    public static function getTableName()
    {
        return 'plugins';
    }

    public function getPluginData()
    {
        global $global;
        
        $sql = "SELECT * FROM {$this->getTableName()} WHERE uuid = 'WWBNIndex'";
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            _error_log($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public function updateObjectData($object_data = "")
    {
        $sql = "UPDATE plugins SET object_data = ? WHERE uuid = ?";
        return sqlDAL::writeSql($sql, "ss", array($object_data, "WWBNIndex"));
    }
}
