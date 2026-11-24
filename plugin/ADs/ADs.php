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

    /**
     * Desktop slots that replace a position while "{parent}Split" is on: [type, isSquare, parent, side].
     * Kept out of AdsPositions so the API and anything else that lists AdsPositions stay unchanged.
     * They use the parent's ShowOnVideoPlayerPage/AllowUserToModify settings (getSettingsType()).
     */
    const AdsSplitPositions = [
        ['leaderBoardTopLeft', 0, 'leaderBoardTop', 'left'],
        ['leaderBoardTopRight', 0, 'leaderBoardTop', 'right'],
        ['leaderBoardTop2Left', 0, 'leaderBoardTop2', 'left'],
        ['leaderBoardTop2Right', 0, 'leaderBoardTop2', 'right'],
        ['channelLeaderBoardTopLeft', 0, 'channelLeaderBoardTop', 'left'],
        ['channelLeaderBoardTopRight', 0, 'channelLeaderBoardTop', 'right'],
        ['leaderBoardMiddleLeft', 0, 'leaderBoardMiddle', 'left'],
        ['leaderBoardMiddleRight', 0, 'leaderBoardMiddle', 'right'],
    ];

    public static function getMobileType($type)
    {
        $types = [
            'leaderBoardBigVideo' => 'leaderBoardBigVideoMobile',
            'leaderBoardTop' => 'leaderBoardTopMobile',
            'leaderBoardTop2' => 'leaderBoardTopMobile2',
            'channelLeaderBoardTop' => 'channelLeaderBoardTopMobile',
            'leaderBoardMiddle' => 'leaderBoardMiddleMobile',
            // Reuse the existing 300x250 position, including its saved images and code.
            'sideRectangle' => 'sideRectangle',
        ];
        return $types[$type] ?? $type;
    }

    public static function isAdsMobile()
    {
        // Preview only: never change global device detection or the public API.
        if (self::isAdsDebug() && isset($_REQUEST['AdsDevice']) && is_string($_REQUEST['AdsDevice'])) {
            if ($_REQUEST['AdsDevice'] === 'mobile') {
                return true;
            }
            if ($_REQUEST['AdsDevice'] === 'desktop') {
                return false;
            }
        }
        return isMobile();
    }

    public static function getAllAdsPositions()
    {
        return array_merge(self::AdsPositions, self::AdsSplitPositions);
    }

    public static function isValidAdsType($type)
    {
        foreach (self::getAllAdsPositions() as $value) {
            if ($type === $value[0]) {
                return true;
            }
        }
        return false;
    }

    /**
     * The position whose display settings a type uses: the parent for a split slot, otherwise itself.
     */
    public static function getSettingsType($type)
    {
        foreach (self::AdsSplitPositions as $value) {
            if ($type === $value[0]) {
                return $value[2];
            }
        }
        return $type;
    }

    /**
     * @return array side => slot type, e.g. ['left' => 'leaderBoardTopLeft', 'right' => 'leaderBoardTopRight']
     */
    public static function getSplitPositions($type)
    {
        $slots = [];
        foreach (self::AdsSplitPositions as $value) {
            if ($type === $value[2]) {
                $slots[$value[3]] = $value[0];
            }
        }
        return $slots;
    }

    public static function isSplitEnabled($type)
    {
        if (empty(self::getSplitPositions($type))) {
            return false;
        }
        $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
        $option = "{$type}Split";
        return !empty($ad) && !empty($ad->$option);
    }

    /**
     * The split is desktop only: phones and tablets keep the existing mobile position.
     */
    public static function isSplitActive($type)
    {
        return !self::isAdsMobile() && self::isSplitEnabled($type);
    }

    public static function isAdsDebug()
    {
        return !empty($_REQUEST['AdsDebug']) && User::isAdmin();
    }

    /**
     * HTML fragments that contain ads must vary with settings, image metadata and viewer rules.
     * Hash stable inputs rather than rendered carousels (their ids change on every render).
     */
    public static function getAdsCacheKey($type)
    {
        $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
        if (empty($ad)) {
            return 'disabled';
        }
        if (self::isAdsMobile()) {
            $type = self::getMobileType($type);
        }
        $owner = intval(getUsers_idOwnerFromRequest());
        $data = [$type, $owner, $ad, self::isAdsDebug(), AVideoPlugin::showAds(getVideos_id())];
        $types = array_merge([$type], array_values(self::getSplitPositions($type)));
        foreach ($types as $slotType) {
            $data[] = self::getAds($slotType);
            if ($owner) {
                $data[] = self::getAds($slotType, $owner);
            }
        }
        return md5(json_encode($data));
    }

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
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/ADs-plugin' target='_blank'>"
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
        return "1.2";
    }

    public static function getDataObjectAdvanced()
    {
        $array = [];

        foreach (ADS::getAllAdsPositions() as $value) {
            $array[] = $value[0];
            $array[] = "{$value[0]}Label";
            if (!empty($value[2])) {
                continue;
            }
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

        foreach (ADS::getAllAdsPositions() as $value) {
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
            } elseif (!empty($value[2])) {
                // half of the 728x90 leaderboard
                $width = 364;
            }

            eval("\$obj->$value[0]Width = {$width};");
            eval("\$obj->$value[0]Height = {$height};");
            eval("\$obj->$value[0]Label = '{$value[0]}';");
            if (!empty($value[2])) {
                // a split slot uses the display settings of the banner it divides
                continue;
            }
            eval("\$obj->$value[0]ShowOnVideoPlayerPage = true;");
            eval("\$obj->$value[0]AllowUserToModify = true;");
        }

        foreach (array_unique(array_column(self::AdsSplitPositions, 2)) as $parent) {
            $option = $parent . 'Split';
            $obj->$option = false;
            self::addDataObjectHelper($option, 'Split the desktop banner', 'Show two 364x90 slots instead of the 728x90 banner. Mobile positions are unchanged. Turning this off restores the original banner; all images and code are kept.');
        }

        $obj->tags3rdParty = "<script> window.abkw = '{ChannelName},{Category}'; </script>";
        $obj->doNotShowAdsForPaidUsers = true;
        $obj->bannerIntervalInSeconds = 5;

        return $obj;
    }

    public function getPluginMenu()
    {
        global $global;
        $fileAPIName = $global['systemRootPath'] . 'plugin/ADs/pluginMenu.html';
        $debugURL = addQueryStringParameter($global['webSiteRootURL'], 'AdsDebug', 1);
        return file_get_contents($fileAPIName) . '<a href="' . $debugURL . '" target="_blank" class="btn btn-default btn-sm btn-xs btn-block"><i class="fas fa-bug"></i> ' . __('Show Ads Positions') . '</a>';
    }

    public function getHeadCode()
    {
        if (isInfiniteScroll()) {
            return '';
        }
        $head = "";
        // Shared placement spacing applies to single banners as well as split banners.
        $head .= '<link href="' . getURL('plugin/ADs/ADs.css') . '" rel="stylesheet" type="text/css"/>';
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
        if (!self::isValidAdsType($type)) {
            return false;
        }

        $videosDir = getVideosDir() . 'ADs/' . $type . '/';
        $videosURL = getCDN() . "videos/ADs/{$type}/";

        // a users id or nothing (site-wide ads): never a path fragment (GHSA-58h3-9722-fgpm)
        $is_regular_user = intval($is_regular_user);
        if (!empty($is_regular_user)) {
            $videosDir .= "{$is_regular_user}/";
            $videosURL .= "{$is_regular_user}/";
        }

        make_path($videosDir);
        //$videosURL = addQueryStringParameter($videosURL, 'cache', 1);
        return ['path' => $videosDir, 'url' => $videosURL];
    }

    /**
     * Whose ads the editor endpoints change: an admin picks it (0 = site-wide ads),
     * a user with "Can have custom ads" only ever edits their own.
     */
    public static function getEditableAdsUsersId()
    {
        if (User::isAdmin()) {
            return intval(@$_REQUEST['is_regular_user']);
        }
        $users_id = intval(User::getId());
        if (empty($users_id)) {
            forbiddenPage('You can not do this');
        }
        return $users_id;
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
            return [];
        }
        $paths = self::getAdsPath($type, $is_regular_user);
        if (empty($paths)) {
            return [];
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
                'imageURL' => "{$paths['url']}{$fileName}.png?cache=" . filemtime($value),
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
        $parsedDir = parse_url($dir, PHP_URL_PATH);
        if (!empty($parsedDir)) {
            $dir = $parsedDir;
        }
        $parsedPath = parse_url($path, PHP_URL_PATH);
        if (!empty($parsedPath)) {
            $path = $parsedPath;
        }
        $path = preg_replace('/[?#].*$/', '', $path);
        $dir = preg_replace('/[?#].*$/', '', $dir);
        $fileName = str_replace($dir, '', $path);
        if ($fileName === $path) {
            $fileName = basename($path);
        }
        return str_replace(['.png', '\\'], '', $fileName);
    }

    public static function getTXT($path)
    {
        $content = file_get_contents($path);
        $json = json_decode($content);
        if (empty($json)) {
            return [
                'url' => isValidURL($content) ? $content : '',
                'title' => '',
                'order' => 0,
            ];
        }
        return object_to_array($json);
    }

    public static function setTXT($path, $url, $title, $order)
    {
        if (!isValidURL($url)) {
            $url = '';
        }
        $title = xss_esc($title);
        $array = [
            'url' => $url,
            'title' => $title,
            'order' => intval($order),
        ];
        return file_put_contents($path, json_encode($array));
    }

    public static function getAdsFromVideosId($type, $videos_id = 0)
    {
        global $global;

        if (isBot()) {
            return ['adCode' => '', 'label' => '', 'paths' => []];
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
            return ['adCode' => '', 'label' => '', 'paths' => []];
        }
        $label = '';
        eval("\$label = \$ad->{$type}Label;");
        $label = "{$label} [$users_id] [{$type}]";

        $allowOption = self::getSettingsType($type) . 'AllowUserToModify';
        $array = empty($users_id) || !empty($ad->$allowOption) ? self::getAdsHTML($type, $users_id) : false;
        if (empty($array)) {
            eval("\$adCode = \$ad->{$type}->value;");
            $array = ['paths' => []];
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
        // (a split slot follows the banner it divides)
        $settingsType = self::getSettingsType($type);
        eval("\$showOnVideoPlayerPage = \$ad->{$settingsType}ShowOnVideoPlayerPage;");
        if (!$showOnVideoPlayerPage && isVideo()) {
            $global['lastAdsCodeReason'] = 'Ads are configured not to show on the video player page for this ad type. '
                . '<br>To enable ads here, go to the Ads plugin settings and set the parameter '
                . "{$settingsType}ShowOnVideoPlayerPage to true if you want to display ads on the video page.";
            return false;
        }
        self::debug(__LINE__);

        // Desktop only: the banner is shown as its left and right slots
        if (self::isSplitActive($type)) {
            return self::formatPlacement(self::getSplitAdsCode($type), $type);
        }

        // Adjust ad type for mobile if applicable
        if (self::isAdsMobile()) {
            $type = self::getMobileType($type);
        }

        // Retrieve ads based on user ID or video ID
        $users_id = getUsers_idOwnerFromRequest();
        $adC = self::getAdsFromUsersId($type, $users_id);
        if (_empty($adC['adCode'])) {
            self::debug(__LINE__);
            $adC = self::getAdsHTML($type);
            if (!empty($adC) && !_empty($adC['html'])) {
                $adCode = self::giveGoogleATimeout($adC['html']);
                $global['lastAdsCodeReason'] = _empty($adCode) ? 'Ads are hidden by the viewer visibility rules.' : 'Site images successfully generated.';
                return self::formatPlacement($adCode, $type);
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
        $global['lastAdsCodeReason'] = _empty($adCode) ? 'Ads are hidden by the viewer visibility rules.' : 'Ad code successfully generated.';
        self::debug(__LINE__);

        return self::formatPlacement($adCode, $type);
    }

    /**
     * One clearly identified placement per banner; split slots share their parent's caption.
     * Empty/hidden ads keep their original return value, without reserving a blank area.
     */
    private static function formatPlacement($adCode, $type)
    {
        if (_empty($adCode) || self::getSettingsType($type) !== $type) {
            return $adCode;
        }
        $shape = 'banner';
        if (preg_match('/mobile/i', $type) || in_array($type, ['sideRectangle', 'leaderBoardBigVideo'], true)) {
            $shape = 'rectangle';
        } elseif (self::isSplitActive($type)) {
            $shape = 'split';
        }
        return '<div class="ADsPlacement ADsPlacement-' . $shape . '">'
            . '<div class="ADsPlacementLabel text-muted">' . __('Advertisement') . '</div>'
            . '<div class="ADsPlacementContent">' . $adCode . '</div></div>';
    }

    /**
     * A desktop banner divided into slots side by side (leaderBoardTop 728x90 -> two 364x90).
     * Each slot runs the normal getAdsCode() flow, so the owner's custom ads, the video page setting
     * and the paid-user rule apply as for any other position. Every slot keeps its side even when the
     * other one is empty; nothing is returned when no slot has an ad.
     */
    private static function getSplitAdsCode($type)
    {
        global $global;
        $isDebug = self::isAdsDebug();
        $hasAd = false;
        $reasons = [];
        $html = '';
        foreach (self::getSplitPositions($type) as $side => $slotType) {
            $slotCode = self::getAdsCode($slotType);
            $reasons[] = "{$slotType}: {$global['lastAdsCodeReason']}";
            if (_empty($slotCode)) {
                $slotCode = '';
            } else {
                $hasAd = true;
            }
            if ($isDebug) {
                $slotCode = getAdsDebugTag($slotCode, $slotType);
            }
            $html .= "<div class=\"ADsSplitSlot ADsSplitSlot-{$side}\" data-ads-type=\"{$slotType}\">{$slotCode}</div>";
        }
        $global['lastAdsCodeType'] = $type;
        $global['lastAdsCodeReason'] = 'Split into ' . implode(' and ', self::getSplitPositions($type)) . ' on desktop.<br>' . implode('<br>', $reasons);
        if (!$hasAd && !$isDebug) {
            return false;
        }
        return "<div class=\"ADsSplitLeaderBoard\" data-ads-type=\"{$type}\">{$html}</div>";
    }

    public static function getAdsCodeReason($type)
    {
        $reasons = [];
        if (self::isSplitActive($type)) {
            $reasons[] = 'Split is on: desktop shows ' . implode(' and ', self::getSplitPositions($type)) . ' instead of ' . $type;
        }

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
            if (self::isAdsMobile()) {
                $type = self::getMobileType($type);
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
        foreach (ADs::getAllAdsPositions() as $key => $value) {
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

        $style = 'max-width: ' . intval($size['width']) . 'px;';
        if ($size['isSquare']) {
            $width = $size['width'];
            $height = $size['height'];
            //Removed because it bugged in the mobile top
            //$style = "width: {$width}px; height: {$height}px;";
        }

        $obj = AVideoPlugin::getDataObject('ADs');
        $interval = $obj->bannerIntervalInSeconds * 1000;

        $html = "<div id=\"{$id}\" class=\"carousel slide ADsImage\" data-ride=\"carousel{$id}\" style=\"{$style}\" data-interval=\"{$interval}\">"
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
        return ['html' => $html, 'paths' => $paths];
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
            if (typeof $.fn.carousel === 'function') {
                $('.carousel').carousel({
                  interval: {$interval}
                });
            }
        });</script>";
        return $js . self::getAdsHighlightFooterCode();
    }

    /**
     * "View on site" from the editor: ?AdsDebug=1&AdsHighlight=<type> scrolls to that position.
     */
    private static function getAdsHighlightFooterCode()
    {
        if (empty($_REQUEST['AdsHighlight']) || !self::isAdsDebug()) {
            return '';
        }
        $type = $_REQUEST['AdsHighlight'];
        if (!is_string($type) || !self::isValidAdsType($type)) {
            return '';
        }
        $info = self::getAdsPositionsInfo();
        $targets = [$type];
        $parent = self::getSettingsType($type);
        if ($parent !== $type) {
            // a split slot is only on the page while the split is on
            $targets[] = $parent;
        }
        if (!empty($info[$type]['desktop'])) {
            // a mobile position opened on a desktop browser
            $targets[] = $info[$type]['desktop'];
        }
        if (!empty($info[$type]['mobile'])) {
            $targets[] = $info[$type]['mobile'];
        }
        $options = [
            'targets' => array_values(array_unique($targets)),
            'name' => $info[$type]['name'],
            'notFound' => __('This ad position is not displayed on this page.'),
            'otherVersion' => __('Showing where this position is on this page. The exact version depends on the device and on the split setting.'),
        ];
        return '<script src="' . getURL('plugin/ADs/adsHighlight.js') . '"></script>'
            . '<script>$(function(){ADsHighlightPosition(' . json_encode($options) . ');});</script>';
    }

    /**
     * Readable name, device and the pages where each position is rendered, as found in the views
     * that call getAds*() (objects/functions.php). Used by the editor and by "View on site".
     *
     * page: where "View on site" opens it (home, video or channel); map: the editor map pages.
     */
    public static function getAdsPositionsInfo()
    {
        $desktop = [
            'leaderBoardTop' => [
                'name' => __('Top banner'),
                'page' => 'video',
                'map' => ['video', 'home', 'channel'],
                'mobile' => 'leaderBoardTopMobile',
                'pages' => [
                    __('Video and live pages: above the player'),
                    __('Playlist pages: above the player when the top-banner option is enabled'),
                    __('Home page (Gallery): top of the page, not on category pages'),
                    __('Home page (Flix): on the featured video, or at the top when the featured video is off'),
                    __('Live embed player: above the video'),
                    __('Channel page (Flix): in the featured video area'),
                ],
            ],
            'leaderBoardTop2' => [
                'name' => __('Second banner'),
                'page' => 'video',
                'map' => ['video', 'home'],
                'mobile' => 'leaderBoardTopMobile2',
                'pages' => [
                    __('Video and live pages: below the player'),
                    __('Home page (Gallery): below the featured video'),
                    __('Home page (Flix): above the category rows'),
                    __('Phones and tablets use the second mobile banner'),
                ],
            ],
            'leaderBoardBigVideo' => [
                'name' => __('Featured video banner'),
                'page' => 'home',
                'map' => ['home', 'channel'],
                'mobile' => 'leaderBoardBigVideoMobile',
                'pages' => [
                    __('Home page: beside the featured video'),
                    __('Channel page: beside the featured video, when the channel shows one'),
                ],
            ],
            'channelLeaderBoardTop' => [
                'name' => __('Channel top banner'),
                'page' => 'channel',
                'map' => ['channel'],
                'mobile' => 'channelLeaderBoardTopMobile',
                'pages' => [
                    __('Channel page: top of the page'),
                ],
            ],
            'leaderBoardMiddle' => [
                'name' => __('Between video sections'),
                'page' => 'home',
                'map' => ['home', 'channel'],
                'mobile' => 'leaderBoardMiddleMobile',
                'pages' => [
                    __('Home page (Gallery): after each video section'),
                    __('Channel page: after each video section'),
                ],
            ],
            'sideRectangle' => [
                'name' => __('Sidebar rectangle'),
                'page' => 'video',
                'map' => ['video'],
                'pages' => [
                    __('Video and live pages: right column, above the related videos'),
                    __('Playlist pages: above the playlist in the sidebar'),
                    __('Phones and tablets use this same 300 x 250 position'),
                ],
            ],
        ];
        $info = [];
        foreach ($desktop as $type => $value) {
            $value['device'] = 'desktop';
            $info[$type] = $value;
            if (empty($value['mobile'])) {
                continue;
            }
            $info[$value['mobile']] = [
                'name' => $value['name'],
                'page' => $value['page'],
                'map' => $value['map'],
                'device' => 'mobile',
                'desktop' => $type,
                'pages' => [__('Phones and tablets, in the same places as the desktop version')],
            ];
        }
        foreach (self::AdsSplitPositions as $position) {
            list($type, , $parent, $side) = $position;
            $info[$type] = $info[$parent];
            unset($info[$type]['mobile']);
            $info[$type]['name'] = $info[$parent]['name'] . ', ' . ($side === 'left' ? __('Left half') : __('Right half'));
            $info[$type]['pages'] = [__('Same pages as the original banner, while its split is on')];
        }
        return $info;
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
