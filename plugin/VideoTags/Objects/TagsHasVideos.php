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
        if(!empty($this->tags_id) && !empty($this->videos_id)){
            $this->loadFromTagsIdAndVideosId($this->tags_id, $this->videos_id);
        }
    }

    function setVideos_id($videos_id) {
        $this->videos_id = $videos_id;
        if(!empty($this->tags_id) && !empty($this->videos_id)){
            $this->loadFromTagsIdAndVideosId($this->tags_id, $this->videos_id);
        }
    }

    function loadFromTagsIdAndVideosId($tags_id, $videos_id) {
        $row = self::getFromTagsIdAndVideosId($tags_id, $videos_id);
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    static protected function getFromTagsIdAndVideosId($tags_id, $videos_id) {
        global $global;
        if(!static::isTableInstalled()){
            return false;
        }
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  tags_id = ? AND videos_id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/YouPHPTube/about
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
        if(!static::isTableInstalled()){
            return false;
        }
        $sql = "SELECT tt.*, tt.name as type_name, t.*, tv.* FROM  " . static::getTableName() . " tv "
                . " LEFT JOIN tags as t ON tags_id = t.id "
                . " LEFT JOIN tags_types as tt ON tags_types_id = tt.id "
                . " WHERE videos_id=? ";
        $res = sqlDAL::readSql($sql,"i",array($videos_id)); 
        $fullData = sqlDAL::fetchAllAssoc($res);
        
        sqlDAL::close($res);
        $rows = array();
        if ($res!=false) {
            foreach ($fullData as $row) {
                $row['total'] = self::getTotalVideosFromTagsId($row['tags_id']);
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }    
    static function getAllVideosIdFromTagsId($tags_id) {
        global $global;
        if(!static::isTableInstalled()){
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . "  "
                . " WHERE tags_id=? ";
        $res = sqlDAL::readSql($sql,"i",array($tags_id)); 
        $fullData = sqlDAL::fetchAllAssoc($res);
        
        sqlDAL::close($res);
        $rows = array();
        if ($res!=false) {
            foreach ($fullData as $row) {
                $rows[] = $row['videos_id'];
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }  
    
    static function getAllFromVideosIdAndTagsTypesId($videos_id, $tags_types_id) {
        global $global;
        if(!static::isTableInstalled()){
            return false;
        }
        $sql = "SELECT t.*, tv.* FROM  " . static::getTableName() . " tv LEFT JOIN tags as t ON tags_id = t.id WHERE tags_types_id = ? AND videos_id=? ";
        $res = sqlDAL::readSql($sql,"ii",array($tags_types_id, $videos_id)); 
        $fullData = sqlDAL::fetchAllAssoc($res);
        
        sqlDAL::close($res);
        $rows = array();
        if ($res!=false) {
            foreach ($fullData as $row) {
                $row['total'] = self::getTotalVideosFromTagsId($row['tags_id']);
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    } 
    
       
    static function getTotalVideosFromTagsId($tags_id) {
        global $global;
        if(!static::isTableInstalled()){
            return false;
        }
        $sql = "SELECT count(*) as total FROM  " . static::getTableName() . "  "
                . " WHERE tags_id=? ";
        $res = sqlDAL::readSql($sql,"i",array($tags_id)); 
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = intval($data['total']);
        } else {
            $row = 0;
        }
        return $row;
    } 

    static function removeAllTagsFromVideo($videos_id){
        global $global;
        if (!empty($videos_id)) {
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE videos_id = ?";
            $global['lastQuery'] = $sql;
            //error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql,"i",array($videos_id));
        }
        error_log("videos_id for table " . static::getTableName() . " not defined for deletion");
        return false;
    }

}
