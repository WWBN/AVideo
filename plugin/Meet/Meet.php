<?php
use Firebase\JWT\JWT;

$JIBRI_INSTANCE = 0;
require_once $global['systemRootPath'] . 'objects/autoload.php';
//require_once $global['systemRootPath'] . 'objects/firebase/php-jwt/src/JWT.php';
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/Meet/Objects/Meet_schedule.php';
require_once $global['systemRootPath'] . 'plugin/Meet/Objects/Meet_schedule_has_users_groups.php';
require_once $global['systemRootPath'] . 'plugin/Meet/Objects/Meet_join_log.php';
User::loginFromRequestIfNotLogged();

//require_once $global['systemRootPath'] . 'objects/firebase/php-jwt/src/JWT.php';
//use \Firebase\JWT\JWT;
class Meet extends PluginAbstract
{
    const PERMISSION_CAN_CREATE_MEET = 0;

    public function getTags()
    {
        return [
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE,
            PluginTags::$LIVE,
        ];
    }

    public function getPluginVersion()
    {
        return "3.0";
    }

    public function updateScript()
    {
        global $global;
        if (AVideoPlugin::compareVersion($this->getName(), "3.0") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Meet/install/updateV3.0.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
        }
        return true;
    }

    public function getDescription()
    {
        $txt = "AVideo Meet/Conference software";
        return $txt;
    }

    public function getName()
    {
        return "Meet";
    }

    public function getUUID()
    {
        return "meet225-3807-4167-ba81-0509dd280e06";
    }

    public function getEmptyDataObject()
    {
        global $global;
        $obj = new stdClass();
        $obj->secret = md5($global['systemRootPath'] . $global['salt'] . "meet");
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "{UserName} is inviting you to a meeting.

Topic: {topic}

Join Meeting
{meetLink}

Passcode: {password}
";
        $obj->invitation = $o;

        $o = new stdClass();
        $o->type = [
            'ca1.ypt.me' => "North America 1",
            //'eu1.ypt.me' => "Europe 1",
            'custom' => "Custom Jitsi",
            'ca2.ypt.me' => "Test Server do not use it",];
        $o->value = 'ca1.ypt.me';
        $obj->server = $o;

        $obj->CUSTOM_JITSI_DOMAIN = "jitsi.ca1.ypt.me";
        $obj->JWT_APP_ID = "my_jitsi_app_id";
        $obj->JWT_APP_SECRET = "my_jitsi_app_secret";
        $obj->hideTopButton = true;
        $obj->buttonTitle = 'Meet';
        self::addDataObjectHelper('hideTopButton', 'Hide Top Button', 'This will hide the button on the top menu bar');
        return $obj;
    }

    public static function getTokenArray($meet_schedule_id, $users_id = 0)
    {
        global $config;
        $obj = AVideoPlugin::getDataObject("Meet");
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        $m = new Meet_schedule($meet_schedule_id);
        $room = $m->getCleanName();

        $isModerator = self::isModerator($meet_schedule_id);

        if (empty($users_id)) {
            $user = [
                "name" => '',
                "email" => '',
                "affiliation"=> ($isModerator?'owner':'member'),
            ];
        } else {
            $u = new User($users_id);
            $user = [
                "avatar" => $u->getPhotoDB(),
                "name" => $u->getNameIdentificationBd(),
                "email" => $u->getEmail(),
                "id" => $users_id,
                "affiliation"=> ($isModerator?'owner':'member'),
            ];
        }
        if(!$isModerator){
            $user['lobby'] = true;
        }

        if(!empty($_REQUEST['debug'])){
            var_dump($user);
            exit;
        }

        $jitsiPayload = [
            "context" => [
                "user" => $user,
                "group" => $config->getWebSiteTitle(),
            ],
            "aud" => self::getAUD(),
            "iss" => self::getISS(),
            "sub" => "meet.jitsi",
            "room" => $room,
            "exp" => strtotime("+30 hours"),
            "nbf" => strtotime("-24 hours"),
            "moderator" => $isModerator,
        ];
        return $jitsiPayload; // HS256
    }

    public static function getToken($meet_schedule_id, $users_id = 0)
    {
        $m = new Meet_schedule($meet_schedule_id);
        $jitsiPayload = self::getTokenArray($meet_schedule_id, $users_id);
        $key = self::getSecret();
        //var_dump($jitsiPayload, $key);

        return JWT::encode($jitsiPayload, $key, 'HS256'); // HS256
    }

    public static function getSecret()
    {
        $obj = AVideoPlugin::getDataObject("Meet");
        if ($obj->server->value == 'custom') {
            if ($obj->JWT_APP_SECRET == 'my_jitsi_app_secret') {
                return $obj->secret;
            } else {
                return $obj->JWT_APP_SECRET;
            }
        } else {
            return $obj->secret;
        }
    }

    public static function getAPPID()
    {
        $obj = AVideoPlugin::getDataObject("Meet");
        if ($obj->server->value == 'custom') {
            if ($obj->JWT_APP_ID == 'my_jitsi_app_id') {
                return "avideo";
            } else {
                return $obj->JWT_APP_ID;
            }
        } else {
            return "avideo";
        }
    }

    public static function getISS()
    {
        $obj = AVideoPlugin::getDataObject("Meet");
        if ($obj->server->value == 'custom') {
            if ($obj->JWT_APP_ID == 'my_jitsi_app_id') {
                return "*";
            } else {
                return $obj->JWT_APP_ID;
            }
        } else {
            return "*";
        }
    }

    public static function getAUD()
    {
        $obj = AVideoPlugin::getDataObject("Meet");
        if ($obj->server->value == 'custom') {
            if ($obj->JWT_APP_ID == 'my_jitsi_app_id') {
                return "avideo";
            } else {
                return $obj->JWT_APP_ID;
            }
        } else {
            return "avideo";
        }
    }

    public static function getMeetServer()
    {
        $obj = AVideoPlugin::getDataObject("Meet");
        return "https://{$obj->server->value}/";
    }

    public function getPluginMenu()
    {
        global $global;
        return '<button onclick="avideoModalIframe(webSiteRootURL +\'plugin/Meet/checkServers.php\');" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fas fa-network-wired"></i> Check Servers</button>';
    }

    public static function getMeetServerStatus($cache = 30)
    {
        global $global;
        $secret = self::getSecret();
        $meetServer = self::getMeetServer();

        if(preg_match('/192.168.1/', $global['webSiteRootURL'])){
            $json = new stdClass();
            $json->error = false;
            $json->url = $global['webSiteRootURL'];
            $json->isInstalled = true;
            $json->msg = $global['webSiteRootURL'];
            $json->host = "custom";
            $json->jibrisInfo = new stdClass();
            $json->jibrisInfo->jibris = [];
            return $json;
        }

        if ($meetServer == "https://custom/") {
            $obj = AVideoPlugin::getDataObject("Meet");
            $json = new stdClass();
            $json->error = false;
            $json->url = $obj->CUSTOM_JITSI_DOMAIN;
            $json->isInstalled = true;
            $json->msg = $obj->CUSTOM_JITSI_DOMAIN;
            $json->host = "custom";
            $json->jibrisInfo = new stdClass();
            $json->jibrisInfo->jibris = [];
            return $json;
        }
        $name = "getMeetServerStatus{$global['webSiteRootURL']}{$secret}{$meetServer}";
        $json = new stdClass();
        $json->content = ObjectYPT::getCache($name, $cache);
        if (!empty($json->content) && !empty($json->time)) {
            $json = _json_decode($json->content);
            $json->msg = "From Cache";
        } else {
            $url = $meetServer . "api/checkMeet.json.php?webSiteRootURL=" . urlencode($global['webSiteRootURL']) . "&secret=" . $secret;
            _error_log("serverlabels getMeetServerStatus $url ");
            $content = url_get_contents($url, '', 10, !empty($_REQUEST['debug']));
            _error_log("serverlabels getMeetServerStatus done $url ");

            $json = _json_decode($content);
            if (!empty($json)) {
                $json->time = time();
                $json->url = $url;
                $json->content = $content;
                if (empty($json->error) && $json->isInstalled) {
                    ObjectYPT::setCache($name, json_encode($json));
                    if (empty($json->msg)) {
                        $json->msg = "Just create Cache";
                    }
                } else {
                    if (empty($json->msg)) {
                        $json->msg = "Error did not create Cache";
                    }
                }
            } else {
                $json = new stdClass();
                $json->time = time();
                $json->error = true;
                $json->msg = "Error we could not check your server";
            }
        }
        $json->when = humanTimingAgo($json->time);
        return $json;
    }

    public static function getDomain()
    {
        $json = self::getMeetServerStatus();
        if (empty($json) || empty($json->host) || empty($json->isInstalled)) {
            return false;
        }
        if ($json->host == 'custom') {
            return "custom";
        }
        $obj = AVideoPlugin::getDataObject("Meet");
        return "{$json->host}.{$obj->server->value}";
    }

    public static function getDomainURL()
    {
        $meetDomain = self::getDomain();
        if ($meetDomain == 'custom') {
            $obj = AVideoPlugin::getDataObject("Meet");
            $domain = $obj->CUSTOM_JITSI_DOMAIN;
        } else {
            $domain = $meetDomain;
        }


        return $domain;
    }

    public static function getJoinURL()
    {
        $domain = self::getDomainURL();
        $url = "https://" . $domain . "/";
        //$url = str_replace('ca2.ypt.me', 'ca1.ypt.me', $url);
        return $url;
    }

    public static function getRoomID($meet_schedule_id)
    {
        $roomName = "";
        $m = new Meet_schedule($meet_schedule_id);
        if (empty($m->getUsers_id())) {
            return $roomName;
        }
        if (!empty($meet_schedule_id)) {
            $roomName .= $m->getCleanName();
        }

        $token = self::getToken($meet_schedule_id);
        $roomName .= "?jwt={$token}";

        $obj = new stdClass();
        if (class_exists("Live")) {
            $obj->getRTMPLink = Live::getRTMPLink($m->getUsers_id());
        }
        $obj->shareLink = Meet::getMeetShortLink($meet_schedule_id);

        $roomName .= "&json=" . urlencode(json_encode($obj));

        return $roomName;
    }

    public static function isCustomJitsi()
    {
        $json = self::getMeetServerStatus();
        if (empty($json) || empty($json->host) || empty($json->isInstalled)) {
            return true;
        }
        if ($json->host == 'custom') {
            return true;
        }
        return false;
    }

    public static function validateRoomName($room)
    {
        return cleanURLName(ucwords($room));
    }

    public static function createRoomName($topic, $users_id = 0)
    {
        if (empty($users_id)) {
            if (User::isLogged()) {
                $identification = User::getNameIdentification();
            }
        } else {
            $identification = User::getNameIdentificationById($users_id);
        }
        if (empty($identification)) {
            die("User could not be identified");
        }

        $roomName = $identification . "-" . $topic;

        return self::validateRoomName($roomName);
    }

    public function getHTMLMenuRight()
    {
        global $global;
        $obj = $this->getDataObject();
        if ($obj->hideTopButton) {
            return '';
        }
        if (!User::isLogged()) {
            return "";
        }
        return '<li>
        <a href="' . $global['webSiteRootURL'] . 'plugin/Meet/"  class="btn btn-default navbar-btn" data-toggle="tooltip" title="' . __('Meet') . '" data-placement="bottom" >
            <i class="fas fa-comments"></i>  <span class="hidden-md hidden-sm hidden-mdx">' . __('Meet') . '</span>
        </a>
    </li>';
    }

    public static function getMeetLink($meet_schedule_id)
    {
        if (empty($meet_schedule_id)) {
            return false;
        }
        $ms = new Meet_schedule($meet_schedule_id);

        return $ms->getMeetLink();
    }

    public static function getMeetShortLink($meet_schedule_id)
    {
        if (empty($meet_schedule_id)) {
            return false;
        }
        $ms = new Meet_schedule($meet_schedule_id);

        return $ms->getMeetShortLink();
    }

    public static function canManageSchedule($meet_schedule_id)
    {
        if (empty($meet_schedule_id)) {
            return false;
        }
        $meet = new Meet_schedule($meet_schedule_id);
        if ($meet->canManageSchedule()) {
            return true;
        }
    }

    public static function canJoinMeet($meet_schedule_id)
    {
        $obj = self::canJoinMeetWithReason($meet_schedule_id);
        return $obj->canJoin;
    }

    public static function canJoinMeetWithReason($meet_schedule_id)
    {
        $obj = new stdClass();
        $obj->canJoin = false;
        $obj->reason = "";


        if (User::isAdmin()) {
            $obj->canJoin = true;
            $obj->reason = "Is Admin";
            return $obj;
        }
        $meet = new Meet_schedule($meet_schedule_id);
        if (User::getId() == $meet->getUsers_id()) {
            $obj->canJoin = true;
            $obj->reason = "Is the meet owner";
            return $obj;
        }
        /**
         * Public = 2
         * Logged Users Only = 1
         * Specific User Groups = 0
         * @return string
         */
        $time = secondsIntervalFromNow($meet->getStarts(), $meet->getTimezone());
        if (empty($meet->getStarts()) || $time>0) {
            // means public
            if ($meet->getPublic() == "2") {
                $obj->canJoin = true;
                $obj->reason = "Is public";
                return $obj;
            } elseif ($meet->getPublic() == "1") {
                $obj->canJoin = User::isLogged();
                $obj->reason = $obj->canJoin ? "Is logged" : "Must be logged to be able to join";
                return $obj;
            } else {
                $obj->canJoin = self::userGroupMatch($meet_schedule_id, User::getId());
                $obj->reason = $obj->canJoin ? "The user group match" : "Must be on the usergroup to be able to join";
                return $obj;
            }
        } else {
            $obj->reason = "The meet does not start yet {$meet->getStarts()} {$meet->getTimezone()} " . humanTimingAfterwards($meet->getStarts(), 2, $meet->getTimezone());
            return $obj;
        }
    }

    public static function isModerator($meet_schedule_id)
    {
        if (empty($meet_schedule_id)) {
            return false;
        }
        if (!User::isLogged()) {
            return false;
        }
        if (User::isAdmin()) {
            return true;
        }
        $meet = new Meet_schedule($meet_schedule_id);
        if (User::getId() == $meet->getUsers_id()) {
            return true;
        }
        return false;
    }

    public static function getButtons($meet_schedule_id)
    {
        /*
          return [
          'microphone', 'camera', 'closedcaptions', 'desktop', 'embedmeeting', 'fullscreen',
          'fodeviceselection', 'hangup', 'profile', 'chat', 'recording',
          'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
          'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts',
          'tileview', 'videobackgroundblur', 'download', 'help', 'mute-everyone', 'security'
          ];
         *
         */
        if (self::isModerator($meet_schedule_id)) {
            if (self::hasJibris() || self::isCustomJitsi()) {
                $return = [
                    'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'profile', 'chat',
                    'livestreaming', 'recording','etherpad', 'settings', 'raisehand',
                    'videoquality', 'filmstrip', 'feedback', 'stats', 'shortcuts',
                    'tileview', 'download', 'help', 'mute-everyone', 'mute-video-everyone', 'videobackgroundblur','select-background','whiteboard',
                    'noisesuppression','participants-pane','reactions','toggle-camera',
                    'invite', 'security', 'isCustomJitsi', 'shareaudio', 'sharedvideo'
                ];
            } else {
                $return = [
                    'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'profile', 'chat',
                    'etherpad', 'settings', 'raisehand',
                    'videoquality', 'filmstrip', 'feedback', 'stats', 'shortcuts',
                    'tileview', 'download', 'help', 'mute-everyone', 'mute-video-everyone','videobackgroundblur','select-background','whiteboard',
                    'noisesuppression','participants-pane','reactions','toggle-camera',
                    'invite', 'security', 'isNOTCustomJitsi', 'shareaudio', 'sharedvideo'
                ];
            }
        } else {
            $return = [
                'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                'fodeviceselection', 'hangup', 'profile', 'chat', 'etherpad', 'settings', 'raisehand',
                'videoquality', 'filmstrip', 'feedback', 'stats', 'shortcuts',
                'tileview', 'download', 'help', 'mute-everyone', 'mute-video-everyone', 'videobackgroundblur','select-background',
                'noisesuppression','toggle-camera',
            ];
        }

        foreach ($return as $key => $value) {
            if(isset($_REQUEST[$value]) && (empty($_REQUEST[$value]) || strtolower($_REQUEST[$value]) === 'false')){
                unset($return[$key]);
            }
        }
        return array_values($return);
    }

    public static function hasJibris()
    {
        $serverStatus = Meet::getMeetServerStatus();
        return count($serverStatus->jibrisInfo->jibris);
    }

    public static function userGroupMatch($meet_schedule_id, $users_id)
    {
        global $global;

        if (User::isAdmin()) {
            return true;
        }
        if (User::isLogged()) {
            require_once $global['systemRootPath'] . 'objects/userGroups.php';
            $userGroups = UserGroups::getUserGroups(User::getId());
            $meetGroups = Meet_schedule_has_users_groups::getAllFromSchedule($meet_schedule_id);
            foreach ($userGroups as $value) {
                foreach ($meetGroups as $value2) {
                    if ($value['id'] == $value2['users_groups_id']) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public static function getServer()
    {
        $m = AVideoPlugin::loadPlugin("Meet");
        $pObj = AVideoPlugin::getDataObject("Meet");
        $obj = $m->getEmptyDataObject();
        $obj->server->type = object_to_array($obj->server->type);
        return ["name" => $obj->server->type[$pObj->server->value], "domain" => $pObj->server->value];
    }

    public static function createJitsiButton($title, $svg, $onclick, $class = "", $style = "", $id = "")
    {
        global $global;
        if (empty($id)) {
            $id = "avideoMeet" . uniqid();
        }
        $svgContent = file_get_contents($global['systemRootPath'] . 'plugin/Meet/buttons/' . $svg);
        $btn = '<div class="toolbox-button aVideoMeet ' . $class . '" tabindex="0" role="button" onclick="' . $onclick . '" id="' . $id . '" style="' . $style . '">'
                . '<div class="tooltip" style="display:none; position: absolute; bottom: 70px;background-color: rgb(13, 20, 36); padding: 5px; border-radius: 4px; font-weight: bold; color: #909eb5; height: 10px; line-height: normal;">' . $title . '</div>'
                . '<div class="toolbox-icon">'
                . '<div class="jitsi-icon">' . $svgContent . '</div>'
                . '</div>'
                . '</div>'
                . '<script>'
                . '$(function () {
    $("#' . $id . '").on("mouseenter",
        function () {
            $(this).find(".tooltip").fadeIn();
    });
    $("#' . $id . '").on("mouseleave",
        function () {
            $(this).find(".tooltip").fadeOut();
    });
});'
                . '</script>';
        return $btn;
    }

    public static function createJitsiRecordStartStopButton($rtmpLink, $dropURL)
    {
        $start = self::createJitsiButton(__("Go Live"), "startLive.svg", "aVideoMeetStartRecording('$rtmpLink','$dropURL');", "hideOnLive");
        $stop = self::createJitsiButton(__("Stop Live"), "stopLive.svg", "aVideoMeetStopRecording('$rtmpLink','$dropURL');", "showOnLive", "display:none;");
        return $start . $stop;
    }

    public static function getInvitation($meet_schedule_id)
    {
        $objM = AVideoPlugin::getObjectDataIfEnabled("Meet");
        $ms = new Meet_schedule($meet_schedule_id);
        $invitation = $objM->invitation->value;
        $topic = $ms->getTopic();
        if (User::isAdmin() || User::getId() == $ms->getUsers_id()) {
            $pass = $ms->getPassword();
        }
        if (empty($topic)) {
            $invitation = preg_replace("/(\n|\r)[^\n\r]*{topic}[^\n\r]*(\n|\r)/i", "", $invitation);
        } else {
            $invitation = preg_replace("/{topic}/i", $topic, $invitation);
        }

        if (empty($pass)) {
            $invitation = preg_replace("/(\n|\r)[^\n\r]*{password}[^\n\r]*(\n|\r)/i", "", $invitation);
        } else {
            $invitation = preg_replace("/{password}/i", $pass, $invitation);
        }

        $invitation = preg_replace("/{UserName}/i", User::getNameIdentificationById($ms->getUsers_id()), $invitation);
        $invitation = preg_replace("/{meetLink}/i", $ms->getMeetLink(), $invitation);
        return $invitation;
    }

    public static function getIframeURL($meet_schedule_id){
        $joinURL = self::getJoinURL();
        $joinURL = addLastSlash($joinURL);
        $roomID = self::getRoomID($meet_schedule_id);
        return "{$joinURL}{$roomID}";
    }

    public static function validatePassword($meet_schedule_id, $password)
    {
        if (User::isAdmin() || self::isModerator($meet_schedule_id)) {
            return true;
        }
        $meet = new Meet_schedule($meet_schedule_id);
        if (!empty($meet->getPassword())) {
            //var_dump($meet_schedule_id, $meet->getPassword());exit;
            if (empty($_SESSION['user']['meet_password'][$meet_schedule_id])) {
                if (!empty($password) && $meet->getPassword() == $password) {
                    _session_start();
                    $_SESSION['user']['meet_password'][$meet_schedule_id] = 1;
                    return true;
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    public function getUploadMenuButton()
    {
        global $global;
        if (!User::isLogged()) {
            return '';
        }
        $obj = $this->getDataObject();
        $buttonTitle = $obj->buttonTitle;
        include $global['systemRootPath'] . 'plugin/Meet/getUploadMenuButton.php';
    }

    function getPermissionsOptions()
    {
        $permissions = array();

        $permissions[] = new PluginPermissionOption(self::PERMISSION_CAN_CREATE_MEET, __("Can create meetings"), "Members of the designated user group will have access create meetings", 'Meet');
        return $permissions;
    }

    static function canCreateMeet()
    {

        if (User::isAdmin() || isCommandLineInterface()) {
            return true;
        }

        if(!User::isLogged()){
            return false;
        }

        return !empty($_SESSION['user']['canCreateMeet']) || Permissions::hasPermission(self::PERMISSION_CAN_CREATE_MEET, 'Meet');
    }
}
