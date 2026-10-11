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
        return "1.1";
    }

    public function getEmptyDataObject()
    {
        global $global;
        $obj = new stdClass();

        $obj->port = 3000;
        $obj->autoStartServerIfIsInactive = true;
        $obj->quickGoLiveEnabled = true;
        self::addDataObjectHelper('quickGoLiveEnabled', 'Enable Quick Go Live', 'Show a one-click "Go Live" button in the top menu bar');

        $obj->quickGoLiveButtonTitle = 'Go Live';
        self::addDataObjectHelper('quickGoLiveButtonTitle', 'Quick Go Live Button Title');

        return $obj;
    }

    function executeEveryMinute()
    {
        self::checkAndUpdate();
        $obj = AVideoPlugin::getDataObject('WebRTC');
        if (!empty($obj->autoStartServerIfIsInactive)) {
            self::restartIfCertificateIsOutdated();
            self::startIfIsInactive();
        }
    }

    /**
     * WebRTC2RTMP reads the SSL certificate only when it starts. After certbot renews it the server
     * keeps serving the old one until it expires, and browsers then refuse to connect.
     * Restart it when the certificate on disk is newer than the one it serves, but never while it
     * forwards a live (its ffmpeg children) and at most once per hour.
     */
    static function restartIfCertificateIsOutdated()
    {
        $obj = AVideoPlugin::getDataObject('WebRTC');
        $json = self::getJson();
        if (empty($json) || empty($json->domain)) {
            return false;
        }
        $diskValidTo = self::getCertificateFileValidTo($json->domain);
        if (empty($diskValidTo)) {
            return false;
        }
        $servedValidTo = self::getServedCertificateValidTo($obj->port, $json->domain);
        if (empty($servedValidTo) || $diskValidTo <= $servedValidTo) {
            return false;
        }
        $pid = getPIDUsingPort($obj->port);
        if (empty($pid)) {
            return false;
        }
        $served = date('Y-m-d H:i:s', $servedValidTo);
        $disk = date('Y-m-d H:i:s', $diskValidTo);
        if (self::hasChildProcesses($pid) !== false) {
            _error_log("WebRTC: SSL certificate was renewed (served={$served} disk={$disk}), restart postponed, PID {$pid} may be forwarding a live");
            return false;
        }
        if (!isProcessOlderThan($pid, 3600)) {
            return false;
        }
        _error_log("WebRTC: SSL certificate was renewed (served={$served} disk={$disk}), restarting PID {$pid}");
        self::stopServer();
        for ($i = 0; $i < 10 && isLocalPortOpen($obj->port); $i++) {
            sleep(1);
        }
        return self::startServer();
    }

    /**
     * Expiration of the certificate WebRTC2RTMP loads: /etc/letsencrypt/live/{domain}/fullchain.pem
     */
    static function getCertificateFileValidTo($domain)
    {
        if (!preg_match('/^[a-z0-9.-]+$/i', $domain)) {
            return false;
        }
        $file = "/etc/letsencrypt/live/{$domain}/fullchain.pem";
        if (!is_readable($file)) {
            return false;
        }
        $cert = openssl_x509_parse(file_get_contents($file));
        return empty($cert['validTo_time_t']) ? false : intval($cert['validTo_time_t']);
    }

    /**
     * Expiration of the certificate the local server is serving right now
     */
    static function getServedCertificateValidTo($port, $domain)
    {
        // Only reads the certificate of our own local server; it must work even when that one has expired
        $context = stream_context_create(array('ssl' => array(
            'capture_peer_cert' => true,
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
            'peer_name' => $domain,
        )));
        $fp = @stream_socket_client('ssl://127.0.0.1:' . intval($port), $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);
        if (!$fp) {
            return false;
        }
        $params = stream_context_get_params($fp);
        fclose($fp);
        if (empty($params['options']['ssl']['peer_certificate'])) {
            return false;
        }
        $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
        return empty($cert['validTo_time_t']) ? false : intval($cert['validTo_time_t']);
    }

    /**
     * @return bool|null null when it cannot be checked
     */
    static function hasChildProcesses($pid)
    {
        if (isWindowsServer()) {
            return null;
        }
        exec('pgrep -P ' . intval($pid), $output, $retval);
        if ($retval === 1) {
            return false;
        }
        return ($retval === 0) ? true : null;
    }

    /**
     * PIDs of WebRTC2RTMP processes running this installation's executable, listening or not
     */
    static function getServerPids()
    {
        if (isWindowsServer()) {
            return array();
        }
        $file = self::getWebRTC2RTMPFile();
        exec('pgrep -x WebRTC2RTMP', $output, $retval);
        $pids = array();
        foreach ($output as $pid) {
            $cmd = getCommandByPid(intval($pid));
            if (!empty($cmd) && reset($cmd) === $file) {
                $pids[] = intval($pid);
            }
        }
        return $pids;
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

    public function getHTMLMenuRight()
    {
        include __DIR__ . '/../../plugin/WebRTC/view/menuRight.php';
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

    static function getWebRTC2RTMPLockFile()
    {
        global $global;
        return "{$global['systemRootPath']}videos/WebRTC2RTMP.lock";
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
            // The cron, quickGoLive and the tools may call this at the same time: check and launch under one lock
            $lockFile = self::getWebRTC2RTMPLockFile();
            $lock = @fopen($lockFile, 'c');
            if ($lock) {
                @chmod($lockFile, 0666); // the cron (root) and the web server user share it
            } else {
                $lock = @fopen($lockFile, 'r');
            }
            if (empty($lock)) {
                _error_log("WebRTC: could not open the lock file {$lockFile}");
            } else if (!flock($lock, LOCK_EX | LOCK_NB)) {
                fclose($lock);
                error_log("WebRTC server is already being started by another process");
                return false;
            }
            try {
                return self::launchServer($obj->port, $file, $log);
            } finally {
                if (!empty($lock)) {
                    flock($lock, LOCK_UN);
                    fclose($lock);
                }
            }
        } else {
            error_log("Unable to make {$file} executable.");
            return false;
        }
    }

    private static function launchServer($port, $file, $log)
    {
        if (isLocalPortOpen($port)) {
            error_log("Port $port is already open");
            return false;
        }
        // A server that is still starting is not listening yet; one that never opened the port is stuck
        foreach (self::getServerPids() as $pid) {
            if (self::hasChildProcesses($pid) !== false || !isProcessOlderThan($pid, 120)) {
                error_log("WebRTC server PID {$pid} is already running but port {$port} is not open");
                return false;
            }
            _error_log("WebRTC server PID {$pid} did not open port {$port}, killing it");
            killProcess($pid);
        }
        $command = "{$file} --port={$port} > $log ";
        error_log("Port $port is not open, start the server with command: $command");
        $pid = execAsync($command);
        // Keep the lock until the new process exists, so a concurrent call finds it
        for ($i = 0; $i < 50 && empty(self::getServerPids()); $i++) {
            usleep(100000);
        }
        return $pid;
    }

    static function stopServer()
    {
        _error_log('Stopping WebRTC Server');
        global $global;
        $obj = AVideoPlugin::getDataObject('WebRTC');
        return killProcessRuningOnPort($obj->port);
    }
}
