<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Shorts extends PluginAbstract {

    public function getTags() {
        return [
            PluginTags::$FREE,
            PluginTags::$MOBILE,
        ];
    }

    public function getDescription() {
        return "Show Portrait shorts";
    }

    public function getName() {
        return "Shorts";
    }

    public function getUUID() {
        return "shortsbec-91db-4357-bb10-shorts913778";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->shortMaxDurationInSeconds = 60;

        return $obj;
    }

}
