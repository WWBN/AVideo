<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/CombineSites/Objects/CombineSitesDB.php';

class CombineSites extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE
        );
    }
    public function getDescription() {
        $desc = "This plugin will share multiple streamers medias<br>";
        $desc .= $this->isReadyLabel(array('API'));
        return $desc;
    }

    public function getName() {
        return "CombineSites";
    }

    public function getUUID() {
        return "6daca392-7b14-44fb-aa33-combine0d92e";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/CombineSites/pluginMenu.html';
        return file_get_contents($filename);
    }

    public function getFirstPage() {
        global $global;
        return $global['systemRootPath'] . 'plugin/CombineSites/page/modeGallery.php';
    }
    
    

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->cacheTimeInSeconds = 600;
        return $obj;
    }

    static function getContent($combine_sites_id, $get="") {
        global $global;
        
        $obj = AVideoPlugin::getObjectData('CombineSites');
        
        $o = new CombineSitesDB($combine_sites_id);
        if(empty($get)){
            $getVars = array(
                'users_id',
                'categories_id',
                'playlists_id',
                'search'
            );
            foreach ($getVars as $value) {
                if (!empty($_REQUEST[$value])) {
                    $get .= "&$value=" . ($_REQUEST[$value]);
                }
            }
        }
        $token = $o->getGet_token();
        $site = $o->getSite_url();

        $url = "{$site}plugin/CombineSites/page/give/index.php?token={$token}{$get}&site_url=" . urlencode($global['webSiteRootURL']);
        
        $cacheName = md5($url);
        $result = ObjectYPT::getCache($cacheName,$obj->cacheTimeInSeconds);
        if(empty($result)){
            $result = url_get_contents($url);
            $result = _json_decode($result);
            ObjectYPT::setCache($cacheName, $result);
        }
        if(empty($result)){
            return $url;
        }
        return $result;
    }

}
