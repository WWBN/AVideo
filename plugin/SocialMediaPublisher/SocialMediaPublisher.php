<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_social_medias.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_user_preferences.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_video_publisher_logs.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_schedule.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/SocialUploader.php';

class SocialMediaPublisher extends PluginAbstract
{

    const SOCIAL_TYPE_YOUTUBE = array(
        'name' => 'youtube',
        'label' => 'YouTube',
        'ico' => '<i class="fab fa-youtube"></i>',
        'iconClass' => 'icoYoutube',
    );
    const SOCIAL_TYPE_FACEBOOK = array(
        'name' => 'facebook',
        'label' => 'Facebook',
        'ico' => '<i class="fab fa-facebook-f"></i>',
        'iconClass' => 'icoFacebook',
    );
    const SOCIAL_TYPE_INSTAGRAM = array(
        'name' => 'instagram',
        'label' => 'Instagram',
        'ico' => '<i class="fab fa-instagram"></i>',
        'iconClass' => 'icoInstagram',
    );
    const SOCIAL_TYPE_TWITCH = array(
        'name' => 'twitch',
        'label' => 'Twitch',
        'ico' => '<i class="fab fa-twitch"></i>',
        'iconClass' => 'icoTwitch',
    );
    const SOCIAL_TYPE_LINKEDIN = array(
        'name' => 'linkedin',
        'label' => 'LinkedIn',
        'ico' => '<i class="fab fa-linkedin-in"></i>',
        'iconClass' => 'icoLinkedIn',
    );

    const SOCIAL_TYPES = array(
        self::SOCIAL_TYPE_YOUTUBE['name'] => self::SOCIAL_TYPE_YOUTUBE,
        self::SOCIAL_TYPE_FACEBOOK['name'] => self::SOCIAL_TYPE_FACEBOOK,
        self::SOCIAL_TYPE_INSTAGRAM['name'] => self::SOCIAL_TYPE_INSTAGRAM,
        //self::SOCIAL_TYPE_TWITCH['name'] => self::SOCIAL_TYPE_TWITCH,
        self::SOCIAL_TYPE_LINKEDIN['name'] => self::SOCIAL_TYPE_LINKEDIN,
    );

    const RESTREAMER_URL = 'https://restream.ypt.me/';
    //const RESTREAMER_URL = 'http://localhost:81/Restreamer/';
    //const RESTREAMER_URL = 'https://vlu.me:444/Restreamer/';

    public function getTags()
    {
        return [
            PluginTags::$FREE,
            //PluginTags::$RECOMMENDED,
            PluginTags::$UNDERDEVELOPMENT,
        ];
    }

    public function getDescription()
    {
        $desc = "SocialMediaPublisher Plugin";
        //$desc .= $this->isReadyLabel(array('YPTWallet'));
        //$help = "<br><small><a href='' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $desc;
    }

    public function getName()
    {
        return "SocialMediaPublisher";
    }

    public function getUUID()
    {
        return "SocialMediaPublisher-5ee8405eaaa16";
    }

    public function getPluginVersion()
    {
        return "1.0";
    }

    public function updateScript()
    {
        global $global;
        /*
        if (AVideoPlugin::compareVersion($this->getName(), "2.0") < 0) {
            sqlDal::executeFile($global['systemRootPath'] . 'plugin/PayPerView/install/updateV2.0.sql');
        }
         * 
         */
        return true;
    }

    public function getEmptyDataObject()
    {
        $obj = new stdClass();
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


    public function getPluginMenu()
    {
        global $global;
        return '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/SocialMediaPublisher/View/editor.php\')" class="btn btn-primary btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';
    }

    static function getOrCreateSocialMedia($provider)
    {
        $row = Publisher_social_medias::getFromProvider($provider);
        if (empty($row)) {
            $o = new Publisher_social_medias(@$_POST['id']);
            $o->setName($provider);
            $o->setStatus('a');
            $o->save();
            $row = Publisher_social_medias::getFromProvider($provider);
        }
        return $row;
    }

    static function getRevalidatedToken($publisher_user_preferences_id, $onlyIfIsExpired = false)
    {
        $resp = self::revalidateTokenAndSave($publisher_user_preferences_id, $onlyIfIsExpired);
        if ($resp->error) {
            //var_dump($resp->revalidateToken->accessToken->access_token);exit;
            if (!empty($resp->revalidateToken->accessToken->access_token)) {
                return $resp->revalidateToken->accessToken->access_token;
            }
            _error_log("getRevalidatedToken($publisher_user_preferences_id, $onlyIfIsExpired) ERROR " . json_encode($resp));
            return false;
        }
        return $resp->respJson->new_access_token->access_token;
    }

    static function revalidateToken($publisher_user_preferences_id, $onlyIfIsExpired = false)
    {

        $response = new stdClass();
        $response->publisher_user_preferences_id = $publisher_user_preferences_id;
        $response->error = true;
        $response->msg = '';

        $pub = Publisher_user_preferences::getFromDb($publisher_user_preferences_id);


        if (empty($pub)) {
            $response->msg = "revalidateToken($publisher_user_preferences_id) json is empty";
            return $response;
        }

        $json = json_decode($pub['json']);

        if (empty($json)) {
            $response->msg = "revalidateToken($publisher_user_preferences_id) json is empty {$pub['json']}";
            return $response;
        }

        $response->accessToken = $json->{"restream.ypt.me"}->accessToken;

        if (empty($response->accessToken)) {
            $response->msg = "revalidateToken($publisher_user_preferences_id) access_token is empty ";
            return $response;
        }

        if ($onlyIfIsExpired && !$pub['accessTokenExpired']) {
            $response->msg = "Not expired yet";
            return $response;
        }

        $access_token = base64_encode(json_encode($response->accessToken));

        $p = new Publisher_social_medias($pub['publisher_social_medias_id']);
        $response->provider = $p->getName();

        $url = SocialMediaPublisher::RESTREAMER_URL . 'refresh.json.php';
        $url = addQueryStringParameter($url, 'access_token', $access_token);
        $response->url = addQueryStringParameter($url, 'provider', $response->provider);
        //var_dump($pub['publisher_social_medias_id'], $response->provider, $url);exit;

        $response->resp = url_get_contents($response->url);
        if (empty($response->resp)) {
            $response->msg = "revalidateToken($publisher_user_preferences_id) response is empty";
            return $response;
        }

        $response->respJson = json_decode($response->resp);

        $response->error = empty($response->respJson) || $response->respJson->error;
        $response->msg = empty($response->respJson) ? 'Empty response' : $response->respJson->msg;

        return $response;
    }

    static function revalidateTokenAndSave($publisher_user_preferences_id, $onlyIfIsExpired = false)
    {
        $obj = new stdClass();
        $obj->revalidateToken = SocialMediaPublisher::revalidateToken($publisher_user_preferences_id, $onlyIfIsExpired);
        $obj->publisher_user_preferences_id = $publisher_user_preferences_id;
        $obj->error = $obj->revalidateToken->error;
        $obj->msg = $obj->revalidateToken->msg;

        if (empty($obj->error)) {
            $o = new Publisher_user_preferences($publisher_user_preferences_id);
            $json = json_decode($o->getJson());
            $json->{'restream.ypt.me'}->accessToken = $obj->revalidateToken->respJson->new_access_token;
            $json->{'restream.ypt.me'}->expires = $obj->revalidateToken->respJson->expires;

            $o->setJson($json);

            $obj->error = empty($o->save());
        }
        return $obj;
    }

    public static function upload($publisher_user_preferences_id, $videos_id)
    {
        $video = new Video('', '', $videos_id);
        //$paths = Video::getFirstSource($video->getFilename());
        $paths = getVideosURLMP4Only($video->getFilename());
        //var_dump($paths['mp4']);exit;
        if (empty($paths['mp4'])) {
            _error_log("SocialMediaPublisher::upload($publisher_user_preferences_id, $videos_id) video paths are empty");
            return false;
        }
        if (!file_exists($paths['mp4']['path'])) {
            _error_log("SocialMediaPublisher::upload($publisher_user_preferences_id, $videos_id) video path does not exist");
            return false;
        }

        $o = new Publisher_user_preferences($publisher_user_preferences_id);
        $publisher_social_medias_id = $o->getPublisher_social_medias_id();

        if (empty($publisher_social_medias_id)) {
            _error_log("SocialMediaPublisher::upload($publisher_user_preferences_id, $videos_id) publisher_social_medias_id not found");
            return false;
        }

        $providerName = $o->getProviderName();
        /*
        $fromFileLocation = $paths['mp4']['url'];
        if (!isDummyFile($paths['mp4']['path'])) {
            $videoPath = $paths['mp4']['path'];
        } else {
            $videoPathToYouTube = $videoPath = ($paths['mp4']['path'] . '.toYouTube.mp4');
        }
        if (!file_exists($videoPath)) {
            _error_log("SocialMediaPublisher::upload($publisher_user_preferences_id, $videos_id) $providerName start conversion ");
            $converted = convertVideoFileWithFFMPEG($fromFileLocation, $videoPath);
            _error_log("SocialMediaPublisher::upload($publisher_user_preferences_id, $videos_id) $providerName end conversion ");
        }
        _error_log("SocialMediaPublisher::upload($publisher_user_preferences_id, $videos_id) $providerName Upload start ");
        */
        if (!empty($_REQUEST['title'])) {
            $title = $_REQUEST['title'];
        } else {
            $title = $video->getTitle();
        }
        if (!empty($_REQUEST['description'])) {
            $description = $_REQUEST['description'];
        } else {
            $description = $video->getDescription();
        }
        if (!empty($_REQUEST['visibility'])) {
            $visibility = $_REQUEST['visibility'];
        } else {
            $visibility = 'public';
        }

        $response = SocialUploader::upload($publisher_user_preferences_id, $paths['mp4'], $title, $description, $visibility);
        _error_log("SocialMediaPublisher::upload($publisher_user_preferences_id, $videos_id) $providerName complete " . json_encode($response));
        self::saveLog($publisher_social_medias_id, $videos_id, array('publisher_user_preferences_id' => $publisher_user_preferences_id, 'response' => $response));
        return $response;
    }

    private static function saveLog($publisher_social_medias_id, $videos_id, $details, $users_id = 0, $status = Publisher_video_publisher_logs::STATUS_UNVERIFIED)
    {
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        $o = new Publisher_video_publisher_logs(0);
        $o->setPublish_datetimestamp(time());
        $o->setStatus($status);
        $o->setDetails($details);
        $o->setVideos_id($videos_id);
        $o->setUsers_id($users_id);
        $o->setPublisher_social_medias_id($publisher_social_medias_id);
        return $o->save();
    }

    public function getHeadCode()
    {
        $js = "<script>var yptURL = '" . SocialMediaPublisher::RESTREAMER_URL . "';</script>";
        return $js;
    }

    static function getProiderItem($provider)
    {
        if (!empty(self::SOCIAL_TYPES[$provider])) {
            return self::SOCIAL_TYPES[$provider];
        }
        return false;
    }

    static function getOrStopIfProviderIsNotValid()
    {
        $provider = $_REQUEST['provider'];
        if (!$item = self::getProiderItem($provider)) {
            forbiddenPage("Invalid provider {$provider}");
        }
        return $item;
    }


    public function getVideosManagerListButton()
    {
        $obj = $this->getDataObject();
        $btn = '';
        $btn .= '<button type="button" ' .
            ' class="btn btn-default btn-light btn-sm btn-xs btn-block" ' .
            ' onclick="avideoModalIframe(webSiteRootURL+\\\'plugin/SocialMediaPublisher/uploadVideo.php?videos_id=\'+row.id+\'\\\');" >' .
            ' <i class="fa-regular fa-share-from-square"></i> ' . __("Share on Social Media") . '</button>';

        return $btn;
    }

    static function scanInstagam()
    {
        global $global;
        $sql = "SELECT psm.*, pvpl.* FROM publisher_video_publisher_logs pvpl LEFT JOIN publisher_social_medias psm ON publisher_social_medias_id = psm.id  
        WHERE pvpl.status='" . Publisher_video_publisher_logs::STATUS_UNVERIFIED . "' 
        AND name = '" . SocialMediaPublisher::SOCIAL_TYPE_INSTAGRAM["name"] . "' ORDER BY pvpl.id DESC LIMIT 100 ";

        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        //var_dump($sql, $fullData);
        if (!empty($fullData)) {
            foreach ($fullData as $key => $row) {
                $json = json_decode($row["details"]);

                $accessToken = $json->response->accessToken;
                $containerId = $json->response->containerId;
                $instagramAccountId = $json->response->instagramAccountId;

                $obj = InstagramUploader::publishMediaIfIsReady($accessToken, $containerId, $instagramAccountId);

                if ((isset($obj['error']) && $obj['error'] === false) || $obj["waitForMediaProcessing"]["response"]["status_code"] === "PUBLISHED") {

                    //var_dump($obj);
                    if (!empty($obj['publishResponse'])) {
                        $json->publishResponse = $obj['publishResponse'];
                    }
                    if (!empty($obj['mediaResponse'])) {
                        $json->mediaResponse = $obj['mediaResponse'];
                    }

                    $smp = new Publisher_video_publisher_logs($row['id'], true);
                    $smp->setDetails($json);
                    $smp->setStatus(Publisher_video_publisher_logs::STATUS_VERIFIED);
                    if ($smp->save()) {
                        $poster = Video::getPoster($row["videos_id"],);
                        $img = "<img src='{$poster}' class='img img-responsive'>";
                        sendSocketMessageToUsers_id('Video published on instagram<br>' . $img, $row["users_id"], 'avideoToastSuccess');
                    }
                }
                return $obj;
            }
        }
        return false;
    }

    function executeEveryMinute()
    {
        self::scanInstagam();
    }
}
