<?php

require_once __DIR__ . '/../locale/function.php';
require_once __DIR__ . '/../objects/plugin.php';

abstract class PluginAbstract {

    private $dataObjectHelper = array();
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
        global $global;
        $pluginName = $this->getName();

        $pattern = '/updateV([\d\.]+)\.sql$/'; // This pattern will match files like "updateV2.0.sql" and capture the version "2.0"

        $dir = $global['systemRootPath'] . "plugin/{$pluginName}/install/";
        //var_dump($dir);exit;
        if(is_dir($dir)){
            $files = scandir($dir);

            $versions = [];

            foreach ($files as $file) {
                if (preg_match($pattern, $file, $matches)) {
                    $versions[] = [
                        'version' => $matches[1], // This captures the version number
                        'filename' => $file       // This captures the entire filename
                    ];
                }
            }

            // Sort by version (optional)
            usort($versions, function ($a, $b) {
                return version_compare($a['version'], $b['version']);
            });

            // Iterate through sorted files
            foreach ($versions as $entry) {
                //var_dump($pluginName, $entry['version'], AVideoPlugin::compareVersion($pluginName, $entry['version']) < 0);
                if (AVideoPlugin::compareVersion($pluginName, $entry['version']) < 0) {
                    _error_log("Update plugin {$pluginName} to version {$entry['version']}");
                    $filename = $dir . '/' . $entry['filename'];
                    $sqls = file_get_contents($filename);
                    $sqlParts = explode(";", $sqls);
                    //var_dump($sqlParts);
                    foreach ($sqlParts as $value) {
                        $sql = trim($value);
                        if(empty($sql)){
                            continue;
                        }
                        if(sqlDal::writeSqlTry($sql)){
                            _error_log("Update plugin {$pluginName} to version {$entry['version']} SQL success");
                        }else{
                            _error_log("Update plugin {$pluginName} to version {$entry['version']} SQL error: {$value}");
                        }
                    }
                }
            }
            $files = scandir($dir);

            $versions = [];

            foreach ($files as $file) {
                if (preg_match($pattern, $file, $matches)) {
                    $versions[] = [
                        'version' => $matches[1], // This captures the version number
                        'filename' => $file       // This captures the entire filename
                    ];
                }
            }

            // Sort by version (optional)
            usort($versions, function ($a, $b) {
                return version_compare($a['version'], $b['version']);
            });

            // Iterate through sorted files
            foreach ($versions as $entry) {
                if (AVideoPlugin::compareVersion($pluginName, $entry['version']) < 0) {
                    _error_log("Update plugin {$pluginName} to version {$entry['version']}");
                    $filename = $dir . '/' . $entry['filename'];
                    $sqls = file_get_contents($filename);
                    $sqlParts = explode(";", $sqls);
                    foreach ($sqlParts as $value) {
                        sqlDal::writeSqlTry(trim($value));
                    }
                }
            }
        }

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

    public function getVideosManagerListButtonTitle() {
        return "";
    }

    public function getVideosManagerListButton() {
        return "";
    }

    public function getUsersManagerListButton() {
        return "";
    }

    public function getTags() {
        return array();
    }

    public function getGallerySection() {
        return "";
    }

    static function getObjectDataFromDatabase($uuid) {
        $obj = Plugin::getPluginByUUID($uuid);
        //echo $obj['object_data'];
        $o = array();
        if (!empty($obj['object_data'])) {
            $o = _json_decode(stripslashes($obj['object_data']));
            if(empty($o)){
                $o = _json_decode($obj['object_data']);
            }
        }
        return $o;
    }

    public function getDataObject() {
        $uuid = $this->getUUID();
        if (empty(PluginAbstract::$dataObject[$uuid]) && class_exists('Plugin')) {
            $obj = Plugin::getPluginByUUID($uuid);
            //echo $obj['object_data'];
            $o = self::getObjectDataFromDatabase($uuid);
            $eo = $this->getEmptyDataObject();
            if(empty($eo)){
                $eo = array();
            }
            // check if the plugin define any array for the select option, if does, overwrite it
            foreach ($eo as $key => $value) {
                if (!isset($o->$key)) {
                    continue;
                }
                $teo = gettype($value);
                $to = gettype($o->$key);
                if ($teo !== $to) { // this will make sure the type is the same
                    if (!is_numeric($value) || !is_numeric($o->$key)) {
                        if (!(is_int($value) && is_bool($o->$key)) && !(is_bool($value) && is_int($o->$key))) {
                            //_error_log("getDataObject - type is different $teo !== $to uuid = $uuid");
                            $o->$key = $value;
                        }else if(empty($o->$key) && $teo == 'object' && $to='string'){
                            $o->$key = $value;
                        }
                    }
                }
                if (isset($value->type) && is_array($value->type) && isset($o->$key) && isset($o->$key->type)) {
                    $o->$key->type = $value->type;
                }
            }
            $wholeObjects = array_merge((array) $eo, (array) $o);
            $disabledPlugins = plugin::getAllDisabled();
            foreach ($disabledPlugins as $value) {
                $p = AVideoPlugin::loadPlugin($value['dirName']);
                if (is_object($p)) {
                    $foreginObjects = $p->getCustomizeAdvancedOptions();
                    if ($foreginObjects) {
                        foreach ($foreginObjects as $optionName => $defaultValue)
                            if (isset($wholeObjects[$optionName]))
                                unset($wholeObjects[$optionName]);
                    }
                }
            }

            PluginAbstract::$dataObject[$uuid] = $wholeObjects;
        } else {
            $wholeObjects = PluginAbstract::$dataObject[$uuid];
        }
        //var_dump($obj['object_data']);
        //var_dump($eo, $o, (object) array_merge((array) $eo, (array) $o));exit;
        return (object) $wholeObjects;
    }

    public function getDataObjectInfo() {
        $eo = $this->getEmptyDataObject();
        if(empty($eo)){
            $eo = array();
        }
        $return = array();
        foreach ($eo as $key => $value) {
            $return[$key] = array(
                'is_deprecated' => $this->isDeprecated($key),
                'is_experimental' => $this->isExperimental($key),
                'is_advanced' => $this->isAdvanced($key)
            );
        }
        return $return;
    }

    public function setDataObject($object) {
        $pluginRow = Plugin::getPluginByUUID($this->getUUID());
        if (empty($pluginRow)) {
            return false;
        }
        $obj = new Plugin($pluginRow['id']);
        $obj->setObject_data(addcslashes(json_encode($object), '\\'));
        return $obj->save();
    }

    public function setDataObjectParameter($parameterName, $value) {
        $object = $this->getDataObject();
        eval("\$object->$parameterName = \$value;");
        return $this->setDataObject($object);
    }

    public static function getDataObjectAdvanced() {
        return array();
    }

    public static function getDataObjectDeprecated() {
        return array();
    }

    public static function getDataObjectExperimental() {
        return array();
    }

    public function isSomething($parameter_name, $type) {
        $name = $this->getName();
        if (empty($name) || !class_exists($name)) {
            return false;
        }
        eval("\$array = {$name}::getDataObject{$type}();");
        /**
         * @var array $array
         */
        return in_array($parameter_name, $array);
    }

    public function isAdvanced($parameter_name) {
        return $this->isSomething($parameter_name, 'Advanced');
    }

    public function isExperimental($parameter_name) {
        return $this->isSomething($parameter_name, 'Experimental');
    }

    public function isDeprecated($parameter_name) {
        return $this->isSomething($parameter_name, 'Deprecated');
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        return $obj;
    }

    public function getFirstPage() {
        return false;
    }

    public function onEncoderNotifyIsDone($videos_id) {
        return false;
    }

    public function onEncoderReceiveImage($videos_id) {
        return false;
    }

    public function onUploadIsDone($videos_id) {
        return false;
    }

    public function onReceiveFile($videos_id) {
        return false;
    }

    public function afterNewVideo($videos_id) {
        return false;
    }

    public function onNewVideo($videos_id) {
        return false;
    }

    public function onUpdateVideo($videos_id) {
        return false;
    }

    public function onDeleteVideo($videos_id) {
        return false;
    }

    public function onVideoLikeDislike($videos_id, $users_id, $isLike) {
        return false;
    }

    public function onNewSubscription($users_id, $subscriber_users_id) {
        return false;
    }

    public function afterDonation($from_users_id, $how_much, $videos_id, $users_id, $extraParameters) {
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

    public function afterVideoJS() {
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

    public function getUserNotificationButton() {
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
     * @return string array(array("key"=>'live key', "users"=>false, "name"=>$userName, "user"=>$user, "photo"=>$photo, "UserPhoto"=>$UserPhoto, "title"=>''));
     */
    public function getLiveApplicationArray() {
        return array();
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

    public function getModeLive($key) {
        return false;
    }

    public function getModeLiveLink($liveLink_id) {
        return false;
    }

    public function getModeYouTubeLive($users_id) {
        return false;
    }

    public function getEmbed($videos_id) {
        return false;
    }

    /**
     * Loads a channel before display the channel page, usefull to create customized channel pages
     * @param string $user is an database array from channels owner
     * @return boolean
     */
    public function getChannel($user_id, $user) {
        return false;
    }

    /**
     *
     * @return string return a list of IDs of the user groups
     */
    public function getDynamicUserGroupsId($users_id) {
        return array();
    }

    public function getDynamicUsersId($users_groups_id) {
        return array();
    }

    public function navBarButtons() {
        return "";
    }

    public function navBarProfileButtons() {
        return "";
    }

    public function navBar() {
        return "";
    }

    public function navBarAfter() {
        return "";
    }

    public function isReady($pluginsList) {
        $return = array('ready' => array(), 'missing' => array());
        foreach ($pluginsList as $name) {
            $plugin = AVideoPlugin::loadPlugin($name);
            if(!empty($plugin)){
                $uuid = $plugin->getUUID();
                if (!AVideoPlugin::isEnabled($uuid)) {
                    $return['missing'][] = array('name' => $name, 'uuid' => $uuid);
                } else {
                    $return['ready'][] = array('name' => $name, 'uuid' => $uuid);
                }
            }else{
                _error_log("isReady Error on load plugin {$name}");
                $return['error'][] = array('name' => $name);
            }
        }
        return $return;
    }

    public function isReadyLabel($pluginsList) {
        $desc = "<br>";

        $ready = $this->isReady($pluginsList);

        foreach ($ready['ready'] as $value) {
            $desc .= "<span class='btn btn-success btn-sm btn-xs' onclick='$(\"#enable{$value['uuid']}\").prop(\"checked\", false);$(\"#enable{$value['uuid']}\").trigger(\"change\");'><i class='fa fa-toggle-on'></i> Disable {$value['name']}</span> ";
        }
        foreach ($ready['missing'] as $value) {
            $desc .= "<span class='btn btn-danger btn-sm btn-xs' onclick='$(\"#enable{$value['uuid']}\").prop(\"checked\", true);$(\"#enable{$value['uuid']}\").trigger(\"change\");'><i class='fa fa-toggle-off'></i> Enable {$value['name']}</span> ";
        }
        foreach ($ready['error'] as $value) {
            $desc .= "<span class='btn btn-warning btn-sm btn-xs'><i class='fa fa-exclamation-triangle'></i> {$value['name']} plugin Not Found</span> ";
        }


        return $desc;
    }

    public function getAllVideosExcludeVideosIDArray() {
        return array();
    }

    /**
     *
     * @param string $users_id
     * @return 0 = I dont know, -1 = can not upload, 1 = can upload
     */
    public function userCanUpload($users_id) {
        return 0;
    }

    /**
     *
     * @param string $users_id
     * @return 0 = I dont know, -1 = can not upload, 1 = can upload
     */
    public function userCanLivestream($users_id) {
        return 0;
    }

    /**
     *
     * @param string $users_id
     * @param string $videos_id
     * @return 0 = I dont know, -1 = can not watch, 1 = can watch
     */
    public function userCanWatchVideo($users_id, $videos_id) {
        return 0;
    }

    /**
     *
     * @param string $users_id
     * @param string $videos_id
     * @return 0 = I dont know, -1 = can not watch, 1 = can watch
     */
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

    function isPaidUser($users_id) {
        return false;
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

    public function on_publish($users_id, $live_servers_id, $liveTransmitionHistory_id, $key, $isReconnection) {
        return null;
    }

    public function on_publish_done($live_transmitions_history_id, $users_id, $key, $live_servers_id) {
        return null;
    }

    public function on_publish_denied($key) {
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

    public static function getManagerVideosEditField($type = 'Advanced') {
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

    public function dataSetup() {
        return "";
    }

    function getPermissionsOptions() {
        return array();
    }

    protected function addDataObjectHelper($property, $name, $description = "") {
        $this->dataObjectHelper[$property] = array("name" => $name, "description" => $description);
    }

    function getDataObjectHelper() {
        return $this->dataObjectHelper;
    }

    function onUserSocketConnect() {

    }

    function onUserSocketDisconnect() {

    }

    function onVideoSetLive_transmitions_history_id($video_id, $oldValue, $newValue) {

    }

    function onVideoSetEncoderURL($video_id, $oldValue, $newValue) {

    }

    function onVideoSetFilepath($video_id, $oldValue, $newValue) {

    }

    function onVideoSetUsers_id($video_id, $oldValue, $newValue) {

    }

    function onVideoSetSites_id($video_id, $oldValue, $newValue) {

    }

    function onVideoSetVideo_password($video_id, $oldValue, $newValue) {

    }

    function onVideoSetClean_title($video_id, $oldValue, $newValue) {

    }

    function onVideoSetDuration($video_id, $oldValue, $newValue) {

    }

    function onVideoSetIsSuggested($video_id, $oldValue, $newValue) {

    }

    function onVideoSetStatus($video_id, $oldValue, $newValue) {

    }

    function onVideoSetType($video_id, $oldValue, $newValue, $force) {

    }

    function onVideoSetRotation($video_id, $oldValue, $newValue) {

    }

    function onVideoSetZoom($video_id, $oldValue, $newValue) {

    }

    function onVideoSetDescription($video_id, $oldValue, $newValue) {

    }

    function onVideoSetCategories_id($video_id, $oldValue, $newValue) {

    }

    function onVideoSetVideoDownloadedLink($video_id, $oldValue, $newValue) {

    }

    function onVideoSetVideoGroups($video_id, $oldValue, $newValue) {

    }

    function onVideoSetTrailer1(Video &$videoObj, $newValue) {

    }

    function onVideoSetTrailer2(Video &$videoObj, $newValue) {

    }

    function onVideoSetTrailer3(Video &$videoObj, $newValue) {

    }

    function onVideoSetRate($video_id, $oldValue, $newValue) {

    }

    function onVideoSetYoutubeId($video_id, $oldValue, $newValue) {

    }

    function onVideoSetTitle($video_id, $oldValue, $newValue) {

    }

    function onVideoSetFilename($video_id, $oldValue, $newValue, $force) {

    }

    function onVideoSetNext_videos_id($video_id, $oldValue, $newValue) {

    }

    function onVideoSetVideoLink($video_id, $oldValue, $newValue) {

    }

    function onVideoSetCan_download($video_id, $oldValue, $newValue) {

    }

    function onVideoSetCan_share($video_id, $oldValue, $newValue) {

    }

    function onVideoSetOnly_for_paid($video_id, $oldValue, $newValue) {

    }

    function onVideoSetRrating($video_id, $oldValue, $newValue) {

    }

    function executeEveryMinute() {

    }

    function executeEveryHour() {

    }

    function executeEveryDay() {

    }

    function executeEveryMonth() {

    }

    /**
     * @param type $file = [
      'filename' => "{$parts['filename']}.{$parts['extension']}",
      'path' => $file,
      'url' => $source['url'],
      'url_noCDN' => @$source['url_noCDN'],
      'type' => $type,
      'format' => strtolower($parts['extension']),
      ]
     * @return $file
     */
    function modifyURL($file, $videos_id=0) {
        return $file;
    }

    function onVideoSetExternalOptions($video_id, $oldValue, $newValue) {

    }

    function onVideoSetVideoStartSeconds($video_id, $oldValue, $newValue) {

    }

    function onVideoSetSerie_playlists_id($video_id, $oldValue, $newValue) {

    }

    function getMobileHomePageURL() {
        return false;
    }

    function updateParameter($parameterName, $newValue) {
        $pluginDO = $this->getDataObject();
        $pluginDB = Plugin::getPluginByName($this->getName());

        foreach ($pluginDO as $key => $value) {
            if ($key == $parameterName) {
                $pluginDO->$key = $newValue;
            }
        }

        $p = new Plugin($pluginDB['id']);
        $p->setObject_data(json_encode($pluginDO));
        return $p->save();
    }

    public function getWalletConfigurationHTML($users_id, $wallet, $walletDataObject) {
        return "";
    }

    function canRecordVideo($key) {
        return true;
    }

    function canNotifyVideo($key) {
        return true;
    }

    function videoHLSProtectionByPass($videos_id) {
        return false;
    }

    function getChannelPageButtons($users_id) {
        return '';
    }

    function decodeAToken() {
        return false;
    }

    function replacePlaceHolders($string, $videos_id) {
        return $string;
    }

}

class PluginPermissionOption {

    private $type, $name, $description, $className;

    function __construct($type, $name, $description, $className) {
        $this->type = $type;
        $this->name = $name;
        $this->description = $description;
        $this->className = $className;
    }

    function getType() {
        return $this->type;
    }

    function getName() {
        return $this->name;
    }

    function getDescription() {
        return $this->description;
    }

    function getClassName() {
        return $this->className;
    }

}
