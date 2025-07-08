<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'objects/video.php';

class MobileYPT extends PluginAbstract
{

    public function getTags()
    {
        return [
            PluginTags::$FREE,
            PluginTags::$MOBILE,
        ];
    }

    public static function getVersion()
    {
        return 2;
    }

    public function getDescription()
    {
        $desc = "Your Media Companion: YPT Mobile App";
        $desc .= "<p>YPT Media is a dynamic mobile app for streaming high-quality videos, audios, managing playlists, supporting PPV, and live streaming. Enjoy an ad-free experience with our server hosting. <a href=\"https://github.com/WWBN/AVideo/wiki/YPT-Media-Mobile-app\">Learn more</a>.</p>";
        $desc .= $this->isReadyLabel(['API']);
        return $desc;
    }

    public function getName()
    {
        return "MobileYPT";
    }

    public function getUUID()
    {
        return "MobileYPT-184efe715c09";
    }

    public function getPluginVersion()
    {
        return "1.0";
    }

    public function getEmptyDataObject()
    {
        global $global;
        $obj = new stdClass();
        $obj->doNotAllowAnonimusAccess = false;
        $obj->doNotAllowUpload = false;
        $obj->hideCreateAccount = false;
        $obj->approvalMode = false;
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "This Software must be used for Good, never Evil. There is no tolerance for objectionable content or abusive users. It is expressly forbidden to use this app to build porn sites, violence, racism or anything else that affects human integrity or denigrates the image of anyone.\n"
            . "Any complaints, or through the application or any other electronic means will be analyzed and in case of any criteria established by the developer or local laws, are disrespected, we reserve the right to block and ban any site from our systems\n"
            . "The banned site will be prohibited from using any of our resources, including mobile applications, encoder, plugins, etc.";
        $obj->EULA = $o;

        $obj->enableLivePublisher = true;
        $obj->enableAudioPlayer = true;

        return $obj;
    }

    public function getStart()
    {
        $videos_id = getVideos_id();
        self::getCheckMP3($videos_id);
    }

    public function onNewVideo($videos_id)
    {
        self::getCheckMP3($videos_id);
    }

    static function getCheckMP3($videos_id)
    {
        if (!empty($videos_id)) {
            $obj = AVideoPlugin::getDataObject('MobileYPT');
            if ($obj->enableAudioPlayer) {
                return convertVideoToMP3FileIfNotExists($videos_id);
            }
        }
        return false;
    }

    public function getHTMLMenuLeft()
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/MobileYPT/HTMLMenuLeft.php';
    }

    public function getHeadCode()
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/MobileYPT/head.php';
    }

    public function getFooterCode()
    {
        include_once __DIR__ . '/open-in-app-button.php';
    }
}
