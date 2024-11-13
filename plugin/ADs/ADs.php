<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class ADs extends PluginAbstract
{
    const AdsPositions = [
        ['leaderBoardBigVideo', 1],
        ['leaderBoardTop', 0],
        ['leaderBoardTop2', 0],
        ['channelLeaderBoardTop', 0],
        ['leaderBoardMiddle', 0],
        ['sideRectangle', 1],
        ['leaderBoardBigVideoMobile', 1],
        ['leaderBoardTopMobile', 1],
        ['leaderBoardTopMobile2', 1],
        ['channelLeaderBoardTopMobile', 1],
        ['leaderBoardMiddleMobile', 1],
    ];

    public function getTags()
    {
        return [
            PluginTags::$MONETIZATION,
            PluginTags::$ADS,
            PluginTags::$FREE,
        ];
    }

    public function getDescription()
    {
        $txt = "Handle the ads system, like Adsense or similar";
        $help = "";
        $help .= "<br><small><a href='https://github.com/WWBN/AVideo/wiki/ADs-plugin' target='_blank'>"
            . "<i class='fas fa-question-circle'></i> Help</a></small>";

        return $txt . $help;
    }

    public function getName()
    {
        return "ADs";
    }

    public function getUUID()
    {
        return "ADs73225-3807-4167-ba81-0509dd280e06";
    }

    public function getPluginVersion()
    {
        return "1.1";
    }

    public static function getDataObjectAdvanced()
    {
        $array = array();

        foreach (ADS::AdsPositions as $value) {
            $array[] = $value[0];
            $array[] = "{$value[0]}Label";
            $array[] = "{$value[0]}ShowOnVideoPlayerPage";
            $array[] = "{$value[0]}AllowUserToModify";
        }
        $array[] = "tags3rdParty";
        return $array;
    }

    public function getEmptyDataObject()
    {
        global $global, $config;
        $obj = new stdClass();

        $adsense = $config->getAdsense();

        foreach (ADS::AdsPositions as $value) {
            $size = '728x90';
            if (!empty($value[1])) {
                $size = '300x250';
            }

            $o = new stdClass();
            $o->type = "textarea";
            //$o->value = empty($adsense) ? "<center><img src='{$global['webSiteRootURL']}plugin/ADs/sample{$size}.jpg'></center>" : $adsense;
            $o->value = '';
            eval("\$obj->$value[0] = \$o;");

            $width = 728;
            $height = 90;
            if (!empty($value[1])) {
                $width = 300;
                $height = 250;
            }

            eval("\$obj->$value[0]Width = {$width};");
            eval("\$obj->$value[0]Height = {$height};");
            eval("\$obj->$value[0]Label = '{$value[0]}';");
            eval("\$obj->$value[0]ShowOnVideoPlayerPage = true;");
            eval("\$obj->$value[0]AllowUserToModify = true;");
        }

        $obj->tags3rdParty = "<script> window.abkw = '{ChannelName},{Category}'; </script>";
        $obj->doNotShowAdsForPaidUsers = true;
        $obj->bannerIntervalInSeconds = 5;
        //var_dump(self::getDataObjectAdvanced(), $obj);exit;

        return $obj;
    }

    public function getPluginMenu()
    {
        global $global;
        $fileAPIName = $global['systemRootPath'] . 'plugin/ADs/pluginMenu.html';
        return file_get_contents($fileAPIName);
    }

    public function getHeadCode()
    {
        if (isInfiniteScroll()) {
            return '';
        }
        $head = "";
        //$head .= "<script> var adsbygoogleTimeout = []; </script>";
        if (!empty($_GET['abkw'])) {
            $abkw = preg_replace('/[^a-zA-Z0-9_ ,-]/', '', $_GET['abkw']);
            $head .= "<script doNotSepareteTag> window.abkw = '{$abkw}'; </script>";
            return $head;
        }
        $obj = $this->getDataObject();
        if (!empty($_GET['videoName'])) {
            if (!empty($obj->tags3rdParty)) {
                $v = Video::getVideoFromCleanTitle($_GET['videoName']);
                if (!empty($v)) {
                    $channelName = $v["channelName"];
                    $category = $v["category"];
                    $head .= str_replace(['{ChannelName}', '{Category}'], [addcslashes($channelName, "'"), addcslashes($category, "'")], $obj->tags3rdParty);

                    return $head;
                }
            }
        }
        if (!empty($_REQUEST['catName'])) {
            if (!empty($obj->tags3rdParty)) {
                $v = Category::getCategoryByName($_REQUEST['catName']);
                if (!empty($v)) {
                    $head .= str_replace([',', '{ChannelName}', '{Category}'], ['', '', addcslashes($v["name"], "'")], $obj->tags3rdParty);
                    return $head;
                }
            }
        }
        if (!empty($_GET['channelName'])) {
            if (!empty($obj->tags3rdParty)) {
                $head .= str_replace([',', '{ChannelName}', '{Category}'], ['', addcslashes($_GET['channelName'], "'"), ''], $obj->tags3rdParty);
                return $head;
            }
        }
        return "{$head}<script> window.abkw = 'home-page'; </script>";
    }

    public static function giveGoogleATimeout($adCode)
    {
        global $adsbygoogle_timeout;
        $videos_id = getVideos_id();
        $showAds = AVideoPlugin::showAds($videos_id);
        if (!$showAds) {
            return "";
        }
        if (preg_match("/adsbygoogle/i", $adCode)) {
            $uid = uniqid();
            $adCode = str_replace("(adsbygoogle = window.adsbygoogle || []).push({});", "document.addEventListener(\"DOMContentLoaded\", function(event) {startGoogleAd('#adContainer{$uid}');});", trim($adCode));
            $adCode = "<div style='min-width:250px;min-height:90px;' id='adContainer{$uid}'>{$adCode}</div>";
        }
        $adCode = str_replace("<script", "<script doNotSepareteTag ", trim($adCode));
        return $adCode;
    }


    public static function addLabel($adCode, $label)
    {
        if (!empty($label) && !empty($adCode) && User::isAdmin()) {
            $adCode = "<span data-toggle=\"tooltip\" title=\"{$label}\">{$adCode}</span>";
        }
        return $adCode;
    }

    public function showAds($videos_id)
    {
        $obj = AVideoPlugin::getDataObject('ADs');
        if ($obj->doNotShowAdsForPaidUsers && User::isLogged()) {
            return !AVideoPlugin::isPaidUser(User::getId());
        }
        return true;
    }

    public static function getAdsPath($type, $is_regular_user = false)
    {
        global $global;
        $typeFound = false;
        foreach (ADs::AdsPositions as $key => $value) {
            if ($type === $value[0]) {
                $typeFound = true;
                break;
            }
        }
        if (empty($typeFound)) {
            return false;
        }

        $videosDir = getVideosDir() . 'ADs/' . $type . '/';
        $videosURL = getCDN() . "videos/ADs/{$type}/";

        if (!empty($is_regular_user)) {
            $videosDir .= "{$is_regular_user}/";
            $videosURL .= "{$is_regular_user}/";
        }

        make_path($videosDir);
        //$videosURL = addQueryStringParameter($videosURL, 'cache', 1);
        return ['path' => $videosDir, 'url' => $videosURL];
    }

    public static function getNewAdsPath($type, $is_regular_user = false)
    {
        $paths = self::getAdsPath($type, $is_regular_user);

        if (empty($paths)) {
            return false;
        }

        $fileName = uniqid();

        return ['fileName' => $fileName, 'path' => $paths['path'] . $fileName . '.png', 'url' => $paths['url'] . $fileName . '.png', 'txt' => $paths['path'] . $fileName . '.txt'];
    }

    public static function getAds($type, $is_regular_user = false)
    {
        global $global;
        if (isBot()) {
            return array();
        }
        $paths = self::getAdsPath($type, $is_regular_user);

        if (empty($paths)) {
            return array();
        }

        $files = _glob($paths['path'], '/.png$/');
        $return = [];
        foreach ($files as $value) {
            $fileName = self::getFileName($paths['path'], $value);
            if (empty($fileName)) {
                continue;
            }
            $txt = self::getTXT("{$paths['path']}{$fileName}.txt");
            $return[] = [
                'type' => $type,
                'fileName' => $fileName,
                'txt' => $txt,
                'url' => $txt['url'],
                'title' => $txt['title'],
                'order' => $txt['order'],
                'imageURL' => "{$paths['url']}{$fileName}.png",
                'imagePath' => $value
            ];
            $fileName = '';
        }

        // Sort the array based on txt.order
        usort($return, function ($a, $b) {
            if (empty($a['txt']['order'])) {
                return 1;
            } else if (empty($b['txt']['order'])) {
                return -1;
            }
            return $a['txt']['order'] - $b['txt']['order'];
        });

        return $return;
    }

    public static function getFileName($dir, $path)
    {
        $fileName = str_replace($dir, '', $path);
        $fileName = str_replace('.png', '', $fileName);
        $fileName = str_replace('\\', '', $fileName);
        return $fileName;
    }

    public static function getTXT($path)
    {
        $content = file_get_contents($path);
        $json = json_decode($content);
        if (empty($json)) {
            return array(
                'url' => isValidURL($content) ? $content : '',
                'title' => '',
                'order' => 0,
            );
        }
        return object_to_array($json);
    }

    public static function setTXT($path, $url, $title, $order)
    {
        if (!isValidURL($url)) {
            $url = '';
        }
        $title = xss_esc($title);
        $array = array(
            'url' => $url,
            'title' => $title,
            'order' => intval($order),
        );
        return file_put_contents($path, json_encode($array));
    }

    public static function getAdsFromVideosId($type, $videos_id = 0)
    {
        global $global;

        if (isBot()) {
            return ['adCode' => '', 'label' => '', 'paths' => array()];
        }

        if (empty($videos_id)) {
            $videos_id = getVideos_id();
        }

        if (!empty($videos_id)) {
            $users_id = Video::getOwner($videos_id);
        } else if (!empty($global['isChannel'])) {
            $users_id = $global['isChannel'];
        } else {
            $users_id = 0;
        }


        return self::getAdsFromUsersId($type, $users_id);
    }

    public static function getAdsFromUsersId($type, $users_id)
    {
        $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
        if (empty($ad->$type)) {
            return ['adCode' => '', 'label' => '', 'paths' => array()];
        }
        $label = '';
        eval("\$label = \$ad->{$type}Label;");
        $label = "{$label} [$users_id] [{$type}]";

        $array = self::getAdsHTML($type, $users_id);
        if (empty($array)) {
            eval("\$adCode = \$ad->{$type}->value;");
            $array = array('paths' => array());
        } else {
            $adCode = $array['html'];

            if (empty($adCode)) {
                eval("\$adCode = \$ad->{$type}->value;");
            }
            if (empty($adCode)) {
                $array = self::getAdsHTML($type);
                $adCode = $array['html'];
            }
        }

        return ['adCode' => $adCode, 'label' => $label, 'paths' => $array['paths']];
    }

    private static function debug($line, $desc = '')
    {
        if (empty($_REQUEST['debug'])) {
            return '';
        }
        var_dump('ADs debug line=' . $line, $desc);
    }

    public static function getAdsCode($type)
    {
        global $global;
        $global['lastAdsCodeType'] = $type;
        $global['lastAdsCodeReason'] = ''; // Initialize last reason as an empty string

        // Check for infinite scroll
        if (isInfiniteScroll()) {
            $global['lastAdsCodeReason'] = 'Infinite scroll is enabled, ads are disabled in this mode.';
            return false;
        }

        // Check if viewer is a bot
        if (isBot()) {
            $global['lastAdsCodeReason'] = 'Viewer is a bot, ads are not shown to bots.';
            self::debug(__LINE__);
            return false;
        }

        $videos_id = getVideos_id();
        if (empty($videos_id)) {
            $global['lastAdsCodeReason'] = 'No video ID found, unable to retrieve ads for specific content.';
        }

        // Check if ADs plugin is enabled
        $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
        $adCode = '';
        if (empty($ad)) {
            $global['lastAdsCodeReason'] = 'ADs plugin is disabled, ads cannot be displayed without this plugin.';
            return false;
        }

        // Determine if ads should be shown on video player page
        $showOnVideoPlayerPage = true;
        // Check if ads are configured to show on the video player page for the specified ad type
        eval("\$showOnVideoPlayerPage = \$ad->{$type}ShowOnVideoPlayerPage;");
        if (!$showOnVideoPlayerPage && isVideo()) {
            $global['lastAdsCodeReason'] = 'Ads are configured not to show on the video player page for this ad type. '
                . '<br>To enable ads here, go to the Ads plugin settings and set the parameter '
                . "{$type}ShowOnVideoPlayerPage to true if you want to display ads on the video page.";
            return false;
        }
        self::debug(__LINE__);

        // Adjust ad type for mobile if applicable
        if (isMobile()) {
            $type .= 'Mobile';
        }

        // Retrieve ads based on user ID or video ID
        $users_id = getUsers_idOwnerFromRequest();
        $adC = self::getAdsFromUsersId($type, $users_id);
        if (_empty($adC['adCode'])) {
            self::debug(__LINE__);
            $adC = self::getAdsHTML($type);
            if (!_empty($adC['html'])) {
                return $adC['html'];
            } else {
                $global['lastAdsCodeReason'] = 'No ad code found for this content type and user ID.';
                return false;
            }
        } else {
            self::debug(__LINE__);
        }

        // Finalize ad code if found, adding label and Google timeout if applicable
        $adCode = ADs::giveGoogleATimeout($adC['adCode']);
        $adCode = ADs::addLabel($adCode, $adC['label']);
        $global['lastAdsCodeReason'] = 'Ad code successfully generated.';
        self::debug(__LINE__);

        return $adCode;
    }


    public static function getAdsCodeReason($type)
    {
        $reasons = array();

        // Check if the viewer is a bot
        if (isBot()) {
            $reasons[] = 'The viewer is a bot, ads are not shown to bots.';
        }

        // Get the video ID, if available
        $videos_id = getVideos_id();
        if (empty($videos_id)) {
            $reasons[] = 'No video ID found';
        } else {
            $reasons[] = 'Video ID detected: ' . $videos_id;
        }

        // Check if the ADs plugin is enabled
        $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
        if (empty($ad)) {
            $reasons[] = 'The ADs plugin is disabled, ads cannot be displayed without this plugin.';
        } else {
            // Adjust ad type for mobile if applicable
            if (isMobile()) {
                $type .= 'Mobile';
                $reasons[] = 'User is on a mobile device, adjusting ad type to: ' . $type;
            } else {
                $reasons[] = 'User is on a non-mobile device, ad type: ' . $type;
            }

            // Check if the content is a live stream
            if (!empty($live) && !empty($live['users_id'])) {
                $reasons[] = 'Content is a live stream from user ID: ' . $live['users_id'];
                $adC = self::getAdsFromUsersId($type, $live['users_id']);
            } else {
                $reasons[] = 'Content is not live, attempting to retrieve ads based on video ID: ' . $videos_id;
                $adC = self::getAdsFromVideosId($type, $videos_id);
            }

            // Add the ad type and label for further clarification
            $reasons[] = 'Ad type: ' . $type;
            if (!empty($adC['label'])) {
                $reasons[] = 'Ad label found: ' . $adC['label'];
            } else {
                $reasons[] = 'No specific ad label found for this content.';
            }

            // Check if the ad code is empty
            if (empty($adC['adCode'])) {
                $reasons[] = 'No ad code found, likely no ads available to display.';
            }
        }

        return $reasons;
    }

    public static function getSize($type)
    {
        $obj = AVideoPlugin::getObjectData("ADs");
        foreach (ADs::AdsPositions as $key => $value) {
            if ($type == $value[0]) {
                $width = 0;
                $height = 0;
                eval("\$width = \$obj->$value[0]Width;");
                eval("\$height = \$obj->$value[0]Height;");
                return ['width' => $width, 'height' => $height, 'isMobile' => preg_match('/mobile/i', $value[0]), 'isSquare' => $value[1]];
            }
        }
        return ['width' => null, 'height' => null];
    }

    public static function getLabel($type)
    {
        $obj = AVideoPlugin::getObjectData("ADs");
        eval("\$label = \$obj->{$type}Label;");
        if (empty($label)) {
            return $type;
        } else {
            return $label;
        }
    }

    public static function getAdsHTML($type, $is_regular_user = false)
    {
        global $global;
        self::debug(__LINE__, "users_id={$is_regular_user}");
        $paths = self::getAds($type, $is_regular_user);

        if (empty($paths)) {
            self::debug(__LINE__, "users_id={$is_regular_user}");
            return false;
        }

        $id = 'myCarousel' . $type . uniqid();

        $size = self::getSize($type);

        $style = '';
        if ($size['isSquare']) {
            $width = $size['width'];
            $height = $size['height'];
            //Removed because it bugged in the mobile top
            //$style = "width: {$width}px; height: {$height}px;";
        }

        $obj = AVideoPlugin::getDataObject('ADs');
        $interval = $obj->bannerIntervalInSeconds * 1000;

        $html = "<div id=\"{$id}\" class=\"carousel slide\" data-ride=\"carousel{$id}\" style=\"{$style}\" data-interval=\"{$interval}\">"
            . "<div class=\"carousel-inner\">";

        $active = 'active';
        $validPaths = 0;
        foreach ($paths as $value) {
            $fsize = filesize($value['imagePath']);
            if ($fsize < 5000) {
                continue;
            }
            $validPaths++;
            $html .= "<div class=\"item {$active}\">";
            if (isValidURL($value['txt']['url'])) {
                $html .= "<a href=\"{$value['txt']['url']}\" target=\"_blank\">";
                $html .= "<!-- getAdsHTML::isValidURL -->";
                $html .= "<img src=\"{$value['imageURL']}\" class=\"img img-responsive\" style=\"width:100%;\" title=\"{$value['txt']['title']}\" >";
                $html .= "</a>";
            } else {
                $html .= "<!-- getAdsHTML -->";
                $html .= "<img src=\"{$value['imageURL']}\" class=\"img img-responsive\" style=\"width:100%;\"  title=\"{$value['txt']['title']}\" >";
            }
            $html .= "</div>";
            $active = '';
        }

        if ($validPaths > 1) {
            $html .= "
              <a class=\"left carousel-control\" href=\"#{$id}\" data-slide=\"prev\">
                <span class=\"glyphicon glyphicon-chevron-left\"></span>
                <span class=\"sr-only\">Previous</span>
              </a>
              <a class=\"right carousel-control\" href=\"#{$id}\" data-slide=\"next\">
                <span class=\"glyphicon glyphicon-chevron-right\"></span>
                <span class=\"sr-only\">Next</span>
              </a>";
        } elseif (empty($validPaths) && User::isAdmin()) {
            $html .= "<div class='alert alert-warning'>{$type} ADs Area</div>";
        }
        $html .= "</div></div>";

        self::debug(__LINE__, $html);
        return array('html' => $html, 'paths' => $paths);
    }

    public function getFooterCode()
    {
        global $global;

        if (isInfiniteScroll()) {
            return '';
        }

        $obj = $this->getDataObject();
        $interval = $obj->bannerIntervalInSeconds * 1000;
        $js = "<script>$(function(){
            $('.carousel').carousel({
              interval: {$interval}
            });
        });</script>";
        return $js;
    }
    public static function saveAdsHTML($type)
    {
        $p = new ADs();
        return $p->updateParameter($type, '');
        $o = new stdClass();
        $o->type = "textarea";
        $array = self::getAdsHTML($type);
        $o->value = $array['html'];
        return $p->updateParameter($type, $o);
    }

    public function getUserOptions()
    {
        $obj = $this->getDataObject();
        $userOptions = [];
        $userOptions["Can have custom ads"] = "CanHaveCustomAds";
        return $userOptions;
    }

    static public function canHaveCustomAds($users_id = 0)
    {
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        return User::externalOptionsFromUserID($users_id, "CanHaveCustomAds");
    }

    public function navBarButtons()
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/ADs/navBarButtons.php';
    }
}
