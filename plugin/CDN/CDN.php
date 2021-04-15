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
        $obj->CDN_YPTStorage = "";
        $obj->CDN_Live = "";
        // this is a JSON with servers_id + URL
        $obj->CDN_LiveServers = "";

        return $obj;
    }

    public function getPluginMenu() {
        global $global;
        $fileAPIName = $global['systemRootPath'] . 'plugin/CDN/pluginMenu.html';
        return file_get_contents($fileAPIName);
    }

}
