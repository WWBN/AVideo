<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/LiveLinks/Objects/LiveLinksTable.php';

class LiveLinks extends PluginAbstract {

    public function getDescription() {
        return "Register Livestreams external Links from any HLS provider, Wowza and others";
    }

    public function getName() {
        return "LiveLinks";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->onlyAdminCanAddLinks = true;
        $obj->buttonTitle = "Add a Live Link";
        $obj->disableGifThumbs = false;
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
        $obj = $this->getDataObject();
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
            '_imgGIF_',
            '_class_'
        );
        $content = file_get_contents($filename);
        $contentExtra = file_get_contents($filenameExtra);
        
        if(empty($_GET['requestComesFromVideoPage'])){
            $regex = "/".addcslashes($global['webSiteRootURL'],"/")."video\/.*/";
            $requestComesFromVideoPage = preg_match($regex, @$_SERVER["HTTP_REFERER"]);
        }else{
            $requestComesFromVideoPage = 1;
        }
        foreach ($row as $value) {
            
            if($value['type']=='unlisted'){
                continue;
            }
            if($value['type']=='logged_only'){
                if(!User::isLogged()){
                    continue;
                }
            }
            $UserPhoto = User::getPhoto($value['users_id']);   
            $name = User::getNameIdentificationById($value['users_id']);
            $replace = array(
                $value['id'],
                $UserPhoto,
                $value['title'],
                $name,
                str_replace('"', "", $value['description']),
                "{$global['webSiteRootURL']}plugin/LiveLinks/view/Live.php?link={$value['id']}",
                '<img src="'."{$global['webSiteRootURL']}plugin/LiveLinks/getImage.php?id={$value['id']}&format=jpg".'" class="thumbsJPG img-responsive" height="130">',
                empty($obj->disableGifThumbs)?('<img src="'."{$global['webSiteRootURL']}plugin/LiveLinks/getImage.php?id={$value['id']}&format=gif".'" style="position: absolute; top: 0px; height: 0px; width: 0px; display: none;" class="thumbsGIF img-responsive" height="130">'):"",
                ($requestComesFromVideoPage)?"col-xs-6":"col-lg-2 col-md-4 col-sm-4 col-xs-6"
            );

            $newContent = str_replace($search, $replace, $content);
            $newContentExtra = str_replace($search, $replace, $contentExtra);
            $array[] = array(
                "html" => $newContent,
                "htmlExtra" => $newContentExtra,
                "UserPhoto" => $UserPhoto,
                "title" => $value['title'],
                "name" => $name,
                "poster" => "{$global['webSiteRootURL']}plugin/LiveLinks/getImage.php?id={$value['id']}&format=jpg",
                "link" => "{$global['webSiteRootURL']}plugin/LiveLinks/view/Live.php?link={$value['id']}&embed=1"
            );
        }

        return $array;
    }

}
