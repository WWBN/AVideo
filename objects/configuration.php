<?php

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

class Configuration {

    private $id;
    private $video_resolution;
    private $users_id;
    private $version;

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
        if (!User::isAdmin()) {
            header('Content-Type: application/json');
            die('{"error":"' . __("Permission denied") . '"}');
        }
        $this->users_id = User::getId();
        $sql = "UPDATE configurations SET video_resolution = '{$this->video_resolution}',users_id = '{$this->users_id}'  WHERE id = 1";

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
    
    function currentVersionLowerThen($version){
        return version_compare($version, $this->getVersion())>0;
    }    
    function currentVersionGreaterThen($version){
        return version_compare($version, $this->getVersion())<0;
    }
    function currentVersionEqual($version){
        return version_compare($version, $this->getVersion())==0;
    }



}
