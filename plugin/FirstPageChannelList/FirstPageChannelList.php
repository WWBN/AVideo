<?php

$isFirstPage = 1;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
class FirstPageChannelList extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE,
            PluginTags::$GALLERY,
            PluginTags::$LAYOUT,
        );
    }
    public function getDescription() {
        return "Make the first page a Channel list";
    }

    public function getName() {
        return "FirstPageChannelList";
    }

    public function getUUID() {
        return "channel-first-page-977a-fd0e5cab205d";
    }

    public function getPluginVersion() {
        return "1.0";   
    }

    public function getFirstPage(){
        global $global;
        if(!AVideoPlugin::isEnabledByName("YouPHPFlix2") && !AVideoPlugin::isEnabledByName("CombineSites")){
            return $global['systemRootPath'].'view/channels.php';
        }
    }   
    
    public function getHeadCode() {
        global $global;
        echo "<link href='".getCDN()."plugin/Gallery/style.css?". filectime("{$global['systemRootPath']}plugin/Gallery/style.css")."' rel='stylesheet' type='text/css'/>";
        return false;
    }
    
}
