<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class MaintenanceMode extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE,
        );
    }

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
        return "2.0";
    }

    public function getStart() {
        global $global, $config;
        if ($this->shouldEnterInMaintenencaMode()) {
            header('HTTP/1.0 403 Forbidden');
            $obj = $this->getDataObject();
            if (isContentTypeJson()) {
                header("Content-Type: application/json");
                $resp = new stdClass();
                $resp->error = true;
                $resp->msg = $obj->text;
                $resp->MaintenanceMode = true;
                echo (json_encode($resp));
            } else if (isContentTypeXML()) {
                header("Content-Type: application/xml");
                echo '<?xml version="1.0" encoding="UTF-8"?>
<response>
    <error>true</error>
    <msg>' . $obj->text . '</msg>
    <MaintenanceMode>true</MaintenanceMode>
</response>
';
            } else {
                include $global['systemRootPath'] . 'plugin/MaintenanceMode/index.php';
            }
            exit;
        }
    }

    public function shouldEnterInMaintenencaMode() {
        global $global, $config;
        $obj = $this->getDataObject();
        if (!empty($obj->stopFeed)) {
            if (preg_match('/feed/i', $_SERVER["SCRIPT_FILENAME"])) {
                return true;
            }
        }
        if (!empty($obj->stopVideo) && !empty(getVideos_id()) && !User::isAdmin()) {
            return true;
        }
        $forbidden = array(
            $global['systemRootPath'] . 'view' . DIRECTORY_SEPARATOR . 'index.php',
            $global['systemRootPath'] . 'view' . DIRECTORY_SEPARATOR . 'channels.php',
            $global['systemRootPath'] . 'view' . DIRECTORY_SEPARATOR . 'channel.php',
            $global['systemRootPath'] . 'view' . DIRECTORY_SEPARATOR . 'trending.php'
        );
        $SCRIPT_FILENAME = str_replace('/', DIRECTORY_SEPARATOR, $_SERVER["SCRIPT_FILENAME"]);
        //var_dump($SCRIPT_FILENAME, $forbidden);exit;
        if (empty($global['disableAdvancedConfigurations']) && !User::isAdmin() && in_array($SCRIPT_FILENAME, $forbidden)) {
            return true;
        }
        return false;
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->text = 'Sorry for the inconvenience but we&rsquo;re performing some maintenance at the moment. If you need to you can always <a href="mailto:{email}">contact us</a>, otherwise we&rsquo;ll be back online shortly!';
        $obj->facebookLink = '';
        $obj->twitterLink = '';
        $obj->googleLink = '';
        $obj->discordLink = '';
        $obj->stopFeed = true;
        $obj->stopVideo = true;
        $obj->endIn = date("Y-m-d H:i:s", strtotime("+1 week"));
        $obj->hideClock = false;
        $obj->backgroundImageURL = $global['webSiteRootURL'] . "plugin/MaintenanceMode/images/bg01.jpg";
        $obj->redirectHere = '';
        return $obj;
    }

    public function getFooterCode() {
        global $global, $config;
        if (!isEmbed()) {
            $obj = $this->getDataObject();
            include $global['systemRootPath'] . 'plugin/MaintenanceMode/footer.php';
        }
    }

}
