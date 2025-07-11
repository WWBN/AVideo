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

    const PERMISSION_CAN_USE_AI_SUGGESTIONS = 0;

    static $typeTranslation = 'translation';
    static $typeTranscription = 'transcription';
    static $typeBasic = 'basic';
    static $typeImage = 'image';
    static $typeShorts = 'shorts';
    static $typeDubbing = 'dubbing';

    const LANGS = [
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


    const DubbingLANGS = [
        ['name' => 'English', 'code' => 'en'],
        ['name' => 'Hindi', 'code' => 'hi'],
        ['name' => 'Portuguese', 'code' => 'pt'],
        ['name' => 'Chinese', 'code' => 'zh'],
        ['name' => 'Spanish', 'code' => 'es'],
        ['name' => 'French', 'code' => 'fr'],
        ['name' => 'German', 'code' => 'de'],
        ['name' => 'Japanese', 'code' => 'ja'],
        ['name' => 'Arabic', 'code' => 'ar'],
        ['name' => 'Russian', 'code' => 'ru'],
        ['name' => 'Korean', 'code' => 'ko'],
        ['name' => 'Indonesian', 'code' => 'id'],
        ['name' => 'Italian', 'code' => 'it'],
        ['name' => 'Dutch', 'code' => 'nl'],
        ['name' => 'Turkish', 'code' => 'tr'],
        ['name' => 'Polish', 'code' => 'pl'],
        ['name' => 'Swedish', 'code' => 'sv'],
        ['name' => 'Filipino', 'code' => 'fil'],
        ['name' => 'Malay', 'code' => 'ms'],
        ['name' => 'Romanian', 'code' => 'ro'],
        ['name' => 'Ukrainian', 'code' => 'uk'],
        ['name' => 'Greek', 'code' => 'el'],
        ['name' => 'Czech', 'code' => 'cs'],
        ['name' => 'Danish', 'code' => 'da'],
        ['name' => 'Finnish', 'code' => 'fi'],
        ['name' => 'Bulgarian', 'code' => 'bg'],
        ['name' => 'Croatian', 'code' => 'hr'],
        ['name' => 'Slovak', 'code' => 'sk'],
        ['name' => 'Tamil', 'code' => 'ta'],
    ];

    static $isTest = 0;
    static $url = 'https://ai.ypt.me/';
    static $url_test = 'http://192.168.0.2:81/AI/';

    public function getTags()
    {
        return [
            PluginTags::$MONETIZATION,
            PluginTags::$FREE,
            PluginTags::$RECOMMENDED,
            PluginTags::$PLAYER,
        ];
    }

    static function getMetadataURL()
    {
        global $global;
        if (!empty($_SERVER["SERVER_NAME"])) {
            $domain = $_SERVER["SERVER_NAME"];
        } else {
            $domain = parse_url($global['webSiteRootURL'], PHP_URL_HOST);
        }
        self::$isTest = ($domain == "vlu.me");
        //return self::$url;
        return self::$isTest ? self::$url_test : self::$url;
    }

    static function getPricesURL()
    {
        return self::$url . 'prices.json.php';
    }

    public function getDescription()
    {
        $desc = "Optimize video visibility with AI-driven meta-tag suggestions and automatic transcription for enhanced SEO performance.";
        $desc .= "<br>You can overprice AI request prices and generate income from users who utilize these AI services.";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/AI-Plugin' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        $help .= "<br><small><a href='https://youphp.tube/marketplace/AI/privacyPolicy.php' target='_blank'><i class='fa-solid fa-money-check-dollar'></i> AI Services Pricing</a></small>";

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
        $obj->priceForBasic = 0;
        self::addDataObjectHelper('priceForBasic', 'Price for Basic Service', "Enter the charge amount for AI processing. Insufficient wallet balance will prevent processing. Successful charges apply to both your and the admin's CDN wallet on the marketplace.");
        $obj->priceForTranscription = 0;
        self::addDataObjectHelper('priceForTranscription', 'Price for Transcription Service', "Enter the charge amount for AI processing. Insufficient wallet balance will prevent processing. Successful charges apply to both your and the admin's CDN wallet on the marketplace.");
        $obj->priceForTranslation = 0;
        self::addDataObjectHelper('priceForTranslation', 'Price for Translation Service', "Enter the charge amount for AI processing. Insufficient wallet balance will prevent processing. Successful charges apply to both your and the admin's CDN wallet on the marketplace.");
        $obj->priceForShorts = 0;
        self::addDataObjectHelper('priceForShorts', 'Price for Shorts Service', "Enter the charge amount for AI processing. Insufficient wallet balance will prevent processing. Successful charges apply to both your and the admin's CDN wallet on the marketplace.");
        $obj->priceForDubbing = 0;
        self::addDataObjectHelper('priceForDubbing', 'Price per second for Dubbing Service', "Enter the charge amount for AI processing. Insufficient wallet balance will prevent processing. Successful charges apply to both your and the admin's CDN wallet on the marketplace.");


        $obj->autoProcessAll = false;
        self::addDataObjectHelper('autoProcessAll', 'Auto Process All', "This will create the transcription + basic + shorts automatically for all new videos");

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

        if (!isCommandLineInterface() && !Video::canEdit($videos_id)) {
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

        if (!isCommandLineInterface() && !Video::canEdit($videos_id)) {
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

        if (!isCommandLineInterface() && !Video::canEdit($videos_id)) {
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

    static function getVideoDubbingMetadata($videos_id, $lang)
    {
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = '';
        $obj->response = array();

        if (empty($videos_id)) {
            $obj->msg = 'empty videos id';
            return $obj;
        }

        if (!isCommandLineInterface() && !Video::canEdit($videos_id)) {
            $obj->msg = 'you cannot edit this video';
            return $obj;
        }

        $video = new Video('', '', $videos_id);
        $mp3 = false;

        $paths = AI::getMP3Path($videos_id);
        $fsize = 0;
        if ($paths['url']) {
            $mp3 = $paths['url'];
            $fsize = filesize($paths['path']);
        }

        //var_dump($paths);exit;
        $obj->response = array(
            'type' => AI::$typeDubbing,
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
            if (filesize($paths['path']) < 20) {
                // it is a dummy file, try the Storage URL
                $duration = getDurationFromFile($paths['url']);
            } else {
                $duration = getDurationFromFile($paths['path']);
            }

            $durationInSeconds = durationToSeconds($duration);
            $video = new Video('', '', $videos_id);
            $videoDuration = $video->getDuration_in_seconds();
            $diff = abs($videoDuration - $durationInSeconds);
            $tenPercentOfVideoDuration = $videoDuration * 0.1; // 10% of $videoDuration
            if ($diff > $tenPercentOfVideoDuration) {

                $f = convertVideoFileWithFFMPEGIsLockedInfo($paths['path']);
                if ($f['isUnlocked']) {
                    _error_log('getMP3RegularAndLower: unlink line=' . __LINE__);
                    unlink($paths['path']);
                    $response = array(
                        'regular' => $arrayRegular,
                        'lower' => $arrayLower,
                        'isValid' => false,
                        'msg' => "Length does not match (Video/MP3) video = {$videoDuration} seconds MP3 = $durationInSeconds seconds",
                    );
                } else {
                    $response = array(
                        'regular' => $arrayRegular,
                        'lower' => $arrayLower,
                        'isValid' => true,
                        'msg' => "The MP3 file is processing",
                    );
                }
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
                if ($duration == "EE:EE:EE" && !empty($pathsLower['url'])) {
                    $duration = getDurationFromFile($pathsLower['url']);
                    if ($duration == "EE:EE:EE") {
                        $pathsLower['url'] = str_replace('_Low.mp3', '.mp3', $pathsLower['url']);
                        $duration = getDurationFromFile($pathsLower['url']);
                    }
                }

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
            $f = convertVideoFileWithFFMPEGIsLockedInfo($arrayRegular['paths']['path']);
            _error_log("convertVideoFileWithFFMPEGIsLockedInfo({$arrayRegular['paths']['path']}) arrayRegular " . json_encode($f));
            if (!$f['isUnlocked']) {
                $msg = "The original audio is processing";
            } else {
                $f = convertVideoFileWithFFMPEGIsLockedInfo($arrayLower['paths']['path']);
                _error_log("convertVideoFileWithFFMPEGIsLockedInfo({$arrayLower['paths']['path']}) arrayLower " . json_encode($f));
                if (!$f['isUnlocked']) {
                    $msg = "The audio is processing";
                } else {
                    $diff = abs($arrayRegular['durationInSeconds'] - $arrayLower['durationInSeconds']);
                    if ($diff <= 2) {
                        $isValid = true;
                    } else {
                        $msg = "durationInSeconds are not the same regular={$arrayRegular['durationInSeconds']} lower={$arrayLower['durationInSeconds']}";
                    }
                }
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
                if (file_exists($mp3s['lower']['paths']['path']) && filesize($mp3s['lower']['paths']['path']) > 20) {
                    _error_log('getLowerMP3: unlink line=' . __LINE__);
                    unlink($mp3s['lower']['paths']['path']);
                }
                $fromFileLocationEscaped = escapeshellarg($mp3s['regular']['paths']['path']);
                $toFileLocationEscaped = escapeshellarg($mp3s['lower']['paths']['path']);
                $command = get_ffmpeg() . " -i {$fromFileLocationEscaped} -ar 16000 -ac 1 -b:a 64k {$toFileLocationEscaped}";
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
        if (AI::canUseAI()) {
            $btn .= '<button type="button" ' .
                ' class="btn btn-default btn-light btn-sm btn-xs btn-block" ' .
                ' onclick="avideoModalIframe(webSiteRootURL+\\\'plugin/AI/page.php?videos_id=\'+row.id+\'\\\');" >' .
                ' <i class="fas fa-robot"></i> ' . __("AI-Powered") . '</button>';
        }

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

    function executeCutVideo($row)
    {
        $ai = new Ai_scheduler($row['id']);
        $ai->setStatus(Ai_scheduler::$statusExecuted);
        $ai->save();
        $obj = _json_decode($ai->getJson());
        if (preg_match('/[0-9]{2}:[0-9]{2}:[0-9]{2}/i', $obj->endTimeInSeconds)) {
            $obj->endTimeInSeconds = durationToSeconds($obj->endTimeInSeconds);
        }
        if (preg_match('/[0-9]{2}:[0-9]{2}:[0-9]{2}/i', $obj->startTimeInSeconds)) {
            $obj->startTimeInSeconds = durationToSeconds($obj->startTimeInSeconds);
        }
        $aspectRatio = Video::ASPECT_RATIO_HORIZONTAL;
        if (!empty($obj->aspectRatio)) {
            $aspectRatio = $obj->aspectRatio;
        }

        _error_log('AI:videoCut start ' . $ai->getJson() . "{$obj->startTimeInSeconds} => {$obj->endTimeInSeconds}");
        $vid = Video::getVideoLight($obj->videos_id);
        $sources = getVideosURLOnly($vid['filename'], false);
        if(!empty($sources['m3u8'])){
            $source = $sources['m3u8'];
        }else{
            $source = end($sources);
        }
        if (!empty($source)) {
            _error_log('AI:videoCut source found  ' . $source["url"]);
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
                _error_log('AI:videoCut new video saved videos_id=' . $newVideos_id);
                $outputFile = Video::getPathToFile("{$videoFileName}.mp4");
                cutVideoWithFFmpeg($source['url'], $obj->startTimeInSeconds, $obj->endTimeInSeconds, $outputFile, $aspectRatio);

                $video = new Video('', '', $newVideos_id);
                if (file_exists($outputFile)) {
                    $video->setAutoStatus(Video::STATUS_ACTIVE);
                    AVideoPlugin::onUploadIsDone($newVideos_id);
                    AVideoPlugin::afterNewVideo($newVideos_id);
                    _error_log('AI:videoCut create file success  ' . $outputFile);
                    $url = Video::getURL($newVideos_id);
                    $obj->socketResponse = sendSocketSuccessMessageToUsers_id("<a href='$url'>Video cutted</a>", $obj->users_id);
                } else {
                    $video->delete(true);
                    _error_log('AI:videoCut error on create file  ' . $outputFile);
                }
            }
            //cutVideoWithFFmpeg($source['url'], $startTimeInSeconds, $endTimeInSeconds, $outputFile);
        }
    }

    static function chargeUser($type, $users_id, $videos_id)
    {
        $price = 0;
        $obj = AVideoPlugin::getObjectData('AI');

        switch ($type) {
            case AI::$typeBasic:
                $price = $obj->priceForBasic;
                break;
            case AI::$typeTranscription:
                $price = $obj->priceForTranscription;
                break;
            case AI::$typeTranslation:
                $price = $obj->priceForTranslation;
                break;
            case AI::$typeShorts:
                $price = $obj->priceForShorts;
                break;
            case AI::$typeDubbing:
                $video = new Video('', '', $videos_id);
                $duration_in_seconds = $video->getDuration_in_seconds();
                if(empty($duration_in_seconds)){
                    _error_log("The video {$videos_id} has not duration set, the price will be calculated over 10 minutes", AVideoLog::$ERROR);
                    $duration_in_seconds = 600; // 10 minutes
                }
                $price = $obj->priceForDubbing * $duration_in_seconds;
                break;
        }
        if (empty($price)) {
            _error_log("AI:asyncVideosId there is no price set for it");
            return true;
        }

        $objWallet = AVideoPlugin::getObjectDataIfEnabled('YPTWallet');
        if (empty($objWallet)) {
            _error_log("AI:asyncVideosId the wallet is disabled");
            return true;
        }

        $description = "AI-powered [{$type}] for videos id {$videos_id}";
        return YPTWallet::transferBalanceToSiteOwner($users_id, $price, $description);
    }

    static function asyncVideosIdAndSendSocketMessage($videos_id, $type, $users_id)
    {
        $objResp = AI::asyncVideosId($videos_id, $type, $users_id);
        if ($objResp->error) {
            sendSocketErrorMessageToUsers_id($objResp->msg, $users_id);
        } else {
            sendSocketSuccessMessageToUsers_id($objResp->msg, $users_id);
        }
    }

    static function asyncVideosId($videos_id, $type, $users_id)
    {
        global $global;

        if (!self::chargeUser($type, $users_id, $videos_id)) {
            _error_log("AI:asyncVideosId error the user $users_id has no balance to pay the service $type for videos_id $videos_id ");
            $obj = new stdClass();
            $obj->error = true;
            $obj->msg = "Transaction failed: Insufficient funds.
            You currently do not have enough balance in your account to cover the AI-powered video $type service.
            Please add funds to proceed. Thank you.";
            return $obj;
        }

        ini_set('max_execution_time', 600);
        _error_log("AI:asyncVideosId start $videos_id, $type");
        $param = array();
        switch ($type) {
            case AI::$typeBasic:
                _error_log('AI:asyncVideosId ' . basename(__FILE__) . ' line=' . __LINE__);
                $obj = AI::getVideoBasicMetadata($videos_id);
                break;
            case AI::$typeTranscription:
                _error_log('AI:asyncVideosId ' . basename(__FILE__) . ' line=' . __LINE__);
                $obj = AI::getVideoTranscriptionMetadata($videos_id, @$_REQUEST['language']);
                break;
            case AI::$typeTranslation:
                _error_log('AI:asyncVideosId ' . basename(__FILE__) . ' line=' . __LINE__);
                $obj = AI::getVideoTranslationMetadata($videos_id, $_REQUEST['lang'], $_REQUEST['langName']);
                $param['lang'] = $_REQUEST['lang'];
                break;
            case AI::$typeShorts:
                _error_log('AI:asyncVideosId ' . basename(__FILE__) . ' line=' . __LINE__);
                $obj = AI::getVideoShortsMetadata($videos_id);
                break;
            case AI::$typeDubbing:
                _error_log('AI:asyncVideosId ' . basename(__FILE__) . ' line=' . __LINE__);
                $obj = AI::getVideoDubbingMetadata($videos_id, @$_REQUEST['language']);
                break;
            case AI::$typeImage:
                _error_log('AI:asyncVideosId typeImage ' . basename(__FILE__) . ' line=' . __LINE__);
                $obj = AI::getVideoBasicMetadata($videos_id);
                break;
            default:
                _error_log('AI:asyncVideosId ' . basename(__FILE__) . ' line=' . __LINE__);
                $obj = new stdClass();
                $obj->error = true;
                $obj->msg = "Undefined type {$type}";
                return $obj;
                break;
        }
        if ($obj->error) {
            $obj2 = new stdClass();
            $obj2->error = true;
            $obj2->msg = 'Something happen: ' . $obj->msg;
            _error_log('AI:asyncVideosId ERROR ' . basename(__FILE__) . ' line=' . __LINE__ . ' ' . json_encode($obj));
            return $obj2;
        }

        $objAI = AVideoPlugin::getDataObjectIfEnabled('AI');

        $json = $obj->response;
        $json['AccessToken'] = $objAI->AccessToken;
        $json['isTest'] = AI::$isTest ? 1 : 0;
        $json['webSiteRootURL'] = $global['webSiteRootURL'];
        $json['PlatformId'] = getPlatformId();
        $json['videos_id'] = $videos_id;
        $json['type'] =  $type;

        _error_log('AI:asyncVideosId ' . basename(__FILE__) . ' line=' . __LINE__);
        $aiURLProgress = AI::getMetadataURL();
        $aiURLProgress = "{$aiURLProgress}progress.json.php";

        $content = postVariables($aiURLProgress, $json, false, 600);

        if (empty($content)) {
            $obj = new stdClass();
            $obj->error = true;
            $obj->msg = "Could not post to {$aiURLProgress} => {$content}";
            return $obj;
        }
        $jsonProgressDecoded = json_decode($content);

        if (empty($jsonProgressDecoded)) {
            $obj = new stdClass();
            $obj->error = true;
            $obj->msg = "Could not decode => {$content}";
            return $obj;
        }
        _error_log('AI:asyncVideosId ' . basename(__FILE__) . ' line=' . __LINE__);
        if (empty($jsonProgressDecoded->canRequestNew)) {
            $obj = new stdClass();
            $obj->error = true;
            $obj->msg = $jsonProgressDecoded->msg;
            $obj->jsonProgressDecoded = $jsonProgressDecoded;
            if (empty($obj->msg)) {
                $obj->msg =  "A process for Video ID {$videos_id} is currently active and in progress.";;
            }
            return $obj;
        }
        _error_log('AI:asyncVideosId ' . basename(__FILE__) . ' line=' . __LINE__);

        $o = new Ai_responses(0);
        $o->setVideos_id($videos_id);
        $Ai_responses_id = $o->save();
        $json['token'] = AI::getTokenForVideo($videos_id, $Ai_responses_id, $param);

        $aiURL = AI::getMetadataURL();
        $aiURL = "{$aiURL}async.json.php";
        $content = postVariables($aiURL, $json, false, 600);
        $jsonDecoded = json_decode($content);

        _error_log('AI:asyncVideosId ' . basename(__FILE__) . ' line=' . __LINE__);
        if (empty($content)) {
            $obj = new stdClass();
            $obj->error = true;
            $obj->msg = "Oops! Our system took a bit longer than expected to process your request.
            Please try again in a few moments. We apologize for any inconvenience and appreciate your patience.";
            return $obj;
        }

        _error_log('AI:asyncVideosId ' . basename(__FILE__) . ' line=' . __LINE__);
        if (empty($jsonDecoded)) {
            $jsonDecoded = new stdClass();
            $jsonDecoded->error = true;
            $jsonDecoded->msg = "Some how we got an error in the response";
            $jsonDecoded->content = $content;
            return $obj;
        }

        _error_log('AI:asyncVideosId ' . basename(__FILE__) . ' line=' . __LINE__);
        $jsonDecoded->aiURL = $aiURL;

        $o = new Ai_responses($Ai_responses_id);
        $o->setElapsedTime($jsonDecoded->elapsedTime);
        $o->setPrice($jsonDecoded->payment->howmuch);
        $jsonDecoded->Ai_responses = $o->save();

        return $jsonDecoded;
    }

    static function AIAlreadyHave($videos_id, $type)
    {
        //_error_log("AI::AIAlreadyHave($videos_id, $type)");
        switch ($type) {
            case AI::$typeBasic:
                $rows = Ai_metatags_responses::getAllFromVideosId($videos_id);
                return !empty($rows);
                break;
            case AI::$typeTranslation:
            case AI::$typeTranscription:
                $trascription = Ai_responses::getTranscriptionVtt($videos_id);
                return !empty($trascription);
                break;
            case AI::$typeShorts:
                $rows = Ai_responses_json::getAllFromAIType(AI::$typeShorts, $videos_id);
                $responses = array();
                foreach ($rows as $key => $value) {
                    if (!empty($value['response'])) {
                        $response = json_decode($value['response']);
                        foreach ($response->shorts as $key2 => $shorts) {
                            foreach ($shorts as $key3 => $short) {
                                $ShortsDuration = $short->endTimeInSeconds - $short->startTimeInSeconds;
                                if ($ShortsDuration < 15 || $ShortsDuration > 60) {
                                    continue;
                                }
                                $responses[] = $short;
                            }
                        }
                    }
                }
                return !empty($responses);
                break;
            default:
                return false;
                break;
        }
    }

    static function processTranscription($row)
    {
        $ai = new Ai_scheduler($row['id']);
        $obj = _json_decode($ai->getJson());
        $mp3s = AI::getLowerMP3($obj->videos_id);
        if (empty($mp3s['isValid'])) {
            _error_log('AI::processTranscription error mp3 is invalid ' . json_encode($mp3s));
            return false;
        }
        if (!AI::AIAlreadyHave($obj->videos_id, AI::$typeTranscription)) {
            if ($ai->getStatus() === Ai_scheduler::$statusProcessingTranscription) {
                _error_log('AI::processTranscription is processing transcription');
            } else {
                _error_log('AI::processTranscription start now');
                $ai->setStatus(Ai_scheduler::$statusProcessingTranscription);
                $ai->save();
                AI::asyncVideosIdAndSendSocketMessage($obj->videos_id, AI::$typeTranscription, $obj->users_id);
            }
            return false;
        } else {
            _error_log('AI::processTranscription exists ');
            return true;
        }
    }

    static function processShorts($row)
    {
        if (!self::processTranscription($row)) {
            _error_log('AI::processShorts processTranscription is required');
            return false;
        }
        $ai = new Ai_scheduler($row['id']);
        $obj = _json_decode($ai->getJson());
        if (!AI::AIAlreadyHave($obj->videos_id, AI::$typeShorts)) {
            if ($ai->getStatus() === Ai_scheduler::$statusProcessingShort) {
                _error_log('AI::processShorts is processing Short');
            } else {
                _error_log('AI::processShorts start now');
                $ai->setStatus(Ai_scheduler::$statusProcessingShort);
                $ai->save();
                AI::asyncVideosIdAndSendSocketMessage($obj->videos_id, AI::$typeShorts, $obj->users_id);
            }
            return false;
        } else {
            return true;
        }
    }

    static function processBasic($row)
    {
        if (!self::processTranscription($row)) {
            _error_log('AI::processBasic processTranscription is required');
            return false;
        }
        $ai = new Ai_scheduler($row['id']);
        $obj = _json_decode($ai->getJson());
        if (!AI::AIAlreadyHave($obj->videos_id, AI::$typeBasic)) {
            if ($ai->getStatus() === Ai_scheduler::$statusProcessingBasic) {
                _error_log('AI::processBasic is processing Basic');
            } else {
                _error_log('AI::processBasic start now');
                $ai->setStatus(Ai_scheduler::$statusProcessingBasic);
                $ai->save();
                AI::asyncVideosIdAndSendSocketMessage($obj->videos_id, AI::$typeBasic, $obj->users_id);
            }
            return false;
        } else {
            return true;
        }
    }

    static function processAll($row)
    {
        $isComplete = false;
        $p1 = self::processTranscription($row);
        if ($p1) {
            $p2 = self::processBasic($row);
            if ($p2) {
                $p3 = self::processShorts($row);
                if ($p3) {
                    $isComplete = true;
                }
            }
        }

        if ($isComplete) {
            $ai = new Ai_scheduler($row['id']);
            $ai->setStatus(Ai_scheduler::$statusExecuted);
            $ai->save();
        }
    }

    function executeEveryMinute()
    {
        $rows = Ai_scheduler::getAllToExecute();
        if (!empty($rows) && is_array($rows)) {
            foreach ($rows as $row) {
                $ai = new Ai_scheduler($row['id']);
                if ($ai->getAi_scheduler_type() === Ai_scheduler::$typeCutVideo) {
                    $this->executeCutVideo($row);
                } else if ($ai->getAi_scheduler_type() === Ai_scheduler::$typeProcessAll) {
                    AI::processAll($row);
                } else {
                    $ai->setStatus(Ai_scheduler::$statusError);
                    $ai->save();
                }
            }
        }
    }

    public function afterNewVideo($videos_id)
    {
        $obj = $this->getDataObject();
        $obj2 = new stdClass();
        $obj2->videos_id = $videos_id;
        $obj2->error = true;
        $obj2->msg = '';
        if (!empty($obj->autoProcessAll)) {
            $users_id = Video::getOwner($videos_id);
            $obj2 = Ai_scheduler::saveToProcessAll($videos_id, $users_id);
        }

        return false;
    }

    function getPermissionsOptions()
    {
        $permissions = array();

        $permissions[] = new PluginPermissionOption(self::PERMISSION_CAN_USE_AI_SUGGESTIONS, __("Can use AI Suggestions"), "Members of the designated user group will have access to AI suggestions and requests. Monetization options are available, as outlined here: <a href='https://github.com/WWBN/AVideo/wiki/AI-Plugin#monetization-and-pricing' target='_blank'>AI Plugin Monetization and Pricing</a>.", 'AI');
        return $permissions;
    }

    static function canUseAI()
    {
        if (User::isAdmin() || isCommandLineInterface()) {
            return true;
        }

        return Permissions::hasPermission(self::PERMISSION_CAN_USE_AI_SUGGESTIONS, 'AI');;
    }
}
