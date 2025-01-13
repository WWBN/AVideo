<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class WebRTC extends PluginAbstract
{

    public function getTags()
    {
        return array(
            PluginTags::$LIVE,
        );
    }
    public function getDescription()
    {
        $txt = "Easily stream live videos from your camera or computer with just one click";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/WebRTC-plugin' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";

        return $txt . $help;
    }

    public function getName()
    {
        return "WebRTC";
    }

    public function getUUID()
    {
        return "webrtc-e578-4b91-96bb-4baaae5c0884";
    }

    public function getPluginVersion()
    {
        return "1.0";
    }

    public function getEmptyDataObject()
    {
        global $global;
        $obj = new stdClass();

        $obj->port = 3000;

        return $obj;
    }

    function executeEveryMinute()
    {
        self::startIfIsInactive();
    }

    static function startIfIsInactive()
    {
        if (!self::checkIfIsActive()) {
            self::startServer();
        }
    }

    static function checkIfIsActive()
    {
        $json = self::getJson();
        if(!empty($json)){
            return ($json->phpTimestamp > strtotime('-2 min')) ? $json->phpTimestamp : false;
        }
        return false;
    }

    public function getPluginMenu() {
        global $global;
        $btn = '<button onclick="avideoModalIframe(webSiteRootURL+\'plugin/WebRTC/status.php\')" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa-solid fa-list-check"></i> Server Status</button>';
        return $btn;
    }

    static function getJson()
    {
        global $global;
        $file = "{$global['systemRootPath']}plugin/WebRTC/WebRTC2RTMP.json";
        if (file_exists($file)) {
            $content = file_get_contents($file);
            if (!empty($content)) {
                $json = json_decode($content);
                if (!empty($json)) {
                    return $json;
                }
            }
        }
        return false;
    }

    static function getLog()
    {
        global $global;
        $file = "{$global['systemRootPath']}videos/WebRTC2RTMP.log";
        if (file_exists($file)) {
            return file_get_contents($file);
        }
        return false;
    }

    static function getWebRTC2RTMPFile(){
        global $global;
        return "{$global['systemRootPath']}plugin/WebRTC/WebRTC2RTMP";
    }

    static function startServer()
    {
        _error_log('Starting WebRTC Server');
        global $global;
        $obj = AVideoPlugin::getDataObject('WebRTC');
        $file = self::getWebRTC2RTMPFile();
        $log = "{$global['systemRootPath']}videos/WebRTC2RTMP.log";
        $command = "{$file} --port={$obj->port} > $log ";

        // Check if the file has executable permissions
        if (!is_executable($file)) {
            // Attempt to give executable permissions
            chmod($file, 0755); // 0755 grants read, write, and execute for the owner, and read and execute for others
        }

        // Try to execute the command
        if (is_executable($file)) {
            return execAsync($command);
        } else {
            error_log("Unable to make {$file} executable.");
            return false;
        }
    }
}
