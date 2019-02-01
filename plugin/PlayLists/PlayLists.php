<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
class PlayLists extends PluginAbstract {

    public function getDescription() {
        return "A playlist video picker for youphptube for embed";
    }

    public function getName() {
        return "PlayLists";
    }

    public function getUUID() {
        return "plist12345-370-4b1f-977a-fd0e5cabtube";
    }

    public function getPluginVersion() {
        return "1.0";   
    }
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->playOnSelect = true;
        $obj->autoadvance = true;
        $obj->usersCanOnlyCreatePlayListsFromTheirContent = false;
        
        return $obj;
    }

    public function getWatchActionButton($videos_id) {
        global $global;
        if(!self::canAddVideoOnPlaylist($videos_id)){
            return "";
        }
        $obj = $this->getDataObject();
        //echo "getNetflixActionButton: ".$videos_id;
        $btnClass = "btn btn-default no-outline";
        include $global['systemRootPath'] . 'plugin/PlayLists/actionButton.php';
    }

    public function getNetflixActionButton($videos_id) {
        global $global;
        if(!self::canAddVideoOnPlaylist($videos_id)){
            return "";
        }
        $obj = $this->getDataObject();
        //echo "getNetflixActionButton: ".$videos_id;
        $btnClass = "btn btn-primary";
        include $global['systemRootPath'] . 'plugin/PlayLists/actionButton.php';
    }
    
    public function getGalleryActionButton($videos_id) {
        global $global;
        if(!self::canAddVideoOnPlaylist($videos_id)){
            return "";
        }
        $obj = $this->getDataObject();
        //echo "getNetflixActionButton: ".$videos_id;
        $btnClass = "text-primary";
        echo '<div class="">';
        include $global['systemRootPath'] . 'plugin/PlayLists/actionButton.php';
        echo '</div>';
    }
    
    public function getHeadCode() {
        global $global;
        $obj = $this->getDataObject();

        $css = '<link href="'.$global['webSiteRootURL'].'plugin/PlayLists/style.css" rel="stylesheet" type="text/css"/>';

        return $css;
    }
    
    public function getFooterCode() {
        global $global;
        $obj = $this->getDataObject();
        
        include $global['systemRootPath'] . 'plugin/PlayLists/footer.php';
    }
    
    static function canAddVideoOnPlaylist($videos_id){
        $obj = YouPHPTubePlugin::getObjectData("PlayLists");
        if(!User::isAdmin() && $obj->usersCanOnlyCreatePlayListsFromTheirContent){
            if(User::isLogged()){
                $users_id = Video::getOwner($videos_id);
                if(User::getId() == $users_id){
                    return true;
                }
            }
            return false;
        }
        return true;
    }
  
}
