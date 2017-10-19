<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class LiveUsers extends PluginAbstract {

    public function getDescription() {
        return "This uses database and javascript heartbeat to detect the online users on each page, but it was made to detect Live videos viwers";
    }

    public function getName() {
        return "LiveUsers";
    }

    public function getFooterCode() {
        global $global;
        include $global['systemRootPath'] . 'plugin/LiveUsers/view/footerCode.php';
    }

    public function getUUID() {
        return "cf145581-7d5e-4bb6-8c12-48fc37c0630d";
    }


}
