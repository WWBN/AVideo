<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/LiveLinks/Objects/LiveLinksTable.php';

class LiveLinks extends PluginAbstract {

    public function getDescription() {
        return "Register Livestreams external events";
    }

    public function getName() {
        return "LiveLinks";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->onlyAdminCanAddLinks = true;
        $obj->buttonTitle = "Add a Live Link";
        return $obj;
    }

    public function getUUID() {
        return "39d3b5fe-9702-4f1d-9ffd-fe1cd22a4dc7";
    }

    public function canAddLinks() {
        $obj = $this->getDataObject();
        if (!User::isLogged()) {
            return false;
        }
        if ($obj->onlyAdminCanAddLinks && !User::isAdmin()) {
            return false;
        }
        return User::canStream();
    }

    public function getHTMLMenuRight() {
        global $global;
        $obj = $this->getDataObject();
        if (!$this->canAddLinks()) {
            return '';
        }

        include $global['systemRootPath'] . 'plugin/LiveLinks/view/menuRight.php';
    }

    static function getAllActive() {
        global $global;
        $sql = "SELECT * FROM  LiveLinks WHERE status='a' AND start_date <= now() AND end_date >= now() ORDER BY start_date ";

        $res = $global['mysqli']->query($sql);
        $rows = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    /**
     * 
     * @return type array(array("key"=>'live key', "users"=>false, "name"=>$userName, "user"=>$user, "photo"=>$photo, "UserPhoto"=>$UserPhoto, "title"=>''));
     */
    public function getLiveApplicationArray() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/LiveLinks/view/menuItem.html';
        $filenameExtra = $global['systemRootPath'] . 'plugin/LiveLinks/view/extraItem.html';
        $row = LiveLinks::getAllActive();
        $array = array();
        $search = array(
            '_unique_id_',
            '_user_photo_',
            '_title_',
            '_user_identification_',
            '_description_',
            '_link_',
            '_imgJPG_',
            '_imgGIF_'
        );
        $content = file_get_contents($filename);
        $contentExtra = file_get_contents($filenameExtra);
        foreach ($row as $value) {
            
            if($value['type']=='unlisted'){
                continue;
            }
            if($value['type']=='logged_only'){
                if(!User::isLogged()){
                    continue;
                }
            }
                        
            $replace = array(
                $value['id'],
                User::getPhoto($value['users_id']),
                $value['title'],
                User::getNameIdentificationById($value['users_id']),
                str_replace('"', "", $value['description']),
                "{$global['webSiteRootURL']}plugin/LiveLinks/view/Live.php?link={$value['id']}",
                "{$global['webSiteRootURL']}plugin/LiveLinks/getImage.php?id={$value['id']}&format=jpg",
                "{$global['webSiteRootURL']}plugin/LiveLinks/getImage.php?id={$value['id']}&format=gif"
            );

            $newContent = str_replace($search, $replace, $content);
            $newContentExtra = str_replace($search, $replace, $contentExtra);
            $array[] = array(
                "html" => $newContent,
                "htmlExtra" => $newContentExtra
            );
        }

        return $array;
    }

}
