<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Cache extends PluginAbstract {

    public function getDescription() {
        return "YouPHPTube application accelerator to cache pages.<br>Your website has 10,000 visitors who are online, and your dynamic page has to send 10,000 times the same queries to database on every page load. With this plugin, your page only sends 1 query to your DB, and uses the cache to serve the 9,999 other visitors.";
    }

    public function getName() {
        return "Cache";
    }

    public function getUUID() {
        return "10573225-3807-4167-ba81-0509dd280e06";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->enableCacheForLoggedUsers = false;
        $obj->cacheTimeInSeconds = 600;
        $obj->cacheDir = $global['systemRootPath'] . 'videos/cache/';
        $obj->logPageLoadTime = false;
        return $obj;
    }

    public function getTags() {
        return array('free', 'cache', 'speed up');
    }
    
    private function getFileName(){
        return md5($_SERVER['REQUEST_URI'].json_encode($_SESSION)) . '.cache';
    }

    public function getStart() {
        global $global;
        $obj = $this->getDataObject();
        if ($obj->logPageLoadTime) {
            $this->start();
        }
        $cachefile = $obj->cacheDir . $this->getFileName(); // e.g. cache/index.php.cache
        if (file_exists($cachefile) && time() - $obj->cacheTimeInSeconds <= filemtime($cachefile)) {
            $c = @file_get_contents($cachefile);
            echo $c;
            if ($obj->logPageLoadTime) {
                $this->end("Cache");
            }
            exit;
        } else if(file_exists($cachefile)){
            unlink($cachefile);
        }
        ob_start();
    }

    public function getEnd() {
        $obj = $this->getDataObject();
        $cachefile = $obj->cacheDir . $this->getFileName();
        $c = ob_get_contents();
        if (!file_exists($obj->cacheDir)) {
            mkdir($obj->cacheDir, 0777, true);
        }
        if(empty(User) || !User::isLogged() || $obj->enableCacheForLoggedUsers){
            file_put_contents($cachefile, $c);
        }
        if ($obj->logPageLoadTime) {
            $this->end();
        }
    }

    private function start() {
        global $global;
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $global['start'] = $time;
    }

    private function end($type = "No Cache") {
        global $global;
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round(($finish - $global['start']), 4);
        error_log("{$type}: Page generated in {$total_time} seconds. ({$_SERVER['REQUEST_URI']})");
    }

}
