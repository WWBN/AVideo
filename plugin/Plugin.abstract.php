<?php

require_once $global['systemRootPath'] . 'objects/plugin.php';

abstract class PluginAbstract {

    /**
     * the plugin identification, that is what differ one prlugin from other, so it needs to be unique
     * return the universally unique identifier (UUID) is a 128-bit number used to identify information in computer systems
     * if you not sure get one here https://www.uuidgenerator.net/
     */
    abstract function getUUID();

    /**
     * most of the cases it must be the same name as the plugin
     * return the name ot the plugin
     */
    abstract function getName();

    /**
     * return the description of the plugin
     */
    abstract function getDescription();

    public function getFooterCode() {
        return "";
    }

    public function getHeadCode() {
        return "";
    }

    public function getHelp() {
        return "";
    }

    public function getHTMLBody() {
        return "";
    }

    public function getHTMLMenuLeft() {
        return "";
    }

    public function getHTMLMenuRight() {
        return "";
    }

    public function getChartContent() {
	return "";
    }

    public function getPluginMenu() {
        return "";
    }

    public function getJSFiles() {
        return array();
    }

    public function getCSSFiles() {
        return array();
    }

    public function getVideosManagerListButton() {
        return "";
    }

    public function getTags() {
        
    }

    public function getGallerySection() {
        return "";
    }

    public function getDataObject() {
        $obj = Plugin::getPluginByUUID($this->getUUID());
        //echo $obj['object_data'];
        $o = array();
        if (!empty($obj['object_data'])) {
            $o = json_decode(stripslashes($obj['object_data']));
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    //echo ' - No errors';
                    break;
                default:
                    error_log('getDataObject - JSON error');
                    error_log($obj['object_data']);
                    error_log('striped slashes');
                    error_log(stripslashes($obj['object_data']));
                case JSON_ERROR_DEPTH:
                    error_log(' - Maximum stack depth exceeded');
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    error_log(' - Underflow or the modes mismatch');
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    error_log(' - Unexpected control character found');
                    break;
                case JSON_ERROR_SYNTAX:
                    error_log(' - Syntax error, malformed JSON');
                    error_log($obj['object_data']);
                    error_log('striped slashes');
                    error_log(stripslashes($obj['object_data']));
                    break;
                case JSON_ERROR_UTF8:
                    error_log(' - Malformed UTF-8 characters, possibly incorrectly encoded');
                    break;
            }
        }
        $eo = $this->getEmptyDataObject();
        //var_dump($obj['object_data']);
        //var_dump($eo, $o, (object) array_merge((array) $eo, (array) $o));exit;
        return (object) array_merge((array) $eo, (array) $o);
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        return $obj;
    }

    public function getFirstPage() {
        return false;
    }

    public function afterNewVideo($videos_id) {
        return false;
    }

    public function afterNewComment($comments_id) {
        return false;
    }

    public function afterNewResponse($comments_id) {
        return false;
    }

    public function xsendfilePreVideoPlay() {
        return false;
    }

    public function getLogin() {
        $obj = new stdClass();
        $obj->class = ""; // btn btn-primary btn-block
        $obj->icon = ""; // fab fa-facebook-square
        $obj->type = ""; // Facebook, Google, etc
        $obj->linkToDevelopersPage = ""; //https://console.developers.google.com/apis/credentials , https://developers.facebook.com/apps

        return $obj;
    }

    public function getWatchActionButton() {
        return "";
    }

    public function getStart() {
        return false;
    }

    public function getEnd() {
        return false;
    }

    public function canEditPlugin() {
        global $global;
        return empty($global['disableAdvancedConfigurations']);
    }

    public function hidePlugin() {
        return false;
    }

    public function getChannelButton() {
        return "";
    }

    public function getLivePanel() {
        return "";
    }

    public function getPlayListButtons($playlist_id) {
	return "";
    }
    /**
     * 
     * @return type array(array("key"=>'live key', "users"=>false, "name"=>$userName, "user"=>$user, "photo"=>$photo, "UserPhoto"=>$UserPhoto, "title"=>''));
     */
    public function getLiveApplicationArray() {
        return array();
    }

}
