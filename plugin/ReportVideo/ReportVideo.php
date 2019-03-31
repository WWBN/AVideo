<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/ReportVideo/Objects/videos_reported.php';

class ReportVideo extends PluginAbstract {

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
        return "1.0";   
    }
    
    public function getTags() {
        return array('free', 'buttons', 'report');
    }

    public function getWatchActionButton($videos_id) {
        global $global, $video;
        include $global['systemRootPath'] . 'plugin/ReportVideo/actionButton.php';
    }

    function send($email, $subject, $body) {
        if (empty($email)) {
            return false;
        }

        global $global, $config;

        require_once $global['systemRootPath'] . 'objects/PHPMailer/PHPMailerAutoload.php';

        //Create a new PHPMailer instance
        $mail = new PHPMailer;
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
            error_log("Notification email sent [{$subject}]");
            return true;
        } else {
            error_log("Notification email FAIL [{$subject}] - " . $mail->ErrorInfo);
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
                
                if(!$videoOwnerSent && !$siteOwnerSent){
                    $resp->msg = __("We could not notify anyone ({$email}, {$siteOwnerEmail}), but we marked it as a inapropriated");
                }else if(!$videoOwnerSent){
                    $resp->msg = __("We could not notify the video owner {$email}, but we marked it as a inapropriated");
                }else if(!$siteOwnerSent){
                    $resp->msg = __("We could not notify the video owner {$siteOwnerEmail}, but we marked it as a inapropriated");
                }else{
                    $resp->error = false;
                    $resp->msg = __("This video was reported to our team, we will review it soon");
                }
            }else{                
                $resp->msg  = __("Error on report this video");
            }
        } else {
            $resp->msg  = __("You already report this video");
        }
        if($resp->error === true){
            error_log("Report Video: ". $resp->msg);
        }
        return $resp;
    }

}
