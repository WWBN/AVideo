<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Gallery extends PluginAbstract {

    public function getDescription() {
        return "Make the first page works as a gallery";
    }

    public function getName() {
        return "Gallery";
    }

    public function getUUID() {
        return "a06505bf-3570-4b1f-977a-fd0e5cab205d";
    }
        
    public function getFirstPage(){
        global $global;
        return $global['systemRootPath'].'plugin/Gallery/view/modeGallery.php';
    }

}
