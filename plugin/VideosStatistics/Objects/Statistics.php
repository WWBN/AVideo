<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class Statistics extends ObjectYPT
{

    protected $id, $users_id, $total_videos, $total_video_views, $total_subscriptions, $total_comments, $total_likes, $total_dislikes, $total_duration_seconds, $collected_date;

    static function getSearchFieldsNames()
    {
        return array();
    }

    static function getTableName()
    {
        return 'statistics';
    }

    static function getAllUsers()
    {
        global $global;
        $table = "users";
        $sql = "SELECT * FROM {$table} WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        }
        return $rows;
    }


    function setId($id)
    {
        $this->id = intval($id);
    }

    function setUsers_id($users_id)
    {
        $this->users_id = intval($users_id);
    }

    function setTotal_videos($total_videos)
    {
        $this->total_videos = intval($total_videos);
    }

    function setTotal_video_views($total_video_views)
    {
        $this->total_video_views = intval($total_video_views);
    }

    function setTotal_subscriptions($total_subscriptions)
    {
        $this->total_subscriptions = intval($total_subscriptions);
    }

    function setTotal_comments($total_comments)
    {
        $this->total_comments = intval($total_comments);
    }

    function setTotal_likes($total_likes)
    {
        $this->total_likes = intval($total_likes);
    }

    function setTotal_dislikes($total_dislikes)
    {
        $this->total_dislikes = intval($total_dislikes);
    }

    function setTotal_duration_seconds($total_duration_seconds)
    {
        $this->total_duration_seconds = intval($total_duration_seconds);
    }

    function setCollected_date($collected_date)
    {
        $this->collected_date = $collected_date;
    }


    function getId()
    {
        return intval($this->id);
    }

    function getUsers_id()
    {
        return intval($this->users_id);
    }

    function getTotal_videos()
    {
        return intval($this->total_videos);
    }

    function getTotal_video_views()
    {
        return intval($this->total_video_views);
    }

    function getTotal_subscriptions()
    {
        return intval($this->total_subscriptions);
    }

    function getTotal_comments()
    {
        return intval($this->total_comments);
    }

    function getTotal_likes()
    {
        return intval($this->total_likes);
    }

    function getTotal_dislikes()
    {
        return intval($this->total_dislikes);
    }

    function getTotal_duration_seconds()
    {
        return intval($this->total_duration_seconds);
    }

    function getCollected_date()
    {
        return $this->collected_date;
    }

    public static function getAllVideoStatistics($users_id = 0)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT vs.*, title FROM videos_statistics vs LEFT JOIN videos v ON vs.videos_id = v.id WHERE 1=1 ";

        if (!empty($users_id)) {
            $sql .= " AND vs.users_id = {$users_id} ";
        }
        $sql .= self::getSqlFromPost();
        //var_dump($sql);
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res !== false) {
            foreach ($fullData as $row) {
                $time = !empty($row['created_php_time'])?$row['created_php_time']:$row['created'];
                $row['when'] = humanTimingAgo($time);
                //$row['poster'] = Video::getPoster($row['videos_id']);
                $row['listItem'] = Video::getVideosListItem($row['videos_id']);
                $row['img'] = '<img src="' . $row['poster'] . '" class="img img-responsive"/>';
                $rows[] = $row;
            }
        }
        return $rows;
    }


    public static function getTotalVideoStatistics($users_id = 0)
    {
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        if (!static::isTableInstalled()) {
            return 0;
        }
        $sql = "SELECT id FROM videos_statistics WHERE 1=1  ";

        if (!empty($users_id)) {
            $sql .= " AND users_id = {$users_id} ";
        }

        $sql .= self::getSqlSearchFromPost();
        $res = sqlDAL::readSql($sql);
        $countRow = sqlDAL::num_rows($res);
        sqlDAL::close($res);
        return $countRow;
    }

    public static function deleteVideoStatistics($users_id = 0, $id = 0)
    {
        global $global;
        if (!empty($users_id)) {
            $formats = "i";
            $values = [$users_id];
            $sql = "DELETE FROM videos_statistics ";
            $sql .= " WHERE users_id = ?";

            if (!empty($id)) {
                $sql .= " AND id = ? ";
                $formats .= "i";
                $values[] = $id;
            }

            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql, $formats, $values);
        }
        _error_log("Id for table videos_statistics not defined for deletion " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)), AVideoLog::$ERROR);
        return false;
    }
}
