<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';

class Channel
{
    public static function getChannels($activeOnly = true, $FIND_IN_SET = "", $users_id_array = array(), $user_groups_id = null)
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

        // Add user group filter if specified
        if (!empty($user_groups_id)) {
            $user_groups_id = intval($user_groups_id);
            $sql .= " AND u.id IN (SELECT users_id FROM users_has_users_groups WHERE users_groups_id = {$user_groups_id}) ";
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


    public static function getTotalChannels($activeOnly=true, $user_groups_id = null)
    {
        global $global;

        $sql = "SELECT count(*) as total "
                . " FROM users u "
                . " WHERE (SELECT count(v.id) FROM videos v where v.users_id = u.id) > 0 ";
        if ($activeOnly) {
            $sql .= " AND u.status = 'a' ";
        }

        // Add user group filter if specified
        if (!empty($user_groups_id)) {
            $user_groups_id = intval($user_groups_id);
            $sql .= " AND u.id IN (SELECT users_id FROM users_has_users_groups WHERE users_groups_id = {$user_groups_id}) ";
        }

        $sql .= BootGrid::getSqlSearchFromPost(['user', 'about']);
        //$sql .= BootGrid::getSqlFromPost(['user', 'about']);
        $res = sqlDAL::readSql($sql);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return $res ? intval($data['total']) : 0;
    }

    public static function getUserGroupsWithChannels($activeOnly = true)
    {
        global $global;

        $sql = "SELECT DISTINCT ug.id, ug.group_name, COUNT(DISTINCT u.id) as total_channels "
                . " FROM users_groups ug "
                . " INNER JOIN users_has_users_groups uhug ON ug.id = uhug.users_groups_id "
                . " INNER JOIN users u ON uhug.users_id = u.id "
                . " WHERE (SELECT count(v.id) FROM videos v WHERE v.users_id = u.id) > 0 ";

        if ($activeOnly) {
            $sql .= " AND u.status = 'a' ";
        }

        $sql .= " GROUP BY ug.id, ug.group_name "
                . " ORDER BY ug.group_name ASC ";

        $res = sqlDAL::readSql($sql);
        $result = [];

        if ($res !== false) {
            $fullResult = sqlDAL::fetchAllAssoc($res);
            foreach ($fullResult as $row) {
                $row = cleanUpRowFromDatabase($row);
                $result[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }

        sqlDAL::close($res);
        return $result;
    }
}
