<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/Bookmark/Objects/BookmarkTable.php';

class Bookmark extends PluginAbstract {

    public function getDescription() {
        return "Save all insert, update and delete queries for audit";
    }

    public function getName() {
        return "Bookmark";
    }

    public function getUUID() {
        return "27570956-dc62-46e3-ace9-86c6e8f9c84b";
    }    
    
    public function getPluginMenu(){
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/Bookmark/pluginMenu.html';
        return file_get_contents($filename);
    }
    
    
    public function getHeadCode() {        
        if(empty($_GET['videoName'])){
            return false;
        }
        global $global;
        $css = '<link href="' .getCDN() . 'plugin/AD_Server/videojs-markers/videojs.markers.css" rel="stylesheet" type="text/css"/>';
        return $css;
    }

    public function getFooterCode() {
        if(empty($_GET['videoName'])){
            return false;
        }
        global $global;
        $video = Video::getVideoFromCleanTitle($_GET['videoName']);
        $rows = BookmarkTable::getAllFromVideo($video['id']);
        include $global['systemRootPath'] . 'plugin/Bookmark/footer.php';
    }
    
    public function getVideosManagerListButton() {
        global $global;
        
        $btn = '';
        $btn .= '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block " onclick="avideoModalIframeFull(webSiteRootURL+\\\'plugin/Bookmark/editorVideo.php?videos_id=\'+ row.id +\'\\\');" ><i class="fas fa-bookmark"></i> Add Bookmarks</button>';
        return $btn;
    }

}
