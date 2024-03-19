<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';

class Channel
{
    public static function getChannels($activeOnly = true, $FIND_IN_SET = "", $users_id_array = array())
    {
        global $global;
        /**
         * Global variables.
         *
         * @var array $global An array of global variables.
         * @property \mysqli $global['mysqli'] A MySQLi connection object.
         * @property mixed $global[] Dynamically loaded variables.
         */
        $sql = "SELECT u.*, "
                . " (SELECT count(v.id) FROM videos v where v.users_id = u.id) as total_videos "
                . " FROM users u "
                . " HAVING total_videos > 0 ";
        if ($activeOnly) {
            $sql .= " AND u.status = 'a' ";
        }
        if(!empty($users_id_array) && is_array($users_id_array)){
            $sql .= " AND u.id IN(".implode(',',$users_id_array ).") ";
        }
        $sql .= BootGrid::getSqlFromPost(['user', 'about', 'channelName', 'u.name', 'u.email'], "", "", false, $FIND_IN_SET);
        //var_dump($sql);exit;
        $res = sqlDAL::readSql($sql);
        $fullResult = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $subscribe = [];
        if ($res !== false) {
            foreach ($fullResult as $row) {
                $row = cleanUpRowFromDatabase($row);
                $subscribe[] = $row;
            }
        } else {
            $subscribe = array();
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $subscribe;
    }


    public static function getTotalChannels($activeOnly=true)
    {
        global $global;
        $sql = "SELECT count(*) as total "
                . " FROM users u "
                . " WHERE (SELECT count(v.id) FROM videos v where v.users_id = u.id) > 0 ";
        if ($activeOnly) {
            $sql .= " AND u.status = 'a' ";
        }
        $sql .= BootGrid::getSqlSearchFromPost(['user', 'about']);
        //$sql .= BootGrid::getSqlFromPost(['user', 'about']);
        $res = sqlDAL::readSql($sql);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return $res ? intval($data['total']) : 0;
    }
}
