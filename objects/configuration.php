<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

includeConfigLog(__LINE__, basename(__FILE__));
require_once $global['systemRootPath'] . 'objects/user.php';
includeConfigLog(__LINE__, basename(__FILE__));
require_once $global['systemRootPath'] . 'objects/functions.php';
includeConfigLog(__LINE__, basename(__FILE__));
require_once $global['systemRootPath'] . 'objects/Object.php';
includeConfigLog(__LINE__, basename(__FILE__));

if (!class_exists('AVideoConf')) {

class AVideoConf extends ObjectYPT
{
    protected $id;
    protected $video_resolution;
    protected $webSiteTitle;
    protected $description;
    protected $language;
    protected $contactEmail;
    protected $users_id;
    protected $version;
    protected $authCanUploadVideos;
    protected $authCanViewChart;
    protected $authCanComment;
    protected $head;
    protected $logo;
    protected $logo_small;
    protected $adsense;
    protected $mode;
    // version 2.7
    protected $disable_analytics;
    protected $disable_youtubeupload;
    protected $allow_download;
    protected $session_timeout;
    protected $autoplay;
    // version 3.1
    protected $theme;
    //version 3.3
    protected $smtp;
    protected $smtpAuth;
    protected $smtpSecure;
    protected $smtpHost;
    protected $smtpUsername;
    protected $smtpPassword;
    protected $smtpPort;
    // version 4
    protected $encoderURL;

    const DARKTHEMES = array('cyborg','darkly','netflix','slate','superhero','solar');

    public function __construct($video_resolution = "")
    {
        $this->load();
        if (!empty($video_resolution)) {
            $this->video_resolution = $video_resolution;
        }
    }

    public function load($id='', $refreshCache = false)
    {
        global $global;
        return parent::load(1, $refreshCache);
    }

    public function save(){
        global $global;
        if (!User::isAdmin()) {
            header('Content-Type: application/json');
            die('{"error":"' . __("Permission denied") . '"}');
        }
        _error_log('Configuration saved '.getRealIpAddr().' '.json_encode(debug_backtrace()));
        $this->users_id = User::getId();
        $this->disable_youtubeupload = 0;
        ObjectYPT::deleteCache("getEncoderURL");
        //var_dump($this->head);exit;
        return parent::save();
    }

    public function getVideo_resolution()
    {
        return $this->video_resolution;
    }

    public function getUsers_id()
    {
        return $this->users_id;
    }

    public function getVersion()
    {
        if (empty($this->version)) {
            return " 0.1";
        }
        return $this->version;
    }

    public function getWebSiteTitle()
    {
        return $this->webSiteTitle;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getLanguage()
    {
        if ($this->language == "en") {
            return "us";
        }
        return $this->language;
    }

    public function getContactEmail()
    {
        return $this->contactEmail;
    }

    public function setVideo_resolution($video_resolution)
    {
        $this->video_resolution = $video_resolution;
    }

    public function setWebSiteTitle($webSiteTitle)
    {
        $this->webSiteTitle = $webSiteTitle;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function setContactEmail($contactEmail)
    {
        $this->contactEmail = $contactEmail;
    }

    public function currentVersionLowerThen($version)
    {
        return version_compare($version, $this->getVersion()) > 0;
    }

    public function currentVersionGreaterThen($version)
    {
        return version_compare($version, $this->getVersion()) < 0;
    }

    public function currentVersionEqual($version)
    {
        return version_compare($version, $this->getVersion()) == 0;
    }

    public function getAuthCanUploadVideos()
    {
        return $this->authCanUploadVideos;
    }

    public function getAuthCanViewChart()
    {
        return $this->authCanViewChart;
    }

    public function getAuthCanComment()
    {
        return $this->authCanComment;
    }

    public function setAuthCanUploadVideos($authCanUploadVideos)
    {
        $this->authCanUploadVideos = intval($authCanUploadVideos);
    }

    public function setAuthCanViewChart($authCanViewChart)
    {
        $this->authCanViewChart = $authCanViewChart;
    }

    public function setAuthCanComment($authCanComment)
    {
        $this->authCanComment = $authCanComment;
    }

    public function getHead()
    {
        return $this->head;
    }

    public function getLogo($timestamp = false)
    {
        global $global;
        if (empty($this->logo)) {
            return "view/img/logo.png";
        }
        $get = '';
        $file = str_replace("?", "", $global['systemRootPath'] . $this->logo);
        if ($timestamp && file_exists($file)) {
            $get .= "?" . filemtime($file);
        }
        return $this->logo . $get;
    }

    public static function _getFavicon($getPNG = false)
    {
        global $global;
        $file = false;
        $url = false;
        if (!$getPNG) {
            $file = $global['systemRootPath'] . "videos/favicon.ico";
            $url = getURL('videos/favicon.ico');
            if (!file_exists($file)) {
                $file = $global['systemRootPath'] . "view/img/favicon.ico";
                $url = getURL("view/img/favicon.ico");
            }
        }
        if (empty($url) || !file_exists($file)) {
            $file = $global['systemRootPath'] . "videos/favicon.png";
            $url = getURL("videos/favicon.png");
            if (!file_exists($file)) {
                $file = $global['systemRootPath'] . "view/img/favicon.png";
                $url = getURL("view/img/favicon.png");
            }
        }
        return ['file' => $file, 'url' => $url];
    }

    public function getFavicon($getPNG = false)
    {
        $return = self::_getFavicon($getPNG);
        return $return['url'];
    }

    public static function getOGImage()
    {
        global $global;
        $destination = Video::getStoragePath()."cache/og_200X200.jpg";
        $return = self::_getFavicon(true);
        if (file_exists($return['file'])) {
            convertImageToOG($return['file'], $destination);
        }
        return getURL("videos/cache/og_200X200.jpg");
    }

    public static function getOGImagePath()
    {
        global $global;
        $destination = $global['systemRootPath']."videos/cache/og_200X200.jpg";
        $return = self::_getFavicon(true);
        if (file_exists($return['file'])) {
            convertImageToOG($return['file'], $destination);
        }
        return $destination ;
    }

    public function setHead($head)
    {
        $this->head = $head;
    }

    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    public function getLogo_small()
    {
        if (empty($this->logo_small)) {
            return "view/img/logo32.png";
        }
        return $this->logo_small;
    }

    public function setLogo_small($logo_small)
    {
        $this->logo_small = $logo_small;
    }

    public function getAdsense()
    {
        return $this->adsense;
    }

    public function setAdsense($adsense)
    {
        $this->adsense = $adsense;
    }

    public function getMode()
    {
        if (empty($this->mode)) {
            return 'Youtube';
        }
        return $this->mode;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    // version 2.7
    public function getDisable_analytics()
    {
        return $this->disable_analytics;
    }

    public function getDisable_youtubeupload()
    {
        return 0;
        //return $this->disable_youtubeupload;
    }

    public function getAllow_download()
    {
        return $this->allow_download;
    }

    public function getSession_timeout()
    {
        return $this->session_timeout;
    }

    public function setDisable_analytics($disable_analytics)
    {
        $this->disable_analytics = ($disable_analytics == 'true' || $disable_analytics == '1') ? 1 : 0;
    }

    public function setDisable_youtubeupload($disable_youtubeupload)
    {
        $this->disable_youtubeupload = ($disable_youtubeupload == 'true' || $disable_youtubeupload == '1') ? 1 : 0;
    }

    public function setAllow_download($allow_download)
    {
        $this->allow_download = ($allow_download == 'true' || $allow_download == '1') ? 1 : 0;
    }

    public function setSession_timeout($session_timeout)
    {
        $this->session_timeout = $session_timeout;
    }

    public function getAutoplay()
    {
        return intval($this->autoplay);
    }

    public function setAutoplay($autoplay)
    {
        $this->autoplay = ($autoplay == 'true' || $autoplay == '1') ? 1 : 0;
    }

    static function updateConfigFile($additions, $replacements, $newVersion)
    {
        global $global;
        $filePath = "{$global['systemRootPath']}videos/configuration.php"; // Hardcoded file path

        // Check if the file exists
        if (!file_exists($filePath)) {
            return false;
        }

        // Read the file into an array
        $lines = file($filePath);

        // Check if the configuration version is already the new version
        foreach ($lines as $line) {
            if (preg_match('/\$global\[\'configurationVersion\'\] = ([0-9]+(?:\.[0-9]+)?);/', $line, $matches)) {
                if (isset($matches[1]) && (float)$matches[1] === (float)$newVersion) {
                    // Version is already the new version, no need to modify the file
                    return false;
                }
                break; // Break out of the loop once the version line is found
            }
        }

        // Create a backup of the file
        copy($filePath, "{$global['systemRootPath']}videos/configuration_bkp_".date('YmdHis').".php");

        // Process each line for replacements
        foreach ($lines as &$line) {
            foreach ($replacements as $pattern => $replacement) {
                if (preg_match($pattern, $line)) {
                    $line = preg_replace($pattern, $replacement, $line);
                }
            }
            if(preg_match('/\$global\[\'configurationVersion\'\] = [0-9]+(\.[0-9]+)?;/', $line)){
                $line = "\$global['configurationVersion'] = {$newVersion};".PHP_EOL;
            }
        }

        // Process each line for additions
        foreach ($additions as $pattern => $addition) {
            foreach ($lines as $index => &$line) {
                if (preg_match($pattern, $line)) {
                    array_splice($lines, $index + 1, 0, $addition . "\n");
                    break; // Assuming only one addition per pattern
                }
            }
        }

        // Write the array back to the file
        return file_put_contents($filePath, implode('', $lines));;
    }


    public function getTheme()
    {
        if (empty($this->theme)) {
            return "default";
        }
        return $this->theme;
    }

    public function getThemes()
    {
        $theme = json_encode(array('light'=>'default', 'dark'=>'netflix', 'defaultTheme'=>'light'));
        if (empty($this->theme)) {
            return json_decode($theme);
        }
        $json = json_decode($this->theme);
        if (empty($json)) {
            if(in_array($this->theme, AVideoConf::DARKTHEMES)){
                $theme = json_encode(array('light'=>'default', 'dark'=>$this->theme, 'defaultTheme'=>'dark'));
            }else{
                $theme = json_encode(array('light'=>$this->theme, 'dark'=>'netflix', 'defaultTheme'=>'light'));
            }
            return json_decode($theme);
        }
        return $json;
    }

    public function getThemeLight()
    {
        $theme = $this->getThemes();
        return $theme->light;
    }

    public function getThemeDark()
    {
        $theme = $this->getThemes();
        return $theme->dark;
    }

    public function getDefaultTheme()
    {
        return $this->isDefaultThemeDark() ? $this->getThemeDark() : $this->getThemeLight();
    }

    public function getAlternativeTheme()
    {
        return !$this->isDefaultThemeDark() ? $this->getThemeDark() : $this->getThemeLight();
    }

    public function isDefaultThemeDark()
    {
        $theme = $this->getThemes();
        return $theme->defaultTheme == 'dark';
    }

    public function setTheme($theme, $setDefault=false)
    {
        $themes = $this->getThemes();
        if(in_array($theme, AVideoConf::DARKTHEMES)){
            $themes->dark = $theme;
            if($setDefault){
                $themes->defaultTheme = 'dark';
            }
        }else{
            $themes->light = $theme;
            if($setDefault){
                $themes->defaultTheme = 'light';
            }
        }
        $this->theme = json_encode($themes);
    }

    public function setThemes($lightTheme, $darkTheme, $defaultTheme)
    {
        $this->theme = json_encode(array('light'=>$lightTheme, 'dark'=>$darkTheme, 'defaultTheme'=>$defaultTheme));
    }

    public function getSmtp()
    {
        return intval($this->smtp);
    }

    public function getSmtpAuth()
    {
        return intval($this->smtpAuth);
    }

    public function getSmtpSecure()
    {
        return $this->smtpSecure;
    }

    public function getSmtpHost()
    {
        return $this->smtpHost;
    }

    public function getSmtpUsername()
    {
        return $this->smtpUsername;
    }

    public function getSmtpPassword()
    {
        return $this->smtpPassword;
    }

    public function setSmtp($smtp)
    {
        $this->smtp = ($smtp == 'true' || $smtp == '1') ? 1 : 0;
    }

    public function setSmtpAuth($smtpAuth)
    {
        $this->smtpAuth = ($smtpAuth == 'true' || $smtpAuth == '1') ? 1 : 0;
    }

    public function setSmtpSecure($smtpSecure)
    {
        $this->smtpSecure = $smtpSecure;
    }

    public function setSmtpHost($smtpHost)
    {
        $this->smtpHost = $smtpHost;
    }

    public function setSmtpUsername($smtpUsername)
    {
        $this->smtpUsername = $smtpUsername;
    }

    public function setSmtpPassword(
        #[\SensitiveParameter]
        $smtpPassword
    )
    {
        $this->smtpPassword = $smtpPassword;
    }

    public function getSmtpPort()
    {
        return intval($this->smtpPort);
    }

    public function setSmtpPort($smtpPort)
    {
        $this->smtpPort = intval($smtpPort);
    }

    public function _getEncoderURL()
    {
        if (substr($this->encoderURL, -1) !== '/') {
            $this->encoderURL .= "/";
        }
        return $this->encoderURL;
    }

    public function shouldUseEncodernetwork()
    {
        global $advancedCustom, $global;
        if (empty($advancedCustom->useEncoderNetworkRecomendation) || empty($advancedCustom->encoderNetwork)) {
            return false;
        }
        if ($advancedCustom->encoderNetwork === 'https://network.wwbn.net/') {
            // check if you have your own encoder
            $encoderConfigFile = "{$global['systemRootPath']}Encoder/videos/configuration.php";
            if (file_exists($encoderConfigFile)) { // you have an encoder do not use the public one
                _error_log("Configuration:shouldUseEncodernetwork 1 You checked the Encoder Network but you have your own encoder, we will ignore this option");
                return false;
            }

            if (substr($this->encoderURL, -1) !== '/') {
                $this->encoderURL .= "/";
            }

            if (!preg_match('/encoder[1-9].avideo.com/i', $this->encoderURL)) {
                $creatingImages = "{$this->encoderURL}view/img/creatingImages.jpg";
                if (isURL200($creatingImages)) {
                    _error_log("Configuration:shouldUseEncodernetwork 2 You checked the Encoder Network but you have your own encoder, we will ignore this option");
                    return false;
                }
            }
        }
        return true;
    }

    public static function deleteEncoderURLCache()
    {
        _error_log_debug("Configuration::deleteEncoderURLCache");
        $name = "getEncoderURL" . DIRECTORY_SEPARATOR;
        $tmpDir = ObjectYPT::getCacheDir();
        $cacheDir = $tmpDir . $name;
        ObjectYPT::deleteCache($name);
        rrmdir($cacheDir);
    }

    public function getEncoderURL($addCredentials = false)
    {
        global $global, $getEncoderURL, $advancedCustom;
        if (!empty($global['forceEncoderURL'])) {
            return $global['forceEncoderURL'];
        }
        if (empty($getEncoderURL)) {
            $getEncoderURL = ObjectYPT::getCache("getEncoderURL". DIRECTORY_SEPARATOR, 60);
            if (empty($getEncoderURL)) {
                if ($this->shouldUseEncodernetwork()) {
                    if (substr($advancedCustom->encoderNetwork, -1) !== '/') {
                        $advancedCustom->encoderNetwork .= "/";
                    }
                    $bestEncoder = _json_decode(url_get_contents($advancedCustom->encoderNetwork . "view/getBestEncoder.php", "", 10));
                    if (!empty($bestEncoder->siteURL)) {
                        $this->encoderURL = $bestEncoder->siteURL;
                    } else {
                        error_log("Configuration::getEncoderURL ERROR your network ($advancedCustom->encoderNetwork) is not configured properly This slow down your site a lot, disable the option useEncoderNetworkRecomendation in your CustomizeAdvanced plugin");
                    }
                } else {
                    //error_log("Configuration::getEncoderURL shouldUseEncodernetwork said no");
                }

                if (empty($this->encoderURL)) {
                    $getEncoderURL = "https://encoder1.wwbn.net/";
                }
                addLastSlash($this->encoderURL);
                $getEncoderURL = $this->encoderURL;
                ObjectYPT::setCache("getEncoderURL", $getEncoderURL);
            } else {
                //error_log("Configuration::getEncoderURL got it from cache ". json_encode($getEncoderURL));
            }
        }
        $return = addLastSlash($getEncoderURL);
        if($addCredentials){
            $return = addQueryStringParameter($return, 'user', User::getUserName());
            $return = addQueryStringParameter($return, 'pass', User::getUserPass());
            $return = addQueryStringParameter($return, 'webSiteRootURL', $global['webSiteRootURL']);
        }
        return $return;
    }

    public function setEncoderURL($encoderURL)
    {
        $this->encoderURL = $encoderURL;
    }

    public function getPageTitleSeparator()
    {
        if (!defined('PAGE_TITLE_SEPARATOR')) {
            define("PAGE_TITLE_SEPARATOR", "&middot;"); // This is ready to be configurable, if needed
        }
        return " " . PAGE_TITLE_SEPARATOR . " ";
    }

    public static function getTableName()
    {
        return 'configurations';
    }

}
}

if (!class_exists('Configuration')) {
    class Configuration extends AVideoConf{}
}
