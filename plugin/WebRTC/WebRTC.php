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
        $obj->autoStartServerIfIsInactive = true;

        return $obj;
    }

    function executeEveryMinute()
    {
        self::checkAndUpdate();
        if (empty($obj->autoStartServerIfIsInactive)) {
            self::startIfIsInactive();
        }
    }

    static function startIfIsInactive()
    {
        if (!self::checkIfIsActive()) {
            _error_log('WebRTC server is inactive');
            self::startServer();
        }
    }

    static function checkIfIsActive()
    {
        $json = self::getJson();
        if (!empty($json)) {
            return ($json->phpTimestamp > strtotime('-2 min')) ? $json->phpTimestamp : false;
        }
        return false;
    }

    public function getPluginMenu()
    {
        global $global;
        $btn = '<button onclick="avideoModalIframe(webSiteRootURL+\'plugin/WebRTC/status.php\')" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa-solid fa-list-check"></i> Server Status</button>';
        return $btn;
    }

    static function getJson()
    {
        global $global;
        $file = self::getWebRTC2RTMPJsonFile();
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
        $file = self::getWebRTC2RTMPLogFile();
        if (file_exists($file)) {
            return file_get_contents($file);
        }
        return false;
    }

    static function getWebRTC2RTMPAssetVersionFile()
    {
        global $global;
        return "{$global['systemRootPath']}plugin/WebRTC/assets/versionInfo.json";
    }

    static function getWebRTC2RTMPAssetFile()
    {
        global $global;
        return "{$global['systemRootPath']}plugin/WebRTC/assets/WebRTC2RTMP";
    }

    static function getWebRTC2RTMPJsonFile()
    {
        global $global;
        return "{$global['systemRootPath']}plugin/WebRTC/WebRTC2RTMP.json";
    }

    static function getWebRTC2RTMPFile()
    {
        global $global;
        return "{$global['systemRootPath']}plugin/WebRTC/WebRTC2RTMP";
    }

    static function getWebRTC2RTMPLogFile()
    {
        global $global;
        return "{$global['systemRootPath']}videos/WebRTC2RTMP.log";
    }

    static function updateFileIfNeed()
    {
        $json = self::getJson();
        if (!empty($json)) {
            return ($json->phpTimestamp > strtotime('-2 min')) ? $json->phpTimestamp : false;
        }
        return false;
    }

    public static function checkAndUpdate() {
        // Define file paths
        $availableFilePath = self::getWebRTC2RTMPAssetVersionFile();
        $sourceExecutablePath = self::getWebRTC2RTMPAssetFile();
        $executablePath = self::getWebRTC2RTMPFile();
        $currentFilePath = self::getWebRTC2RTMPJsonFile();

        try {
            // Read the JSON files
            $currentData = readJsonFile($currentFilePath);
            $availableData = readJsonFile($availableFilePath);

            // Skip if any of the JSON files do not exist
            if (empty($currentData) || empty($availableData)) {
                _error_log("WebRTC::checkAndUpdate: Required JSON file(s) missing. Skipping update.");
                return false;
            }

            // Compare versions
            if ($currentData['version'] != $availableData['version']) {
                _error_log("WebRTC::checkAndUpdate: A new version is available Current={$currentData['version']} available={$availableData['version']}. Updating...");

                // Stop the current server
                _error_log("WebRTC::checkAndUpdate: Stopping current server...");
                exec("pkill WebRTC2RTMP", $output, $status);

                if ($status !== 0) {
                    _error_log("WebRTC::checkAndUpdate: Warning: Could not stop the server or it was not running.");
                }
                
                self::stopServer();

                // Remove old executable
                if (file_exists($executablePath)) {
                    _error_log("WebRTC::checkAndUpdate: Removing old executable...");
                    unlink($executablePath);
                }

                // Copy new executable
                _error_log("WebRTC::checkAndUpdate: Copying new executable...");
                copy($sourceExecutablePath, $executablePath);

                // Make new executable runnable
                _error_log("WebRTC::checkAndUpdate: Making new executable runnable...");
                chmod($executablePath, 0755);

                _error_log("WebRTC::checkAndUpdate: Update completed successfully!");
                return true; // Indicates that an update was performed
            } else {
                _error_log("WebRTC::checkAndUpdate: You are already running the latest version.");
                return false; // No update needed
            }
        } catch (Exception $e) {
            _error_log("WebRTC::checkAndUpdate: Error: " . $e->getMessage());
            return false; // Indicates failure or no update performed
        }
    }

    static function startServer()
    {
        _error_log('Starting WebRTC Server');
        global $global;
        $obj = AVideoPlugin::getDataObject('WebRTC');

        $file = self::getWebRTC2RTMPFile();
        $fileAsset = self::getWebRTC2RTMPAssetFile();
        $log = self::getWebRTC2RTMPLogFile();

        if(!file_exists($file)){
            copy($fileAsset, $file);
        }

        // Check if the file has executable permissions
        if (!is_executable($file)) {
            // Attempt to give executable permissions
            chmod($file, 0755); // 0755 grants read, write, and execute for the owner, and read and execute for others
        }

        // Try to execute the command
        if (is_executable($file)) {
            if(isLocalPortOpen($obj->port)){
                error_log("Port $obj->port is already open");
                return false;
            }else{
                error_log("Port $obj->port is not open, start the server");
                $command = "{$file} --port={$obj->port} > $log ";
                return execAsync($command);
            }
        } else {
            error_log("Unable to make {$file} executable.");
            return false;
        }
    }

    static function stopServer()
    {
        _error_log('Starting WebRTC Server');
        global $global;
        $obj = AVideoPlugin::getDataObject('WebRTC');
        return killProcessRuningOnPort($obj->port);
    }
}
