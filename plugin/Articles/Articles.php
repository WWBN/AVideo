<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Articles extends PluginAbstract {

    public function getDescription() {
        return "Create rich text articles";
    }

    public function getName() {
        return "Articles";
    }

    public function getUUID() {
        return "articles-91db-4357-bb10-ee08b0913778";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->allowAttributes = false;
        $obj->allowCSS = false;
        return $obj;
    }


}
