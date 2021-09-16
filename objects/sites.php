<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
class Sites extends ObjectYPT {
    protected $name, $url, $status, $secret;

    public static function getSearchFieldsNames() {
        return array('name', 'url');
    }

    public static function getTableName() {
        return 'sites';
    }

    function getName() {
        return $this->name;
    }

    function getUrl() {
        return $this->url;
    }

    function getStatus() {
        return $this->status;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setUrl($url) {
        $this->url = $url;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function getSecret() {
        return $this->secret;
    }

    function setSecret($secret) {
        $this->secret = $secret;
    }

    function save() {
        if(empty($this->getSecret())){
            $this->setSecret(md5(uniqid()));
        }

        $siteURL = $this->getUrl();
        if (substr($siteURL, -1) !== '/') {
            $siteURL .= "/";
        }
        $this->setUrl($siteURL);
        return parent::save();
    }

    static function getFromFileName($fileName){
        $obj = new stdClass();
        $obj->url = "";
        $obj->secret = "";
        $obj->filename = $fileName;
        $video = Video::getVideoFromFileNameLight($fileName);
        if(!empty($video['sites_id'])){
            $site = new Sites($video['sites_id']);
            $obj->url = $site->getUrl();
            $obj->secret = $site->getSecret();
        }
        return $obj;
    }
    
    static function getFromStatus($status) {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status = ? ";

        $res = sqlDAL::readSql($sql, 's', array($status));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

}
