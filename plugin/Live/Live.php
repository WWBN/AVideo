<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Live extends PluginAbstract {

    public function getDescription() {
        return "Broadcast a RTMP video from your computer<br> and receive HLS streaming from servers";
    }

    public function getName() {
        return "Live";
    }

    public function getHTMLMenuRight() {
        global $global;
        $buttonTitle = $this->getButtonTitle();
        include $global['systemRootPath'] . 'plugin/Live/view/menuRight.php';
    }

    public function getUUID() {
        return "e06b161c-cbd0-4c1d-a484-71018efa2f35";
    }

    public function getEmptyDataObject() {
        global $global;
        $server = parse_url($global['webSiteRootURL']);
        $obj = new stdClass();
        $obj->button_title = "LIVE";
        $obj->server = "rtmp://{$server['host']}/live";
        $obj->playerServer = "http://{$server['host']}:8080/live";
        // for secure connections
        //$obj->playerServer = "https://{$server['host']}:444/live";
        $obj->stats = "http://{$server['host']}/stat";
        return $obj;
    }

    public function getButtonTitle() {
        $o = $this->getDataObject();
        return $o->button_title;
    }

    public function getKey() {
        $o = $this->getDataObject();
        return $o->key;
    }

    public function getServer() {
        $o = $this->getDataObject();
        return $o->server;
    }

    public function getPlayerServer() {
        $o = $this->getDataObject();
        return $o->playerServer;
    }

    public function getStatsURL() {
        $o = $this->getDataObject();
        return $o->stats;
    }

    public function getChat($uuid) {
        global $global;
        //check if LiveChat Plugin is available
        $filename = $global['systemRootPath'] . 'plugin/LiveChat/LiveChat.php';
        if (file_exists($filename)) {
            require_once $filename;
            LiveChat::includeChatPanel($uuid);
        }
    }

    function getStatsObject() {
        ini_set('allow_url_fopen ', 'ON');
        $xml = simplexml_load_string($this->get_data($this->getStatsURL()));
        return $xml;
    }

    function get_data($url) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

}
