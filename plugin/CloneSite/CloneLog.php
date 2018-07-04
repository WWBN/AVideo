<?php

class CloneLog{
    public $file;
    
    function __construct() {
        global $global;
        $clonesDir = "{$global['systemRootPath']}videos/cache/clones/";
        $this->file = "{$clonesDir}client.log";
        if (!file_exists($clonesDir)) {
            mkdir($clonesDir, 0777, true);
            file_put_contents($clonesDir."index.html", '');
        }
        file_put_contents($this->file, "");
    }
    
    function add($message){
        file_put_contents($this->file, $message.PHP_EOL , FILE_APPEND | LOCK_EX);
    }
}
