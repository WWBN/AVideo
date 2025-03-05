<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/Cache/Objects/CacheDB.php';
require_once $global['systemRootPath'] . 'plugin/Cache/Objects/Cache_schedule_delete.php';

class Cache extends PluginAbstract {

    public function getTags() {
        return [
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE,
        ];
    }

    public function getDescription() {
        global $global;
        $txt = "AVideo application accelerator to cache pages.<br>Your website has 10,000 visitors who are online, and your dynamic page has to send 10,000 times the same queries to database on every page load. With this plugin, your page only sends 1 query to your DB, and uses the cache to serve the 9,999 other visitors.";
        $txt .= "<br>To auto delete the old cache files you can use this crontab command <code>0 2 * * * php {$global['systemRootPath']}plugin/Cache/crontab.php</code> this will delete cache files that are 3 days old everyday at 2 AM";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/Cache-Plugin' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $txt . $help;
    }

    public function getName() {
        return "Cache";
    }

    public function getUUID() {
        return "10573225-3807-4167-ba81-0509dd280e06";
    }

    public function getPluginVersion() {
        return "8.0";
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
        $obj->deleteStatisticsDaysOld = 180; // 6 months
        return $obj;
    }

    public function getCacheDir($ignoreFirstPage = true) {
        global $global;
        $obj = $this->getDataObject();
        if (!$ignoreFirstPage && $this->isFirstPage()) {
            $obj->cacheDir .= "firstPage" . DIRECTORY_SEPARATOR;
            /*
            if(isMobile()){
                $obj->cacheDir .= "mobile" . DIRECTORY_SEPARATOR;
            }else{
                $obj->cacheDir .= "desktop" . DIRECTORY_SEPARATOR;
            }
            */
            if (User::isLogged()) {
                $obj->cacheDir .= 'users_id_' . md5(User::getId() . $global['salt']) . DIRECTORY_SEPARATOR;
            }
        } else if (User::isLogged()) {
            if (User::isAdmin()) {
                $obj->cacheDir .= 'admin_' . md5("admin" . $global['salt']) . DIRECTORY_SEPARATOR;
            } else {
                $obj->cacheDir .= 'user_' . md5("user" . $global['salt']) . DIRECTORY_SEPARATOR;
            }
        } else {
            $obj->cacheDir .= 'notlogged_' . md5("notlogged" . $global['salt']) . DIRECTORY_SEPARATOR;
        }
        if(!empty($_COOKIE['forKids'])){
            $obj->cacheDir .= 'forkids' . DIRECTORY_SEPARATOR;
        }
        $obj->cacheDir = fixPath($obj->cacheDir, true);
        if (!file_exists($obj->cacheDir)) {
            mkdir($obj->cacheDir, 0777, true);
        }
        return $obj->cacheDir;
    }

    private function getFileName() {
        global $global;
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
        $compl .= getValueOrBlank(['ads_app_store_url']);
        $dir = "";
        $plugin = AVideoPlugin::loadPluginIfEnabled('User_Location');
        if (!empty($plugin)) {
            $location = User_Location::getThisUserLocation();
            if (!empty($location['country_code']) && $location['country_code'] != '-') {
                $dir = $location['country_code'] . DIRECTORY_SEPARATOR;
            }
        }
        if ($this->isFirstPage()) {
            $dir .= (isMobile() ? 'mobile' : 'desktop') . DIRECTORY_SEPARATOR;
        }
        return $dir . User::getId() . "_{$compl}" . md5(@$_SESSION['channelName'] . $_SERVER['REQUEST_URI'] . @$_SERVER['HTTP_HOST']) . "_" . $session_id . "_" . (!empty($_SERVER['HTTPS']) ? 'a' : ''). (!empty($_COOKIE['forKids']) ? 'k' : '') . (@$_SESSION['language']) . '.cache';
    }

    private function isFirstPage() {
        return isFirstPage();
    }


    public function getStart() {
        global $global;
        // ignore cache if it is command line
        //var_dump($this->isFirstPage());exit;
        $obj = $this->getDataObject();
        if ($obj->logPageLoadTime) {
            $this->start();
        }

        if (isCommandLineInterface()) {
            return true;
        }
        $whitelistedFiles = ['user.php', 'status.php', 'canWatchVideo.json.php', '/login', '/status'];
        $whitelistedScriptName = ['/plugin/Live/index.php'];
        $blacklistedFiles = ['videosAndroid.json.php'];
        $baseName = basename($_SERVER["SCRIPT_NAME"]);
        if (getVideos_id() || isVideo() || isLive() || isLiveLink() || in_array($baseName, $whitelistedFiles) || in_array($_SERVER['REQUEST_URI'], $whitelistedFiles) || in_array($_SERVER['SCRIPT_NAME'], $whitelistedScriptName)) {
            return true;
        }

        $isBot = isBot();
        if ($this->isBlacklisted() || $this->isFirstPage() || !class_exists('User') || !User::isLogged() || !empty($obj->enableCacheForLoggedUsers)) {
            $cacheName = $this->getFileName();
            if ($this->isFirstPage()) {
                if (isMobile()) {
                    $cacheName = "mobile_{$cacheName}";
                }
                $cacheName = 'firstPage' . DIRECTORY_SEPARATOR . $cacheName;

                if (isIframe()) {
                    $cacheName .= '_iframe';
                }
            }
            //var_dump(__LINE__, $cacheName);exit;
            /*
            $lifetime = $obj->cacheTimeInSeconds;
            if ($isBot && $lifetime < 3600) {
                $lifetime = 3600;
            }
            */
            if (isBot()) {
                return 0; // 1 week
            }else{
                $lifetime = cacheExpirationTime();
            }
            if (empty($_REQUEST['debug_cache'])) {
                $firstPageCache = ObjectYPT::getCache($cacheName, $lifetime, true);
                //var_dump($cacheName, $firstPageCache);exit;
            }
            if (!empty($firstPageCache) && strtolower($firstPageCache) != 'false') {
                if ($isBot && $_SERVER['REQUEST_URI'] !== '/login') {
                    //_error_log("Bot Detected, showing the cache ({$_SERVER['REQUEST_URI']}) FROM: {$_SERVER['REMOTE_ADDR']} Browser: {$_SERVER['HTTP_USER_AGENT']}");
                }
                //$c = @local_get_contents($cachefile);
                if (preg_match("/\.json\.?/", $baseName)) {
                    header('Content-Type: application/json');
                }

                if ($isBot) {
                    $firstPageCache = strip_specific_tags($firstPageCache, ['script', 'style', 'iframe', 'object', 'applet', 'link'], true);
                    $firstPageCache = strip_render_blocking_resources($firstPageCache);
                } else {
                    $firstPageCache = optimizeHTMLTags($firstPageCache);
                }

                echo $firstPageCache . PHP_EOL . '<!-- Cached Page Generated in ' . getScriptRunMicrotimeInSeconds() . ' Seconds [' . User::getId() . '] '.$_SERVER["SCRIPT_NAME"].' -->';
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
            //_error_log("Bot Detected, NOT showing the cache ({$_SERVER['REQUEST_URI']}) FROM: {$_SERVER['REMOTE_ADDR']} Browser: {$_SERVER['HTTP_USER_AGENT']}");
            if ($obj->stopBotsFromNonCachedPages) {
                _error_log("Bot stopped  ({$_SERVER['REQUEST_URI']}) FROM: {$_SERVER['REMOTE_ADDR']} Browser: {$_SERVER['HTTP_USER_AGENT']}");
                exit;
            }
        }
        //ob_start('sanitize_output');
        _ob_start();
    }

    public function getEnd() {
        global $global;
        $obj = $this->getDataObject();
        echo PHP_EOL . '<!--        Page Generated in ' . getScriptRunMicrotimeInSeconds() . ' Seconds -->';
        $c = _ob_get_clean();
        $c = optimizeHTMLTags($c);
        _ob_start();
        echo $c;
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
            $cacheName = $this->getFileName();

            if ($this->isFirstPage()) {
                if (isMobile()) {
                    $cacheName = "mobile_{$cacheName}";
                }
                $cacheName = 'firstPage' . DIRECTORY_SEPARATOR . $cacheName;
            }

            $c = preg_replace('/<script id="infoForNonCachedPages">[^<]+<\/script>/', '', $c);

            $r = ObjectYPT::setCache($cacheName, $c);
            //var_dump($r);
        }
        if ($obj->logPageLoadTime) {
            $this->end();
        }

        //self::saveCache();
    }

    private function isREQUEST_URIWhitelisted() {
        $cacheBotWhitelist = [
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
            '/sitemap.xml',
            'plugin/Live/verifyToken.json.php',
            'control.json.php',
            'robots.txt',
            'Live_restreams'
        ];
        foreach ($cacheBotWhitelist as $value) {
            if (strpos($_SERVER['REQUEST_URI'], $value) !== false) {
                _error_log("Cache::isREQUEST_URIWhitelisted: ($value) is whitelisted");
                return true;
            }
        }
        return false;
    }

    private function isBlacklisted() {
        $blacklistedFiles = ['videosAndroid.json.php'];
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
        //_error_log("Page generated in {$total_time} seconds. {$type} ({$_SERVER['REQUEST_URI']}) FROM: {$_SERVER['REMOTE_ADDR']} Browser: {$_SERVER['HTTP_USER_AGENT']}");
    }

    public function getPluginMenu() {
        global $global;
        $fileAPIName = $global['systemRootPath'] . 'plugin/Cache/pluginMenu.html';
        $content = file_get_contents($fileAPIName);
        return $content;
    }

    public function getFooterCode() {
        global $global;
        if (preg_match('/managerPlugins.php$/', $_SERVER["SCRIPT_FILENAME"])) {
            return "<script src=\"{$global['webSiteRootURL']}plugin/Cache/pluginMenu.js\"></script>";
        }
    }

    public static function getCacheMetaData() {
        global $_getCacheMetaData;
        if (!empty($_getCacheMetaData)) {
            return $_getCacheMetaData;
        }
        $domain = getDomain();
        $ishttps = isset($_SERVER["HTTPS"]) ? 1 : 0;
        $user_location = 'undefined';
        if (class_exists("User_Location")) {
            $loc = User_Location::getThisUserLocation();
            if (!empty($loc) && !empty($loc['country_code']) && $loc['country_code'] != '-') {
                $user_location = $loc['country_code'];
            }
        }
        $loggedType = CacheDB::$loggedType_NOT_LOGGED;
        if (User::isLogged()) {
            if (User::isAdmin()) {
                $loggedType = CacheDB::$loggedType_ADMIN;
            } else {
                $loggedType = CacheDB::$loggedType_LOGGED;
            }
        }
        $_getCacheMetaData = ['domain' => $domain, 'ishttps' => $ishttps, 'user_location' => $user_location, 'loggedType' => $loggedType];
        return $_getCacheMetaData;
    }

    public static function _getCache($name, $ignoreMetadata = false) {
        global $cache_setCacheToSaveAtTheEnd;
        if(!empty($cache_setCacheToSaveAtTheEnd[$name])){
            return $cache_setCacheToSaveAtTheEnd[$name];
        }
        $metadata = self::getCacheMetaData();
        return CacheDB::getCache($name, $metadata['domain'], $metadata['ishttps'], $metadata['user_location'], $metadata['loggedType'], $ignoreMetadata);
    }

    public static function _setCache($name, $value) {
        global $cache_setCacheToSaveAtTheEnd;
        if(!isset($cache_setCacheToSaveAtTheEnd)){
            $cache_setCacheToSaveAtTheEnd = array();
        }
        $cache_setCacheToSaveAtTheEnd[$name] = $value;
        return true;
    }

    static function saveCache() {
        if (isBot()) {
            if(isCommandLineInterface()){
                echo "saveCache isBot".PHP_EOL;
            }
            return false;
        }
        global $cache_setCacheToSaveAtTheEnd;
        if(!empty($cache_setCacheToSaveAtTheEnd)){
            $metadata = self::getCacheMetaData();
            CacheDB::setBulkCache($cache_setCacheToSaveAtTheEnd, $metadata);
        }else{
            if(isCommandLineInterface()){
                echo "saveCache cache_setCacheToSaveAtTheEnd is empty".PHP_EOL;
            }
        }
    }

    public static function getCache($name, $lifetime = 60, $ignoreMetadata = false) {
        global $_getCacheDB, $global, $cacheFound, $cacheNotFound;
        if (!empty($global['ignoreAllCache'])) {
            return null;
        }
        if (!isset($_getCacheDB)) {
            $_getCacheDB = [];
            $cacheFound = 0;
            $cacheNotFound = 0;
        }
        if(!empty($lifetime)){
            if(isBot()){
                $lifetime = 0;
            }else if($cacheNotFound>100){
                $lifetime = 0; // make it unlimited
            }else {
                // increase timeout
                $lifetime += $cacheNotFound*30;
            }
        }
        $index = "{$name}_{$lifetime}";
        if (!isset($_getCacheDB[$index])) {
            $_getCacheDB[$index] = false;
            $metadata = self::getCacheMetaData();
            $row = CacheDB::getCache($name, $metadata['domain'], $metadata['ishttps'], $metadata['user_location'], $metadata['loggedType'], $ignoreMetadata);
            if (!empty($row) && !empty($row['id'])) {
                //$time = getTimeInTimezone(strtotime($row['modified']), $row['timezone']);
                $created_php_time = $row['created_php_time'];
                $maxTimeTolerance = ($created_php_time + $lifetime);
                $timeNow = time();
                $isExpired = !empty($lifetime) && $maxTimeTolerance < $timeNow;
                if ($isExpired) {
                    $moreInfo = "Lifetime expired = ".($timeNow-$maxTimeTolerance);
                    //_error_log("getCache($name, $lifetime, $ignoreMetadata) is expired cacheNotFoundCount=$cacheNotFound $moreInfo line=".__LINE__);
                    $cacheNotFound++;
                } else if(!$isExpired && !empty($row['content'])) {
                    $_getCacheDB[$index] = _json_decode($row['content']);
                    $cacheFound++;
                    if($_getCacheDB[$index] === null){
                        $_getCacheDB[$index] = $row['content'];
                    }
                }
            }
        }
        return $_getCacheDB[$index];
    }

    public static function deleteCache($name) {
        return CacheDB::deleteCache($name);
    }

    public static function deleteAllCache() {
        return CacheDB::deleteAllCache();
    }

    public static function deleteFirstPageCache() {
        clearCache(true);
        return CacheDB::deleteCacheStartingWith('firstPage', false);
    }

    public static function deleteCacheDir($prefix){;
        $dir = ObjectYPT::getTmpCacheDir() . $prefix;
        $resp = exec("rm -R {$dir}");
        return $resp;
    }

    public static function deleteOldCache($days, $limit = 5000) {
        global $global;
        $days = intval($days);
        if (!empty($days)) {
            $time = strtotime("-{$days} days");
            $sql = "DELETE FROM CachesInDB ";
            //$sql .= " WHERE created < DATE_SUB(NOW(), INTERVAL ? DAY) ";
            $sql .= " WHERE created_php_time < {$time} ";
            $sql .= " LIMIT $limit";
            $global['lastQuery'] = $sql;

            //return sqlDAL::writeSql($sql, "i", [$days]);
            return sqlDAL::writeSql($sql);
        }
        return false;
    }

    function executeEveryMinute() {
        global $global;
        $global['systemRootPath'] . 'plugin/Cache/deleteStatistics.json.php';
        self::deleteOldCache(1);

        $rows = Cache_schedule_delete::getAll();
        Cache_schedule_delete::truncateTable();
        if (is_iterable($rows)) {
            foreach ($rows as $row) {
                CacheDB::deleteCacheStartingWith($row['name'], false);
                self::deleteCacheDir($row['name']);
            }
        }
    }

}

function sanitize_output($buffer) {
    $search = [
        '/\>[^\S ]+/s', // strip whitespaces after tags, except space
        '/[^\S ]+\</s', // strip whitespaces before tags, except space
        '/(\s)+/s', // shorten multiple whitespace sequences
        '/<!--(.|\s)*?-->/', // Remove HTML comments
    ];

    $replace = [
        '>',
        '<',
        '\\1',
        '',
    ];

    $len = strlen($buffer);
    if ($len) {
        _error_log("Before Sanitize: " . strlen($buffer));
        $buffer = preg_replace($search, $replace, $buffer);
        $lenAfter = strlen($buffer);
        _error_log("After Sanitize: {$lenAfter} = " . (($len / $lenAfter) * 100) . "%");
    }
    return $buffer;
}
