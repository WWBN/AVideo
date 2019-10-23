<?php

require_once $global['systemRootPath'] . 'objects/plugin.php';

abstract class PluginAbstract {

    static $dataObject = array();

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

    /**
     * return the version of the plugin
     */
    public function getPluginVersion() {
        return "1.0";
    }

    public function updateScript() {
        return true;
    }

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

    public function getUsersManagerListButton() {
        return "";
    }

    public function getTags() {
        
    }

    public function getGallerySection() {
        return "";
    }

    public function getDataObject() {
        if (empty(PluginAbstract::$dataObject[$this->getUUID()])) {
            $obj = Plugin::getPluginByUUID($this->getUUID());
            //echo $obj['object_data'];
            $o = array();
            if (!empty($obj['object_data'])) {
                $o = json_decode(stripslashes($obj['object_data']));
                $json_last_error = json_last_error();
                if ($json_last_error !== JSON_ERROR_NONE) {
                    //error_log('getDataObject - JSON error (' . $json_last_error . ')' . $this->getName());
                    $o = json_decode($obj['object_data']);
                    $json_last_error = json_last_error();
                }
                switch ($json_last_error) {
                    case JSON_ERROR_NONE:
                        //echo ' - No errors';
                        break;
                    default:
                        error_log('getDataObject - JSON error ' . $this->getName());
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
            $wholeObjects = array_merge((array) $eo, (array) $o);
            $disabledPlugins = plugin::getAllDisabled();
            foreach ($disabledPlugins as $value) {
                $p = YouPHPTubePlugin::loadPlugin($value['dirName']);
                if (is_object($p)) {
                    $foreginObjects = $p->getCustomizeAdvancedOptions();
                    if ($foreginObjects) {
                        foreach ($foreginObjects as $optionName => $defaultValue)
                            if (isset($wholeObjects[$optionName]))
                                unset($wholeObjects[$optionName]);
                    }
                }
            }

            PluginAbstract::$dataObject[$this->getUUID()] = $wholeObjects;
        }else {
            $wholeObjects = PluginAbstract::$dataObject[$this->getUUID()];
        }
        //var_dump($obj['object_data']);
        //var_dump($eo, $o, (object) array_merge((array) $eo, (array) $o));exit;
        return (object) $wholeObjects;
    }

    public function setDataObject($object) {
        $pluginRow = Plugin::getPluginByUUID($this->getUUID());
        $obj = new Plugin($pluginRow['id']);
        $obj->setObject_data(addcslashes(json_encode($object), '\\'));
        return $obj->save();
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

    public function getWatchActionButton($videos_id) {
        return "";
    }

    public function getNetflixActionButton($videos_id) {
        return "";
    }

    public function getGalleryActionButton($videos_id) {
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

    public function getVideoManagerButton() {
        return "";
    }

    public function getLivePanel() {
        return "";
    }

    public function getPlayListButtons($playlist_id) {
        return "";
    }

    public function getMyAccount($users_id) {
        return "";
    }

    /**
     * 
     * @return type array(array("key"=>'live key', "users"=>false, "name"=>$userName, "user"=>$user, "photo"=>$photo, "UserPhoto"=>$UserPhoto, "title"=>''));
     */
    public function getLiveApplicationArray() {
        return array();
    }

    public function addRoutes() {
        return false;
    }

    public function addView($videos_id, $total) {
        return false;
    }

    public function getCustomizeAdvancedOptions() {
        return false;
    }

    public function getUserOptions() {
        return array();
    }

    public function getModeYouTube($videos_id) {
        return false;
    }

    /**
     * Loads a channel before display the channel page, usefull to create customized channel pages
     * @param type $user is an database array from channels owner
     * @return boolean
     */
    public function getChannel($user_id, $user) {
        return false;
    }

    /**
     * 
     * @return type return a list of IDs of the user groups
     */
    public function getDynamicUserGroupsId($users_id) {
        return array();
    }

    public function navBarButtons() {
        return "";
    }

    public function navBar() {
        return "";
    }

    public function isReady($pluginsList) {
        $return = array('ready' => array(), 'missing' => array());
        foreach ($pluginsList as $name) {
            $plugin = YouPHPTubePlugin::loadPlugin($name);
            $uuid = $plugin->getUUID();
            if (!YouPHPTubePlugin::isEnabled($uuid)) {
                $return['missing'][] = array('name' => $name, 'uuid' => $uuid);
            } else {
                $return['ready'][] = array('name' => $name, 'uuid' => $uuid);
            }
        }
        return $return;
    }

    public function isReadyLabel($pluginsList) {
        $desc = "<br>";

        $ready = $this->isReady($pluginsList);

        foreach ($ready['ready'] as $value) {
            $desc .= "<span class='btn btn-success btn-sm btn-xs' onclick='$(\"#enable{$value['uuid']}\").prop(\"checked\", false);$(\"#enable{$value['uuid']}\").trigger(\"change\");'>{$value['name']}</span> ";
        }
        foreach ($ready['missing'] as $value) {
            $desc .= "<span class='btn btn-danger btn-sm btn-xs' onclick='$(\"#enable{$value['uuid']}\").prop(\"checked\", true);$(\"#enable{$value['uuid']}\").trigger(\"change\");'>{$value['name']}</span> ";
        }

        return $desc;
    }

    public function getAllVideosExcludeVideosIDArray() {
        return array();
    }

    /**
     * 
     * @param type $users_id
     * @param type $videos_id
     * @return 0 = I dont know, -1 = can not watch, 1 = can watch
     */
    public function userCanWatchVideo($users_id, $videos_id) {
        return 0;
    }

    public function userCanWatchVideoWithAds($users_id, $videos_id) {
        return 0;
    }

    /**
     * temporary, to avoid error on old secureVideosDirectory plugins
     * @return boolean
     */
    function verifyEmbedSecurity() {
        return true;
    }

    function showAds($videos_id) {
        return true;
    }

    function getVideo() {
        return null;
    }

    public function onUserSignIn($users_id) {
        return null;
    }

    public function onUserSignup($users_id) {
        return null;
    }

    public function onLiveStream($users_id) {
        return null;
    }

    public function thumbsOverlay($videos_id) {
        return "";
    }

    public static function profileTabName($users_id) {
        return "";
    }

    public static function profileTabContent($users_id) {
        return "";
    }

    public static function getVideoWhereClause() {
        return "";
    }

    public static function getManagerVideosAddNew() {
        return "";
    }

    public static function saveVideosAddNew($post, $videos_id) {
        return true;
    }

    public static function getManagerVideosReset() {
        return "";
    }

    public static function getManagerVideosEdit() {
        return "";
    }

    public static function getManagerVideosEditField() {
        return "";
    }

    public static function getManagerVideosJavaScripts() {
        return "";
    }

    public static function getManagerVideosTab() {
        return "";
    }

    public static function getManagerVideosBody() {
        return "";
    }

    public static function getAllVideosArray($videos_id) {
        return array();
    }

    public static function getVideoTags($videos_id) {
        return array();
    }

    public function getMobileInfo() {
        return null;
    }

    public function getUploadMenuButton() {
        return "";
    }

}
