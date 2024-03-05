<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AI/Objects/Ai_responses.php';
require_once $global['systemRootPath'] . 'plugin/AI/Objects/Ai_metatags_responses.php';
require_once $global['systemRootPath'] . 'plugin/AI/Objects/Ai_transcribe_responses.php';
require_once $global['systemRootPath'] . 'plugin/AI/Objects/Ai_responses_json.php';
require_once $global['systemRootPath'] . 'plugin/AI/Objects/Ai_scheduler.php';

class AI extends PluginAbstract
{

    static $typeTranslation = 'translation';
    static $typeTranscription = 'transcription';
    static $typeBasic = 'basic';
    static $typeShorts = 'shorts';

    static $languages = [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'it' => 'Italian',
        'pt' => 'Portuguese',
        'ru' => 'Russian',
        'zh' => 'Chinese',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'ar' => 'Arabic',
        'hi' => 'Hindi',
        'bn' => 'Bengali',
        'pl' => 'Polish',
        'tr' => 'Turkish',
        'nl' => 'Dutch',
        'sv' => 'Swedish',
        'da' => 'Danish',
        'fi' => 'Finnish',
        'no' => 'Norwegian',
        'cs' => 'Czech',
        'el' => 'Greek',
        'he' => 'Hebrew',
        'th' => 'Thai',
        'hu' => 'Hungarian',
        'id' => 'Indonesian',
        'ms' => 'Malay',
        'fa' => 'Persian',
        'uk' => 'Ukrainian',
        'vi' => 'Vietnamese'
    ];

    static $isTest = 0;
    static $url = 'https://ai.ypt.me/';
    static $url_test = 'http://192.168.0.2:81/AI/';

    static function getMetadataURL()
    {
        self::$isTest = ($_SERVER["SERVER_NAME"] == "vlu.me");
        return self::$isTest ? self::$url_test : self::$url;
    }

    static function getPricesURL()
    {
        return self::$url . 'prices.json.php';
    }

    public function getDescription()
    {
        $desc = "Optimize video visibility with AI-driven meta-tag suggestions and automatic transcription for enhanced SEO performance.";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/AI-Plugin' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";

        //$desc .= $this->isReadyLabel(array('YPTWallet'));
        return $desc . $help;
    }

    public function getName()
    {
        return "AI";
    }

    public function getUUID()
    {
        return "AI-5ee8405eaaa16";
    }

    public function getPluginVersion()
    {
        return "6.0";
    }

    public function getEmptyDataObject()
    {
        $obj = new stdClass();
        $obj->AccessToken = "";
        /*
          $obj->textSample = "text";
          $obj->checkboxSample = true;
          $obj->numberSample = 5;

          $o = new stdClass();
          $o->type = array(0=>__("Default"))+array(1,2,3);
          $o->value = 0;
          $obj->selectBoxSample = $o;

          $o = new stdClass();
          $o->type = "textarea";
          $o->value = "";
          $obj->textareaSample = $o;
         */
        return $obj;
    }

    static function getVideoShortsMetadata($videos_id)
    {
        global $global;
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = '';
        $obj->response = array();

        if (empty($videos_id)) {
            $obj->msg = 'empty videos id';
            return $obj;
        }

        if (!Video::canEdit($videos_id)) {
            $obj->msg = 'you cannot edit this video';
            return $obj;
        }

        $trascription = Ai_responses::getTranscriptionVtt($videos_id);

        if (empty($trascription)) {
            $obj->msg = 'empty transcription';
            return $obj;
        }

        //var_dump($paths);exit;
        $obj->response = array(
            'type' => AI::$typeShorts,
            'transcription' => $trascription,
        );

        $obj->error = false;
        return $obj;
    }

    static function getVideoTranslationMetadata($videos_id, $lang, $langName)
    {
        global $global;
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = '';
        $obj->response = array();

        if (empty($videos_id)) {
            $obj->msg = 'empty videos id';
            return $obj;
        }

        if (!Video::canEdit($videos_id)) {
            $obj->msg = 'you cannot edit this video';
            return $obj;
        }

        $video = new Video('', '', $videos_id);
        $filename = $video->getFilename();

        if (AVideoPlugin::isEnabledByName('SubtitleSwitcher')) {
            SubtitleSwitcher::transcribe($videos_id, false);
        }

        $firstVTTPath = AI::getFirstVTTFile($videos_id);
        $vttURL = str_replace($global['systemRootPath'], $global['webSiteRootURL'], $firstVTTPath);

        //var_dump($paths);exit;
        $obj->response = array(
            'type' => AI::$typeTranslation,
            'vtt' => $vttURL,
            //'firstVTTPath' => $firstVTTPath,
            'lang' => $lang,
            'langName' => $langName
        );

        $obj->error = false;
        return $obj;
    }

    static function getVideoBasicMetadata($videos_id)
    {
        global $global;
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = '';
        $obj->response = array();

        if (empty($videos_id)) {
            $obj->msg = 'empty videos id';
            return $obj;
        }

        if (!Video::canEdit($videos_id)) {
            $obj->msg = 'you cannot edit this video';
            return $obj;
        }

        $video = new Video('', '', $videos_id);
        $filename = $video->getFilename();

        if (AVideoPlugin::isEnabledByName('SubtitleSwitcher')) {
            SubtitleSwitcher::transcribe($videos_id, false);
        }
        /*
        */
        $firstVTTPath = AI::getFirstVTTFile($videos_id);
        $vttURL = str_replace(getVideosDir(), $global['webSiteRootURL'], $firstVTTPath);
        //var_dump($paths);exit;
        $obj->response = array(
            'type' => AI::$typeBasic,
            'filename' => $filename,
            'videos_id' => $videos_id,
            'title' => strip_tags($video->getTitle()),
            'description' => strip_tags($video->getDescription()),
            'duration_in_seconds' => $video->getDuration_in_seconds(),
            'vtt' => $vttURL,
            'text' => Ai_responses::getTranscriptionText($videos_id)
        );

        $obj->error = false;
        return $obj;
    }

    static function getVideoTranscriptionMetadata($videos_id, $lang)
    {
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = '';
        $obj->response = array();

        if (empty($videos_id)) {
            $obj->msg = 'empty videos id';
            return $obj;
        }

        if (!Video::canEdit($videos_id)) {
            $obj->msg = 'you cannot edit this video';
            return $obj;
        }

        $video = new Video('', '', $videos_id);
        $mp3 = false;
        $mp3s = self::getLowerMP3($videos_id);
        $fsize = 0;
        if ($mp3s['isValid']) {
            $mp3 = $mp3s['lower']['paths']['url'];
            $fsize = filesize($mp3s['lower']['paths']['path']);
        }

        //var_dump($paths);exit;
        $obj->response = array(
            'type' => AI::$typeTranscription,
            'videos_id' => $videos_id,
            'mp3' => $mp3,
            'filesize' => $fsize,
            'language' => $lang,
            'filesizeHuman' => humanFileSize($fsize),
            'duration_in_seconds' => $video->getDuration_in_seconds(),
        );

        $obj->error = false;
        return $obj;
    }

    static function getTokenForVideo($videos_id, $ai_responses_id, $param)
    {
        global $global;
        $obj = new stdClass();
        $obj->videos_id = $videos_id;
        $obj->users_id = User::getId();
        $obj->ai_responses_id = $ai_responses_id;
        $obj->param = $param;
        $obj->created = time();

        return encryptString(_json_encode($obj));
    }


    static function getTokenFromRequest()
    {

        if (empty($_REQUEST['token'])) {
            return false;
        }

        $string = decryptString($_REQUEST['token']);

        if (empty($string)) {
            return false;
        }

        $json = _json_decode($string);

        if (empty($json)) {
            return false;
        }

        return $json;
    }

    static function getMP3Path($videos_id)
    {
        $convert = convertVideoToMP3FileIfNotExists($videos_id);
        if (empty($convert) || empty($convert['url'])) {
            return false;
        }
        return $convert;
    }

    static function getMP3LowerPath($videos_id)
    {
        $convert = self::getMP3Path($videos_id);
        if (empty($convert) || empty($convert['url'])) {
            return false;
        }
        $convert['path'] = str_replace('.mp3', '_Low.mp3', $convert['path']);
        $convert['url'] = str_replace('.mp3', '_Low.mp3', $convert['url']);
        return $convert;
    }

    static function getMP3RegularAndLower($videos_id)
    {
        $arrayRegular = array(
            'paths' => false,
            'duration' => false,
            'durationInSeconds' => 0,
            'isValid' => false,
        );
        $arrayLower = array(
            'paths' => false,
            'duration' => false,
            'durationInSeconds' => 0,
            'isValid' => false,
        );

        $paths = self::getMP3Path($videos_id);
        if (!empty($paths)) {
            $duration = getDurationFromFile($paths['path']);
            $durationInSeconds = durationToSeconds($duration);
            $video = new Video('', '', $videos_id);
            $videoDuration = $video->getDuration_in_seconds();
            $diff = abs($videoDuration - $durationInSeconds);
            if ($diff > 2) {
                unlink($paths['path']);
                $response = array(
                    'regular' => $arrayRegular,
                    'lower' => $arrayLower,
                    'isValid' => false,
                    'msg' => "Length does not match (Video/MP3) video = {$videoDuration} seconds MP3 = $durationInSeconds seconds",
                );
                return $response;
            }

            $arrayRegular = array(
                'paths' => $paths,
                'duration' => $duration,
                'durationInSeconds' => $durationInSeconds,
                'isValid' => !empty($durationInSeconds),
            );

            $pathsLower = self::getMP3LowerPath($videos_id);
            if (!empty($pathsLower)) {
                $duration = getDurationFromFile($pathsLower['path']);
                $durationInSeconds = durationToSeconds($duration);
                $arrayLower = array(
                    'paths' => $pathsLower,
                    'duration' => $duration,
                    'durationInSeconds' => $durationInSeconds,
                    'isValid' => !empty($durationInSeconds),
                );

                $pathsLower = self::getMP3LowerPath($videos_id);
            }
        }
        $msg = '';
        $isValid = false;
        if ($arrayRegular['isValid'] && $arrayLower['isValid']) {
            $diff = abs($arrayRegular['durationInSeconds'] - $arrayLower['durationInSeconds']);
            if ($diff <= 2) {
                $isValid = true;
            } else {
                $msg = "durationInSeconds are not the same regular={$arrayRegular['durationInSeconds']} lower={$arrayLower['durationInSeconds']}";
            }
        } else {
            if (!$arrayRegular['isValid']) {
                $msg = 'Regular MP3 is invalid';
            }
            if (!$arrayRegular['isValid']) {
                $msg .= ' Lower MP3 is invalid';
            }
        }

        $response = array(
            'regular' => $arrayRegular,
            'lower' => $arrayLower,
            'isValid' => $isValid,
            'msg' => $msg,
        );
        return $response;
    }

    static function getLowerMP3($videos_id, $try = 0)
    {
        $mp3s = self::getMP3RegularAndLower($videos_id);
        if ($mp3s['regular']['isValid']) {
            if (!$mp3s['isValid']) {
                ini_set('max_execution_time', 300);
                set_time_limit(300);
                if (file_exists($mp3s['lower']['paths']['path'])) {
                    unlink($mp3s['lower']['paths']['path']);
                }
                $fromFileLocationEscaped = escapeshellarg($mp3s['regular']['paths']['path']);
                $toFileLocationEscaped = escapeshellarg($mp3s['lower']['paths']['path']);
                $command = get_ffmpeg() . " -i {$fromFileLocationEscaped} -ar 16000 -ac 1 -b:a 16k {$toFileLocationEscaped}";
                $command = removeUserAgentIfNotURL($command);
                exec($command, $output);
                _error_log('getLowerMP3: ' . json_encode($output));
                return self::getMP3RegularAndLower($videos_id);
            }
        } else {
            return $mp3s;
        }
        return $mp3s;
    }


    public function getPluginMenu()
    {
        global $global;
        return '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/AI/View/editor.php\')" class="btn btn-primary btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';
    }

    public function getVideosManagerListButton()
    {
        $obj = $this->getDataObject();
        $btn = '';
        $btn .= '<button type="button" ' .
            ' class="btn btn-default btn-light btn-sm btn-xs btn-block" ' .
            ' onclick="avideoModalIframe(webSiteRootURL+\\\'plugin/AI/page.php?videos_id=\'+row.id+\'\\\');" >' .
            ' <i class="fas fa-robot"></i> ' . __("AI-Powered") . '</button>';

        return $btn;
    }

    public static function getTagTypeId()
    {
        $VideoTags = AVideoPlugin::isEnabledByName('VideoTags');
        if (empty($VideoTags)) {
            return false;
        }
        $typeName = 'Keywords';
        $row = TagsTypes::getFromName($typeName);
        if (empty($row)) {
            $tagType = new TagsTypes(0);
            $tagType->setName($typeName);
            return $tagType->save();
        } else {
            return $row['id'];
        }
    }


    static function deleteAllRecords()
    {
        $tables = array('ai_transcribe_responses', 'ai_metatags_responses', 'ai_responses');
        foreach ($tables as $key => $value) {
            $sql = "DELETE FROM {$value} ";
            $sql .= " WHERE id > 0 ";
            $global['lastQuery'] = $sql;
            sqlDAL::writeSql($sql);
        }

        return true;
    }

    static function getVTTLanguageCodes($videos_id)
    {
        $video = new Video('', '', $videos_id);
        $dir = getVideosDir() . DIRECTORY_SEPARATOR . $video->getFilename();
        $languageCodes = [];
        $filePattern = '/video_[\w\d]+\.([\w\d_]+)\.vtt$/';

        if (is_dir($dir) && ($handle = opendir($dir))) {
            while (false !== ($entry = readdir($handle))) {
                if (is_file($dir . '/' . $entry) && preg_match($filePattern, $entry, $matches)) {
                    $languageCodes[] = $matches[1]; // Add the language code to the array
                }
            }
            closedir($handle);
        }

        return array_unique($languageCodes); // Return unique language codes
    }

    public function getFooterCode()
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/AI/footer.php';
    }

    static function getVTTFiles($videos_id)
    {
        $video = new Video('', '', $videos_id);
        $filename = $video->getFilename();
        $dir = getVideosDir() . "{$filename}/";

        // Find all .vtt files in the directory
        $vttFiles = glob($dir . "*.vtt");

        // Return the array of .vtt files
        return $vttFiles;
    }


    static function getFirstVTTFile($videos_id)
    {
        $vttFiles = self::getVTTFiles($videos_id);
        if (empty($vttFiles)) {
            return false;
        }
        return $vttFiles[0];
    }

    static function getProgressBarHTML($classname, $text)
    {
        return '
        <div class="progress progressAI ' . $classname . '" style="display:none;"> 
            <div class="progress-bar progress-bar-striped progress-bar-animated" 
            role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">                
            <strong class="progressAITitle">' . $text . '</strong>
            </div>
        </div>';
    }


    function executeEveryMinute()
    {
        $rows = Ai_scheduler::getAllActive();
        if (!empty($rows) && is_array($rows)) {
            foreach ($rows as $row) {
                $ai = new Ai_scheduler($row['id']);
                $ai->setStatus(Ai_scheduler::$statusExecuted);
                $ai->save();
                if ($ai->getAi_scheduler_type() === Ai_scheduler::$typeCutVideo) {
                    $obj = _json_decode($ai->getJson());
                    _error_log('AI:videoCut start '.$ai->getJson());
                    $vid = Video::getVideoLight($obj->videos_id);
                    $sources = getVideosURLOnly($vid['filename'], false);
                    $source = end($sources);
                    if (!empty($source)) {
                        _error_log('AI:videoCut source found  '.$source["url"]);
                        $duration_in_seconds = $obj->endTimeInSeconds - $obj->startTimeInSeconds;
                        $date = date('YMDHis');
                        $videoFileName = "cut{$obj->videos_id}_{$date}_{$obj->startTimeInSeconds}{$obj->endTimeInSeconds}";
                        $video = new Video($obj->title, $videoFileName);
                        $video->setDescription($obj->description);
                        $video->setUsers_id($obj->users_id);
                        $video->setDuration_in_seconds($duration_in_seconds);
                        $video->setDuration(secondsToDuration($duration_in_seconds));
                        $newVideos_id = $video->save(false, true);
                        if (!empty($newVideos_id)) {
                            _error_log('AI:videoCut new video saved videos_id='.$newVideos_id);
                            $outputFile = Video::getPathToFile("{$videoFileName}.mp4");
                            cutVideoWithFFmpeg($source['url'], $obj->startTimeInSeconds, $obj->endTimeInSeconds, $outputFile);
                            
                            $video = new Video('', '', $newVideos_id);
                            if(file_exists($outputFile)){
                                $video->setAutoStatus(Video::$statusActive);
                                AVideoPlugin::onUploadIsDone($newVideos_id);
                                AVideoPlugin::afterNewVideo($newVideos_id);
                                _error_log('AI:videoCut create file success  '.$outputFile);
                                $url = Video::getURL($newVideos_id);
                                $obj->socketResponse = sendSocketSuccessMessageToUsers_id("<a href='$url'>Video cutted</a>", $obj->users_id);
                            }else{
                                $video->delete(true);
                                _error_log('AI:videoCut error on create file  '.$outputFile);
                            }
                        }
                        //cutVideoWithFFmpeg($source['url'], $startTimeInSeconds, $endTimeInSeconds, $outputFile);
                    }
                }
            }
        }
    }
}
