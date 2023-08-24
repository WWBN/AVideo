<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class ADs extends PluginAbstract
{
    public static $AdsPositions = [
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
        return array(
            'leaderBoardBigVideo',
            'leaderBoardBigVideoLabel',
            'leaderBoardTop',
            'leaderBoardTopLabel',
            'leaderBoardTop2',
            'leaderBoardTop2Label',
            'channelLeaderBoardTop',
            'channelLeaderBoardTopLabel',
            'leaderBoardMiddle',
            'leaderBoardMiddleLabel',
            'sideRectangle',
            'sideRectangleLabel',
            'leaderBoardBigVideoMobile',
            'leaderBoardBigVideoMobileLabel',
            'leaderBoardTopMobile',
            'leaderBoardTopMobileLabel',
            'leaderBoardTopMobile2',
            'leaderBoardTopMobile2Label',
            'channelLeaderBoardTopMobile',
            'channelLeaderBoardTopMobileLabel',
            'leaderBoardMiddleMobile',
            'leaderBoardMiddleMobileLabel',
            'tags3rdParty',
        );
    }

    public function getEmptyDataObject()
    {
        global $global, $config;
        $obj = new stdClass();

        $adsense = $config->getAdsense();

        foreach (self::$AdsPositions as $value) {
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
            eval("\$obj->$value[0]AllowUserToModify = true;");
        }

        $obj->tags3rdParty = "<script> window.abkw = '{ChannelName},{Category}'; </script>";
        $obj->doNotShowAdsForPaidUsers = true;


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
        foreach (ADs::$AdsPositions as $key => $value) {
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
            $fileName = str_replace($paths['path'], '', $value);
            $fileName = str_replace('.png', '', $fileName);
            $fileName = str_replace('\\', '', $fileName);
            if (empty($fileName)) {
                continue;
            }
            $return[] = [
                'type' => $type,
                'fileName' => $fileName,
                'url' => file_get_contents("{$paths['path']}{$fileName}.txt"),
                'imageURL' => "{$paths['url']}{$fileName}.png",
                'imagePath' => $value
            ];
            $fileName = '';
        }

        return $return;
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
        
        if(!empty($videos_id)){
            $users_id = Video::getOwner($videos_id);
        }else if(!empty($global['isChannel'])){
            $users_id = $global['isChannel'];
        }else{
            $users_id = 0;
        }
        

        return self::getAdsFromUsersId($type, $users_id);
    }

    public static function getAdsFromUsersId($type, $users_id)
    {
        $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
        if(empty($ad->$type)){
            return ['adCode' => '', 'label' => '', 'paths' => array()];
        }
        $label = '';
        eval("\$label = \$ad->{$type}Label;");
        $label = "{$label} [$users_id] [{$type}]";

        $array = self::getAdsHTML($type, $users_id);
        if(empty($array)){
            eval("\$adCode = \$ad->{$type}->value;");
            $array=array('paths'=>array());
        }else{
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

    public static function getAdsCode($type)
    {
        global $global;
        if (isBot()) {
            return false;
        }
        $videos_id = 0;
        if (empty($videos_id)) {
            $videos_id = getVideos_id();
        }
        $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
        $adCode = '';
        if (!empty($ad)) {
            if (isMobile()) {
                $type = $type . 'Mobile';
            }
            $users_id = getUsers_idOwnerFromRequest();
            //var_dump($users_id);exit;
            $adC =  self::getAdsFromUsersId($type, $users_id);
            $adCode = ADs::giveGoogleATimeout($adC['adCode']);
            $adCode = ADs::addLabel($adCode, $adC['label']);
        }
        return $adCode;
    }

    public static function getAdsCodeReason($type)
    {
        $reasons = array();
        if (isBot()) {
            $reasons[] = 'Is a bot';
        }
        $videos_id = 0;
        if (empty($videos_id)) {
            $videos_id = getVideos_id();
        }
        $reasons[] = 'videos_id='.$videos_id;
        $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
        if (!empty($ad)) {
            if (isMobile()) {
                $type = $type . 'Mobile';
            }
            if(!empty($live) && !empty($live['users_id'])){
                $adC = self::getAdsFromUsersId($type, $live['users_id']);
            }else{
                $adC = self::getAdsFromVideosId($type, $videos_id);
            }
            $reasons[] = 'type='.$type;
            $reasons[] = 'label='.$adC['label'];
            if(!empty($live) && !empty($live['users_id'])){
                $reasons[] = 'It was a live';
            }
            if(empty($adC['adCode'])){
                $reasons[] = 'adCode is empty';
            }
        }else{
            $reasons[] = 'ADs plugin disabled';
        }
        return $reasons;
    }

    public static function getSize($type)
    {
        $obj = AVideoPlugin::getObjectData("ADs");
        foreach (ADs::$AdsPositions as $key => $value) {
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
        $paths = self::getAds($type, $is_regular_user);

        if (empty($paths)) {
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


        $html = "<div id=\"{$id}\" class=\"carousel slide\" data-ride=\"carousel{$id}\" style=\"{$style}\">"
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
            if (isValidURL($value['url'])) {
                $html .= "<a href=\"{$value['url']}\" target=\"_blank\">";
                $html .= "<!-- getAdsHTML::isValidURL -->";
                $html .= "<img src=\"{$value['imageURL']}\" class=\"img img-responsive\" style=\"width:100%;\" title=\"{$fsize}\" >";
                $html .= "</a>";
            } else {
                $html .= "<!-- getAdsHTML -->";
                $html .= "<img src=\"{$value['imageURL']}\" class=\"img img-responsive\" style=\"width:100%;\"  title=\"{$fsize}\" >";
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
        return array('html' => $html, 'paths' => $paths);
    }

    public function getFooterCode()
    {
        global $global;
        $js = "<script>$(function(){
            $('.carousel').carousel({
              interval: 5000
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
