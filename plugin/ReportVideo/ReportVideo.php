<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/ReportVideo/Objects/videos_reported.php';

class ReportVideo extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE,
        );
    }
    public function getDescription() {
        return "Create a button to report videos with inapropriate content";
    }

    public function getName() {
        return "ReportVideo";
    }

    public function getUUID() {
        return "b5e223db-785b-4436-8f7b-f297860c9be0";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->emailLogo = "";

        return $obj;
    }

    public function getPluginVersion() {
        return "2.1";
    }

    public function updateScript() {
        global $global;
        //update version 2.0
        if (AVideoPlugin::compareVersion($this->getName(), "2.0") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/ReportVideo/install/updateV2.0.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
        }
        //update version 2.1
        if (AVideoPlugin::compareVersion($this->getName(), "2.1") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/ReportVideo/install/updateV2.1.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
        }
        return true;
    }
    public function getWatchActionButton($videos_id) {
        global $global, $video;
        if (!isVideo()) {
            return '';
        }
        if (empty($video['id']) && empty($videos_id)) {
            return '';
        }
        if (empty($video['id'])) {
            $video['id'] = intval($videos_id);
        }
        include $global['systemRootPath'] . 'plugin/ReportVideo/actionButton.php';

        if (isVideo() && !empty($video['users_id'])) {
            $users_id = $video['users_id'];
            echo self::actionButtonBlockUser($users_id);
        }
    }

    function send($email, $subject, $body) {
        if (empty($email)) {
            return false;
        }

        global $global, $config;

        require_once $global['systemRootPath'] . 'objects/PHPMailer/src/PHPMailer.php';
        require_once $global['systemRootPath'] . 'objects/PHPMailer/src/SMTP.php';
        require_once $global['systemRootPath'] . 'objects/PHPMailer/src/Exception.php';

        //Create a new PHPMailer instance
        $mail = new PHPMailer\PHPMailer\PHPMailer;
        setSiteSendMessage($mail);
        //Set who the message is to be sent from
        $mail->setFrom($config->getContactEmail(), $config->getWebSiteTitle());
        //Set who the message is to be sent to
        $mail->addAddress($email);
        //$mail->addAddress($config->getContactEmail());
        //Set the subject line
        $mail->Subject = $subject;
        $mail->msgHTML($body);

        //send the message, check for errors
        if ($mail->send()) {
            _error_log("Notification email sent [{$subject}]");
            return true;
        } else {
            _error_log("Notification email FAIL [{$subject}] - " . $mail->ErrorInfo);
            return false;
        }
    }

    private function replaceText($users_id, $videos_id, $text) {

        $user = new User($users_id);
        $userName = $user->getNameIdentification();

        $video = new Video("", "", $videos_id);
        $videoName = $video->getTitle();
        $videoLink = Video::getPermaLink($videos_id);

        $words = array($userName, $videoName, $videoLink);
        $replace = array('{user}', '{videoName}', '{videoLink}');

        return str_replace($replace, $words, $text);
    }

    private function getTemplateText($videos_id, $message) {
        global $global, $config;
        $obj = $this->getDataObject();
        $text = file_get_contents("{$global['systemRootPath']}plugin/ReportVideo/template.html");
        $video = new Video("", "", $videos_id);
        $videoName = $video->getTitle();
        $images = Video::getImageFromFilename($video->getFilename());
        $videoThumbs = "<img src='{$images->thumbsJpg}'/>";
        $videoLink = Video::getPermaLink($videos_id);
        $logo = "<img src='{$obj->emailLogo}'/>";
        $siteTitle = $config->getWebSiteTitle();
        $footer = "";

        $words = array($logo, $videoName, $videoThumbs, $videoLink, $siteTitle, $footer, $message);
        $replace = array('{logo}', '{videoName}', '{videoThumbs}', '{videoLink}', '{siteTitle}', '{footer}', '{message}');

        return str_replace($replace, $words, $text);
    }

    function report($users_id, $videos_id) {
        global $global, $config;
        // check if this user already report this video
        $report = VideosReported::getFromDbUserAndVideo($users_id, $videos_id);
        $resp = new stdClass();
        $resp->error = true;
        $resp->msg = "Report not made";

        if (empty($report)) {
            //save it on the database
            $reportObj = new VideosReported(0);
            $reportObj->setUsers_id($users_id);
            $reportObj->setVideos_id($videos_id);
            if ($reportObj->save()) {
                $body = $this->getTemplateText($videos_id, $this->replaceText($users_id, $videos_id, "The <a href='{videoLink}'>{videoName}</a> video was reported as inapropriate from {user} "));
                $subject = $this->replaceText($users_id, $videos_id, "The {videoName} video was reported as inapropriate");
                // notify video owner from user id
                $user = new User($users_id);
                $email = $user->getEmail();
                $videoOwnerSent = $this->send($email, $subject, $body);

                // notify site owner from configuratios email
                $siteOwnerEmail = $config->getContactEmail();
                $siteOwnerSent = $this->send($siteOwnerEmail, $subject, $body);

                if (!$videoOwnerSent && !$siteOwnerSent) {
                    $resp->msg = __("We could not notify anyone ({$email}, {$siteOwnerEmail}), but we marked it as a inapropriated");
                } else if (!$videoOwnerSent) {
                    $resp->msg = __("We could not notify the video owner {$email}, but we marked it as a inapropriated");
                } else if (!$siteOwnerSent) {
                    $resp->msg = __("We could not notify the video owner {$siteOwnerEmail}, but we marked it as a inapropriated");
                } else {
                    $resp->error = false;
                    $resp->msg = __("This video was reported to our team, we will review it soon");
                }
            } else {
                $resp->msg = __("Error on report this video");
            }
        } else {
            $resp->msg = __("You already report this video");
        }
        if ($resp->error === true) {
            _error_log("Report Video: " . $resp->msg);
        }
        return $resp;
    }

    function block($users_id, $reported_users_id) {
        global $global, $config;
        // check if this user already report this video
        $report = VideosReported::getFromDbUserAndReportedUser($users_id, $reported_users_id);
        $resp = new stdClass(); 
        $resp->error = true;
        $resp->msg = "Block not made";

        if (empty($report)) {
            //save it on the database
            $reportObj = new VideosReported(0);
            $reportObj->setUsers_id($users_id);
            $reportObj->setReported_users_id($reported_users_id);
            if ($reportObj->save()) {
                $resp->msg = "";
                $resp->error = false;
            } else {
                $resp->msg = __("Error on block this user");
            }
        } else {
            $resp->msg = __("User Already blocked");
        }
        if ($resp->error === true) {
            _error_log("Block User: " . $resp->msg);
        }
        return $resp;
    }

    function unBlock($users_id, $reported_users_id) {
        global $global, $config;
        // check if this user already report this video
        $report = VideosReported::getFromDbUserAndReportedUser($users_id, $reported_users_id);
        $resp = new stdClass();
        $resp->error = true;
        $resp->msg = "Block not made";

        if (!empty($report)) {
            //save it on the database
            $reportObj = new VideosReported($report['id']);
            if ($reportObj->delete()) {
                $resp->msg = "";
                $resp->error = false;
            } else {
                $resp->msg = __("Error on unblock this user");
            }
        } else {
            $resp->msg = __("User Already unblocked");
        }
        if ($resp->error === true) {
            _error_log("Block user: " . $resp->msg);
        }
        return $resp;
    }

    static function isBlocked($reported_users_id, $users_id = 0) {
        global $global, $config;
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        $users_id = intval($users_id);
        // check if this user already report this video
        $reportedUsersId = VideosReported::getAllReportedUsersIdFromUser($users_id);
        return in_array($reported_users_id, $reportedUsersId);
    }

    static function getAllReportedUsersIdFromUser($users_id=0) {
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        $users_id = intval($users_id);
        return VideosReported::getAllReportedUsersIdFromUser($users_id);
    }

    static function buttonBlockUser($users_id) {
        if ($users_id == User::getId()) {
            return '';
        }
        global $global, $config;
        $variable = ob_get_clean();
        ob_start();
        include $global['systemRootPath'] . 'plugin/ReportVideo/buttonBlockUser.php';
        $button = ob_get_clean();
        ob_start();
        echo $variable;
        return $button;
    }

    static function actionButtonBlockUser($users_id) {
        if ($users_id == User::getId()) {
            return '';
        }
        global $global, $config;
        $variable = ob_get_clean();
        ob_start();
        include $global['systemRootPath'] . 'plugin/ReportVideo/actionButtonBlockUser.php';
        $button = ob_get_clean();
        ob_start();
        echo $variable;
        return $button;
    }

}
