<?php

global $global;
require_once $global['systemRootPath'] . 'objects/ICS.php';
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/Scheduler/Objects/Scheduler_commands.php';
require_once $global['systemRootPath'] . 'plugin/Scheduler/Objects/Emails_messages.php';
require_once $global['systemRootPath'] . 'plugin/Scheduler/Objects/Email_to_user.php';

class Scheduler extends PluginAbstract
{

    public function getDescription()
    {
        global $global;
        $desc = "Scheduler Plugin";
        $desc = '<br><a href="https://github.com/WWBN/AVideo/wiki/Scheduler-Plugin" target="_blank"><i class="fas fa-question-circle"></i> Help</a>';
        if (!_isSchedulerPresentOnCrontab()) {
            $desc = "<strong onclick='tooglePluginDescription(this);'>";
            $desc .= self::getCronHelp();
        }
        $desc .= '<br>';
        $desc .= getIncludeFileContent($global['systemRootPath'] . 'plugin/Scheduler/View/activeLabel.php');
        //$desc .= $this->isReadyLabel(array('YPTWallet'));
        return $desc;
    }

    static function getCronHelp()
    {
        global $global;
        $desc = "To use the Scheduler Plugin, you MUST add it on your crontab";
        $desc .= "</strong>";
        $desc .= "<br>Open a terminal and type <code>crontab -e</code> than add a crontab for every 1 minute<br><code>* * * * * php {$global['systemRootPath']}plugin/Scheduler/run.php</code>";
        return $desc;
    }

    public function getName()
    {
        return "Scheduler";
    }

    public function getUUID()
    {
        return "Scheduler-5ee8405eaaa16";
    }

    public function getPluginVersion()
    {
        return "5.1";
    }

    public function getEmptyDataObject()
    {
        $obj = new stdClass();

        $obj->watchDogSocket = true;
        $obj->watchDogLiveServer = true;
        $obj->watchDogLiveServerSSL = true;
        $obj->sendEmails = true;
        $obj->deleteOldUselessVideos = true;

        $obj->disableReleaseDate = false;
        self::addDataObjectHelper('disableReleaseDate', 'Disable Release Date');

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

    static function sendEmails()
    {
        $obj = AVideoPlugin::getDataObjectIfEnabled('Scheduler');
        if ($obj->sendEmails) {
            $messages = Email_to_user::getAllEmailsToSend();
            $total = count($messages);
            if ($total > 0) {
                echo 'Scheduler::sendEmails found ' . count($messages) . PHP_EOL;
                foreach ($messages as $value) {
                    $to = explode(',', $value['emails']);
                    // Make sure the emails in $to are unique
                    $to = array_unique($to);

                    $subject = $value['subject'];
                    $message = $value['message'];
                    echo "Scheduler::sendEmails [{$subject}] found emails " . count($to) . PHP_EOL;
                    //var_dump($to);
                    sendSiteEmailAsync($to, $subject, $message);
                    $ids = explode(',', $value['ids']);
                    Email_to_user::setSent($ids);
                }
            }
        }
    }

    public function getPluginMenu()
    {
        global $global;
        $btn = '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/Scheduler/View/editor.php\')" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';
        $btn .= '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/Scheduler/run.php\')" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fas fa-terminal"></i> Run now</button>';
        return $btn;
    }

    static public function run($scheduler_commands_id)
    {
        global $_executeSchelude, $global;
        _error_log("Scheduler::run {$scheduler_commands_id}");
        if (!isset($_executeSchelude)) {
            $_executeSchelude = array();
        }
        $e = new Scheduler_commands($scheduler_commands_id);

        $videos_id = $e->getVideos_id();
        if (!empty($videos_id)) { // make it active
            $response = self::releaseVideosNow($videos_id);
            if (!$response) {
                _error_log("Scheduler::run error on release video {$videos_id} ");
                return false;
            } else {
                return $e->setExecuted(array('videos_id' => $videos_id, 'response' => $response));
            }
        }

        $type = $e->getType();
        $parameters = $e->getParameters();
        if ($type == 'SocketRestart') {
            if (AVideoPlugin::isEnabledByName('YPTSocket')) {
                YPTSocket::restart();
                $json = _json_decode($parameters);
                $users_id = $json->users_id;
                _error_log("Scheduler::SocketRestart users_id={$users_id}");
                //sleep(5);
                //YPTSocket::send('Socket restarted', "", $users_id);
                return $e->setExecuted(array('YPTSocket' => time()));
            }
        }

        $callBackURL = $e->getCallbackURL();
        $callBackURL = str_replace('{webSiteRootURL}', $global['webSiteRootURL'], $callBackURL);
        if (!isValidURL($callBackURL)) {
            return false;
        }
        if (empty($_executeSchelude[$callBackURL])) {
            $callBackURL = addQueryStringParameter($callBackURL, 'token', getToken(60));
            $callBackURL = addQueryStringParameter($callBackURL, 'scheduler_commands_id', $scheduler_commands_id);
            _error_log("Scheduler::run getting callback URL {$callBackURL}");
            $_executeSchelude[$callBackURL] = url_get_contents($callBackURL, '', 30);
            _error_log("Scheduler::run got callback " . json_encode($_executeSchelude[$callBackURL]));
            $json = _json_decode($_executeSchelude[$callBackURL]);
            if (is_object($json) && !empty($json->error)) {
                _error_log("Scheduler::run callback ERROR " . json_encode($json));
                return false;
            }
        }
        if (!empty($_executeSchelude[$callBackURL])) {
            return $e->setExecuted($_executeSchelude[$callBackURL]);
        }
        return false;
    }

    static function isActiveFromVideosId($videos_id)
    {
        return Scheduler_commands::isActiveFromVideosId($videos_id);;
    }

    static public function addVideoToRelease($date_to_execute, $time_to_execute, $videos_id)
    {
        _error_log("Scheduler::addVideoToRelease [$date_to_execute] [$videos_id]");
        if (empty($date_to_execute)) {
            _error_log("Scheduler::addVideoToRelease ERROR date_to_execute is empty");
            return false;
        }

        $date_to_execute_time = _strtotime($date_to_execute);

        if ($date_to_execute_time <= time()) {
            _error_log("Scheduler::addVideoToRelease ERROR date_to_execute must be greater than now [{$date_to_execute}] " . date('Y/m/d H:i:s', $date_to_execute_time) . ' ' . date('Y/m/d H:i:s'));
            return false;
        }

        if (empty($videos_id)) {
            _error_log("Scheduler::addVideoToRelease ERROR videos_id is empty");
            return false;
        }

        $id = 0;
        $row = Scheduler_commands::getFromVideosId($videos_id);
        if (!empty($row)) {
            $id = $row['id'];
        }

        $e = new Scheduler_commands($id);
        $e->setTime_to_execute($time_to_execute);
        $e->setDate_to_execute($date_to_execute);
        $e->setVideos_id($videos_id);

        return $e->save();
    }

    static public function add($date_to_execute, $callbackURL, $parameters = '', $type = '')
    {
        _error_log("Scheduler::add [$date_to_execute] [$callbackURL]");
        if (empty($date_to_execute)) {
            _error_log("Scheduler::add ERROR date_to_execute is empty");
            return false;
        }

        $date_to_execute_time = _strtotime($date_to_execute);

        if ($date_to_execute_time <= time()) {
            _error_log("Scheduler::add ERROR date_to_execute must be greater than now [{$date_to_execute}] " . date('Y/m/d H:i:s', $date_to_execute_time) . ' ' . date('Y/m/d H:i:s'));
            return false;
        }

        if (empty($callbackURL)) {
            _error_log("Scheduler::add ERROR callbackURL is empty");
            return false;
        }
        $e = new Scheduler_commands(0);
        $e->setDate_to_execute($date_to_execute);
        $e->setCallbackURL($callbackURL);
        if (!empty($parameters)) {
            $e->setParameters($parameters);
        }
        if (!empty($type)) {
            $e->setType($type);
        }
        return $e->save();
    }

    static public function addSendEmail($date_to_execute, $emailTo, $emailSubject, $emailEmailBody, $emailFrom = '', $emailFromName = '', $type = '')
    {
        global $global;
        $parameters = array(
            'emailSubject' => $emailSubject,
            'emailEmailBody' => $emailEmailBody,
            'emailTo' => $emailTo,
            'emailFrom' => $emailFrom,
            'emailFromName' => $emailFromName,
        );
        //var_dump($parameters);exit;
        $url = "{webSiteRootURL}plugin/Scheduler/sendEmail.json.php";

        if (empty($type)) {
            $type = 'SendEmail';
        }

        $scheduler_commands_id = Scheduler::add($date_to_execute, $url, $parameters, $type);
        return $scheduler_commands_id;
    }

    static public function getReminderOptions(
        $destinationURL,
        $title,
        $date_start,
        $selectedEarlierOptions = array(),
        $date_end = '',
        $joinURL = '',
        $description = '',
        $earlierOptions = array(
            '10 minutes earlier' => 10,
            '30 minutes earlier' => 30,
            '1 hour earlier' => 60,
            '2 hours earlier' => 120,
            '1 day earlier' => 1440,
            '2 days earlier' => 2880,
            '1 week earlier' => 10080
        )
    ) {
        global $global;
        $varsArray = array(
            'destinationURL' => $destinationURL,
            'title' => $title,
            'date_start' => $date_start,
            'selectedEarlierOptions' => $selectedEarlierOptions,
            'date_end' => $date_end,
            'joinURL' => $joinURL,
            'description' => $description,
            'earlierOptions' => $earlierOptions
        );
        $filePath = "{$global['systemRootPath']}plugin/Scheduler/reminderOptions.php";
        return getIncludeFileContent($filePath, $varsArray);
    }

    /**
     *
     * @global array $global
     * @param string $title
     * @param string $description
     * @param string $date_start
     * @param string $date_end
     *
     *  description - string description of the event.
        dtend - date/time stamp designating the end of the event. You can use either a DateTime object or a PHP datetime format string (e.g. "now + 1 hour").
        dtstart - date/time stamp designating the start of the event. You can use either a DateTime object or a PHP datetime format string (e.g. "now + 1 hour").
        location - string address or description of the location of the event.
        summary - string short summary of the event - usually used as the title.
        url - string url to attach to the the event. Make sure to add the protocol (http:// or https://).
     */
    static public function downloadICS($title, $date_start, $date_end = '', $reminderInMinutes = '', $joinURL = '', $description = '')
    {
        global $global, $config;
        //var_dump(date_default_timezone_get());exit;
        header('Content-Type: text/calendar; charset=utf-8');
        if (empty($_REQUEST['open'])) {
            $ContentDisposition = 'attachment';
        } else {
            $ContentDisposition = 'inline';
        }

        $filename = cleanURLName("{$title}-{$date_start}");

        header("Content-Disposition: {$ContentDisposition}; filename={$filename}.ics");
        $location = $config->getWebSiteTitle();
        if (!isValidURL($joinURL)) {
            $joinURL = $global['webSiteRootURL'];
        }

        if (empty($description)) {
            $description = $location;
        }

        $date_start = _strtotime($date_start);
        $date_end = _strtotime($date_end);

        if (empty($date_end) || $date_end <= $date_start) {
            $date_end = strtotime(date('Y/m/d H:i:s', $date_start) . ' + 1 hour');
        }
        $dtstart = date('Y/m/d H:i:s', $date_start);
        $dtend = date('Y/m/d H:i:s', $date_end);
        $reminderInMinutes = intval($reminderInMinutes);
        if (!empty($reminderInMinutes)) {
            $VALARM = "-P{$reminderInMinutes}M";
        } else {
            $VALARM = '';
        }

        $props = array(
            'location' => $location,
            'description' => $description,
            'dtstart' => $dtstart,
            'dtend' => $dtend,
            'summary' => $title,
            'url' => $joinURL,
            'valarm' => $VALARM,
            //'X-WR-TIMEZONE' => date_default_timezone_get()
        );
        $ics = new ICS($props);
        //var_dump($props);
        $icsString = $ics->to_string();

        header('content-length: ' . strlen($icsString));
        echo $icsString;
    }



    public static function getManagerVideosAddNew()
    {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/Scheduler/getManagerVideosAddNew.js';
        return file_get_contents($filename);
    }

    public static function getManagerVideosEdit()
    {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/Scheduler/getManagerVideosEdit.js';
        return file_get_contents($filename);
    }

    public static function getManagerVideosEditField($type = 'Advanced')
    {
        global $global;
        if ($type == 'Advanced') {
            include $global['systemRootPath'] . 'plugin/Scheduler/managerVideosEdit.php';
        }
        return '';
    }

    public static function releaseVideosNow($videos_id)
    {
        if (empty($videos_id)) {
            return false;
        }
        if (!Video::canEdit($videos_id) && !isCommandLineInterface()) {
            return false;
        }
        global $advancedCustom;
        if (empty($advancedCustom)) {
            $advancedCustom = AVideoPlugin::getDataObject('CustomizeAdvanced');
        }

        $video = new Video('', '', $videos_id);
        $row = Scheduler_commands::getFromVideosId($videos_id);
        if (!empty($row)) {
            $e = new Scheduler_commands($row['id']);
            $e->setExecuted($videos_id);
        }

        //$status = $video->setStatus(Video::$statusActive);
        $status = $video->setStatus($advancedCustom->defaultVideoStatus->value);

        return $status;
    }

    private static function convertIfTimezoneIsPassed($releaseDate)
    {
        if (empty($releaseDate)) {
            return $releaseDate;
        }
        if (empty($_REQUEST['timezone'])) {
            return $releaseDate;
        }
        return convertDateFromToTimezone($releaseDate, $_REQUEST['timezone'], date_default_timezone_get());
    }

    public static function saveVideosAddNew($post, $videos_id)
    {
        return self::addNewVideoToRelease($videos_id, @$post['releaseDate'], @$post['releaseDateTime'], @$post['releaseTime']);
    }

    public function afterNewVideo($videos_id)
    {
        return self::addNewVideoToRelease($videos_id, @$_REQUEST['releaseDate'], @$_REQUEST['releaseDateTime'], @$_REQUEST['releaseTime']);
    }

    public static function addNewVideoToRelease($videos_id, $releaseDate, $releaseDateTime = '', $releaseTime = '')
    {
        if (!empty($releaseDate)) {
            if ($releaseDate !== 'now') {
                if (empty($releaseTime) || !is_numeric($releaseTime)) {
                    if ($releaseDate == 'in-1-hour') {
                        $releaseTime = strtotime('+1 hour');
                    } else if (!empty($releaseDateTime)) {
                        $releaseDateTime = self::convertIfTimezoneIsPassed($releaseDateTime);
                        $releaseTime = _strtotime($releaseDateTime);
                    } else {
                        $releaseDate = self::convertIfTimezoneIsPassed($releaseDate);
                        $releaseTime = _strtotime($releaseDate);
                    }
                }
                $video = new Video('', '', $videos_id);
                if ($releaseTime > time()) {
                    $releaseDateTime = date('Y-m-d H:i:s', $releaseTime);
                    $video->setStatus(Video::$statusScheduledReleaseDate);
                    self::setReleaseDateTime($videos_id, $releaseDateTime, $releaseTime);
                    self::addVideoToRelease($releaseDateTime, $releaseTime, $videos_id);
                    return true;
                } else if ($video->getStatus() == Video::$statusScheduledReleaseDate) {
                    self::releaseVideosNow($videos_id);
                }
            }
        }
        return false;
    }

    public static function setReleaseDateTime($videos_id, $releaseDateTime, $releaseTime)
    {
        if (!Video::canEdit($videos_id)) {
            return false;
        }
        $video = new Video('', '', $videos_id, true);
        $externalOptions = _json_decode($video->getExternalOptions());
        if (empty($externalOptions)) {
            $externalOptions = new stdClass();
        }
        $externalOptions->releaseTime = $releaseTime;
        $externalOptions->releaseDateTime = $releaseDateTime;
        $externalOptions->releaseDateTimeZone = date_default_timezone_get();
        $video->setExternalOptions(json_encode($externalOptions));
        return $video->save();
    }

    public static function getReleaseDateTime($videos_id)
    {
        $video = new Video('', '', $videos_id);
        $externalOptions = _json_decode($video->getExternalOptions());
        if (empty($externalOptions) || !is_object($externalOptions)) {
            $externalOptions = new stdClass();
        }
        if (empty($externalOptions->releaseDateTimeZone)) {
            return $externalOptions->releaseDateTime;
        }

        return convertDateFromToTimezone($externalOptions->releaseDateTime, $externalOptions->releaseDateTimeZone, date_default_timezone_get());
    }

    public static function getLastVisitFile()
    {
        $lastVisitFile = getVideosDir() . 'cache/schedulerLastVisit.log';
        return $lastVisitFile;
    }

    public static function setLastVisit()
    {
        $lastVisitFile = self::getLastVisitFile();
        if (_file_put_contents($lastVisitFile, time())) {
            @chmod($lastVisitFile, 0777);
            $size = filesize($lastVisitFile);
            if (empty($size)) {
                _error_log('setLastVisit error on create file ' . $lastVisitFile);
            }
            return array('file' => $lastVisitFile, 'size' => $size);
        } else {
            return false;
        }
    }

    public static function getLastVisit()
    {
        $lastVisitFile = self::getLastVisitFile();
        if (empty($lastVisitFile) || !file_exists($lastVisitFile)) {
            return 0;
        }
        return file_get_contents($lastVisitFile);
    }

    public static function whyIsActive()
    {
        $lastVisitTime = self::getLastVisit();
        if (empty($lastVisitTime)) {
            $lastVisitFile = self::getLastVisitFile();
            return "Time is not found in the file {$lastVisitFile}";
        }

        $TwoMinutes = 120;
        $time = time();
        $result = $lastVisitTime + $TwoMinutes - $time;
        if ($result > 0) {
            return "Last visit time is older then 2 minutes lastVisitTime=$lastVisitTime (" . date('H:i:s', $lastVisitTime) . "), time=$time (" . date('H:i:s', $time) . ")";
        }

        return '';
    }

    public static function isActive()
    {
        $lastVisitTime = self::getLastVisit();
        if (empty($lastVisitTime)) {
            return false;
        }

        $TenMinutes = 600;

        $result = $lastVisitTime + $TenMinutes - time();
        return $result > 0;
    }

    function executeEveryMinute()
    {
        $rows = Video::getAllVideosLight(Video::$statusScheduledReleaseDate);
        foreach ($rows as $key => $value) {
            $releaseDate = self::getReleaseDateTime($value['id']);
            if (empty($releaseDate) || strtotime($releaseDate) <= time()) {
                $response = self::releaseVideosNow($value['id']);
                if (!$response) {
                    _error_log("Scheduler::run error on release video {$value['id']} ");
                } else {
                    _error_log("Scheduler::run release video {$value['id']} ");
                }
            }
        }
    }

    function executeEveryDay()
    {
        global $global;
        $obj = AVideoPlugin::getDataObject('Scheduler');
        if (!empty($obj->deleteOldUselessVideos)) {
            Video::deleteUselessOldVideos(30);
        }

        // Run the function to delete files older than 7 days from /var/www/tmp
        $this->deleteOldFiles();
        self::manageLogFile();
    }

    function deleteOldFiles($directory = '/var/www/tmp', $days = 7)
    {
        global $global;
        // Check if the directory exists
        if (!is_dir($directory)) {
            _error_log("Directory does not exist: $directory");
            return false;
        }

        // Get current time
        $now = time();

        // Define the time limit in seconds (days * 24 * 60 * 60)
        $timeLimit = $days * 24 * 60 * 60;

        execAsync("php {$global['systemRootPath']}install/cleanup_systemd-private.php");

        // Open the directory
        if ($handle = opendir($directory)) {
            // Loop through each file in the directory
            while (false !== ($file = readdir($handle))) {
                // Skip "." and ".."
                if ($file === '.' || $file === '..') {
                    continue;
                }

                // Full path to the file
                $filePath = $directory . '/' . $file;

                // Check if it is a file and not a directory
                if (is_file($filePath)) {
                    // Get the file's modification time
                    $fileModTime = filemtime($filePath);

                    // If the file is older than the defined time limit, delete it
                    if ($now - $fileModTime > $timeLimit) {
                        if (unlink($filePath)) {
                            _error_log("Deleted old file: $filePath");
                        } else {
                            _error_log("Failed to delete file: $filePath");
                        }
                    }
                }
            }
            // Close the directory
            closedir($handle);
        } else {
            _error_log("Failed to open directory: $directory");
            return false;
        }

        return true;
    }

    static function manageLogFile()
    {
        global $global;
        $logFilePath = $global['logfile'];

        // Ensure the logfile is not empty and has a .log extension
        if (empty($logFilePath)) {
            _error_log("manageLogFile: Log file path is empty; no action required.");
            return;
        }

        if (pathinfo($logFilePath, PATHINFO_EXTENSION) !== 'log') {
            _error_log("manageLogFile: Log file path is not a .log file; no action required. [$logFilePath]");
            return;
        }

        // Get yesterday's date
        $yesterdayDate = date('Y-m-d', strtotime('-1 day'));

        // Define the new logfile name with yesterday's date
        $newLogFileName = $logFilePath . '.' . $yesterdayDate . '.log';

        // Check if the current logfile exists
        if (file_exists($logFilePath)) {
            // Get the original log file size
            $originalSize = filesize($logFilePath);

            // Ensure log content is preserved before rotation
            if (!file_exists($newLogFileName)) {
                if (copy($logFilePath, $newLogFileName)) {
                    $copiedSize = filesize($newLogFileName);

                    // Log file sizes for verification
                    _error_log("manageLogFile: Log file successfully copied to: $newLogFileName (Original size: $originalSize bytes, Copied size: $copiedSize bytes)");

                    // Verify file sizes match
                    if ($originalSize !== $copiedSize) {
                        _error_log("manageLogFile: WARNING - File size mismatch after copying! Original: $originalSize bytes, Copied: $copiedSize bytes");
                        return; // Stop execution if copy failed
                    }
                } else {
                    _error_log("manageLogFile: Failed to copy log file to: $newLogFileName");
                    return; // Stop execution if copying failed
                }
            } else {
                _error_log("manageLogFile: Log file already exists, skipping copy: $newLogFileName");
            }

            // Clear the original log file only if copy was successful and verified
            if (file_put_contents($logFilePath, "") !== false) {
                _error_log("manageLogFile: Log file successfully cleared: $logFilePath");
            } else {
                _error_log("manageLogFile: Failed to clear log file: $logFilePath");
                return; // Stop execution if clearing failed
            }
        } else {
            _error_log("manageLogFile: Log file does not exist: $logFilePath");
        }

        // Ensure a new empty logfile is created
        if (!file_exists($logFilePath)) {
            if (touch($logFilePath)) {
                _error_log("manageLogFile: New log file created: $logFilePath");

                // Ensure Apache/PHP can write to the new log file
                if (chmod($logFilePath, 0666)) {
                    _error_log("manageLogFile: Permissions set to 0666 for: $logFilePath");
                } else {
                    _error_log("manageLogFile: Failed to set permissions for: $logFilePath");
                }
            } else {
                _error_log("manageLogFile: Failed to create new log file: $logFilePath");
            }
        }

        // Delete log files older than 30 days in the same directory
        $logDir = dirname($logFilePath);
        $files = glob($logDir . '/*.log'); // Get all .log files in the directory

        $thirtyDaysAgo = time() - (30 * 24 * 60 * 60); // Timestamp for 30 days ago

        foreach ($files as $file) {
            if (filemtime($file) < $thirtyDaysAgo) {
                if (unlink($file)) {
                    _error_log("manageLogFile: Deleted old log file: $file");
                } else {
                    _error_log("manageLogFile: Failed to delete old log file: $file");
                }
            }
        }
    }
}
