<?php

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

class Configuration {

    private $id;
    private $video_resolution;
    private $webSiteTitle;
    private $language;
    private $contactEmail;
    private $users_id;
    private $version;
    private $authGoogle_id;
    private $authGoogle_key;
    private $authGoogle_enabled;
    private $authFacebook_id;
    private $authFacebook_key;
    private $authFacebook_enabled;
    private $authCanUploadVideos;
    private $authCanComment;

    function __construct($video_resolution="") {
        $this->load();
        if(!empty($video_resolution)){
            $this->video_resolution = $video_resolution;
        }
    }

    function load() {
        global $global;
        $sql = "SELECT * FROM configurations WHERE id = 1 LIMIT 1";
        //echo $sql;exit;
        $res = $global['mysqli']->query($sql);
        if ($res) {
            $config = $res->fetch_assoc();
            foreach ($config as $key => $value){
                $this->$key = $value;
            }
        } else {
            return false;
        }
        
    }

    function save() {
        global $global;
        if (!User::isAdmin()) {
            header('Content-Type: application/json');
            die('{"error":"' . __("Permission denied") . '"}');
        }
        $this->users_id = User::getId();
        $sql = "UPDATE configurations SET "
                . "video_resolution = '{$this->video_resolution}',"
                . "webSiteTitle = '{$this->webSiteTitle}',"
                . "language = '{$this->language}',"
                . "contactEmail = '{$this->contactEmail}',"
                . "users_id = '{$this->users_id}',  "
                . "authGoogle_id = '{$this->authGoogle_id}',"
                . "authGoogle_key = '{$this->authGoogle_key}',"
                . "authGoogle_enabled = '{$this->authGoogle_enabled}',"
                . "authFacebook_id = '{$this->authFacebook_id}',"
                . "authFacebook_key = '{$this->authFacebook_key}',"
                . "authFacebook_enabled = '{$this->authFacebook_enabled}',"
                . "authCanUploadVideos = '{$this->authCanUploadVideos}',"
                . "authCanComment = '{$this->authCanComment}'"
                . "WHERE id = 1";

        $insert_row = $global['mysqli']->query($sql);

        if ($insert_row) {
            return true;
        } else {
            die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }
    
    function getVideo_resolution() {
        return $this->video_resolution;
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function getVersion() {
        if(empty($this->version)){
            return " 0.1";
        }
        return $this->version;
    }
    
    function getWebSiteTitle() {
        return $this->webSiteTitle;
    }

    function getLanguage() {
        return $this->language;
    }

    function getContactEmail() {
        return $this->contactEmail;
    }

    function setVideo_resolution($video_resolution) {
        $this->video_resolution = $video_resolution;
    }

    function setWebSiteTitle($webSiteTitle) {
        $this->webSiteTitle = $webSiteTitle;
    }

    function setLanguage($language) {
        $this->language = $language;
    }

    function setContactEmail($contactEmail) {
        $this->contactEmail = $contactEmail;
    }        
        
    function currentVersionLowerThen($version){
        return version_compare($version, $this->getVersion())>0;
    }    
    function currentVersionGreaterThen($version){
        return version_compare($version, $this->getVersion())<0;
    }
    function currentVersionEqual($version){
        return version_compare($version, $this->getVersion())==0;
    }


    function getAuthGoogle_id() {
        return $this->authGoogle_id;
    }

    function getAuthGoogle_key() {
        return $this->authGoogle_key;
    }

    function getAuthGoogle_enabled() {
        return intval($this->authGoogle_enabled);
    }

    function getAuthFacebook_id() {
        return $this->authFacebook_id;
    }

    function getAuthFacebook_key() {
        return $this->authFacebook_key;
    }

    function getAuthFacebook_enabled() {
        return intval($this->authFacebook_enabled);
    }

    function setAuthGoogle_id($authGoogle_id) {
        $this->authGoogle_id = $authGoogle_id;
    }

    function setAuthGoogle_key($authGoogle_key) {
        $this->authGoogle_key = $authGoogle_key;
    }

    function setAuthGoogle_enabled($authGoogle_enabled) {
        $this->authGoogle_enabled = intval($authGoogle_enabled);
    }

    function setAuthFacebook_id($authFacebook_id) {
        $this->authFacebook_id = $authFacebook_id;
    }

    function setAuthFacebook_key($authFacebook_key) {
        $this->authFacebook_key = $authFacebook_key;
    }

    function setAuthFacebook_enabled($authFacebook_enabled) {
        $this->authFacebook_enabled = intval($authFacebook_enabled);
    }

    function getAuthCanUploadVideos() {
        return $this->authCanUploadVideos;
    }

    function getAuthCanComment() {
        return $this->authCanComment;
    }

    function setAuthCanUploadVideos($authCanUploadVideos) {
        $this->authCanUploadVideos = $authCanUploadVideos;
    }

    function setAuthCanComment($authCanComment) {
        $this->authCanComment = $authCanComment;
    }



}
