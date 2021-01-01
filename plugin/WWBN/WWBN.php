<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class WWBN extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE,
        );
    }
    public function getDescription() {
        global $global;
        $desc = "WWBN Network Index (this plugin is under development)<br>";
        $desc .= "<b>Validation Token:</b><br>"
                . "<input style='width:100%' type='text' value='". self::getToken()."' readonly>";
        return $desc;
    }

    static function getToken(){
        global $global;
        $obj = new stdClass();
        $obj->plugin = "WWBN";
        $obj->webSiteRootURL = $global['webSiteRootURL'];
        $obj->time = time();
        return encryptString($obj);
    }

    public function getName() {
        return "WWBN";
    }

    public function getUUID() {
        return "WWBN-Network-Index";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        return $obj;
    }

    public function getPluginMenu() {
        global $global;
        return '
            <a href="'.$global['webSiteRootURL'].'plugin/WWBN/page/info.php" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fas fa-info-circle"></i> Info</a>
            <a href="'.$global['webSiteRootURL'].'plugin/WWBN/page/wwbn_signup.php" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-cog"></i> Set up</a>';
    }

}
