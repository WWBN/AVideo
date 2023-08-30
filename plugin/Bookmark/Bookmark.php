<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/Bookmark/Objects/BookmarkTable.php';

class Bookmark extends PluginAbstract
{

    public function getDescription()
    {
        return "You can add bookmarks in a video or audio clip to highlight interest points or select chapters";
    }

    public function getName()
    {
        return "Bookmark";
    }

    public function getUUID()
    {
        return "27570956-dc62-46e3-ace9-86c6e8f9c84b";
    }

    public function getPluginMenu()
    {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/Bookmark/pluginMenu.html';
        return file_get_contents($filename);
    }

    public function getFooterCode()
    {
        global $global;
        $videos_id = getVideos_id();
        if (!empty($videos_id)) {
            $rows = BookmarkTable::getAllFromVideo($videos_id);
            //var_dump($rows);exit;
            if (!empty($rows)) {
                include $global['systemRootPath'] . 'plugin/Bookmark/footer.php';
            }
        }
    }

    public function getVideosManagerListButton()
    {
        global $global;

        $btn = '';
        $btn .= '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block " onclick="avideoModalIframeFull(webSiteRootURL+\\\'plugin/Bookmark/editorVideo.php?videos_id=\'+ row.id +\'\\\');" ><i class="fas fa-bookmark"></i> Add Bookmarks</button>';
        return $btn;
    }

    static function parseTimeString($str)
    {
        $pattern = "/\s*(\d{2}:\d{2}(?::\d{2})?)\s*-\s*(.+)$/m";
        preg_match_all($pattern, $str, $matches, PREG_SET_ORDER);

        $result = [];

        foreach ($matches as $match) {
            // Normalize time format to HH:MM:SS
            $timeParts = explode(":", $match[1]);
            if (count($timeParts) == 2) {
                $match[1] = $match[1] . ":00";
            }

            $result[] = [
                'seconds' => timeToSeconds($match[1]),
                'time' => $match[1],
                'text' => $match[2]
            ];
        }
        // Sort the array by time
        usort($result, function ($a, $b) {
            return $a['seconds'] -  $b['seconds'];
        });
        return $result;
    }

    static function videoToVtt($videos_id)
    {
        $data = BookmarkTable::getAllFromVideo($videos_id);

        $output = "WEBVTT\n\n";

        foreach ($data as $index => $item) {
            $start_time = $item["timeInSeconds"];

            // Assume each chapter is 5 seconds long if it's the last one, 
            // or calculate the time until the next chapter starts
            $end_time = ($index == count($data) - 1) ? $start_time + 5 : $data[$index + 1]["timeInSeconds"] - 1;

            $output .= secondsToTime($start_time) . " --> " . secondsToTime($end_time) . "\n";
            $output .= $item["name"] . "\n\n";
        }

        // Save to a WebVTT file
        $bytes = file_put_contents(self::getChaptersFilename($videos_id), $output);
        return $bytes;
        //var_dump($bytes, self::getChaptersFilename($videos_id), $output);exit;
    }

    static function getChaptersFilenameFromFilename($fileName, $lang='en') {
        $video = Video::getVideoFromFileNameLight($fileName);
        //var_dump($video);
        return self::getChaptersFilename($video['id'], $lang);
    }

    static function getChaptersFilename($videos_id, $lang='en') {
        $video = new Video("", "", $videos_id);
        $filename = $video->getFilename();
        $path = Video::getPathToFile($filename);
        if (empty($lang) || strtoupper($lang) == 'CC') {
            $vttFilename = "{$path}.chapters.vtt";
        } else {
            $vttFilename = "{$path}.chapters.{$lang}.vtt";
        }

        return $vttFilename;
    }
    
}

function getVTTCaptionTracks($fileName, $returnArray = false) {
    global $global;
    $cache = getVTTCaptionCache($fileName);
    if (!empty($cache)) {
        $objCache = _json_decode($cache);
    } else {
        $sourcesArray = array();
        $tracks = "";
        if (!empty($fileName)) {
            $defaultFile = Bookmark::getChaptersFilenameFromFilename($fileName);
            //var_dump( $defaultFile, Video::getCleanFilenameFromFile($defaultFile));exit;
            if (file_exists($defaultFile)) {
                $path_parts = pathinfo($defaultFile);
                $src = Video::getURLToFile($path_parts['basename']);
                $obj = new stdClass();
                $obj->srclang = 'en';
                $obj->src = $src;
                $obj->filename = $defaultFile;
                $obj->label = 'Chapters';
                $obj->desc = 'Chapters';

                $tracks .= "<track kind=\"chapters\" src=\"{$obj->src}\" srclang=\"{$obj->srclang}\" label=\"{$obj->label}\" default>";

                $sourcesArray[] = $obj;
            }
        }
        $objCache = new stdClass();
        $objCache->sourcesArray = $sourcesArray;
        $objCache->tracks = $tracks;
        createVTTCaptionCache($fileName, json_encode($objCache));
    }
    return $returnArray ? $objCache->sourcesArray : $objCache->tracks;
}

function getVTTCaptionCache($fileName) {
    global $global;
    $cacheDir = $global['systemRootPath'] . 'videos/cache/vttCaption/';
    $file = "{$cacheDir}{$fileName}.cache";
    if (!file_exists($file)) {
        return false;
    }
    return file_get_contents($file);
}

function createVTTCaptionCache($fileName, $data) {
    global $global;
    $cacheDir = $global['systemRootPath'] . 'videos/cache/vttCaption/';
    if (!file_exists($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }
    file_put_contents("{$cacheDir}{$fileName}.cache", $data);
}

function deleteVTTCaptionCache($fileName) {
    global $global;
    $cacheDir = $global['systemRootPath'] . 'videos/cache/vttCaption/';
    @unlink("{$cacheDir}{$fileName}.cache");
}