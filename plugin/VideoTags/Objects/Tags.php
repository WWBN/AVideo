<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';
require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/TagsHasVideos.php';

class Tags extends ObjectYPT {

    protected $id, $name, $tags_types_id;

    static function getSearchFieldsNames() {
        return array('name');
    }

    static function getTableName() {
        return 'tags';
    }
    
    function loadFromName($name, $tags_types_id) {
        $row = self::getFromName($name, $tags_types_id);
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    static protected function getFromName($name, $tags_types_id) {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  name = ? AND tags_types_id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/YouPHPTube/about
        $res = sqlDAL::readSql($sql,"si",array($name, $tags_types_id)); 
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }
    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setName($name) {
        $name = trim(preg_replace("/[^[:alnum:][:space:]_]/u", '', $name));
        $this->name = $name;
    }  
    
    function getTags_types_id() {
        return $this->tags_types_id;
    }

    function setTags_types_id($tags_types_id) {
        $this->tags_types_id = $tags_types_id;
    }
    
    public function _addVideo($videos_id) {
        if(empty($this->id) || empty($videos_id)){
            return false;
        }
        $tagHasVideos = new TagsHasVideos(0);
        $tagHasVideos->setTags_id($this->id);
        $tagHasVideos->setVideos_id($videos_id);
        return $tagHasVideos->save();
    }  
    
    static function addVideo($tags_id, $videos_id) {
        $tag = new Tags($tags_id);
        return $tag->_addVideo($videos_id);
    }
    
    static function getAllFromVideosId($videos_id) {        
        $tags = TagsHasVideos::getAllFromVideosId($videos_id);
        if(!is_array($tags)){
            //error_log("getAllFromVideosId($videos_id) ".  json_encode($tags));
            return array();
        }
        //var_dump($tags);
        $tagsArray = array();
        foreach ($tags as $value) {
            $obj = new stdClass();
            $obj->type_name = $value['type_name'];
            $obj->tag_types_id = $value['tags_types_id'];
            $obj->name = $value['name'];
            $tagsArray[] = $obj;
        }
        return $tagsArray;
    }
    
    static function getObjectFromVideosId($videos_id) {        
        $array = self::getAllFromVideosId($videos_id);
        $tagsArray = array();
        foreach ($array as $value) {
            if(empty($tagsArray[$value->type_name])){
                $tagsArray[$value->type_name] = array();
            }
            $tagsArray[$value->type_name][] = $value->name;
        }
        return empty($tagsArray)?(new stdClass()):$tagsArray;
    }
    
    static function getAllTagsList($tags_types_id) {
        global $global;
        $tags_types_id = intval($tags_types_id);
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";
        if(!empty($tags_types_id)){
            $sql .= " AND tags_types_id = $tags_types_id ";
        }
        $sql .= " ORDER BY name ";
        $res = sqlDAL::readSql($sql); 
        $fullData = sqlDAL::fetchAllAssoc($res);
        
        sqlDAL::close($res);
        $rows = array();
        if ($res!=false) {
            foreach ($fullData as $row) {
                $rows[] = $row['name'];
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }   
    
        
}
