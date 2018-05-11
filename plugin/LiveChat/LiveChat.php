<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
class LiveChat extends PluginAbstract{

    public function getDescription() {
        global $global;
        return "A live chat for multiple propouses<br>Initiate it on terminal with the command <code>nohup php {$global['systemRootPath']}plugin/LiveChat/chat-server.php &</code>";
    }
    
    public function getName() {
        return "LiveChat";
    }

    public function getUUID() {
        return "52222da2-3f14-49db-958e-15ccb1a07f0e";
    }
    
    public static function getChatPanelFile(){
        global $global;
        return $global['systemRootPath'].'plugin/LiveChat/view/panel.php';
    }
    
    public static function includeChatPanel($chatId = ""){
        global $global;
        if(Plugin::isEnabledByUUID(self::getUUID())){
            require static::getChatPanelFile();            
        }
    }
    
    public function getEmptyDataObject() {
        global $global;
        $server = parse_url($global['webSiteRootURL']);
        $obj = new stdClass();
        $obj->port = "8888";
        $obj->websocket = "ws://{$server['host']}:{$obj->port}";
        $obj->onlyForLoggedUsers = false;
        $obj->loadLastMessages = 10;
        return $obj;
    }
    
    
    public function getWebSocket() {
        $o = $this->getDataObject();
        return $o->websocket;
    }

    
    public function getTags() {
        return array('free', 'live', 'streaming', 'live stream', 'chat');
    }
    
    public function canSendMessage(){
        $obj = $this->getDataObject();
        if(empty($obj->onlyForLoggedUsers) || User::isLogged()){
            return true;
        }
        return false;
    }

}