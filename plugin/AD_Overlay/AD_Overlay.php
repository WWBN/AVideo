<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AD_Overlay/Objects/AD_Overlay_Code.php';

class AD_Overlay extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$MONETIZATION,
            PluginTags::$ADS,
            PluginTags::$FREE,
            PluginTags::$PLAYER,
        );
    }

    public function getDescription() {
        $txt = "Display simple overlays - similar to YouTube's \"Annotations\" feature in appearance - during video playback.";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/AD_Overlay-Plugin' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";

        return $txt . $help;
    }

    public function getName() {
        return "AD_Overlay";
    }

    public function getUUID() {
        return "ADO73225-3807-4167-ba81-0509dd280e06";
    }

    public function getPluginVersion() {
        return "2.1";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();

        $o = new stdClass();
        $o->type = "textarea";
        $o->value = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- AVideo Videos -->
<ins class="adsbygoogle"
     style="display:inline-block;width:468px;height:60px"
     data-ad-client="ca-pub-8404441263723333"
     data-ad-slot="6092946505"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
';
        $obj->adText = $o;

        $o = new stdClass();
        $o->type = "textarea";
        $o->value = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- AVideo Videos -->
<ins class="adsbygoogle"
     style="display:inline-block;width:468px;height:60px"
     data-ad-client="ca-pub-8404441263723333"
     data-ad-slot="6092946505"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
';
        $obj->mobileAdText = $o;

        //$obj->allowUsersToAddCustomText = false;
        //Where to display overlays, by default. Assuming the included stylesheet is used, the following values are supported:
        // "top-left", "top", "top-right", "right", "bottom-right", "bottom", "bottom-left", "left".
        $obj->align = "bottom-left";
        $obj->showBackground = true;
        //bottom aligned overlays will adjust positioning when the control bar minimizes.
        $obj->attachToControlBar = false;
        /*
          $obj->start = true;
          $obj->mid25Percent = false;
          $obj->mid50Percent = false;
          $obj->mid75Percent = false;
          $obj->end = false;

          $obj->durationInSeconds = 30;
         * 
         */
        $obj->debug = false;
        //$obj->adWidth = 0;
        //$obj->adHeight = 0;
        $obj->allowUserAds = true;
        $obj->AdminMustApproveUserAds = true;

        return $obj;
    }

    public function getHeadCode() {
        if (empty($_GET['videoName']) && empty($_GET['u']) && empty($_GET['link'])) {
            return false;
        }
        $videos_id = getVideos_id();
        $showAds = AVideoPlugin::showAds($videos_id);
        if (!$showAds) {
            return "";
        }
        $obj = $this->getDataObject();
        global $global;
        $style = "width: 100%;";
        if (!empty($obj->adWidth) && !empty($obj->adHeight)) {
            $style = "width: $obj->adWidth; height: width: $obj->adHeight;";
        }
        $css = '<link href="' .getCDN() . 'plugin/AD_Overlay/videojs-overlay/videojs-overlay.css" rel="stylesheet" type="text/css"/>';

        $css .= '<style>#adOverlay{min-width: 640px;}.video-js .vjs-overlay-background, .video-js .vjs-overlay-no-background {
    max-height: 50%;
    max-width: 100%;
    ' . $style . '
    margin-left:-5px;
    overflow: hidden;

}</style>';
        return $css;
    }

    public function getFooterCode() {

        global $global, $video;
        $videos_id = getVideos_id();
        $showAds = AVideoPlugin::showAds($videos_id);
        if (!$showAds) {
            return "";
        }
        if (basename($_SERVER["SCRIPT_FILENAME"]) === 'managerUsers.php') {
            include $global['systemRootPath'] . 'plugin/AD_Overlay/footer.php';
        }
        if (empty($_GET['videoName']) && empty($_GET['u']) && empty($_GET['link'])) {
            return false;
        }
        $obj = $this->getDataObject();

        if (isMobile()) {
            $adText = $obj->mobileAdText->value;
        } else {
            $adText = $obj->adText->value;
        }


        if ($obj->allowUserAds) {
            if (!empty($video['id'])) {
                $v = Video::getVideoLight($video['id']);
                $users_id = $video['users_id'];
            }
            if (!empty($_GET['c'])) {
                $u = new User(0, $_GET['u'], false);
                $users_id = $u->getBdId();
            }

            if (empty($users_id)) {
                return '<!-- AD_Overlay users_id not detected -->';
            }

            $code = $this->getAdsFromUserIfActive($users_id);
            if (!empty($code)) {
                $adText = $code;
            }
        }
        if (empty(trim($adText))) {
            return '<!-- AD_Overlay adText not detected -->';
        }

        $ad = AVideoPlugin::getObjectData('ADs');

        $js = '<div id="adOverlay" style="display:none;"><button class="pull-right btn" onclick="$(\'.vjs-overlay\').fadeOut();"><i class="fa fa-times"></i></button>'
                . '<center>' . ADs::giveGoogleATimeout($adText) . '</center>'
                . '</div>';

        $js .= '<script src="' .getCDN() . 'plugin/AD_Overlay/videojs-overlay/videojs-overlay.js" type="text/javascript"></script>';

        $onPlayerReady = "setTimeout(function(){
                        \$('#cbb').click(function() {
                            \$('.vjs-overlay').fadeOut();
                            $('#mainVideo .vjs-control-bar').removeClass('vjs-hidden');
                            $('#mainVideo .vjs-control-bar').addClass('vjs-fade-out');
                        });
                    },1000);
                    setTimeout(function(){
                        $('#mainVideo .vjs-control-bar').removeClass('vjs-hidden');
                        $('#mainVideo .vjs-control-bar').addClass('vjs-fade-out');
                    },3000);
                player.overlay({
        content: $('#adOverlay').html(),
        debug: true,
        showBackground:" . ($obj->showBackground ? "true" : "false") . ",
        attachToControlBar:" . ($obj->attachToControlBar ? "true" : "false") . ",
        overlays: [{
          start: 'play',
          end: 3600,
          align: '{$obj->align}'
        }]
      });";
        $js .= '<script>' . PlayerSkins::getStartPlayerJS($onPlayerReady) . '</script>';

        return $js;
    }

    public static function profileTabName($users_id) {
        global $global;
        if (!User::canUpload()) {
            return '';
        }
        include $global['systemRootPath'] . 'plugin/AD_Overlay/profileTabName.php';
    }

    public static function profileTabContent($users_id) {
        global $global;
        if (!User::canUpload()) {
            return '';
        }
        include $global['systemRootPath'] . 'plugin/AD_Overlay/profileTabContent.php';
    }

    public function getUsersManagerListButton() {
        $btn = "";
        $obj = $this->getDataObject();
        if (!empty($obj->allowUserAds)) {
            $btn = '<button type="button" class="btn btn-warning btn-light btn-sm btn-xs" onclick="adsUser(\' + row.id + \');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="Ad Code">Ad Code</button>';
        }
        return $btn;
    }

    private function getAdsFromUserIfActive($users_id) {
        $ad = new AD_Overlay_Code(0);
        $ad->loadFromUser($users_id);
        if (!empty($ad->getStatus()) && $ad->getStatus() == 'a') {
            return $ad->getCode();
        }
        return false;
    }

}
