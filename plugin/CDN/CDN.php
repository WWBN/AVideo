<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/CDN/Storage/CDNStorage.php';

class CDN extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$LIVE,
            PluginTags::$PLAYER,
            PluginTags::$STORAGE
        );
    }

    public function getDescription() {
        global $global;
        $txt = "With our CDN we will provide you a highly-distributed platform of servers that helps minimize delays in loading web page content "
                . "by reducing the physical distance between the server and the user. This helps users around the world view the same high-quality "
                . "content without slow loading times";
        $txt .= "<br>If you are using the CDN Storage, add this into your crontab <code>2 1 * * * php {$global['systemRootPath']}plugin/CDN/tools/moveMissingFiles.php</code>. "
                . "This command will daily check your files and free some space into your server";
        $help = "";
        return $txt . $help;
    }

    public function getName() {
        return "CDN";
    }

    public function getUUID() {
        return "CDN73225-3807-4167-ba81-0509dd280e06";
    }

    public function getPluginVersion() {
        return "2.0";
    }

    public function getEmptyDataObject() {
        global $global, $config;
        $obj = new stdClass();
        $obj->key = "";
        $obj->CDN = "";
        $obj->CDN_S3 = "";
        $obj->CDN_B2 = "";
        $obj->CDN_FTP = "";
        // this is a JSON with site_id + URL
        $obj->CDN_YPTStorage = ""; // array
        $obj->CDN_Live = "";
        // this is a JSON with servers_id + URL
        $obj->CDN_LiveServers = ""; // array
        $obj->enable_storage = false;
        $obj->storage_autoupload_new_videos = true;
        $obj->storage_users_can_choose_storage = true;
        $obj->storage_username = "";
        $obj->storage_password = "";
        $obj->storage_hostname = "";

        return $obj;
    }

    public function getVideosManagerListButton() {
        if(!self::userCanMoveVideoStorage()){
            return '';
        }
        $btn = '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block " onclick="avideoModalIframeSmall(webSiteRootURL+\\\'plugin/CDN/Storage/syncVideo.php?videos_id=\'+ row.id +\'\\\');" ><i class="fas fa-project-diagram"></i> CDN Storage</button>';
        return $btn;
    }

    public function getPluginMenu() {
        global $global;
        $fileAPIName = $global['systemRootPath'] . 'plugin/CDN/pluginMenu.html';
        $content = file_get_contents($fileAPIName);
        $obj = $this->getDataObject();

        $url = "https://youphp.tube/marketplace/CDN/iframe.php?hash={hash}";

        $url = addQueryStringParameter($url, 'hash', $obj->key);
        $url = addQueryStringParameter($url, 'webSiteRootURL', $global['webSiteRootURL']);

        $cdnMenu = str_replace('{url}', $url, $content);
        $storageMenu = '';
        if(self::userCanMoveVideoStorage()){
            $fileStorageMenu = $global['systemRootPath'] . 'plugin/CDN/Storage/pluginMenu.html';
            $storageMenu = file_get_contents($fileStorageMenu);
        }
        return $cdnMenu.$storageMenu;
    }

    /**
     * 
     * @param type $type enum(CDN, CDN_S3,CDN_B2,CDN_YPTStorage,CDN_Live,CDN_LiveServers)
     * @param type $id the ID of the URL in case the CDN is an array 
     * @return boolean
     */
    static public function getURL($type = 'CDN', $id = 0) {

        $obj = AVideoPlugin::getObjectData('CDN');

        if (empty($obj->{$type})) {
            return false;
        }
        if (isIPPrivate(getDomain())) {
            _error_log('The CDN will not work under a private network $type=' . $type);
            return false;
        }
        $url = '';
        switch ($type) {
            case 'CDN':
            case 'CDN_S3':
            case 'CDN_B2':
            case 'CDN_FTP':
            case 'CDN_Live':
                $url = $obj->{$type};
                break;
            case 'CDN_LiveServers':
            case 'CDN_YPTStorage':
                if (!empty($id)) {
                    $json = _json_decode($obj->{$type});
                    //var_dump(!empty($json), is_object($json), is_array($json));//exit;
                    if (!empty($json) && (is_object($json) || is_array($json))) {
                        foreach ($json as $value) {
                            if ($value->id == $id) {
                                $url = $value->URLToCDN;
                                break;
                            }
                        }
                    }
                }
                //var_dump($url);exit;
                break;
        }

        if (!empty($url) && isValidURL($url)) {
            return addLastSlash($url);
        }

        return false;
    }
    
    static function getCDN_S3URL() {
        $plugin = AVideoPlugin::getDataObjectIfEnabled('AWS_S3');
        $CDN_S3 = '';
        if (!empty($plugin)) {
            $region = trim($plugin->region);
            $bucket_name = trim($plugin->bucket_name);
            $endpoint = trim($plugin->endpoint);
            if (!empty($endpoint)) {
                $CDN_S3 = str_replace('https://', "https://{$bucket_name}.", $endpoint);
            } else if (!empty($plugin->region)) {
                $CDN_S3 = "https://{$bucket_name}.s3-accesspoint.{$region}.amazonaws.com";
            }
            if (!empty($resp->CDN_S3)) {
                $CDN_S3 = addLastSlash($resp->CDN_S3);
            }
        }
        return $CDN_S3;
    }

    static function getCDN_B2URL() {
        $CDN_B2 = '';
        $plugin = AVideoPlugin::getDataObjectIfEnabled('Blackblaze_B2');
        if (!empty($plugin)) {
            $b2 = new Blackblaze_B2();
            $CDN_B2 = $b2->getEndpoint();
            if (!empty($resp->CDN_B2)) {
                $CDN_B2 = addLastSlash($resp->CDN_B2);
            }
        }
        return $CDN_B2;
    }

    static function getCDN_FTPURL() {
        $CDN_FTP = '';
        $plugin = AVideoPlugin::getDataObjectIfEnabled('CDN');
        if (!empty($plugin)) {
            $CDN_FTP = addLastSlash($plugin->endpoint);
        }
        return $CDN_FTP;
    }
        
    public static function getVideoTags($videos_id) {
        global $global;
        if (empty($videos_id)) {
            return array();
        }
        if (!Video::canEdit($videos_id)) {
            return array();
        }
        $video = Video::getVideoLight($videos_id);
        $sites_id = $video['sites_id'];

        $obj = new stdClass();
        $obj->label = 'Storage';
        $isMoving = CDNStorage::isMoving($videos_id);
        if ($isMoving) {
            $obj->type = "danger";
            $obj->text = '<i class="fas fa-sync fa-spin"></i> ' . __('Moving');
        } else if (empty($sites_id)) {
            $obj->type = "success";
            $obj->text = '<i class="fas fa-map-marker-alt"></i> ' . __('Local');
        } else {
            $obj->type = "warning";
            $obj->text = "<i class=\"fas fa-project-diagram\"></i> " . __('Storage');
        }
        //var_dump($obj);exit;
        return array($obj);
    }

    
    public function onEncoderNotifyIsDone($videos_id) {
        return $this->processNewVideo($videos_id);
    }

    public function onUploadIsDone($videos_id) {
        return $this->processNewVideo($videos_id);
    }

    private function processNewVideo($videos_id) {
        $obj = AVideoPlugin::getDataObjectIfEnabled('CDN');
        if($obj->enable_storage){
            if($obj->storage_autoupload_new_videos){
                CDNStorage::moveLocalToRemote($videos_id, false);
            }
        }
    }
    
    public static function userCanMoveVideoStorage(){
        $obj = AVideoPlugin::getDataObjectIfEnabled('CDN');
        if(empty($obj->enable_storage)){
            return false;
        }
        if(User::isAdmin()){
            return true;
        }
        if (!empty($obj->storage_users_can_choose_storage) && User::canUpload()) {
            return true;
        }
        return false;
    }
    
    public function getFooterCode() {
        global $global;
        if(self::userCanMoveVideoStorage()){
            include $global['systemRootPath'] . 'plugin/CDN/Storage/footer.php';
        }
    }

}
