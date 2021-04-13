<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Cache extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE
        );
    }

    public function getDescription() {
        global $global;
        $txt = "AVideo application accelerator to cache pages.<br>Your website has 10,000 visitors who are online, and your dynamic page has to send 10,000 times the same queries to database on every page load. With this plugin, your page only sends 1 query to your DB, and uses the cache to serve the 9,999 other visitors.";
        $txt .= "<br>To auto delete the old cache files you can use this crontab command <code>0 2 * * * php {$global['systemRootPath']}plugin/Cache/crontab.php</code> this will delete cache files that are 3 days old everyday at 2 AM";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/Cache-Plugin' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $txt . $help;
    }

    public function getName() {
        return "Cache";
    }

    public function getUUID() {
        return "10573225-3807-4167-ba81-0509dd280e06";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->enableCachePerUser = false;
        $obj->enableCacheForLoggedUsers = false;
        $obj->cacheTimeInSeconds = 600;
        $obj->cacheDir = $global['systemRootPath'] . 'videos/cache/';
        $obj->logPageLoadTime = false;
        $obj->stopBotsFromNonCachedPages = false;
        return $obj;
    }

    public function getCacheDir($ignoreFirstPage = true) {
        global $global;
        $obj = $this->getDataObject();
        if (!$ignoreFirstPage && $this->isFirstPage()) {
            $obj->cacheDir .= "firstPage".DIRECTORY_SEPARATOR;
        }
        if (User::isLogged()) {
            if(User::isAdmin()){
                $obj->cacheDir .= 'admin_'.md5("admin".$global['salt']).DIRECTORY_SEPARATOR;
            }else{
                $obj->cacheDir .= 'user_'.md5("user".$global['salt']).DIRECTORY_SEPARATOR;
            }
        }else{
            $obj->cacheDir .= 'notlogged_'.md5("notlogged".$global['salt']).DIRECTORY_SEPARATOR;
        }
        
        $obj->cacheDir = fixPath($obj->cacheDir, true);
        if (!file_exists($obj->cacheDir)) {
            $obj->cacheDir = $global['systemRootPath'] . 'videos'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;
            $this->setDataObject($obj);
            if (!file_exists($obj->cacheDir)) {
                mkdir($obj->cacheDir, 0777, true);
            }
        }
        
        
        return $obj->cacheDir;
    }

    private function getFileName() {
        if (empty($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = "";
        }
        $obj = $this->getDataObject();
        $session_id = "";
        if (!empty($obj->enableCachePerUser)) {
            $session_id = session_id();
        }
        $compl = "";
        if (!empty($_SERVER['HTTP_USER_AGENT']) && get_browser_name($_SERVER['HTTP_USER_AGENT']) === 'Safari') {
            $compl .= "safari_";
        }

        $dir = "";
        $plugin = AVideoPlugin::loadPluginIfEnabled('User_Location');
        if (!empty($plugin)) {
            $location = User_Location::getThisUserLocation();
            if (!empty($location['country_code'])) {
                $dir = $location['country_code'] . "/";
            }
        }

        return $dir . User::getId() . "_{$compl}" . md5(@$_SESSION['channelName'] . $_SERVER['REQUEST_URI'] . $_SERVER['HTTP_HOST']) . "_" . $session_id . "_" . (!empty($_SERVER['HTTPS']) ? 'a' : '') . (@$_SESSION['language']) . '.cache';
    }

    private function isFirstPage() {
        return isFirstPage();
    }

    public function getStart() {
        global $global;
        // ignore cache if it is command line

        $obj = $this->getDataObject();
        if ($obj->logPageLoadTime) {
            $this->start();
        }

        if (isCommandLineInterface()) {
            return true;
        }

        $whitelistedFiles = array('user.php', 'status.php', 'canWatchVideo.json.php', '/login', '/status');
        $blacklistedFiles = array('videosAndroid.json.php');
        $baseName = basename($_SERVER["SCRIPT_FILENAME"]);
        if (isVideo() || isLive() || isLiveLink() || in_array($baseName, $whitelistedFiles) || in_array($_SERVER['REQUEST_URI'], $whitelistedFiles)) {
            return true;
        }

        $isBot = isBot();
        if ($this->isBlacklisted() || $this->isFirstPage() || !class_exists('User') || !User::isLogged() || !empty($obj->enableCacheForLoggedUsers)) {
            $cacheName = 'firstPage'.DIRECTORY_SEPARATOR.$this->getFileName();
            $lifetime = $obj->cacheTimeInSeconds;
            $firstPageCache = ObjectYPT::getCache($cacheName, $lifetime, true);
            if(!empty($firstPageCache) && strtolower($firstPageCache) != 'false'){
                if ($isBot && $_SERVER['REQUEST_URI'] !== '/login') {
                    //_error_log("Bot Detected, showing the cache ({$_SERVER['REQUEST_URI']}) FROM: {$_SERVER['REMOTE_ADDR']} Browser: {$_SERVER['HTTP_USER_AGENT']}");
                }
                //$c = @local_get_contents($cachefile);
                if (preg_match("/\.json\.?/", $baseName)) {
                    header('Content-Type: application/json');
                }
                echo $firstPageCache.PHP_EOL.'<!-- Cached Page Generated in '.getScriptRunMicrotimeInSeconds().' Seconds -->';
                if ($obj->logPageLoadTime) {
                    $this->end("Cache");
                }
                exit;
            }
        }

        if ($isBot && !self::isREQUEST_URIWhitelisted() && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
            if (empty($_SERVER['HTTP_USER_AGENT'])) {
                $_SERVER['HTTP_USER_AGENT'] = "";
            }
            _error_log("Bot Detected, NOT showing the cache ({$_SERVER['REQUEST_URI']}) FROM: {$_SERVER['REMOTE_ADDR']} Browser: {$_SERVER['HTTP_USER_AGENT']}");
            if ($obj->stopBotsFromNonCachedPages) {
                _error_log("Bot stopped");
                exit;
            }
        }
        //ob_start('sanitize_output');
        ob_start();
    }
    
    public function getEnd() {
        global $global;
        $obj = $this->getDataObject();
        echo PHP_EOL.'<!--        Page Generated in '.getScriptRunMicrotimeInSeconds().' Seconds -->';
        $c = ob_get_contents();
        ob_start();
        if (!headers_sent()) {
            header_remove('Set-Cookie');
        }
        /*
          if (!file_exists($this->getCacheDir())) {
          mkdir($this->getCacheDir(), 0777, true);
          }
         * 
         */

        if ($this->isBlacklisted() || $this->isFirstPage() || !class_exists('User') || !User::isLogged() || !empty($obj->enableCacheForLoggedUsers)) {
            $cacheName = 'firstPage'.DIRECTORY_SEPARATOR.$this->getFileName();
            ObjectYPT::setCache($cacheName, $c);
        }
        if ($obj->logPageLoadTime) {
            $this->end();
        }
    }

    private function isREQUEST_URIWhitelisted() {
        $cacheBotWhitelist = array(
            'aVideoEncoder',
            'plugin/Live/on_',
            'plugin/YPTStorage',
            '/login',
            'restreamer.json.php',
            'plugin/API',
            '/info?version=',
            'Meet',
            '/roku.json',
            'mrss',
            'plugin/Live/verifyToken.json.php');
        foreach ($cacheBotWhitelist as $value) {
            if (strpos($_SERVER['REQUEST_URI'], $value) !== false) {
                _error_log("Cache::isREQUEST_URIWhitelisted: ($value) is whitelisted");
                return true;
            }
        }
        return false;
    }

    private function isBlacklisted() {
        $blacklistedFiles = array('videosAndroid.json.php');
        $baseName = basename($_SERVER["SCRIPT_FILENAME"]);
        return in_array($baseName, $blacklistedFiles);
    }


    private function start() {
        global $global;
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $global['cachePluginStart'] = $time;
    }

    private function end($type = "No Cache") {
        global $global;
        if (empty($global['cachePluginStart'])) {
            return false;
        }
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;

        if (User::isLogged()) {
            $type = "User: " . User::getUserName() . " - " . $type;
        } else {
            $type = "User: Not Logged - " . $type;
        }
        $t = (floatval($finish) - floatval($global['cachePluginStart']));
        $total_time = round($t, 4);
        _error_log("Page generated in {$total_time} seconds. {$type} ({$_SERVER['REQUEST_URI']}) FROM: {$_SERVER['REMOTE_ADDR']} Browser: {$_SERVER['HTTP_USER_AGENT']}");
    }

}

function sanitize_output($buffer) {

    $search = array(
        '/\>[^\S ]+/s', // strip whitespaces after tags, except space
        '/[^\S ]+\</s', // strip whitespaces before tags, except space
        '/(\s)+/s', // shorten multiple whitespace sequences
        '/<!--(.|\s)*?-->/' // Remove HTML comments
    );

    $replace = array(
        '>',
        '<',
        '\\1',
        ''
    );

    $len = strlen($buffer);
    if ($len) {
        _error_log("Before Sanitize: " . strlen($buffer));
        $buffer = preg_replace($search, $replace, $buffer);
        $lenAfter = strlen($buffer);
        _error_log("After Sanitize: {$lenAfter} = " . (($len / $lenAfter) * 100) . "%");
    }
    return $buffer;
}
