<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class MaintenanceMode extends PluginAbstract {

    public function getDescription() {
        global $global;
        $desc = "Put your site in Maintenance Mode";
        if (!empty($global['disableAdvancedConfigurations'])) {
            $desc .= "<div class='alert alert-warning'>Maintenance Mode is disabled on this site</div>";
        }
        return $desc;
    }

    public function getName() {
        return "MaintenanceMode";
    }

    public function getUUID() {
        return "6dmaa392-7b14-44fb-aa33-51cba620d92e";
    }

    public function getPluginVersion() {
	return "1.0";
    }

    public function getStart() {
        global $global, $config;
        if(empty($global['disableAdvancedConfigurations']) && !User::isAdmin() 
                && $_SERVER["SCRIPT_FILENAME"] !== $global['systemRootPath'].'view/user.php'
                && $_SERVER["SCRIPT_FILENAME"] !== $global['systemRootPath'].'objects/login.json.php'
                && $_SERVER["SCRIPT_FILENAME"] !== $global['systemRootPath'].'install/recoverPassword.php'){
            $obj = $this->getDataObject();
            include $global['systemRootPath'] . 'plugin/MaintenanceMode/index.php';
            exit;
        }
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->text = 'Sorry for the inconvenience but we&rsquo;re performing some maintenance at the moment. If you need to you can always <a href="mailto:{email}">contact us</a>, otherwise we&rsquo;ll be back online shortly!';
        $obj->facebookLink = '';
        $obj->twitterLink = '';
        $obj->googleLink = '';
	$obj->discordLink = '';
        $obj->endIn = date("Y-m-d H:i:s", strtotime("+1 week"));
        $obj->hideClock = false;
        $obj->backgroundImageURL = $global['webSiteRootURL']."plugin/MaintenanceMode/images/bg01.jpg";
        return $obj;
    }

    public function getFooterCode() {
        global $global, $config;
        $obj = $this->getDataObject();
        include $global['systemRootPath'] . 'plugin/MaintenanceMode/footer.php';
    }

}
