<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class BookmarkTable extends ObjectYPT {

    protected $id, $timeInSeconds, $name, $videos_id;
    
    static function getSearchFieldsNames() {
        return array('name','title');
    }

    static function getTableName() {
        return 'bookmarks';
    }
        
    function getTimeInSeconds() {
        return $this->timeInSeconds;
    }

    function getName() {
        return $this->name;
    }

    function getVideos_id() {
        return $this->videos_id;
    }

    function setTimeInSeconds($timeInSeconds) {
        $this->timeInSeconds = floatval($timeInSeconds);
    }

    function setName($name) {
        $this->name = $name;
    }

    function setVideos_id($videos_id) {
        $this->videos_id = $videos_id;
    }

    static function deleteAllFromVideo($videos_id) {
        global $global;
        if (!empty($videos_id)) {
            Bookmark::videoToVtt($videos_id);
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE videos_id = ?";
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql,"i",array($videos_id));
        }
        return false;
    }
    
    static function getAllFromVideo($videos_id) {
        global $global;
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE videos_id = ? ORDER BY timeInSeconds ASC ";
        //$sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql,"i",array($videos_id)); 
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res!=false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } 
        return $rows;
    }
    
    
    static function getAll() {
        global $global;
        $sql = "SELECT b.*, title, filename FROM  " . static::getTableName() . " b LEFT JOIN videos v on v.id = videos_id  WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql); 
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res!=false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
    
    public function save() {
        // make sure that will be only one bookmark for each time
        $row = self::getFromTime($this->getVideos_id(), $this->getTimeInSeconds());
        if(!empty($row)){
            $this->id = $row['id'];
        }
        $id = parent::save();
        if(!empty($id) && !empty($this->getVideos_id())){
            Bookmark::videoToVtt($this->getVideos_id());
        }
        return $id;
    }

    public function delete() {
        if(!empty($this->id)){
            $b = new BookmarkTable($this->id);            
        }
        $deleted = parent::delete();
        if(!empty($b)){
            Bookmark::videoToVtt($b->getVideos_id());        
        }
        return $deleted;
    }
    
    static protected function getFromTime($videos_id, $timeInSeconds) {
        global $global;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  videos_id = ? AND timeInSeconds = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql,"ii",array($videos_id, $timeInSeconds)); 
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

}
