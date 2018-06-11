<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class CookieAlert extends PluginAbstract {

    public function getDescription() {
        return "EU Cookie Law Notice Plugin";
    }

    public function getName() {
        return "CookieAlert";
    }

    public function getUUID() {
        return "6daca392-7b14-44fb-aa33-51cba620d92e";
    }

    public function getHeadCode() {
        $obj = $this->getDataObject();
        global $global;
        $css = '<link href="' . $global['webSiteRootURL'] . 'plugin/CookieAlert/cookiealert.css" rel="stylesheet" type="text/css"/>';
        $css .= '<style></style>';
        return $css;
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->text = '<b>This website uses cookies</b> &#x1F36A; so we can provide you with the best user experiense. Without these cookies, the website simply would not work.';
        $obj->btnText = 'I agree';
        return $obj;
    }

    public function getFooterCode() {
        $obj = $this->getDataObject();
        global $global;

        include $global['systemRootPath'] . 'plugin/CookieAlert/footer.php';
    }

}
