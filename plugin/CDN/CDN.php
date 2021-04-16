<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class CDN extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$RECOMMENDED
        );
    }

    public function getDescription() {
        $txt = "(Under development, do not enable yet) With our CDN we will provide you a highly-distributed platform of servers that helps minimize delays in loading web page content "
                . "by reducing the physical distance between the server and the user. This helps users around the world view the same high-quality "
                . "content without slow loading times";
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
        return "1.0";
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

        return $obj;
    }

    public function getPluginMenu() {
        global $global;
        $fileAPIName = $global['systemRootPath'] . 'plugin/CDN/pluginMenu.html';
        return file_get_contents($fileAPIName);
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
        if(isIPPrivate(getDomain())){
            _error_log('The CDN will not work under a private network $type='.$type);
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

}
