<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class TagsHasVideos extends ObjectYPT {

    protected $id, $tags_id, $videos_id;

    static function getSearchFieldsNames() {
        return array('name', 'title');
    }

    static function getTableName() {
        return 'tags_has_videos';
    }

    function getTags_id() {
        return $this->tags_id;
    }

    function getVideos_id() {
        return $this->videos_id;
    }

    function setTags_id($tags_id) {
        $this->tags_id = $tags_id;
        if (!empty($this->tags_id) && !empty($this->videos_id)) {
            $this->loadFromTagsIdAndVideosId($this->tags_id, $this->videos_id);
        }
    }

    function setVideos_id($videos_id) {
        $this->videos_id = $videos_id;
        if (!empty($this->tags_id) && !empty($this->videos_id)) {
            $this->loadFromTagsIdAndVideosId($this->tags_id, $this->videos_id);
        }
    }

    function loadFromTagsIdAndVideosId($tags_id, $videos_id) {
        $row = self::getFromTagsIdAndVideosId($tags_id, $videos_id);
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            @$this->$key = $value;
            //$this->properties[$key] = $value;
        }
        return true;
    }

    static function getFromTagsIdAndVideosId($tags_id, $videos_id) {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  tags_id = ? AND videos_id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "ii", array($tags_id, $videos_id));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    static function getAllFromVideosId($videos_id) {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT tt.*, tt.name as type_name, t.*, tv.* FROM  " . static::getTableName() . " tv "
                . " LEFT JOIN tags as t ON tags_id = t.id "
                . " LEFT JOIN tags_types as tt ON tags_types_id = tt.id "
                . " WHERE videos_id=? ";
        $res = sqlDAL::readSql($sql, "i", array($videos_id));
        $fullData = sqlDAL::fetchAllAssoc($res);

        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $row['total'] = self::getTotalVideosFromTagsId($row['tags_id']);
                $rows[] = $row;
            }
        } 
        return $rows;
    }

    static function getTotalVideosFromTagsId($tags_id, $status = Video::SORT_TYPE_VIEWABLE) {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT count(thv.id) as total FROM  " . static::getTableName() . " thv LEFT JOIN videos v ON v.id = thv.videos_id  "
                . " WHERE tags_id=? ";
        if ($status == Video::SORT_TYPE_VIEWABLE) {
            $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus(true)) . "')";
        } elseif ($status == Video::SORT_TYPE_VIEWABLENOTUNLISTED) {
            $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus(false)) . "')";
        } elseif ($status == Video::SORT_TYPE_PUBLICONLY) {
            $sql .= " AND v.status IN ('a', 'k') AND (SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = v.id ) = 0";
        } elseif ($status == Video::SORT_TYPE_PRIVATEONLY) {
            $sql .= " AND v.status IN ('a', 'k') AND (SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = v.id ) > 0";
        } elseif (!empty($status)) {
            $sql .= " AND v.status = '{$status}'";
        }
        $res = sqlDAL::readSql($sql, "i", array($tags_id));
        $fullData = sqlDAL::fetchAssoc($res);

        sqlDAL::close($res);

        //var_dump($sql, $tags_id, $fullData);//exit;
        return intval($fullData['total']);
    }
    
    static function getAllVideosFromTagsId($tags_id, $limit = 100, $status = Video::SORT_TYPE_VIEWABLE) {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT v.*, thv.* FROM  " . static::getTableName() . " thv LEFT JOIN videos v ON v.id = thv.videos_id  "
                . " WHERE thv.tags_id=? ";
        if ($status == Video::SORT_TYPE_VIEWABLE) {
            $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus(true)) . "')";
        } elseif ($status == Video::SORT_TYPE_VIEWABLENOTUNLISTED) {
            $sql .= " AND v.status IN ('" . implode("','", Video::getViewableStatus(false)) . "')";
        } elseif ($status == Video::SORT_TYPE_PUBLICONLY) {
            $sql .= " AND v.status IN ('a', 'k') AND (SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = v.id ) = 0";
        } elseif ($status == Video::SORT_TYPE_PRIVATEONLY) {
            $sql .= " AND v.status IN ('a', 'k') AND (SELECT count(id) FROM videos_group_view as gv WHERE gv.videos_id = v.id ) > 0";
        } elseif (!empty($status)) {
            $sql .= " AND v.status = '{$status}'";
        }
        $sql .= " LIMIT {$limit}";
        //var_dump($sql, $tags_id);//exit;
        $res = sqlDAL::readSql($sql, "i", array($tags_id));
        $fullData = sqlDAL::fetchAllAssoc($res);

        sqlDAL::close($res);

        return $fullData;
    }

    static function getAllVideosIdFromTagsId($tags_id, $limit = 100, $status = Video::SORT_TYPE_VIEWABLE) {
        global $global;
        $rows = self::getAllVideosFromTagsId($tags_id, $limit, $status);
        $ids = array();
        foreach ($rows as $row) {
            $ids[] = $row['videos_id'];
        }
        return $ids;
    }

    static function getAllFromVideosIdAndTagsTypesId($videos_id, $tags_types_id) {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT t.*, tv.* FROM  " . static::getTableName() . " tv LEFT JOIN tags as t ON tags_id = t.id WHERE tags_types_id = ? AND videos_id=? ";
        $res = sqlDAL::readSql($sql, "ii", array($tags_types_id, $videos_id));
        //var_dump($sql, $tags_types_id, $videos_id);
        $fullData = sqlDAL::fetchAllAssoc($res);

        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $row['total'] = self::getTotalVideosFromTagsId($row['tags_id']);
                $rows[] = $row;
            }
        }
        return $rows;
    }

    static function removeAllTagsFromVideo($videos_id) {
        global $global;
        if (!empty($videos_id)) {
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE videos_id = ?";
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql, "i", array($videos_id));
        }
        _error_log("videos_id for table " . static::getTableName() . " not defined for deletion");
        return false;
    }
    
    
    public static function getAllWithVideo($limit=100)
    {
        global $global, $_getAllTagsWithVideo;
        if(isset($_getAllTagsWithVideo)){
            return $_getAllTagsWithVideo;
        }
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT DISTINCT tv.tags_id, t.*
        FROM tags_has_videos tv
        LEFT JOIN tags t ON tv.tags_id = t.id
        GROUP BY tv.tags_id
        ORDER BY COUNT(tv.videos_id) DESC, t.name ASC
        LIMIT {$limit};
        ";
        //echo $sql;exit;
        $res = sqlDAL::readSql($sql, "", array());
        $fullData = sqlDAL::fetchAllAssoc($res);

        sqlDAL::close($res);
        $_getAllTagsWithVideo = $fullData;
        return $fullData;
        
    }

}
