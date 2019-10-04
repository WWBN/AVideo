<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Articles extends PluginAbstract {

    public function getDescription() {
        global $global;
        $str = "Create rich text articles";
        $alert = "";
        $dir = $global['systemRootPath']."objects/htmlpurifier/HTMLPurifier/DefinitionCache/Serializer";
        if (!is_writable($dir)) {
            $alert = "<div class='alert alert-danger'>Your directory must be writable<br><code>sudo chmod 777 {$dir}</code></div>";
        }
        return $str.$alert;
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
    
    public function getPluginMenu() {
        global $global;
        $btn = '<a href="' . $global['webSiteRootURL'] . 'plugin/Articles/updateDescriptions.php" class="btn btn-default btn-xs btn-block" target="_blank">Update Old Descriptions</a>';
        $btn .= '<a href="' . $global['webSiteRootURL'] . 'plugin/Articles/updateDescriptionsRemoveTags.php" class="btn btn-default btn-xs btn-block" target="_blank">Revert Descriptions to NON-HTML</a>';
        return $btn;
    }


}
