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
        return "";
    }

    public function getName()
    {
        return "WebRTC";
    }

    public function getUUID()
    {
        return "webrtc-e578-4b91-96bb-4baaae5c0884";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();

        $obj->port = 3000;

        return $obj;
    }

    function executeEveryMinute() {
        self::startIfIsInactive();
    }

    static function startIfIsInactive(){
        if(!self::checkIfIsActive()){
            self::startServer();
        }
    }

    static function checkIfIsActive(){
        global $global;
        $file = "{$global['systemRootPath']}plugin/WebRTC/WebRTC2RTMP.json";
        if(file_exists($file)){
            $content = file_get_contents($file);
            if(!empty($content)){
                $json = json_decode($content);
                if(!empty($json)){
                    return $json->phpTimestamp > strtotime('-2 min');                    
                }   
            }
        }
        return false;
    }

    
    static function startServer(){
        _error_log('Starting WebRTC Server');
        global $global;
        $obj = AVideoPlugin::getDataObject('WebRTC');
        $file = "{$global['systemRootPath']}plugin/WebRTC/WebRTC2RTMP";
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

    static function killProcessOnPort() {
        $obj = AVideoPlugin::getDataObject('WebRTC');
        $port = intval($obj->port);
        if (!empty($port)) {
            echo 'Searching for port: ' . $port . PHP_EOL;
            //$command = 'netstat -ano | findstr ' . $port;
            //exec($command, $output, $retval);
            $pid = getPIDUsingPort($port);
            if (!empty($pid)) {
                echo 'Server is already runing on port '.$port.' Killing, PID ' . $pid . PHP_EOL;
                killProcess($pid);
            } else {
                echo 'No Need to kill, port NOT found' . PHP_EOL;
            }
        }
    }

}
