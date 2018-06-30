<?php

function getCloneFilesInfo($dir, $subdir = "", $extensionsToCopy = array('mp4', 'webm', 'gif', 'jpg', 'png')) {
    global $global;
    $files = array();
    // get video files
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != ".." && !is_dir($dir . $entry)) {
                $path_info = pathinfo($entry);
                if (!in_array($path_info['extension'], $extensionsToCopy)) {
                    continue;
                }
                $f = new stdClass();
                $f->filename = $entry;
                $f->url = "{$global['webSiteRootURL']}videos/{$subdir}{$entry}";
                $f->filesize = filesize($dir . $entry);
                $f->filemtime = filemtime($dir . $entry);
                $files[] = $f;
            }
        }
        closedir($handle);
    }
    return $files;
}

/**
 * 
 * @param type $serverArray a Json with the server files retrieve from getCloneFilesInfo function
 * @param type $clientArray a Json with the client files retrieve from getCloneFilesInfo function
 * @return type a Json with the new files
 */
function detectNewFiles($serverArray, $clientArray){
    foreach ($serverArray as $key => $value) {
        foreach ($clientArray as $key2 => $value2) {
            if(
                    $value->filename===$value2->filename &&
                    $value->filesize===$value2->filesize &&
                    $value->filemtime===$value2->filemtime
                    ){
                unset($serverArray[$key]);
                unset($clientArray[$key2]);
            }
        }
    }
    return $serverArray;
}