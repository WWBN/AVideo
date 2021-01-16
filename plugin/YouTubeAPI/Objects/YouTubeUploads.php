<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/video.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class YouTubeUploads extends ObjectYPT {

    protected $id, $created, $modified, $videos_id, $url;


    static function getSearchFieldsNames() {
        return array('url');
    }

    static function getTableName() {
        return 'youTube_uploads';
    }

    function getVideos_id() {
        return $this->videos_id;
    }

    function getUrl() {
        return $this->url;
    }

    function setVideos_id($videos_id) {
        $this->videos_id = $videos_id;
    }

    function setUrl($url) {
        $this->url = $url;
    }

    function loadFromVideosID($videos_id) {
        $row = self::getFromDbVideosID($videos_id);
        if (empty($row))
            return false;
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    static function getFromDbVideosID($videos_id) {
        global $global;
        $videos_id = intval($videos_id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  videos_id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "i", array($videos_id));
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
