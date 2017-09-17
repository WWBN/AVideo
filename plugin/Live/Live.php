<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Live extends PluginAbstract {

    public function getDescription() {
        return "Broadcast a RTMP video from your webcam and receive RTMP streaming from servers";
    }

    public function getName() {
        return "Live";
    }

    public function getHTMLMenuRight() {
        global $global;
        $buttonTitle = $this->getButtonTitle();
        include $global['systemRootPath'].'plugin/Live/view/menuRight.php';
    }

    public function getUUID() {
        return "e06b161c-cbd0-4c1d-a484-71018efa2f35";
    }
    
    public function getEmptyDataObject(){
        $obj = new stdClass();
        $obj->key = "you need a key";
        $obj->button_title = "LIVE";
        $obj->server = "rtmp://your.address/live";
        $obj->playerServer = "rtmp://your.address/live360p";
        $obj->stats = "http://your.address/stats";
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
    
    public function getChat($uuid){
        global $global;
        //check if LiveChat Plugin is available
        $filename = $global['systemRootPath'].'plugin/LiveChat/LiveChat.php';;
        if(file_exists($filename)){
            require_once $filename;
            LiveChat::includeChatPanel($uuid);
        }
    }
    
    function getStatsObject(){
        $xml = simplexml_load_file($this->getStatsURL());
        return $xml;
    }

}
