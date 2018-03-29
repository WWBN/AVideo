<?php

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

class Configuration {

    private $id;
    private $video_resolution;
    private $webSiteTitle;
    private $language;
    private $contactEmail;
    private $users_id;
    private $version;
    private $authCanUploadVideos;
    private $authCanComment;
    private $head;
    private $logo;
    private $logo_small;
    private $adsense;
    private $mode;
    // version 2.7
    private $disable_analytics;
    private $disable_youtubeupload;
    private $allow_download;
    private $session_timeout;
    private $autoplay;
    // version 3.1
    private $theme;
    //version 3.3
    private $smtp;
    private $smtpAuth;
    private $smtpSecure;
    private $smtpHost;
    private $smtpUsername;
    private $smtpPassword;
    private $smtpPort;
    // version 4
    private $encoderURL;

    function __construct($video_resolution = "") {
        $this->load();
        if (!empty($video_resolution)) {
            $this->video_resolution = $video_resolution;
        }
    }

    function load() {
        global $global;
        $sql = "SELECT * FROM configurations WHERE id = 1 LIMIT 1";
        //echo $sql;exit;
        $res = $global['mysqli']->query($sql);
        if ($res) {
            $config = $res->fetch_assoc();
            //var_dump($config);exit;
            foreach ($config as $key => $value) {
                $this->$key = $value;
            }
        } else {
            return false;
        }
    }

    function save() {
        global $global;
        if (!User::isAdmin()) {
            header('Content-Type: application/json');
            die('{"error":"' . __("Permission denied") . '"}');
        }
        $this->users_id = User::getId();



        $sql = "UPDATE configurations SET "
                . "video_resolution = '{$this->video_resolution}',"
                . "webSiteTitle = '{$this->webSiteTitle}',"
                . "language = '{$this->language}',"
                . "contactEmail = '{$this->contactEmail}',"
                . "users_id = '{$this->users_id}',  "
                . "authCanUploadVideos = '{$this->authCanUploadVideos}',"
                . "authCanComment = '{$this->authCanComment}',"
                . "encoderURL = '{$global['mysqli']->real_escape_string($this->getEncoderURL())}',"
                . "head = '{$global['mysqli']->real_escape_string($this->getHead())}',"
                . "adsense = '{$global['mysqli']->real_escape_string($this->getAdsense())}',"
                . "mode = '{$this->getMode()}',"
                . "logo = '{$global['mysqli']->real_escape_string($this->getLogo())}',"
                . "logo_small = '{$global['mysqli']->real_escape_string($this->getLogo_small())}',"
                . "disable_analytics = '{$this->getDisable_analytics()}',"
                . "disable_youtubeupload = '{$this->getDisable_youtubeupload()}',"
                . "allow_download = '{$this->getAllow_download()}',"
                . "session_timeout = '{$this->getSession_timeout()}',"
                . "autoplay = '{$global['mysqli']->real_escape_string($this->getAutoplay())}',"
                . "theme = '{$global['mysqli']->real_escape_string($this->getTheme())}',"
                . "smtp = '{$this->getSmtp()}',"
                . "smtpAuth = '{$this->getSmtpAuth()}',"
                . "smtpSecure = '{$global['mysqli']->real_escape_string($this->getSmtpSecure())}',"
                . "smtpHost = '{$global['mysqli']->real_escape_string($this->getSmtpHost())}',"
                . "smtpUsername = '{$global['mysqli']->real_escape_string($this->getSmtpUsername())}',"
                . "smtpPort = '{$global['mysqli']->real_escape_string($this->getSmtpPort())}',"
                . "smtpPassword = '{$global['mysqli']->real_escape_string($this->getSmtpPassword())}'"
                . " WHERE id = 1";


        $insert_row = $global['mysqli']->query($sql);

        if ($insert_row) {
            return true;
        } else {
            die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }

    function getVideo_resolution() {
        return $this->video_resolution;
    }

    function getUsers_id() {
        return $this->users_id;
    }

    function getVersion() {
        if (empty($this->version)) {
            return " 0.1";
        }
        return $this->version;
    }

    function getWebSiteTitle() {
        return $this->webSiteTitle;
    }

    function getLanguage() {
        if ($this->language == "en") {
            return "us";
        }
        return $this->language;
    }

    function getContactEmail() {
        return $this->contactEmail;
    }

    function setVideo_resolution($video_resolution) {
        $this->video_resolution = $video_resolution;
    }

    function setWebSiteTitle($webSiteTitle) {
        $this->webSiteTitle = $webSiteTitle;
    }

    function setLanguage($language) {
        $this->language = $language;
    }

    function setContactEmail($contactEmail) {
        $this->contactEmail = $contactEmail;
    }

    function currentVersionLowerThen($version) {
        return version_compare($version, $this->getVersion()) > 0;
    }

    function currentVersionGreaterThen($version) {
        return version_compare($version, $this->getVersion()) < 0;
    }

    function currentVersionEqual($version) {
        return version_compare($version, $this->getVersion()) == 0;
    }

    function getAuthCanUploadVideos() {
        return $this->authCanUploadVideos;
    }

    function getAuthCanComment() {
        return $this->authCanComment;
    }

    function setAuthCanUploadVideos($authCanUploadVideos) {
        $this->authCanUploadVideos = $authCanUploadVideos;
    }

    function setAuthCanComment($authCanComment) {
        $this->authCanComment = $authCanComment;
    }

    function getHead() {
        return $this->head;
    }

    function getLogo() {
        if (empty($this->logo)) {
            return "view/img/logo.png";
        }
        return $this->logo;
    }

    function setHead($head) {
        $this->head = $head;
    }

    function setLogo($logo) {
        $this->logo = $logo;
    }

    function getLogo_small() {
        if (empty($this->logo_small)) {
            return "view/img/logo32.png";
        }
        return $this->logo_small;
    }

    function setLogo_small($logo_small) {
        $this->logo_small = $logo_small;
    }

    function getAdsense() {
        return $this->adsense;
    }

    function setAdsense($adsense) {
        $this->adsense = $adsense;
    }

    function getMode() {
        if (empty($this->mode)) {
            return 'Youtube';
        }
        return $this->mode;
    }

    function setMode($mode) {
        $this->mode = $mode;
    }

    // version 2.7
    function getDisable_analytics() {
        return $this->disable_analytics;
    }
    
    function getDisable_youtubeupload() {
        return $this->disable_youtubeupload;
    }

    function getAllow_download() {
        return $this->allow_download;
    }
    
    function getSession_timeout() {
        return $this->session_timeout;
    }

    function setDisable_analytics($disable_analytics) {
        $this->disable_analytics = ($disable_analytics == 'true' || $disable_analytics == '1') ? 1 : 0;
    }
    
    function setDisable_youtubeupload($disable_youtubeupload) {
        $this->disable_youtubeupload = ($disable_youtubeupload == 'true' || $disable_youtubeupload == '1') ? 1 : 0;
    }
    
    function setAllow_download($allow_download) {
        $this->allow_download = ($allow_download == 'true' || $allow_download == '1') ? 1 : 0;
    }

    function setSession_timeout($session_timeout) {
        $this->session_timeout = $session_timeout;
    }

    function getAutoplay() {
        return $this->autoplay;
    }

    function setAutoplay($autoplay) {
        $this->autoplay = ($autoplay == 'true' || $autoplay == '1') ? 1 : 0;
    }

    // end version 2.7

    static function rewriteConfigFile() {
        global $global, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase;
        $content = "<?php
\$global['disableAdvancedConfigurations'] = 0;
\$global['videoStorageLimitMinutes'] = 0;
\$global['webSiteRootURL'] = '{$global['webSiteRootURL']}';
\$global['systemRootPath'] = '{$global['systemRootPath']}';

\$mysqlHost = '{$mysqlHost}';
\$mysqlUser = '{$mysqlUser}';
\$mysqlPass = '{$mysqlPass}';
\$mysqlDatabase = '{$mysqlDatabase}';

/**
 * Do NOT change from here
 */

require_once \$global['systemRootPath'].'objects/include_config.php';
";

        $fp = fopen($global['systemRootPath'] . "videos/configuration.php", "wb");
        fwrite($fp, $content);
        fclose($fp);
    }

    function getTheme() {
        if (empty($this->theme)) {
            return "default";
        }
        return $this->theme;
    }

    function setTheme($theme) {
        $this->theme = $theme;
    }

    function getSmtp() {
        return intval($this->smtp);
    }

    function getSmtpAuth() {
        return intval($this->smtpAuth);
    }

    function getSmtpSecure() {
        return $this->smtpSecure;
    }

    function getSmtpHost() {
        return $this->smtpHost;
    }

    function getSmtpUsername() {
        return $this->smtpUsername;
    }

    function getSmtpPassword() {
        return $this->smtpPassword;
    }

    function setSmtp($smtp) {
        $this->smtp = ($smtp == 'true' || $smtp == '1') ? 1 : 0;
    }

    function setSmtpAuth($smtpAuth) {
        $this->smtpAuth = ($smtpAuth == 'true' || $smtpAuth == '1') ? 1 : 0;
    }

    function setSmtpSecure($smtpSecure) {
        $this->smtpSecure = $smtpSecure;
    }

    function setSmtpHost($smtpHost) {
        $this->smtpHost = $smtpHost;
    }

    function setSmtpUsername($smtpUsername) {
        $this->smtpUsername = $smtpUsername;
    }

    function setSmtpPassword($smtpPassword) {
        $this->smtpPassword = $smtpPassword;
    }

    function getSmtpPort() {
        return intval($this->smtpPort);
    }

    function setSmtpPort($smtpPort) {
        $this->smtpPort = intval($smtpPort);
    }

    function getEncoderURL() {
        if (empty($this->encoderURL)) {
            return "https://encoder.youphptube.com/";
        }
        if (substr($this->encoderURL, -1) !== '/') {
            $this->encoderURL .= "/";
        }
        return $this->encoderURL;
    }

    function setEncoderURL($encoderURL) {
        $this->encoderURL = $encoderURL;
    }

}
