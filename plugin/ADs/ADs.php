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
        //$help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/AD_Overlay-Plugin' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        $help = "";
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
            $o->value = empty($adsense) ? "<center><img src='{$global['webSiteRootURL']}plugin/ADs/sample{$size}.jpg'></center>" : $adsense;
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
        $head = "<script> var adsbygoogleTimeout; </script>";
        if (!empty($_GET['abkw'])) {
            $abkw = preg_replace('/[^a-zA-Z0-9_ ,-]/', '', $_GET['abkw']);
            $head .= "<script> window.abkw = '{$abkw}'; </script>";
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
        if (!empty($_GET['catName'])) {
            if (!empty($obj->tags3rdParty)) {
                $v = Category::getCategoryByName($_GET['catName']);
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
        $videos_id = getVideos_id();
        $showAds = AVideoPlugin::showAds($videos_id);
        if (!$showAds) {
            return "";
        }
        if (preg_match("/adsbygoogle/i", $adCode)) {
            $adCode = str_replace("(adsbygoogle = window.adsbygoogle || []).push({});", "clearTimeout(adsbygoogleTimeout); adsbygoogleTimeout = setTimeout(function () {(adsbygoogle = window.adsbygoogle || []).push({});},5000);", trim($adCode));
            $adCode = "<div style='min-width:250px;min-height:90px;'>{$adCode}</div>";
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

    public static function getAdsPath($type)
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
        $videosURL = getCDN()."videos/ADs/{$type}/";
        make_path($videosDir);

        return ['path' => $videosDir, 'url' => $videosURL];
    }

    public static function getNewAdsPath($type)
    {
        $paths = self::getAdsPath($type);

        if (empty($paths)) {
            return false;
        }

        $fileName = uniqid();

        return ['fileName' => $fileName, 'path' => $paths['path'] . $fileName . '.png', 'url' => $paths['url'] . $fileName . '.png', 'txt' => $paths['path'] . $fileName . '.txt'];
    }

    public static function getAds($type)
    {
        global $global;
        $paths = self::getAdsPath($type);

        if (empty($paths)) {
            return false;
        }

        $videosDir = getVideosDir() . 'ADs/' . $type . '/';
        $videosURL = getCDN()."videos/ADs/{$type}/";


        $files = _glob($paths['path'], '/.png$/');
        $return = [];
        foreach ($files as $value) {
            $fileName = str_replace($videosDir, '', $value);
            $fileName = str_replace('.png', '', $fileName);
            if(empty($fileName)){
                continue;
            }
            $return[] = ['type' => $type, 'fileName' => $fileName, 'url' => file_get_contents($videosDir . "{$fileName}.txt"), 'imageURL' => $videosURL . "{$fileName}.png", 'imagePath' => $value];
            $fileName = '';
        }

        return $return;
    }

    public static function getSize($type) {
        $obj = AVideoPlugin::getObjectData("ADs");
        foreach (ADs::$AdsPositions as $key => $value) {
            if ($type == $value[0]) {
                eval("\$width = \$obj->$value[0]Width;");
                eval("\$height = \$obj->$value[0]Height;");
                return ['width' => $width, 'height' => $height, 'isMobile' => preg_match('/mobile/i', $value[0]), 'isSquare' => $value[1]];
            }
        }
        return ['width' => null, 'height' => null];
    }
    
    public static function getLabel($type) {
        $obj = AVideoPlugin::getObjectData("ADs");
        eval("\$label = \$obj->{$type}Label;");
        if(empty($label)){
            return $type;
        }else{
            return $label;
        }
    }

    public static function getAdsHTML($type)
    {
        global $global;
        $paths = self::getAds($type);

        if (empty($paths)) {
            return false;
        }

        $id = 'myCarousel' . $type . uniqid();

        $size = self::getSize($type);

        $style = '';
        if ($size['isSquare']) {
            $width = $size['width'];
            $height = $size['height'];
            $style = "width: {$width}px; height: {$height}px;";
        }


        $html = "<center><div id=\"{$id}\" class=\"carousel slide\" data-ride=\"carousel{$id}\" style=\"{$style}\">"
                . "<div class=\"carousel-inner\">";

        $active = 'active';
        $validPaths = 0;
        foreach ($paths as $value) {
            $fsize= filesize($value['imagePath']);
            if ($fsize<5000) {
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

        if (count($validPaths) > 1) {
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
        $html .= "</div></div></center>";
        return $html;
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
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = self::getAdsHTML($type);
        $p = new ADs();
        return $p->updateParameter($type, $o);
    }
}
