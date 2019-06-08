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

    public function getPluginVersion() {
	return "1.0";
    }

    public function getHeadCode() {
        if($this->doNotShow()){
            return "";
        }
        $obj = $this->getDataObject();
        global $global;
        $css = '<link href="' . $global['webSiteRootURL'] . 'plugin/CookieAlert/cookiealert.css" rel="stylesheet" type="text/css"/>';
        $css .= '<style></style>';
        return $css;
    }
    
    private function doNotShow(){
        $baseName = basename($_SERVER["SCRIPT_FILENAME"]);
        if(preg_match("/embed/i", $baseName) || !empty($_GET['embed'])){
            return true;
        }
        return false;
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->text = '<b>This website uses cookies</b> &#x1F36A; so we can provide you with the best user experience. Without these cookies, the website simply would not work.';
        $obj->btnText = 'I agree';
        return $obj;
    }

    public function getFooterCode() {
        if($this->doNotShow()){
            return "";
        }
        $obj = $this->getDataObject();
        global $global;

        include $global['systemRootPath'] . 'plugin/CookieAlert/footer.php';
    }

}
