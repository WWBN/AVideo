<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/Bookmark/Objects/BookmarkTable.php';

class Bookmark extends PluginAbstract
{

    public function getDescription() {
        $txt = "You can add bookmarks in a video or audio clip to highlight interest points or select Chapters.";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/Bookmark-Plugin' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";

        return $txt . $help;
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
        $btn .= '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block " onclick="avideoModalIframeFull(webSiteRootURL+\\\'plugin/Bookmark/editorVideo.php?videos_id=\'+ row.id +\'\\\');" ><i class="fas fa-bookmark"></i> '.__('Add Bookmarks').'</button>';
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
                'seconds' => timeToSecondsInt($match[1]),
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

    static function generateChaptersHTML($videos_id)
    {
        $ChaptersData = BookmarkTable::getAllFromVideo($videos_id);
        if (empty($ChaptersData)) {
            return '';
        }
        $html = '<div class="Chapters">';
        foreach ($ChaptersData as $index => $item) {
            $time = secondsToTime($item["timeInSeconds"], '%02d');
            $html .= '<a href="#" id="Chapter-' . $index . '" class="Chapter" data-timestamp="' . $time . '" onclick="playChapter(' . $item["timeInSeconds"] . ');return false;">'
                . $time . '</a> '
                . $item["name"] . '<br>';
        }
        $html .= '</div>';

        return $html;
    }

    static function generateChaptersJSONLD($videos_id)
    {
        $ChaptersData = BookmarkTable::getAllFromVideo($videos_id);
        $Chapters = [];

        if (!empty($ChaptersData)) {
            $video = new VIdeo('', '', $videos_id);
            $durationInSeconds = $video->getDuration_in_seconds();
            foreach ($ChaptersData as $index => $item) {
                $startTime = secondsToTime($item["timeInSeconds"], '%02d');

                // Calculate end time based on the next chapter's start time
                $endTimeInSeconds = ($index < count($ChaptersData) - 1)
                    ? $ChaptersData[$index + 1]["timeInSeconds"]
                    : $durationInSeconds;

                $endTime = secondsToTime($endTimeInSeconds, '%02d');

                $Chapter = [
                    "@type" => "VideoGameClip",
                    "name" => $item["name"],
                    "startTime" => "PT" . $startTime . "S",
                    "endTime" => "PT" . $endTime . "S"
                ];

                $Chapters[] = $Chapter;
            }
        }

        return $Chapters;
    }


    static function videoToVtt($videos_id)
    {
        $data = BookmarkTable::getAllFromVideo($videos_id);

        $output = "WEBVTT\n\n";

        foreach ($data as $index => $item) {
            $start_time = $item["timeInSeconds"];

            // Assume each Chapter is 5 seconds long if it's the last one,
            // or calculate the time until the next Chapter starts
            $end_time = ($index == count($data) - 1) ? $start_time + 5 : $data[$index + 1]["timeInSeconds"] - 1;

            $output .= secondsToTime($start_time) . " --> " . secondsToTime($end_time) . "\n";
            $output .= $item["name"] . "\n\n";
        }

        // Save to a WebVTT file
        $bytes = file_put_contents(self::getChaptersFilename($videos_id), $output);
        return $bytes;
        //var_dump($bytes, self::getChaptersFilename($videos_id), $output);exit;
    }

    static function getChaptersFilenameFromFilename($fileName, $lang = 'en')
    {
        $video = Video::getVideoFromFileNameLight($fileName);
        //var_dump($video);
        return self::getChaptersFilename($video['id'], $lang);
    }

    static function getChaptersFilename($videos_id, $lang = 'en')
    {
        $video = new Video("", "", $videos_id);
        $filename = $video->getFilename();
        $path = Video::getPathToFile($filename);
        if (empty($lang) || strtoupper($lang) == 'CC') {
            $vttFilename = "{$path}.Chapters.vtt";
        } else {
            $vttFilename = "{$path}.Chapters.{$lang}.vtt";
        }

        return $vttFilename;
    }
}

function getVTTChapterTracks($fileName, $returnArray = false)
{
    global $global;
    $cache = getVTTChapterCache($fileName);
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
                //var_dump($src, $path_parts['basename'], $defaultFile);exit;
                $obj = new stdClass();
                $obj->srclang = 'en';
                $obj->src = $src;
                $obj->filename = $defaultFile;
                $obj->label = 'Chapters';
                $obj->desc = 'Chapters';

                $tracks .= "<track kind=\"chapters\" src=\"{$obj->src}\" default>";

                $sourcesArray[] = $obj;
            }
        }
        $objCache = new stdClass();
        $objCache->sourcesArray = $sourcesArray;
        $objCache->tracks = $tracks;
        createVTTChapterCache($fileName, json_encode($objCache));
    }
    return $returnArray ? $objCache->sourcesArray : $objCache->tracks;
}

function getVTTChapterCache($fileName)
{
    global $global;
    $cacheDir = $global['systemRootPath'] . 'videos/cache/vttChapter/';
    $file = "{$cacheDir}{$fileName}.cache";
    if (!file_exists($file)) {
        return false;
    }
    return file_get_contents($file);
}

function createVTTChapterCache($fileName, $data)
{
    global $global;
    $cacheDir = $global['systemRootPath'] . 'videos/cache/vttChapter/';
    if (!file_exists($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }
    file_put_contents("{$cacheDir}{$fileName}.cache", $data);
}

function deleteVTTChapterCache($fileName)
{
    global $global;
    $cacheDir = $global['systemRootPath'] . 'videos/cache/vttChapter/';
    @unlink("{$cacheDir}{$fileName}.cache");
}
