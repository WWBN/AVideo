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
        $obj->enableCachePerUser = false;
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
        if(empty($_SERVER['REQUEST_URI'])){
            $_SERVER['REQUEST_URI'] = "";
        }
        $obj = $this->getDataObject();
        $user = "";
        if(!empty($obj->enableCachePerUser)){
            $user = json_encode($_SESSION);
        }
        return md5($_SERVER['REQUEST_URI'].$user) . '.cache';
    }

    public function getStart() {
        global $global;
        $obj = $this->getDataObject();       
        if ($obj->logPageLoadTime) {
            $this->start();
        }
        if(!class_exists('User') || !User::isLogged() || !empty($obj->enableCacheForLoggedUsers)){ 
            $cachefile = $obj->cacheDir . $this->getFileName(); // e.g. cache/index.php.
            $lifetime = $obj->cacheTimeInSeconds;
            if(!empty($_GET['lifetime'])){
                $lifetime = intval($_GET['lifetime']);
            }            
            // if is a bot always show a cache
            if (file_exists($cachefile) && (((time() - $lifetime) <= filemtime($cachefile))) || isBot()) {
                $c = @url_get_contents($cachefile);
                echo $c;
                if ($obj->logPageLoadTime) {
                    $this->end("Cache");
                }
                exit;
            } else if(file_exists($cachefile)){
                unlink($cachefile);
            }
        }
        //ob_start('sanitize_output');
        ob_start();
    }

    public function getEnd() {
        $obj = $this->getDataObject();
        $cachefile = $obj->cacheDir . $this->getFileName();
        $c = ob_get_contents();
        if (!file_exists($obj->cacheDir)) {
            mkdir($obj->cacheDir, 0777, true);
        }
        if(!class_exists('User') || !User::isLogged() || !empty($obj->enableCacheForLoggedUsers)){
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
        require_once $global['systemRootPath'] . 'objects/user.php';
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        
        if(User::isLogged()){
            $type = "User: ".User::getUserName()." - ".$type;
        }else{
            $type = "User: Not Logged - ".$type;            
        }
        $total_time = round(($finish - $global['start']), 4);
        error_log("Page generated in {$total_time} seconds. {$type} ({$_SERVER['REQUEST_URI']}) FROM: {$_SERVER['REMOTE_ADDR']} Browser: {$_SERVER['HTTP_USER_AGENT']}");
    }

}


function sanitize_output($buffer) {

    $search = array(
        '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
        '/[^\S ]+\</s',     // strip whitespaces before tags, except space
        '/(\s)+/s',         // shorten multiple whitespace sequences
        '/<!--(.|\s)*?-->/' // Remove HTML comments
    );

    $replace = array(
        '>',
        '<',
        '\\1',
        ''
    );
    
    $len = strlen($buffer);
    if($len){
        error_log("Before Sanitize: ".strlen($buffer));
        $buffer = preg_replace($search, $replace, $buffer);  
        $lenAfter = strlen($buffer);
        error_log("After Sanitize: {$lenAfter} = ".(($len/$lenAfter)*100)."%");
    }
    return $buffer;
}