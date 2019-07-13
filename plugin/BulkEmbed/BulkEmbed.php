<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class BulkEmbed extends PluginAbstract {

    public function getDescription() {
        $str = 'Set DEVELOPER_KEY to the "API key" value from the "Access" tab of the<br>
Google Developers Console https://console.developers.google.com<br>
Please ensure that you have enabled the YouTube Data API for your project.';
        return $str;
    }

    public function getName() {
        return "BulkEmbed";
    }

    public function getUUID() {
        return "bulkembed-8c31-4f15-a355-48715fac13f3";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();

        $obj->Google_Client_ID = "";
        $obj->Google_Client_secret = "";
        return $obj;
    }

    public function getTags() {
        return array('free', 'google');
    }
    
    
    public function getPluginMenu() {
        global $global;
        $menu = '<a href="' . $global['webSiteRootURL'] . 'plugin/BulkEmbed/youtubesearch.json.php" class="btn btn-primary btn-xs btn-block" target="_blank">Test it</a>';
        $menu .= '<a href="' . $global['webSiteRootURL'] . 'plugin/BulkEmbed/search.php" class="btn btn-primary btn-xs btn-block" target="_blank">Search</a>';
        return $menu;
    }


}
