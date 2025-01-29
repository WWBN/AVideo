<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class API extends PluginAbstract
{

    public function getTags()
    {
        return [
            PluginTags::$FREE,
            PluginTags::$MOBILE,
        ];
    }

    public function getDescription()
    {
        return "Handle APIs for third party Applications";
    }

    public function getName()
    {
        return "API";
    }

    public function getUUID()
    {
        return "1apicbec-91db-4357-bb10-ee08b0913778";
    }

    private static function addRowInfo($obj)
    {
        if (!isset($obj->current)) {
            $obj->current = getCurrentPage();
        }
        if (!isset($obj->rowCount)) {
            $obj->rowCount = getRowCount();
        }

        $obj->hasMore = true;
        if (!empty($obj->rows) && is_array($obj->rows)) {
            if (count($obj->rows) < $obj->rowCount) {
                $obj->hasMore = false;
            }
        } else if (!empty($obj->videos) && is_array($obj->videos)) {
            if (count($obj->videos) < $obj->rowCount) {
                $obj->hasMore = false;
            }
        }
        if ($obj->current * $obj->rowCount >= $obj->totalRows) {
            $obj->hasMore = false;
        }
        return $obj;
    }

    public function getEmptyDataObject()
    {
        global $global;
        $obj = new stdClass();
        $obj->APISecret = md5($global['salt'] . $global['systemRootPath'] . 'API');
        $obj->standAloneFFMPEG = '';
        return $obj;
    }

    public function getPluginMenu()
    {
        global $global;
        $fileAPIName = $global['systemRootPath'] . 'plugin/API/pluginMenu.html';
        return file_get_contents($fileAPIName);
    }

    public function set($parameters)
    {
        if (empty($parameters['APIName'])) {
            $object = new ApiObject("Parameter APIName can not be empty (set)");
        } else {
            if (!empty($parameters['pass'])) {
                $parameters['password'] = $parameters['pass'];
            }
            if (!empty($parameters['encodedPass']) && strtolower($parameters['encodedPass']) === 'false') {
                $parameters['encodedPass'] = false;
            }
            if (!empty($parameters['user']) && !empty($parameters['password'])) {
                $user = new User("", $parameters['user'], $parameters['password']);
                $user->login(false, @$parameters['encodedPass']);
            }
            $APIName = $parameters['APIName'];
            if (method_exists($this, "set_api_$APIName")) {
                $str = "\$object = \$this->set_api_$APIName(\$parameters);";
                eval($str);
            } else {
                $method = "API_set_{$parameters['APIName']}";
                if (
                    !empty($parameters['APIPlugin']) &&
                    AVideoPlugin::isEnabledByName($parameters['APIPlugin']) &&
                    method_exists($parameters['APIPlugin'], $method)
                ) {
                    $str = "\$object = {$parameters['APIPlugin']}::{$method}(\$parameters);";
                    eval($str);
                } else {
                    $object = new ApiObject();
                }
            }
        }
        return $object;
    }

    public function get($parameters)
    {
        if (empty($parameters['APIName'])) {
            $object = new ApiObject("Parameter APIName can not be empty (get)");
        } else {
            if (!empty($parameters['pass'])) {
                $parameters['password'] = $parameters['pass'];
            }
            if (!empty($parameters['user']) && !empty($parameters['password'])) {
                if (!empty($parameters['encodedPass']) && strtolower($parameters['encodedPass']) === 'false') {
                    $parameters['encodedPass'] = false;
                }
                $user = new User("", $parameters['user'], $parameters['password']);
                $user->login(false, @$parameters['encodedPass']);
            }
            $APIName = $parameters['APIName'];
            if (method_exists($this, "get_api_$APIName")) {
                $str = "\$object = \$this->get_api_$APIName(\$parameters);";
                eval($str);
            } else {
                $method = "API_get_{$parameters['APIName']}";
                if (
                    !empty($parameters['APIPlugin']) &&
                    AVideoPlugin::isEnabledByName($parameters['APIPlugin']) &&
                    method_exists($parameters['APIPlugin'], $method)
                ) {
                    $str = "\$object = {$parameters['APIPlugin']}::{$method}(\$parameters);";
                    eval($str);
                } else {
                    $object = new ApiObject();
                }
            }
        }
        return $object;
    }

    private function startResponseObject($parameters)
    {
        $obj = new stdClass();
        if (empty($parameters['sort']) && !empty($parameters['order'][0]['dir'])) {
            $index = intval($parameters['order'][0]['column']);
            $parameters['sort'][$parameters['columns'][$index]['data']] = $_GET['order'][0]['dir'];
        }
        $array = ['sort', 'rowCount', 'current', 'searchPhrase'];
        foreach ($array as $value) {
            if (!empty($parameters[$value])) {
                $obj->$value = $parameters[$value];
                $_POST[$value] = $parameters[$value];
            }
        }

        return $obj;
    }

    private function getToPost()
    {
        foreach ($_GET as $key => $value) {
            $_POST[$key] = $value;
        }
    }

    /**
     * @param array $parameters
     * 'plugin_name' The plugin name that you want to retrieve the parameters
     * 'APISecret' to list all videos
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&rowCount=3&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_plugin_parameters($parameters)
    {
        global $global;
        $name = "get_api_plugin_parameters" . json_encode($parameters);
        $obj = ObjectYPT::getCacheGlobal($name, 3600);
        if (empty($obj)) {
            $obj = $this->startResponseObject($parameters);
            if (!empty($parameters['plugin_name'])) {
                if (self::isAPISecretValid()) {
                    $obj->response = AVideoPlugin::getDataObject($parameters['plugin_name']);
                } else {
                    return new ApiObject("APISecret is required");
                }
            } else {
                return new ApiObject("Plugin name Not found");
            }
            ObjectYPT::setCacheGlobal($name, $obj);
        }
        return new ApiObject("", false, $obj);
    }

    /**
     * Get ads information for a specific video, user, or live stream.
     *
     * @param array $parameters
     * 'users_id'  (optional) The users_id for which you want to retrieve ads information. If provided,
     *              ads specific to the user will be retrieved. If no ads are set for the user, global ads will be returned.
     * 'videos_id' (optional) The videos_id for which you want to retrieve the users_id.
     * 'live_key'  (optional) The live_key for which you want to retrieve the users_id
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&users_id=1
     * @return array|ApiObject An array containing the ads information or an ApiObject with an error message.
     */
    public function get_api_adsInfo($parameters)
    {

        $ads = AVideoPlugin::loadPluginIfEnabled('ADs');
        if (empty($ads)) {
            return new ApiObject("ADs Plugin is disabled");
        }
        $users_id = 0;
        if (!empty($parameters['users_id'])) {
            $users_id = $parameters['users_id'];
        } else if (!empty($parameters['videos_id'])) {
            $users_id = Video::getOwner($parameters['videos_id']);
        } else if (!empty($parameters['live_key']) && AVideoPlugin::isEnabledByName('Live')) {
            $row = LiveTransmition::keyExists($parameters['live_key']);
            $users_id = $row['users_id'];
        }
        $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
        $array = array('users_id' => $users_id, 'ads' => array());
        foreach (ADs::AdsPositions as $key => $value) {
            $type = $value[0];
            $desktopGlobal = false;
            $mobileGlobal = false;
            $desktop = ADs::getAds($type, $users_id);
            if (empty($desktop)) {
                $desktopGlobal = true;
                $desktop = ADs::getAds($type, false);
            }
            $mobile = ADs::getAds($type . 'Mobile', $users_id);
            if (empty($mobile)) {
                $mobileGlobal = true;
                $mobile = ADs::getAds($type . 'Mobile', false);
            }
            //var_dump($desktop);exit;
            $desktopURLs = array();
            foreach ($desktop as $item) {
                $desktopURLs[] = array('image' => $item['imageURL'], 'url' => $item['url'], 'info' => $item['txt']);
            }

            $mobileURLs = array();
            foreach ($mobile as $item) {
                $mobileURLs[] = array('image' => $item['imageURL'], 'url' => $item['url'], 'info' => $item['txt']);
            }
            $label = '';
            eval("\$label = \$ad->{$type}Label;");
            $array['ads'][] = array(
                'label' => $label,
                'type' => $type,
                'desktop' => array('isValid' => !empty($desktopURLs), 'isGlobal' => $desktopGlobal, 'urls' => $desktopURLs),
                'mobile' => array('isValid' => !empty($mobileURLs), 'isGlobal' => $mobileGlobal, 'urls' => $mobileURLs)
            );
        }

        return new ApiObject("", false, $array);
    }


    /**
     * @param array $parameters
     * Returns the site unique ID
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * @return \ApiObject
     */
    public function get_api_id($parameters)
    {
        global $global;
        $obj = $this->startResponseObject($parameters);
        $obj->id = getPlatformId();
        return new ApiObject("", false, $obj);
    }

    /**
     * @param array $parameters
     * This will check if the provided UserAgent/Headers comes from a mobile
     * Returns true if any type of mobile device detected, including special ones
     * PHP Sample code: "plugin/API/{getOrSet}.json.php?APIName={APIName}&userAgent=".urlencode($_SERVER["HTTP_USER_AGENT"])."&httpHeaders=".urlencode(json_encode(getallheaders()))
     * ['userAgent' usually is the variable $_SERVER["HTTP_USER_AGENT"]]
     * ['httpHeaders' usually is the variable $_SERVER['HTTP_X_REQUESTED_WITH']]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&userAgent=Mozilla%2F5.0+%28Windows+NT+10.0%3B+Win64%3B+x64%29+AppleWebKit%2F537.36+%28KHTML%2C+like+Gecko%29+Chrome%2F89.0.4389.82+Safari%2F537.36
     * @return \ApiObject
     */
    public function get_api_is_mobile($parameters)
    {
        global $global;
        $obj = $this->startResponseObject($parameters);
        if (!empty($_REQUEST['httpHeaders'])) {
            $json = _json_decode($_REQUEST['httpHeaders']);
            if (!empty($json)) {
                $_REQUEST['httpHeaders'] = $json;
            } else {
                $_REQUEST['httpHeaders'] = [$_REQUEST['httpHeaders']];
            }
        }
        $obj->userAgent = @$_REQUEST['userAgent'];
        $obj->httpHeaders = @$_REQUEST['httpHeaders'];
        $obj->isMobile = isMobile(@$obj->userAgent, @$obj->httpHeaders);
        return new ApiObject("", false, $obj);
    }

    /**
     * @param array $parameters
     * ['sort' database sort column]
     * ['rowCount' max numbers of rows]
     * ['current' current page]
     * ['searchPhrase' to search on the categories]
     * ['parentsOnly' will bring only the parents, not children categories]
     * ['catName' the clean_Name of the category you want to filter]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&rowCount=3&current=1&sort[created]=DESC
     * @return \ApiObject
     */
    public function get_api_category($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/category.php';
        $obj = $this->startResponseObject($parameters);
        if (!empty($parameters['catName'])) {
            $row = Category::getCategoryByName($parameters['catName']);
            $fullTotals = Category::getTotalFromCategory($row['id'], false, true, true);
            $totals = Category::getTotalFromCategory($row['id']);
            $row['total'] = $totals['total'];
            $row['fullTotal'] = $fullTotals['total'];
            $row['fullTotal_videos'] = $fullTotals['videos'];
            $row['fullTotal_lives'] = $fullTotals['lives'];
            $row['fullTotal_livelinks'] = $fullTotals['livelinks'];
            $row['fullTotal_livelinks'] = $fullTotals['livelinks'];

            $rows = array($row);
        } else {
            $rows = Category::getAllCategories();
        }
        foreach ($rows as $key => $value) {
            $totalVideosOnChilds = Category::getTotalFromChildCategory($value['id']);
            $childs = Category::getChildCategories($value['id']);
            $photo = Category::getCategoryPhotoPath($value['id']);
            $photoBg = Category::getCategoryBackgroundPath($value['id']);
            $link = $global['webSiteRootURL'] . 'cat/' . $value['clean_name'];

            if (!empty($value['fullTotal_videos'])) {
                $video = Category::getLatestVideoFromCategory($value['id'], true, true);
                $images = Video::getImageFromID($video['id']);
                $image = $images->default['url'];
            } elseif (!empty($value['fullTotal_lives'])) {
                $live = Category::getLatestLiveFromCategory($value['id'], true, true);
                $image = Live::getImage($live['users_id'], $live['live_servers_id']);
            } elseif (!empty($value['fullTotal_livelinks'])) {
                $liveLinks = Category::getLatestLiveLinksFromCategory($value['id'], true, true);
                $image = LiveLinks::getImage($liveLinks['id']);
            }

            $rows[$key]['image'] = $image;
            $rows[$key]['totalVideosOnChilds'] = $totalVideosOnChilds;
            $rows[$key]['childs'] = $childs;
            $rows[$key]['photo'] = $photo;
            $rows[$key]['photoBg'] = $photoBg;
            $rows[$key]['link'] = $link;
        }
        //array_multisort(array_column($rows, 'hierarchyAndName'), SORT_ASC, $rows);
        $totalRows = Category::getTotalCategories();
        $obj->totalRows = $totalRows;
        $obj->rows = $rows;
        return new ApiObject("", false, $obj);
    }

    /**
     * @param array $parameters
     * 'APISecret' to list all videos
     * 'playlists_id' the program id
     * 'index' the position of the video
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&playlists_id=1&index=2&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_video_from_program($parameters)
    {
        global $global;
        $playlists = AVideoPlugin::loadPlugin("PlayLists");
        if (empty($parameters['playlists_id'])) {
            //return new ApiObject("Playlist ID is empty", true, $parameters);
            $_POST['sort']['created'] = 'DESC';
            $videos = PlayList::getVideosIDFromPlaylistLight(0);
        } else {
            $videos = PlayLists::getOnlyVideosAndAudioIDFromPlaylistLight($parameters['playlists_id']);
        }
        if (empty($videos)) {
            return new ApiObject("There are no videos for this playlist", true, $parameters);
        }

        if (empty($parameters['playlists_id'])) {
            //return new ApiObject("Playlist ID is empty", true, $parameters);
            $_POST['sort']['created'] = 'DESC';
            $config = new AVideoConf();
            $videos = Video::getAllVideos();
            $playlist = new PlayList($parameters['playlists_id']);
            $parameters['playlist_name'] = __('Date Added');
            $parameters['modified'] = date('Y-m-d H:i:s');
            $parameters['users_id'] = 0;
            $parameters['channel_name'] = $config->getWebSiteTitle();
            $parameters['channel_photo'] = $config->getFavicon(true);
            $parameters['channel_bg'] = $config->getFavicon(true);
            $parameters['channel_link'] = $global['webSiteRootURL'];
        } else {
            $playlist = new PlayList($parameters['playlists_id']);
            $parameters['playlist_name'] = $playlist->getNameOrSerieTitle();
            $parameters['modified'] = $playlist->getModified();
            $users_id = $playlist->getUsers_id();
            $user = new User($users_id);
            $parameters['users_id'] = $users_id;
            $parameters['channel_name'] = $user->getChannelName();
            $parameters['channel_photo'] = $user->getPhotoDB();
            $parameters['channel_bg'] = $user->getBackground();
            $parameters['channel_link'] = $user->getChannelLink();
        }

        $parameters = array_merge($parameters, PlayLists::videosToPlaylist($videos, @$parameters['index'], !empty($parameters['audioOnly'])));

        return new ApiObject("", false, $parameters);
    }

    /**
     * @param array $parameters
     * 'APISecret' to list all videos
     * 'playlists_id' the program id
     * 'index' the position of the video
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&playlists_id=1&index=2&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_audio_from_program($parameters)
    {
        $parameters['audioOnly'] = 1;
        return $this->get_api_video_from_program($parameters);
    }

    /**
     * @param array $parameters
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * @return \ApiObject
     */
    public function get_api_suggested_programs($parameters)
    {
        global $global;
        $playlists = AVideoPlugin::loadPlugin("PlayLists");
        //var_dump($videos);exit;
        $config = new AVideoConf();
        $users_id = User::getId();
        $list = [];
        $obj = new stdClass();
        $obj->id = 0;
        $obj->photo = $config->getFavicon(true);
        $obj->channelLink = $global['webSiteRootURL'];
        $obj->username = $config->getWebSiteTitle();
        $obj->name = __('Date added');
        $obj->link = $global['webSiteRootURL'];
        $_POST['sort']['created'] = 'DESC';
        $obj->videos = PlayList::getVideosIDFromPlaylistLight(0);
        $list[] = $obj;
        $videos = PlayList::getSuggested();
        foreach ($videos as $value) {
            $videosArrayId = PlayList::getVideosIdFromPlaylist($value['serie_playlists_id']);
            if (empty($videosArrayId) || $value['status'] == "favorite" || $value['status'] == "watch_later") {
                continue;
            }
            $obj = new stdClass();
            $obj->id = $value['serie_playlists_id'];
            $obj->photo = User::getPhoto($value['users_id']);
            $obj->channelLink = User::getChannelLink($value['users_id']);
            $obj->username = User::getNameIdentificationById($value['users_id']);
            $obj->name = $value['title'];
            $obj->link = PlayLists::getLink($value['serie_playlists_id']);
            $obj->videos = PlayList::getVideosIDFromPlaylistLight($value['serie_playlists_id']);
            $list[] = $obj;
        }
        return new ApiObject("", false, $list);
    }

    /**
     * This API will return all the tags from VideoTags plugin, also will list the latest 100 videos from the tags your user is subscribed to
     * @param array $parameters
     * 'audioOnly' 1 or 0, this option will extract the MP3 from the video file
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * @return \ApiObject
     */
    public function get_api_tags($parameters)
    {
        global $global;
        $vtags = AVideoPlugin::loadPluginIfEnabled("VideoTags");

        if (empty($vtags)) {
            return new ApiObject("VideoTags is disabled");
        }

        $tags = VideoTags::getAll(User::getId());
        if (is_array($tags)) {
            foreach ($tags as $key => $row) {
                $tags[$key]['videos'] = array();
                $tags[$key]['photo'] = ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_URL);
                if (!empty($row['subscription'])) {
                    $videos = TagsHasVideos::getAllVideosFromTagsId($row['id']);
                    $tags[$key]['videos'] = PlayLists::videosToPlaylist($videos, @$parameters['index'], !empty($parameters['audioOnly']));
                    if (!empty($tags[$key]['videos'][0])) {
                        $tags[$key]['photo'] = $tags[$key]['videos'][0]['images']['poster'];
                    }
                }
            }
        } else {
            $tags = array();
        }

        return new ApiObject("", false, $tags);
    }

    /**
     * @param array $parameters
     * 'APISecret' to list all videos
     * 'videos_id' the video id
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_video_file($parameters)
    {
        global $global;
        $obj = $this->startResponseObject($parameters);
        $obj->videos_id = $parameters['videos_id'];
        if (self::isAPISecretValid()) {
            if (!User::canWatchVideoWithAds($obj->videos_id)) {
                return new ApiObject("You cannot watch this video");
            }
        }
        $video = new Video('', '', $obj->videos_id);
        $obj->filename = $video->getFilename();
        $obj->duration_in_seconds = $video->getDuration_in_seconds();
        $obj->title = $video->getTitle();
        $obj->video_file = Video::getHigherVideoPathFromID($obj->videos_id);
        $obj->sources = getSources($obj->filename, true);
        $obj->images = Video::getImageFromFilename($obj->filename);
        return new ApiObject("", false, $obj);
    }

    /**
     * @param array $parameters
     * 'videos_id' the video id
     * 'users_id' the user id
     * Returns if the user can watch the video
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * @return \ApiObject
     */
    public function get_api_user_can_watch_video($parameters)
    {

        $obj = new stdClass();
        $obj->users_id = intval($parameters['users_id']);
        $obj->videos_id = intval($parameters['videos_id']);
        $obj->userCanWatchVideo = false;
        $obj->userCanWatchVideoWithAds = false;
        $error = true;
        $msg = '';

        if (!empty($obj->videos_id)) {
            $error = false;
            $obj->userCanWatchVideo = AVideoPlugin::userCanWatchVideo($obj->users_id, $obj->videos_id);
            $obj->userCanWatchVideoWithAds = AVideoPlugin::userCanWatchVideoWithAds($obj->users_id, $obj->videos_id);
        } else {
            $msg = 'Videos id is required';
        }


        return new ApiObject($msg, $error, $obj);
    }


    /**
     * @param array $parameters
     * 'videos_id' the video id
     * 'password' a string with the user password
     * Returns if the password is correct or not, if there is no password it will return true
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * @return \ApiObject
     */
    public function get_api_video_password_is_correct($parameters)
    {

        $obj = new stdClass();
        $obj->videos_id = intval($parameters['videos_id']);
        $obj->passwordIsCorrect = true;
        $error = true;
        $msg = '';

        if (!empty($obj->videos_id)) {
            $error = false;
            $video = new Video('', '', $obj->videos_id);
            $password = $video->getVideo_password();
            if (!empty($password)) {
                $obj->passwordIsCorrect = $password == $parameters['video_password'];
            }
        } else {
            $msg = 'Videos id is required';
        }


        return new ApiObject($msg, $error, $obj);
    }

    /**
     * @param array $parameters
     * videos_id
     * Returns the payperview plans
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=2
     * @return \ApiObject
     */
    public function get_api_ppv_plans($parameters)
    {
        global $global;
        $obj = new stdClass();
        $error = true;
        $msg = '';

        $objPPV = AVideoPlugin::getObjectDataIfEnabled('PayPerView');
        if (empty($objPPV)) {
            return new ApiObject('PayPerView is disabled');
        }

        $objWallet = AVideoPlugin::getObjectDataIfEnabled('YPTWallet');
        if (empty($objWallet)) {
            return new ApiObject('YPTWallet is disabled');
        }

        $obj->videos_id = intval($parameters['videos_id']);
        if (empty($obj->videos_id)) {
            return new ApiObject('videos_id is empty');
        }

        $obj->ppv = PayPerView::getAllPlansFromVideo($obj->videos_id);
        //var_dump($obj->ppv);
        foreach ($obj->ppv as $key => $value) {
            $obj->ppv[$key]['valueString'] = YPTWallet::formatCurrency($value['value']);
        }
        if (!empty($obj->ppv)) {
            $error = false;
        }
        return new ApiObject($msg, $error, $obj);
    }

    /**
     * @param array $parameters
     * reduces the wallet balance of a user by the cost of a pay-per-view (PPV) video and returns the updated balance. It checks if the user has sufficient funds to make the purchase
     * plans_id
     * videos_id
     * 'user' username of the user
     * 'pass' password  of the user
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=2&plans_id=4
     * @return \ApiObject
     */
    public function set_api_ppv_buy($parameters)
    {
        global $global;
        $obj = new stdClass();
        $error = true;
        $msg = '';
        $obj->users_id = User::getId();
        if (empty($obj->users_id)) {
            return new ApiObject('You must login');
        }

        $objPPV = AVideoPlugin::getObjectDataIfEnabled('PayPerView');
        if (empty($objPPV)) {
            return new ApiObject('PayPerView is disabled');
        }

        $objWallet = AVideoPlugin::getObjectDataIfEnabled('YPTWallet');
        if (empty($objWallet)) {
            return new ApiObject('YPTWallet is disabled');
        }

        $obj->videos_id = intval($parameters['videos_id']);
        if (empty($obj->videos_id)) {
            return new ApiObject('videos_id is empty');
        }

        $obj->plans_id = intval($parameters['plans_id']);
        if (empty($obj->plans_id)) {
            return new ApiObject('plans_id is empty');
        }

        $obj->plan = PPV_Plans::getFromDb($obj->plans_id);
        if (empty($obj->plan)) {
            return new ApiObject('PPV plan does not exists');
        }
        $error = false;
        $obj->plans = PayPerView::getActivePlan($obj->users_id, $obj->videos_id);
        if (empty($obj->plans)) {
            $obj->plans = PayPerView::buyPPV(User::getId(), $obj->plans_id, $obj->videos_id);
            $error = $obj->plans->error;
            $msg = $obj->plans->msg;
        } else {
            $error = false;
        }
        return new ApiObject($msg, $error, $obj);
    }

    /**
     * @param array $parameters
     * videos_id
     * Returns the payperview plans
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=2
     * @return \ApiObject
     */
    public function get_api_subscription_plans($parameters)
    {
        global $global;
        $obj = new stdClass();
        $error = true;
        $msg = '';

        $objPPV = AVideoPlugin::getObjectDataIfEnabled('Subscription');
        if (empty($objPPV)) {
            return new ApiObject('Subscription is disabled');
        }

        $objWallet = AVideoPlugin::getObjectDataIfEnabled('YPTWallet');
        if (empty($objWallet)) {
            return new ApiObject('YPTWallet is disabled');
        }

        $obj->videos_id = intval($parameters['videos_id']);
        if (empty($obj->videos_id)) {
            return new ApiObject('videos_id is empty');
        }

        $sub = new Subscription();
        $obj->plans = $sub->getPlansFromVideo($obj->videos_id);
        if (!empty($obj->plans)) {
            $error = false;
        }
        return new ApiObject($msg, $error, $obj);
    }

    /**
     * @param array $parameters
     * reduces the wallet balance of a user by the cost of a pay-per-view (PPV) video and returns the updated balance. It checks if the user has sufficient funds to make the purchase
     * plans_id
     * videos_id
     * 'user' username of the user
     * 'pass' password  of the user
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&plans_id=2
     * @return \ApiObject
     */
    public function set_api_subscription_buy($parameters)
    {
        global $global;
        $obj = new stdClass();
        $error = true;
        $msg = '';
        $obj->users_id = User::getId();
        if (empty($obj->users_id)) {
            return new ApiObject('You must login');
        }

        $objPPV = AVideoPlugin::getObjectDataIfEnabled('Subscription');
        if (empty($objPPV)) {
            return new ApiObject('Subscription is disabled');
        }

        $objWallet = AVideoPlugin::getObjectDataIfEnabled('YPTWallet');
        if (empty($objWallet)) {
            return new ApiObject('YPTWallet is disabled');
        }

        $obj->videos_id = intval($parameters['videos_id']);
        if (empty($obj->videos_id)) {
            return new ApiObject('videos_id is empty');
        }

        $obj->plans_id = intval($parameters['plans_id']);
        if (empty($obj->plans_id)) {
            return new ApiObject('plans_id is empty');
        }

        $obj->plans = SubscriptionPlansTable::getFromDb($obj->plans_id);
        if (empty($obj->plan)) {
            return new ApiObject('Plan does not exists');
        }

        // check if the user has a valid plan for this video
        $obj->activePlan = SubscriptionTable::getSubscription($obj->users_id, $obj->plans_id);
        if (empty($obj->activePlan)) {
            $obj->activePlan = Subscription::renew($obj->users_id, $obj->plans_id);
            $error = empty($obj->activePlan);
        } else {
            $error = false;
        }
        return new ApiObject($msg, $error, $obj);
    }

    /**
     * @param array $parameters
     * Obs: in the Trending sort also pass the current=1, otherwise it will return a random order
     *
     * ['APISecret' to list all videos]
     * ['sort' database sort column]
     * ['videos_id' the video id (will return only 1 or 0 video)]
     * ['clean_title' the video clean title (will return only 1 or 0 video)]
     * ['rowCount' max numbers of rows]
     * ['current' current page]
     * ['searchPhrase' to search on the categories]
     * ['tags_id' the ID of the tag you want to filter]
     * ['catName' the clean_name of the category you want to filter, the value can be an array of categories clean titles]
     * ['doNotShowCats' the clean_name of the category you want to exclude from the list, the value can be an array of categories clean titles]
     * ['channelName' the channelName of the videos you want to filter]
     * ['playlist' use playlist=1 to get a response compatible with the playlist endpoint]
     * ['videoType' the type of the video, the valid options are 'audio_and_video_and_serie', 'audio_and_video', 'audio', 'video', 'embed', 'linkVideo', 'linkAudio', 'torrent', 'pdf', 'image', 'gallery', 'article', 'serie', 'image', 'zip', 'notfound', 'blockedUser']
     * ['is_serie' if is 0 return only videos, if is 1 return only series, if is not set, return all]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&catName=default&rowCount=10
     * @example Suggested ----> {webSiteRootURL}plugin/API/get.json.php?APIName={APIName}&rowCount=10&sort[suggested]=1
     * @example DateAdded ----> {webSiteRootURL}plugin/API/get.json.php?APIName={APIName}&rowCount=10&sort[created]=desc
     * @example Trending ----> {webSiteRootURL}plugin/API/get.json.php?APIName={APIName}&rowCount=10&sort[trending]=1
     * @example Shorts ----> {webSiteRootURL}plugin/API/get.json.php?APIName={APIName}&rowCount=10&sort[shorts]=1
     * @example MostWatched ----> {webSiteRootURL}plugin/API/get.json.php?APIName={APIName}&rowCount=10&sort[views_count]=desc
     * @return \ApiObject
     */
    public function get_api_video($parameters)
    {
        $start = microtime(true);
        /*
        $cacheParameters = array('noRelated', 'APIName', 'catName', 'rowCount', 'APISecret', 'sort', 'searchPhrase', 'current', 'tags_id', 'channelName', 'videoType', 'is_serie', 'user', 'videos_id', 'playlist');

        $cacheVars = array('users_id' => User::getId(), 'requestUniqueString'=>getRequestUniqueString());
        foreach ($cacheParameters as $value) {
            $cacheVars[$value] = @$_REQUEST[$value];
        }
        */

        // use 1 hour cache
        $videosListCache = new VideosListCacheHandler();
        //$cacheName = 'get_api_video' . md5(json_encode($cacheVars));
        if (empty($parameters['videos_id'])) {
            //$obj = ObjectYPT::getCacheGlobal($cacheName, 3600);
            $obj = $videosListCache->getCacheWithAutoSuffix(3600);
            if (!empty($obj)) {
                $end = microtime(true) - $start;
                return new ApiObject("Cached response in {$end} seconds", false, $obj);
            }
        }

        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $obj = $this->startResponseObject($parameters);
        if (!empty($parameters['videos_id'])) {
            $status = Video::SORT_TYPE_VIEWABLE;
            $ignoreGroup = false;
            if (self::isAPISecretValid()) {
                $status = "";
                $ignoreGroup = true;
            }
            //              getVideo($id = "", $status = Video::SORT_TYPE_VIEWABLE, $ignoreGroup = false, $random = false, $suggestedOnly = false, $showUnlisted = false, $ignoreTags = false, $activeUsersOnly = true)
            $rows = [Video::getVideo($parameters['videos_id'], $status, $ignoreGroup, false, false, true)];
            $totalRows = empty($rows) ? 0 : 1;
        } elseif (self::isAPISecretValid()) {
            $rows = Video::getAllVideos(Video::SORT_TYPE_VIEWABLE, false, true);
            $totalRows = Video::getTotalVideos(Video::SORT_TYPE_VIEWABLE, false, true);
        } elseif (!empty($parameters['clean_title'])) {
            $rows = Video::getVideoFromCleanTitle($parameters['clean_title']);
            $totalRows = empty($rows) ? 0 : 1;
        } else {
            $rows = Video::getAllVideos();
            $totalRows = Video::getTotalVideos();
            /*
            if(!empty($_REQUEST['debug'])){
                global $_lastGetAllSQL;
                global $lastGetTotalVideos;
                var_dump($totalRows, $lastGetTotalVideos, $_lastGetAllSQL);exit;
            }
            */
        }

        if (!empty($_REQUEST['catName']) && empty($parameters['videos_id'])) {
            $currentCat = Category::getCategoryByName($_REQUEST['catName']);
            if (!empty($currentCat)) {
                if (empty($parameters['videoType'])) {
                    $liveVideos = getLiveVideosFromCategory($currentCat['id']);
                    if (!empty($liveVideos)) {
                        $rows = array_merge($liveVideos, $rows);
                        $totalRows += count($liveVideos);
                    }
                }

                $fullTotals = Category::getTotalFromCategory($currentCat['id'], false, true, true);
                $totals = Category::getTotalFromCategory($currentCat['id']);
                $currentCat['total'] = $totals['total'];
                $currentCat['fullTotal'] = $fullTotals['total'];
                $currentCat['fullTotal_videos'] = $fullTotals['videos'];
                $currentCat['fullTotal_lives'] = $fullTotals['lives'];
                $currentCat['fullTotal_livelinks'] = $fullTotals['livelinks'];
                $currentCat['fullTotal_livelinks'] = $fullTotals['livelinks'];

                $currentCat['totalVideosOnChilds'] = Category::getTotalFromChildCategory($currentCat['id']);
                $currentCat['childs'] = Category::getChildCategories($currentCat['id']);
                $currentCat['photo'] = Category::getCategoryPhotoPath($currentCat['id']);
                $currentCat['photoBg'] = Category::getCategoryBackgroundPath($currentCat['id']);
                $currentCat['link'] = $global['webSiteRootURL'] . 'cat/' . $currentCat['clean_name'];

                foreach ($currentCat['childs'] as $key => $child) {
                    $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&catName={$child['clean_name']}";
                    $currentCat['childs'][$key]['section'] = new SectionFirstPage('SubCategroy', $child['name'], $endpoint, getRowCount());
                }
                $obj->category = $currentCat;
            }
        }

        unsetSearch();
        $objMob = AVideoPlugin::getObjectData("MobileManager");
        $SubtitleSwitcher = AVideoPlugin::getDataObjectIfEnabled("SubtitleSwitcher");

        // check if there are custom ads for this video
        $objAds = AVideoPlugin::getDataObjectIfEnabled('ADs');

        foreach ($rows as $key => $value) {
            if (is_object($value)) {
                $value = object_to_array($value);
            }
            if (empty($value['filename'])) {
                continue;
            }
            if ($value['type'] == Video::$videoTypeSerie) {
                require_once $global['systemRootPath'] . 'objects/playlist.php';
                $rows[$key]['playlist'] = PlayList::getVideosFromPlaylist($value['serie_playlists_id']);
                //var_dump($rows[$key]['playlist']);exit;
            }
            $images = Video::getImageFromFilename($rows[$key]['filename'], $rows[$key]['type']);
            $rows[$key]['images'] = $images;
            if ($rows[$key]['type'] !== Video::$videoTypeLinkVideo) {
                $rows[$key]['videos'] = Video::getVideosPaths($value['filename'], true);
            } else {
                $extension = getExtension($rows[$key]['videoLink']);
                $rows[$key]['videoLink'] = AVideoPlugin::modifyURL($rows[$key]['videoLink'], $rows[$key]['id']);
                if ($extension == 'mp4') {
                    $rows[$key]['videos'] = array(
                        'mp4' => array(
                            '720' => $rows[$key]['videoLink']
                        )
                    );
                } else if ($extension == 'm3u8') {
                    $rows[$key]['videos'] = array(
                        'm3u8' => array(
                            'url' => $rows[$key]['videoLink'],
                            'url_noCDN' => $rows[$key]['videoLink'],
                            'type' => 'video',
                            'format' => 'm3u8',
                            'resolution' => 'auto',
                        )
                    );
                }
            }
            $rows[$key]['sources'] = array();
            if (empty($rows[$key]['videos'])) {
                $rows[$key]['videos'] = new stdClass();
            } else {
                $rows[$key]['sources'] = Video::getVideosPathsToSource($rows[$key]['videos']);
            }

            $rows[$key]['Poster'] = !empty($objMob->portraitImage) ? $images->posterPortrait : $images->poster;
            $rows[$key]['Thumbnail'] = !empty($objMob->portraitImage) ? $images->posterPortraitThumbs : $images->thumbsJpg;
            $rows[$key]['imageClass'] = !empty($objMob->portraitImage) ? "portrait" : "landscape";
            $rows[$key]['createdHumanTiming'] = humanTiming(strtotime($rows[$key]['created']));
            $rows[$key]['pageUrl'] = Video::getLink($rows[$key]['id'], $rows[$key]['clean_title'], false);
            $rows[$key]['embedUrl'] = Video::getLink($rows[$key]['id'], $rows[$key]['clean_title'], true);
            $rows[$key]['UserPhoto'] = User::getPhoto($rows[$key]['users_id']);
            $rows[$key]['isSubscribed'] = false;

            //make playlist compatible
            if (!empty($parameters['playlist'])) {
                $rows[$key]['mp3'] = convertVideoToMP3FileIfNotExists($value['id']);
                $rows[$key]['category_name'] = $value['category'];
                $rows[$key]['category'] = array('name' => $rows[$key]['category_name']);
                $rows[$key]['channel_name'] = User::_getChannelName($rows[$key]['users_id']);;
            }

            if (User::isLogged()) {
                require_once $global['systemRootPath'] . 'objects/subscribe.php';
                $rows[$key]['isSubscribed'] = Subscribe::isSubscribed($rows[$key]['users_id']);
            }


            $sub = self::getSubtitle($value['filename']);

            $rows[$key]['subtitles_available'] = $sub['subtitles_available'];
            $rows[$key]['subtitles'] = $sub['subtitles'];
            $rows[$key]['subtitlesSRT'] = $sub['subtitlesSRT'];

            require_once $global['systemRootPath'] . 'objects/comment.php';
            require_once $global['systemRootPath'] . 'objects/subscribe.php';
            unset($_POST['sort']);
            unset($_POST['current']);
            unset($_POST['searchPhrase']);
            $_REQUEST['rowCount'] = 10;
            $_POST['sort']['created'] = "desc";
            $rows[$key]['comments'] = Comment::getAllComments($rows[$key]['id']);
            $rows[$key]['commentsTotal'] = Comment::getTotalComments($rows[$key]['id']);
            foreach ($rows[$key]['comments'] as $key2 => $value2) {
                $user = new User($value2['users_id']);
                $rows[$key]['comments'][$key2]['userPhotoURL'] = User::getPhoto($rows[$key]['comments'][$key2]['users_id']);
                $rows[$key]['comments'][$key2]['userName'] = $user->getNameIdentificationBd();
            }
            $rows[$key]['subscribers'] = Subscribe::getTotalSubscribes($rows[$key]['users_id']);

            //wwbn elements
            $rows[$key]['wwbnURL'] = $rows[$key]['pageUrl'];
            $rows[$key]['wwbnEmbedURL'] = $rows[$key]['embedUrl'];
            $rows[$key]['wwbnImgThumbnail'] = $rows[$key]['Thumbnail'];
            $rows[$key]['wwbnImgPoster'] = $rows[$key]['Poster'];
            //$rows[$key]['wwbnImgGif'] = $rows[$key]['pageUrl'];
            //$rows[$key]['wwbnTags'] = $rows[$key]['pageUrl'];
            $rows[$key]['wwbnTitle'] = $rows[$key]['title'];
            //$rows[$key]['wwbnDescription'] = $rows[$key]['description'];
            //$rows[$key]['wwbnChannel'] = User::getChannelLink($rows[$key]['users_id']);
            $rows[$key]['wwbnChannelURL'] = User::getChannelLink($rows[$key]['users_id']);
            $rows[$key]['wwbnImgChannel'] = $rows[$key]['UserPhoto'];
            //$rows[$key]['wwbnProgram'] = $rows[$key]['pageUrl'];
            //$rows[$key]['wwbnProgramURL'] = $rows[$key]['pageUrl'];
            $rows[$key]['wwbnType'] = $rows[$key]['type'];

            if (empty($parameters['noRelated'])) {
                $rows[$key]['relatedVideos'] = Video::getRelatedMovies($rows[$key]['id']);
                foreach ($rows[$key]['relatedVideos'] as $key2 => $value2) {
                    $rows[$key]['relatedVideos'][$key2]['tags'] = Video::getTags($value2['id']);

                    $sub = self::getSubtitle($rows[$key]['relatedVideos'][$key2]['filename']);

                    $rows[$key]['relatedVideos'][$key2]['subtitles_available'] = $sub['subtitles_available'];
                    $rows[$key]['relatedVideos'][$key2]['subtitles'] = $sub['subtitles'];
                    $rows[$key]['relatedVideos'][$key2]['subtitlesSRT'] = $sub['subtitlesSRT'];

                    if (AVideoPlugin::isEnabledByName("VideoTags")) {
                        $rows[$key]['relatedVideos'][$key2]['videoTags'] = Tags::getAllFromVideosId($value2['id']);
                        $rows[$key]['relatedVideos'][$key2]['videoTagsObject'] = Tags::getObjectFromVideosId($value2['id']);
                    }
                    if ($rows[$key]['relatedVideos'][$key2]['type'] !== Video::$videoTypeLinkVideo) {
                        $rows[$key]['relatedVideos'][$key2]['videos'] = Video::getVideosPaths($value2['filename'], true);
                    } else if (preg_match('/m3u8/', $rows[$key]['relatedVideos'][$key2]['videoLink'])) {
                        $url = AVideoPlugin::modifyURL($rows[$key]['relatedVideos'][$key2]['videoLink']);
                        $rows[$key]['relatedVideos'][$key2]['videos']['m3u8']['url'] = $url;
                        $rows[$key]['relatedVideos'][$key2]['videos']['m3u8']['url_noCDN'] = $url;
                        $rows[$key]['relatedVideos'][$key2]['videos']['m3u8']['type'] = 'video';
                        $rows[$key]['relatedVideos'][$key2]['videos']['m3u8']['format'] = 'm3u8';
                        $rows[$key]['relatedVideos'][$key2]['videos']['m3u8']['resolution'] = 'auto';
                    }
                    if (!empty($rows[$key]['relatedVideos'][$key2]['videos'])) {
                        $rows[$key]['relatedVideos'][$key2]['sources'] = Video::getVideosPathsToSource($rows[$key]['relatedVideos'][$key2]['videos']);
                    }
                    if (!empty($rows[$key]['relatedVideos'][$key2]['videoLink'])) {
                        $rows[$key]['relatedVideos'][$key2]['videoLink'] = AVideoPlugin::modifyURL($rows[$key]['relatedVideos'][$key2]['videoLink'], $value2['id']);
                    }
                }
            }
            $rows[$key]['adsImages'] = array();
            if (!empty($objAds)) {
                foreach (ADs::AdsPositions as $value) {
                    $type = $value[0];
                    $rows[$key]['adsImages'][] = array('type' => $type, 'assets' => ADs::getAdsFromVideosId($type, $rows[$key]['id']));
                }
            }
        }
        $obj->totalRows = $totalRows;

        if (!empty($parameters['playlist'])) {
            $obj->videos = $rows;
        } else {
            $obj->rows = $rows;
        }
        $obj = self::addRowInfo($obj);
        //var_dump($obj->rows );exit;
        //ObjectYPT::setCacheGlobal($cacheName, $obj);
        $videosListCache->setCache($obj);
        return new ApiObject("", false, $obj);
    }

    private static function getSubtitle($filename)
    {
        global $_SubtitleSwitcher;
        if (!isset($_SubtitleSwitcher)) {
            $_SubtitleSwitcher = AVideoPlugin::getDataObjectIfEnabled("SubtitleSwitcher");
        }
        $row = array();
        $row['subtitles_available'] = false;
        $row['subtitles'] = [];
        $row['subtitlesSRT'] = [];
        if ($_SubtitleSwitcher) {
            $subtitles = getVTTTracks($filename, true);
            $row['subtitles_available'] = !empty($subtitles);
            if (empty($_SubtitleSwitcher->disableOnAPI)) {
                $row['subtitles'] = $subtitles;
                foreach ($row['subtitles'] as $key2 => $value) {
                    $row['subtitlesSRT'][] = convertSRTTrack($value);
                }
            }
        }
        return $row;
    }

    /**
     * @param array $parameters
     *
     * 'videos_id' the video id what you will update
     * ['user' username of the user]
     * ['pass' password  of the user]
     * ['APISecret' to update the video ]     *
     * ['next_videos_id' id for the next suggested video]
     * ['title' String]
     * ['status' String]
     * ['description' String]
     * ['categories_id' int]
     * ['can_download' 0 or 1]
     * ['can_share']
     * ['only_for_paid' 0 or 1]
     * ['video_password' a string with a video password]
     * ['trailer1' a trailer URL]
     * ['rrating' the valid values are 'g', 'pg', 'pg-13', 'r', 'nc-17', 'ma']
     * ['created' to change the created your user/pass must be a valid admin or you need to provide the APISecret]
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b
     * @return \ApiObject
     */
    public function set_api_video_save($parameters)
    {
        global $advancedCustomUser;

        // Check if parameters array is not empty
        if (empty($parameters)) {
            return new ApiObject('Parameters array is empty');
        }

        // Check for the existence of the required key
        if (empty($parameters['videos_id'])) {
            return new ApiObject('videos_id is empty');
        }

        if (!Video::canEdit($parameters['videos_id']) && !Permissions::canModerateVideos() && !self::isAPISecretValid()) {
            return new ApiObject('Permission denied');
        }

        $obj = new Video('', '', $parameters['videos_id'], true);

        if (empty($obj->getCreated())) {
            return new ApiObject('Video not found');
        }

        if (isset($parameters['next_videos_id'])) {
            $obj->setNext_videos_id($parameters['next_videos_id']);
        }

        if (isset($parameters['description'])) {
            $obj->setDescription($parameters['description']);
        }

        if (!empty($advancedCustomUser->userCanNotChangeCategory) || Permissions::canModerateVideos()) {
            if (isset($parameters['categories_id'])) {
                $obj->setCategories_id($parameters['categories_id']);
            }
        }

        if (isset($parameters['can_download'])) {
            $obj->setCan_download($parameters['can_download']);
        }

        if (isset($parameters['can_share'])) {
            $obj->setCan_share($parameters['can_share']);
        }

        if (isset($parameters['only_for_paid'])) {
            $obj->setOnly_for_paid($parameters['only_for_paid']);
        }

        if (isset($parameters['video_password'])) {
            $obj->setVideo_password($parameters['video_password']);
        }

        if (isset($parameters['trailer1'])) {
            $obj->setTrailer1($parameters['trailer1']);
        }

        if (isset($parameters['rrating'])) {
            $obj->setRrating($parameters['rrating']);
        }

        if (Permissions::canAdminVideos() || self::isAPISecretValid()) {
            if (isset($_REQUEST['created'])) {
                $obj->setCreated($parameters['created']);
            }
        }

        if (!empty($parameters['title'])) {
            $obj->setTitle($parameters['title']);
        }
        $id = $obj->save(false, true);
        // set status must be after save videos parameters
        if (!empty($parameters['status'])) {
            $obj->setStatus($parameters['status']);
        }
        return new ApiObject("", false, $id);
    }

    /**
     * @param array $parameters
     * ['APISecret' to list all videos]
     * ['searchPhrase' to search on the categories]
     * ['tags_id' the ID of the tag you want to filter]
     * ['catName' the clean_APIName of the category you want to filter]
     * ['channelName' the channelName of the videos you want to filter]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_videosCount($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $obj = $this->startResponseObject($parameters);
        if (self::isAPISecretValid()) {
            $totalRows = Video::getTotalVideos(Video::SORT_TYPE_VIEWABLE, false, true);
        } else {
            $totalRows = Video::getTotalVideos();
        }
        //$objMob = AVideoPlugin::getObjectData("MobileManager");
        //$SubtitleSwitcher = AVideoPlugin::loadPluginIfEnabled("SubtitleSwitcher");
        $obj->totalRows = $totalRows;
        return new ApiObject("", false, $obj);
    }

    /**
     * @param array $parameters
     * 'videos_id' the video id that will be deleted
     * ['APISecret' if passed will not require user and pass]
     * ['user' username of the user that will login]
     * ['pass' password  of the user that will login]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_video_delete($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $obj = $this->startResponseObject($parameters);
        if (!empty($parameters['videos_id'])) {
            if (!User::canUpload()) {
                return new ApiObject("Access denied");
            }
            if (!empty($_REQUEST['APISecret']) && !self::isAPISecretValid()) {
                return new ApiObject("Secret does not match");
            }
            $vid = new Video('', '', $parameters['videos_id'], true);
            if (!$vid->userCanManageVideo()) {
                return new ApiObject("User cannot manage the video");
            }
            $id = $vid->delete();
            return new ApiObject("", !$id, $id);
        } else {
            return new ApiObject("Video ID is required");
        }
    }

    /**
     * @param array $parameters
     * 'comment' String with the comment
     * 'videos_id' the video that will receive the comment
     * ['id' the comment id if you will edit some]
     * ['APISecret' if passed will not require user and pass]
     * ['user' username of the user that will login]
     * ['pass' password  of the user that will login]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123&APISecret={APISecret}
     * @return \ApiObject
     */
    public function set_api_comment($parameters)
    {
        global $global;
        $obj = $this->startResponseObject($parameters);
        if (!empty($parameters['videos_id'])) {
            if (!empty($_REQUEST['APISecret']) && !self::isAPISecretValid()) {
                return new ApiObject("Secret does not match");
            } elseif (!User::canComment()) {
                return new ApiObject("Access denied");
            }
            $parameters['comments_id'] = intval(@$parameters['comments_id']);
            require_once $global['systemRootPath'] . 'objects/comment.php';
            if (!empty($parameters['id'])) {
                $parameters['id'] = intval($parameters['id']);
                if (Comment::userCanEditComment($parameters['id'])) {
                    $obj = new Comment("", 0, $parameters['id']);
                    $obj->setComment($parameters['comment']);
                }
            } else {
                $obj = new Comment($parameters['comment'], $parameters['videos_id']);
                $obj->setComments_id_pai($parameters['comments_id']);
            }
            $objResponse = new stdClass();
            $objResponse->comments_id = $obj->save();
            $objResponse->videos_id = $parameters['videos_id'];
            return new ApiObject("", !$objResponse->comments_id, $objResponse);
        } else {
            return new ApiObject("Video ID is required");
        }
    }

    /**
     * @param array $parameters
     * 'comment' String with the comment
     * 'videos_id' the video that will retreive the comments
     * ['APISecret' if passed will not require user and pass]
     * ['user' username of the user that will login]
     * ['pass' password  of the user that will login]
     * ['rowCount' max numbers of rows]
     * ['current' current page]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_comment($parameters)
    {
        global $global;
        $obj = $this->startResponseObject($parameters);
        if (!empty($parameters['videos_id'])) {
            if (!empty($_REQUEST['APISecret']) && !self::isAPISecretValid()) {
                return new ApiObject("Secret does not match");
            } elseif (!User::canComment()) {
                return new ApiObject("Access denied");
            }

            if (!User::canWatchVideo($parameters['videos_id'])) {
                return new ApiObject("Cannot watch video");
            }

            require_once $global['systemRootPath'] . 'objects/comment.php';

            $_POST['sort']['created'] = "desc";
            $obj = Comment::getAllComments($parameters['videos_id']);
            $obj = Comment::addExtraInfo($obj);
            return new ApiObject("", false, $obj);
        } else {
            return new ApiObject("Video ID is required");
        }
    }

    /**
     * @param array $parameters
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * ['live_schedule_id' if you pass it will return a specific live_schedule record]
     * 'user' username of the user that will login
     * 'pass' password  of the user that will login
     * @return \ApiObject
     */
    public function get_api_live_schedule($parameters)
    {
        if (!User::canStream()) {
            return new ApiObject("You cannot stream");
        } else {
            $users_id = User::getId();
            $_POST['sort'] = array('scheduled_time' => 'DESC');
            if (empty($parameters['live_schedule_id'])) {
                $obj = Live_schedule::getAll($users_id);
            } else {
                $row = Live_schedule::getFromDb($parameters['live_schedule_id']);
                if ($row['users_id'] != $users_id) {
                    return new ApiObject("This live schedule does not belong to you");
                } else {
                    $obj = $row;
                }
            }
        }
        return new ApiObject("", false, $obj);
    }

    /**
     * @param array $parameters
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * ['live_schedule_id' if you pass it will return a specific live_schedule record]
     * 'user' username of the user that will login
     * 'pass' password  of the user that will login
     * @return \ApiObject
     */
    public function set_api_live_schedule_delete($parameters)
    {
        if (!User::canStream()) {
            return new ApiObject("You cannot stream");
        } else {
            $users_id = User::getId();
            if (empty($parameters['live_schedule_id'])) {
                return new ApiObject("live_schedule_id cannot be empty");
            } else {
                $row = new Live_schedule($parameters['live_schedule_id']);
                if ($row->getUsers_id() != $users_id) {
                    return new ApiObject("This live schedule does not belong to you");
                } else {
                    $obj = $row->delete();
                }
            }
        }
        return new ApiObject("", false, $obj);
    }

    /**
     * @param array $parameters
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * ['live_servers_id' by default it is 0]
     * ['live_schedule_id' if you pass it want to edit a specific record]
     * ['base64PNGImageRegular' a png image base64 encoded]
     * ['base64PNGImagePreRoll' a png image base64 encoded]
     * ['base64PNGImagePostRoll' a png image base64 encoded]
     * 'title'
     * 'description'
     * 'scheduled_time' pass it in the YYYY-mm-dd HH:ii:ss format
     * 'status' a for active or i for inactive
     * 'scheduled_password'
     * 'user' username of the user that will login
     * 'pass' password  of the user that will login
     * @return \ApiObject
     */
    public function set_api_live_schedule($parameters)
    {
        $live_schedule_id = 0;
        $obj = new stdClass();
        if (!User::canStream()) {
            return new ApiObject("You cannot stream");
        } else {
            $users_id = User::getId();
            if (empty($parameters['live_schedule_id'])) {
                if (empty($parameters['title'])) {
                    return new ApiObject("Title cannot be empty");
                }
                if (empty($parameters['scheduled_time'])) {
                    return new ApiObject("scheduled_time cannot be empty");
                }
                if (empty($parameters['status']) || $parameters['status'] !== 'i') {
                    $parameters['status'] = 'a';
                }
                $o = new Live_schedule(0);
            } else {
                $o = new Live_schedule($parameters['live_schedule_id']);
                if ($o->getUsers_id() != $users_id) {
                    return new ApiObject("This live schedule does not belong to you");
                } else {
                    $o = new Live_schedule($parameters['live_schedule_id']);
                }
            }
            //var_dump($parameters);exit;
            if (isset($parameters['title'])) {
                $o->setTitle($parameters['title']);
            }
            if (isset($parameters['description'])) {
                $o->setDescription($parameters['description']);
            }
            if (isset($parameters['live_servers_id'])) {
                $o->setLive_servers_id($parameters['live_servers_id']);
            }
            if (isset($parameters['scheduled_time'])) {
                $o->setScheduled_time($parameters['scheduled_time']);
            }
            if (isset($parameters['status'])) {
                $o->setStatus($parameters['status']);
            }
            if (isset($parameters['scheduled_password'])) {
                $o->setScheduled_password($parameters['scheduled_password']);
            }

            $o->setUsers_id($users_id);
            $live_schedule_id = $o->save();
            if ($live_schedule_id) {
                if (!empty($parameters['base64PNGImageRegular'])) {
                    $image = Live_schedule::getPosterPaths($live_schedule_id, 0, Live::$posterType_regular);
                    saveBase64DataToPNGImage($parameters['base64PNGImageRegular'], $image['path']);
                }
                if (!empty($parameters['base64PNGImagePreRoll'])) {
                    $image = Live_schedule::getPosterPaths($live_schedule_id, 0, Live::$posterType_preroll);
                    saveBase64DataToPNGImage($parameters['base64PNGImagePreRoll'], $image['path']);
                }
                if (!empty($parameters['base64PNGImagePostRoll'])) {
                    $image = Live_schedule::getPosterPaths($live_schedule_id, 0, Live::$posterType_postroll);
                    saveBase64DataToPNGImage($parameters['base64PNGImagePostRoll'], $image['path']);
                }


                $o = new Live_schedule($live_schedule_id);
                $obj->live_schedule_id = $live_schedule_id;
            }
        }
        return new ApiObject("", empty($live_schedule_id), $obj);
    }

    /**
     * @param array $parameters
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * @return \ApiObject
     */
    public function get_api_livestreams($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'plugin/Live/stats.json.php';
        exit;
    }

    /**
     * Return a user livestream information
     * @param array $parameters
     * ['title' Livestream title]
     * ['public' 1 = live is listed; 0 = not listed]
     * ['APISecret' if passed will not require user and pass]
     * ['users_id' the user ID]
     * ['user' username if does not have the APISecret]
     * ['pass' password  if does not have the APISecret]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&users_id=1
     * @return \ApiObject
     */
    public function set_api_livestream_save($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $obj = $this->startResponseObject($parameters);
        if (empty($parameters['title']) && !isset($parameters['public'])) {
            return new ApiObject("Invalid parameters");
        }
        if (self::isAPISecretValid() || User::isLogged()) {
            if (!empty($parameters['users_id'])) {
                if (!self::isAPISecretValid()) {
                    $parameters['users_id'] = User::getId();
                }
            } else {
                $parameters['users_id'] = User::getId();
            }

            $user = new User($parameters['users_id']);
            if (empty($user->getUser())) {
                return new ApiObject("User Not defined");
            }
            $p = AVideoPlugin::loadPlugin("Live");

            $trasnmition = LiveTransmition::createTransmitionIfNeed($parameters['users_id']);
            $trans = new LiveTransmition($trasnmition['id']);
            $trans->setTitle($parameters['title']);
            $trans->setPublic($parameters['public']);
            if ($obj->id = $trans->save()) {
                return new ApiObject("", false, $obj);
            } else {
                return new ApiObject("Error on save");
            }
        } else {
            return new ApiObject("API Secret is not valid");
        }
    }

    /**
     * Return a user livestream information
     * @param array $parameters
     * ['APISecret' if passed will not require user and pass]
     * ['users_id' the user ID]
     * ['user' username if does not have the APISecret]
     * ['pass' password  if does not have the APISecret]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&users_id=1
     * @return \ApiObject
     */
    public function get_api_user($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $obj = $this->startResponseObject($parameters);
        if (!empty($parameters['users_id'])) {
            if (!self::isAPISecretValid()) {
                $parameters['users_id'] = User::getId();
            }
        } else {
            $parameters['users_id'] = User::getId();
        }

        $user = new User($parameters['users_id']);
        if (empty($user->getUser())) {
            return new ApiObject("User Not found");
        }
        $p = AVideoPlugin::loadPlugin("Live");

        $obj->user = User::getUserFromID($user->getBdId());

        unset($obj->user['externalOptions']);
        unset($obj->user['extra_info']);
        $obj->user['canStream'] = $obj->user['canStream'] || $obj->user['isAdmin'];
        $obj->user['DonationButtons'] = _json_decode(@$obj->user['DonationButtons']);


        $obj->livestream = LiveTransmition::createTransmitionIfNeed($user->getBdId());

        $str = "{$obj->livestream['key']}";
        $encrypt = encryptString($str);

        $url = Live::getServer();

        $obj->livestream["users_id"] = $user->getBdId();
        $obj->livestream["live_servers_id"] = Live::getCurrentLiveServersId();
        $obj->livestream["server"] = $p->getServer($obj->livestream["live_servers_id"]) . "?p=" . $user->getPassword();
        $obj->livestream["server_v2"] = Live::getRTMPLinkWithOutKey($user->getBdId());
        // those are for the ypt mobile app
        $obj->livestream["server_v3"] = addLastSlash($url);
        $obj->livestream["key_v3"] = "{$obj->livestream['key_with_index']}?s={$encrypt}";

        $obj->livestream["poster"] = $global['webSiteRootURL'] . Live::getRegularPosterImage($user->getBdId(), $obj->livestream["live_servers_id"], 0, 0);
        $obj->livestream["joinURL"] = Live::getLinkToLiveFromUsers_idAndLiveServer($user->getBdId(), $obj->livestream["live_servers_id"]);

        $obj->livestream["activeLives"] = array();
        $obj->livestream["latestLives"] = array();
        $obj->livestream["scheduledLives"] = array();
        $obj->wallet = array('isEnabled' => false, 'balance' => 0, 'balance_formated' => '');

        if (AVideoPlugin::isEnabledByName('Live')) {
            $rows = LiveTransmitionHistory::getActiveLiveFromUser($parameters['users_id'], '', '', 100);

            foreach ($rows as $value) {
                $value['live_transmitions_history_id'] = $value['id'];
                $value['joinURL'] = LiveTransmitionHistory::getLinkToLive($value['id']);
                $value['isPrivate'] = LiveTransmitionHistory::isPrivate($value['id']);
                $value['isPasswordProtected'] = LiveTransmitionHistory::isPasswordProtected($value['id']);
                $value['isRebroadcast'] = LiveTransmitionHistory::isRebroadcast($value['id']);
                $obj->livestream["activeLives"][] = $value;
            }

            $rows = LiveTransmitionHistory::getLastsLiveHistoriesFromUser($parameters['users_id'], 5, true);

            foreach ($rows as $value) {
                $value['live_transmitions_history_id'] = $value['id'];
                $value['joinURL'] = LiveTransmitionHistory::getLinkToLive($value['id']);
                $obj->livestream["latestLives"][] = $value;
            }

            $rows = Live_schedule::getAllActiveLimit($parameters['users_id']);

            foreach ($rows as $value) {
                $obj->livestream["scheduledLives"][] = $value;
            }
        }

        if ($walletObj = AVideoPlugin::loadPluginIfEnabled('YPTWallet')) {
            $wallet = $walletObj->getOrCreateWallet($parameters['users_id']);

            $obj->wallet['isEnabled'] = true;
            $obj->wallet['balance'] = $walletObj->getBalance($parameters['users_id']);
            $obj->wallet['balance_formated'] = YPTWallet::formatCurrency($obj->wallet['balance'], false);
        }

        return new ApiObject("", false, $obj);
    }

    /**
     * Return a users list
     * @param array $parameters
     * 'APISecret' is required for this call
     * ['rowCount' max numbers of rows]
     * ['current' current page]
     * ['searchPhrase' to search on the user and name columns]
     * ['status' if passed will filter active or inactive users]
     * ['isAdmin' if passed will filter only admin]
     * ['isCompany' if passed will filter only companies]
     * ['canUpload' if passed will filter only users that can upload]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&status=a&rowCount=3&searchPhrase=test
     * @return \ApiObject
     */
    public function get_api_users_list($parameters)
    {
        global $global;
        $obj = $this->startResponseObject($parameters);
        if (self::isAPISecretValid()) {
            $status = '';
            if (!empty($_GET['status'])) {
                if ($_GET['status'] === 'i') {
                    $status = 'i';
                } else {
                    $status = 'a';
                }
            }
            $isAdmin = null;
            if (!empty($_GET['isAdmin'])) {
                $isAdmin = 1;
            }
            $isCompany = null;
            if (!empty($_GET['isCompany'])) {
                $isCompany = 1;
            }
            $canUpload = null;
            if (!empty($_GET['canUpload'])) {
                $canUpload = 1;
            }


            //getAllUsers($ignoreAdmin = false, $searchFields = ['name', 'email', 'user', 'channelName', 'about'], $status = "", $isAdmin = null, $isCompany = null, $canUpload = null)
            $rows = User::getAllUsers(true, ['user', 'name'], $status, $isAdmin, $isCompany, $canUpload);

            return new ApiObject("", false, $rows);
        } else {
            return new ApiObject("API Secret is not valid");
        }
    }

    /**
     * @param array $parameters
     * ['APISecret' to list all videos]
     * ['sort' database sort column]
     * ['videos_id' the video id (will return only 1 or 0 video)]
     * ['clean_title' the video clean title (will return only 1 or 0 video)]
     * ['rowCount' max numbers of rows]
     * ['current' current page]
     * ['searchPhrase' to search on the categories]
     * ['tags_id' the ID of the tag you want to filter]
     * ['catName' the clean_Name of the category you want to filter]
     * ['channelName' the channelName of the videos you want to filter]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_videosViewsCount($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $obj = $this->startResponseObject($parameters);
        if (self::isAPISecretValid()) {
            $rows = Video::getAllVideos(Video::SORT_TYPE_VIEWABLE, false, true);
            $totalRows = Video::getTotalVideos(Video::SORT_TYPE_VIEWABLE, false, true);
        } elseif (!empty($parameters['videos_id'])) {
            $rows = [Video::getVideo($parameters['videos_id'])];
            $totalRows = empty($rows) ? 0 : 1;
        } elseif (!empty($parameters['clean_title'])) {
            $rows = Video::getVideoFromCleanTitle($parameters['clean_title']);
            $totalRows = empty($rows) ? 0 : 1;
        } else {
            $rows = Video::getAllVideos();
            $totalRows = Video::getTotalVideos();
        }
        $objMob = AVideoPlugin::getObjectData("MobileManager");
        $viewsCount = 0;
        foreach ($rows as $key => $value) {
            if (is_object($value)) {
                $value = object_to_array($value);
            }
            $viewsCount += $value['views_count'];
        }
        $obj->totalRows = $totalRows;
        $obj->viewsCount = $viewsCount;
        return new ApiObject("", false, $obj);
    }

    /**
     * @param array $parameters
     * Return all channels on this site
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * @return \ApiObject
     */
    public function get_api_channels($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/Channel.php';
        $channels = Channel::getChannels();
        $list = [];
        foreach ($channels as $value) {
            $obj = new stdClass();
            $obj->id = $value['id'];
            $obj->photo = User::getPhoto($value['id']);
            $obj->channelLink = User::getChannelLink($value['id']);
            $obj->name = User::getNameIdentificationById($value['id']);
            $obj->channelName = $value['channelName'];

            $list[] = $obj;
        }
        return new ApiObject("", false, $list);
    }

    /**
     * @param array $parameters
     * Return a single Program (Playlists) on this site
     * 'playlists_id' or 'videos_id' if it is a serie
     * If you pass the playlists_id it will only return if the program belongs to you
     * if you pass videos_id it will return only if you have rights to watch the video
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&playlists_id=12
     * @return \ApiObject
     */
    public function get_api_program($parameters)
    {
        global $global;
        if (!empty($parameters['videos_id'])) {
            $v = new Video('', '', $parameters['videos_id']);
            $parameters['playlists_id'] = $v->getSerie_playlists_id();
        }
        if (empty($parameters['playlists_id'])) {
            return new ApiObject("playlists_id is required");
        }
        require_once $global['systemRootPath'] . 'objects/playlist.php';
        $obj = new PlayList($parameters['playlists_id']);
        if (empty($obj)) {
            forbiddenPage();
        }
        if (empty($parameters['videos_id'])) {
            if (!empty($obj->getUsers_id())) {
                forbidIfItIsNotMyUsersId($obj->getUsers_id());
            }
        } else {
            $cansee = User::canWatchVideoWithAds($parameters['videos_id']);
            if (!$cansee) {
                return new ApiObject("You cannot watch this video");
            }
        }
        $obj = new stdClass();
        $obj->videos = PlayList::getAllFromPlaylistsID($parameters['playlists_id']);

        return new ApiObject("", false, $obj);
    }

    /**
     * @param array $parameters
     * Return all Programs (Playlists) on this site
     * ['onlyWithVideos' can be 0 or 1 return only programs that contain videos, the default is 1]
     * ['returnFavoriteAndWatchLater' can be 0 or 1, the default is 0]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * @return \ApiObject
     */
    public function get_api_programs($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/playlist.php';

        $config = new AVideoConf();
        $users_id = User::getId();
        $list = [];
        if (!empty($users_id)) {
            //getAllFromUserLight($userId, $publicOnly = true, $status = false, $playlists_id = 0, $onlyWithVideos = false, $includeSeries = false)
            $playlists = PlayList::getAllFromUserLight($users_id, false, false, 0, _empty($parameters['onlyWithVideos']) ? 0 : 1, true);
            foreach ($playlists as $value) {
                $videosArrayId = PlayList::getVideosIdFromPlaylist($value['id']);
                if (!_empty($parameters['onlyWithVideos']) && empty($videosArrayId)) {
                    continue;
                }
                if (_empty($parameters['returnFavoriteAndWatchLater'])) {
                    if ($value['status'] == "favorite" || $value['status'] == "watch_later") {
                        continue;
                    }
                }
                $obj = new stdClass();
                $obj->id = $value['id'];
                $obj->photo = User::getPhoto($value['users_id']);
                $obj->channelLink = User::getChannelLink($value['users_id']);
                $obj->username = User::getNameIdentificationById($value['users_id']);
                $obj->name = $value['name'];
                $obj->status = $value['status'];
                $obj->link = PlayLists::getLink($value['id']);
                $obj->videos = $value['videos'];
                $list[] = $obj;
            }
        }
        return new ApiObject("", false, $list);
    }

    /**
     * @param array $parameters
     * Create new programs
     * 'name' the new program name
     * 'status' the new program status ['public', 'private', 'unlisted', 'favorite', 'watch_later']
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&name=NewPL&status=unlisted
     * @return \ApiObject
     */
    public function set_api_create_programs($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/playlist.php';
        $users_id = User::getId();
        if (empty($users_id)) {
            return new ApiObject("You must login first");
        }
        $array = array('name');
        foreach ($array as $value) {
            if (empty($parameters[$value])) {
                return new ApiObject("{$value} cannot be empty");
            }
        }

        $plugin = AVideoPlugin::loadPluginIfEnabled("PlayLists");
        if (empty($plugin)) {
            return new ApiObject("Plugin not enabled");
        }

        $playList = new PlayList(0);
        $playList->setName($parameters['name']);
        $playList->setStatus(@$parameters['status']);

        $obj = new stdClass();
        $obj->error = empty($playList->save());

        return new ApiObject("", false, $obj);
    }

    /**
     * @param array $parameters
     * Delete programs
     * 'playlists_id' the id of the program you want to delete
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&playlists_id=12
     * @return \ApiObject
     */
    public function set_api_delete_programs($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/playlist.php';
        $users_id = User::getId();
        if (empty($users_id)) {
            return new ApiObject("You must login first");
        }
        $array = array('playlists_id');
        foreach ($array as $value) {
            if (empty($parameters[$value])) {
                return new ApiObject("{$value} cannot be empty");
            }
        }

        $plugin = AVideoPlugin::loadPluginIfEnabled("PlayLists");
        if (empty($plugin)) {
            return new ApiObject("Plugin not enabled");
        }

        $playList = new PlayList($parameters['playlists_id']);
        if (empty($playList) || User::getId() !== $playList->getUsers_id()) {
            return new ApiObject("Permission denied");
        }

        $obj = new stdClass();
        $obj->error = empty($playList->delete());

        return new ApiObject("", false, $obj);
    }

    /**
     * @param array $parameters
     * Return all Programs (Playlists) on this site
     * 'videos_id'
     * 'playlists_id' ,
     * 'add' 1 = will add, 0 = will remove,
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=11&playlists_id=10&add=1
     * @return \ApiObject
     */
    public function set_api_programs($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/playlist.php';
        $users_id = User::getId();
        if (empty($users_id)) {
            return new ApiObject("You must login first");
        }
        $array = array('videos_id', 'playlists_id');
        foreach ($array as $value) {
            if (empty($parameters[$value])) {
                return new ApiObject("{$value} cannot be empty");
            }
        }

        $obj = new stdClass();
        $obj->error = true;
        $obj->status = 0;

        $plugin = AVideoPlugin::loadPluginIfEnabled("PlayLists");
        if (empty($plugin)) {
            return new ApiObject("Plugin not enabled");
        }

        if (!PlayLists::canAddVideoOnPlaylist($parameters['videos_id'])) {
            return new ApiObject("You can not add this video on playlist");
        }

        $playList = new PlayList($parameters['playlists_id']);
        if (empty($playList) || User::getId() !== $playList->getUsers_id() || empty($parameters['videos_id'])) {
            return new ApiObject("Permission denied");
        }

        $obj->error = false;
        $obj->status = $playList->addVideo($parameters['videos_id'], $parameters['add']);

        return new ApiObject("", false, $obj);
    }

    /**
     * @param array $parameters
     * Return all Subscribers from an user
     * 'users_id' users ID
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?users_id=1&APIName={APIName}&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_subscribers($parameters)
    {
        global $global;

        $name = "get_api_subscribers" . json_encode($parameters);
        $subscribers = ObjectYPT::getCacheGlobal($name, 3600);
        if (empty($subscribers)) {
            $obj = $this->startResponseObject($parameters);
            if (self::isAPISecretValid()) {
                return new ApiObject("Invalid APISecret");
            }
            if (empty($parameters['users_id'])) {
                return new ApiObject("User ID can not be empty");
            }
            require_once $global['systemRootPath'] . 'objects/subscribe.php';
            $subscribers = Subscribe::getAllSubscribes($parameters['users_id']);
            ObjectYPT::setCacheGlobal($name, $subscribers);
        }
        return new ApiObject("", false, $subscribers);
    }

    /**
     * @param array $parameters
     * Return all categories on this site
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * @return \ApiObject
     */
    public function get_api_categories($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/category.php';
        $categories = Category::getAllCategories();
        array_multisort(array_column($categories, 'hierarchyAndName'), SORT_ASC, $categories);
        $list = [];
        foreach ($categories as $value) {
            $obj = new stdClass();
            $obj->id = $value['id'];
            $obj->iconClass = $value['iconClass'];
            $obj->hierarchyAndName = $value['hierarchyAndName'];
            $obj->name = $value['name'];
            $obj->clean_name = $value['clean_name'];
            $obj->fullTotal = $value['fullTotal'];
            $obj->total = $value['total'];
            $list[] = $obj;
        }
        return new ApiObject("", false, $list);
    }

    /**
     * @param array $parameters
     * 'videos_id' the video ID what you want to get the likes
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1
     * @return \ApiObject
     */
    public function get_api_likes($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/like.php';
        if (empty($parameters['videos_id'])) {
            return new ApiObject("Videos ID can not be empty");
        }
        return new ApiObject("", false, Like::getLikes($parameters['videos_id']));
    }

    /**
     * @param array $parameters (all parameters are mandatories)
     * 'videos_id' the video ID what you want to send the like
     * 'user' username of the user that will login
     * 'pass' password  of the user that will login
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123
     * @return \ApiObject
     */
    public function set_api_like($parameters)
    {
        return $this->like($parameters, 1);
    }

    /**
     * @param array $parameters (all parameters are mandatories)
     * 'videos_id' the video ID what you want to send the like
     * 'user' username of the user that will login
     * 'pass' password  of the user that will login
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123
     * @return \ApiObject
     */
    public function set_api_dislike($parameters)
    {
        return $this->like($parameters, -1);
    }

    /**
     * @param array $parameters (all parameters are mandatories)
     * 'videos_id' the video ID what you want to send the like
     * 'user' username of the user that will login
     * 'pass' password  of the user that will login
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123
     * @return \ApiObject
     */
    public function set_api_removelike($parameters)
    {
        return $this->like($parameters, 0);
    }

    /**
     *
     * @param array $parameters
     * 'user' username of the user
     * 'pass' password  of the user
     * ['encodedPass' tell the script id the password submitted  is raw or encrypted]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return string
     */
    public function get_api_signIn($parameters)
    {
        global $global;
        $this->getToPost();
        require_once $global['systemRootPath'] . 'objects/login.json.php';
        exit;
    }

    /**
     *
     * @param array $parameters
     * 'user' username of the user
     * 'pass' password  of the user
     * 'email' email of the user
     * 'name' real name of the user
     * ['emailVerified' 1 = email verified]
     * ['canCreateMeet' 1 = Can create meetings]
     * ['canStream' 1 = Can make live streams]
     * ['canUpload' 1 = Can upload files]
     * 'APISecret' mandatory for security reasons
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&user=admin&pass=123&email=me@mysite.com&name=Yeshua
     * @return string
     */
    public function set_api_signUp($parameters)
    {
        global $global;
        $this->getToPost();
        $obj = $this->getDataObject();
        if ($obj->APISecret !== @$_GET['APISecret']) {
            return new ApiObject("APISecret Not valid");
        }
        $ignoreCaptcha = 1;
        if (isset($_REQUEST['emailVerified'])) {
            $global['emailVerified'] = intval($_REQUEST['emailVerified']);
        }
        if (isset($_REQUEST['canCreateMeet'])) {
            $global['canCreateMeet'] = intval($_REQUEST['canCreateMeet']);
        }
        if (isset($_REQUEST['canStream'])) {
            $global['canStream'] = intval($_REQUEST['canStream']);
        }
        if (isset($_REQUEST['canUpload'])) {
            $global['canUpload'] = intval($_REQUEST['canUpload']);
        }
        require_once $global['systemRootPath'] . 'objects/userCreate.json.php';
        exit;
    }

    private function like($parameters, $like)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/like.php';
        if (empty($parameters['videos_id'])) {
            return new ApiObject("Videos ID can not be empty");
        }
        if (!User::isLogged()) {
            return new ApiObject("User must be logged");
        }
        new Like($like, $parameters['videos_id']);

        $obj = Like::getLikes($parameters['videos_id']);
        if (empty($obj)) {
            $obj = new stdClass();
        }

        return new ApiObject("", false, $obj);
    }

    /**
     * If you do not pass the user and password, it will always show ads, if you pass it the script will check if will display ads or not
     * @param array $parameters
     * 'videos_id' the video id to calculate the ads length
     * ['optionalAdTagUrl' a tag number 1 or 2 or 3 or 4 to use another tag, if do not pass it will use the default tag]
     * ['user' username of the user]
     * ['pass' password  of the user]
     * ['encodedPass' tell the script id the password submitted  is raw or encrypted]
     * @example for XML response: {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true&optionalAdTagUrl=2
     * @example for JSON response: {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true&optionalAdTagUrl=2&json=1
     * @return string
     */
    public function get_api_vmap($parameters)
    {
        global $global;
        $this->getToPost();
        header('Content-type: application/xml');
        if (AVideoPlugin::isEnabledByName('GoogleAds_IMA')) {
            require_once $global['systemRootPath'] . 'plugin/GoogleAds_IMA/VMAP.php';
        } else if (AVideoPlugin::isEnabledByName('AD_Server')) {
            require_once $global['systemRootPath'] . 'plugin/AD_Server/VMAP.php';
        } else if (AVideoPlugin::isEnabledByName('AdsForJesus')) {
            $videos_id = getVideos_id();
            $url = AdsForJesus::getVMAPURL($videos_id);
            if (!empty($url)) {
                echo url_get_contents($url);
            }
        } else {
            echo '<?xml version="1.0" encoding="UTF-8"?><vmap:VMAP xmlns:vmap="http://www.iab.net/videosuite/vmap" version="1.0"></vmap:VMAP> ';
            echo '<!-- VMAP API not found --> ';
        }
        exit;
    }

    /**
     * If you do not pass the user and password, it will always show ads, if you pass it the script will check if will display ads or not
     * @param array $parameters
     * 'videos_id' the video id to calculate the ads length
     * ['optionalAdTagUrl' a tag number 1 or 2 or 3 or 4 to use another tag, if do not pass it will use the default tag]
     * ['user' username of the user]
     * ['pass' password  of the user]
     * ['encodedPass' tell the script id the password submitted  is raw or encrypted]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true&optionalAdTagUrl=2
     * @return string
     */
    public function get_api_vast($parameters)
    {
        global $global;
        $this->getToPost();
        $vastOnly = 1;
        require_once $global['systemRootPath'] . 'plugin/GoogleAds_IMA/VMAP.php';
        exit;
    }

    /**
     * Return the location based on the provided IP
     * @param array $parameters
     * 'APISecret' mandatory for security reasons
     * 'ip' Ip to verify
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&ip=2.20.147.123
     * @return string
     */
    public function get_api_IP2Location($parameters)
    {
        global $global;
        $this->getToPost();
        $obj = $this->getDataObject();
        if ($obj->APISecret !== @$_GET['APISecret']) {
            return new ApiObject("APISecret Not valid");
        }
        if (AVideoPlugin::isEnabledByName("User_Location")) {
            $row = IP2Location::getLocation($parameters['ip']);
            if (!empty($row)) {
                return new ApiObject("", false, $row);
            }
        }
        return new ApiObject("IP2Location not working");
        exit;
    }

    /**
     * Return all favorites from a user
     * @param array $parameters
     * 'user' username of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submitted  is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return string
     */
    public function get_api_favorite($parameters)
    {
        $plugin = AVideoPlugin::loadPluginIfEnabled("PlayLists");
        if (empty($plugin)) {
            return new ApiObject("Plugin disabled");
        }
        if (!User::isLogged()) {
            return new ApiObject("User must be logged");
        }
        $row = PlayList::getAllFromUser(User::getId(), false, 'favorite');
        foreach ($row as $key => $value) {
            $row[$key] = cleanUpRowFromDatabase($row[$key]);
            foreach ($value['videos'] as $key2 => $value2) {
                if (!empty($row[$key]['videos'][$key2]['next_videos_id'])) {
                    unset($_POST['searchPhrase']);
                    $row[$key]['videos'][$key2]['next_video'] = Video::getVideo($row[$key]['videos'][$key2]['next_videos_id']);
                }
                $row[$key]['videos'][$key2]['videosURL'] = getVideosURL($row[$key]['videos'][$key2]['filename']);
                $row[$key]['videos'][$key2] = cleanUpRowFromDatabase($row[$key]['videos'][$key2]);
            }
        }
        header('Content-Type: application/json');
        echo json_encode($row);
        exit;
    }

    /**
     * add a video into a user favorite play list
     * @param array $parameters
     * 'videos_id' the video id that you want to add
     * 'user' username of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submitted  is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return string
     */
    public function set_api_favorite($parameters)
    {
        $this->favorite($parameters, true);
    }

    /**
     * @param array $parameters
     * 'videos_id' the video id that you want to remove
     * 'user' username of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submitted  is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return string
     */
    public function set_api_removeFavorite($parameters)
    {
        $this->favorite($parameters, false);
    }

    private function favorite($parameters, $add)
    {
        global $global;
        $plugin = AVideoPlugin::loadPluginIfEnabled("PlayLists");
        if (empty($plugin)) {
            return new ApiObject("Plugin disabled");
        }
        if (!User::isLogged()) {
            return new ApiObject("Wrong user or password");
        }
        $_REQUEST['videos_id'] = $parameters['videos_id'];
        $_REQUEST['add'] = $add;
        $_REQUEST['playlists_id'] = PlayLists::getFavoriteIdFromUser(User::getId());
        header('Content-Type: application/json');
        require_once $global['systemRootPath'] . 'objects/playListAddVideo.json.php';
        exit;
    }

    /**
     * Return all watch_later from a user
     * @param array $parameters
     * 'user' username of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submitted  is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return string
     */
    public function get_api_watch_later($parameters)
    {
        $plugin = AVideoPlugin::loadPluginIfEnabled("PlayLists");
        if (empty($plugin)) {
            return new ApiObject("Plugin disabled");
        }
        if (!User::isLogged()) {
            return new ApiObject("User must be logged");
        }
        $row = PlayList::getAllFromUser(User::getId(), false, 'watch_later');
        foreach ($row as $key => $value) {
            unset($row[$key]['password']);
            unset($row[$key]['recoverPass']);
            foreach ($value['videos'] as $key2 => $value2) {
                //$row[$key]['videos'][$key2] = Video::getVideo($value2['id']);
                unset($row[$key]['videos'][$key2]['password']);
                unset($row[$key]['videos'][$key2]['recoverPass']);
                if (!empty($row[$key]['videos'][$key2]['next_videos_id'])) {
                    unset($_POST['searchPhrase']);
                    $row[$key]['videos'][$key2]['next_video'] = Video::getVideo($row[$key]['videos'][$key2]['next_videos_id']);
                }
                $row[$key]['videos'][$key2]['videosURL'] = getVideosURL($row[$key]['videos'][$key2]['filename']);
                unset($row[$key]['videos'][$key2]['password']);
                unset($row[$key]['videos'][$key2]['recoverPass']);
            }
        }
        echo json_encode($row);
        exit;
    }

    /**
     * add a video into a user watch_later play list
     * @param array $parameters
     * 'videos_id' the video id that you want to add
     * 'user' username of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submitted  is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return string
     */
    public function set_api_watch_later($parameters)
    {
        $this->watch_later($parameters, true);
    }

    /**
     * @param array $parameters
     * 'videos_id' the video id that you want to remove
     * 'user' username of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submitted  is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return string
     */
    public function set_api_removeWatch_later($parameters)
    {
        $this->watch_later($parameters, false);
    }

    private function watch_later($parameters, $add)
    {
        global $global;
        $plugin = AVideoPlugin::loadPluginIfEnabled("PlayLists");
        if (empty($plugin)) {
            return new ApiObject("Plugin disabled");
        }
        if (!User::isLogged()) {
            return new ApiObject("Wrong user or password");
        }
        $_POST['videos_id'] = $parameters['videos_id'];
        $_POST['add'] = $add;
        $_POST['playlists_id'] = PlayLists::getWatchLaterIdFromUser(User::getId());
        require_once $global['systemRootPath'] . 'objects/playListAddVideo.json.php';
        exit;
    }

    /**
     * @param array $parameters
     * Try this API <a href="../Chat2/api.html">here</a>
     * 'message' the message for the chat
     * ['users_id'] User's ID to what this message will be sent to (send the users_id or room_users_id)
     * ['room_users_id'] User's ID from the channel where this message will be sent to (send the users_id or room_users_id)
     * 'message' URL encoded message
     * 'user' username of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submitted  is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&message=HelloWorld&users_id=2&room_users_id=4&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return string
     */
    public function set_api_chat2_message($parameters)
    {
        global $global;
        $plugin = AVideoPlugin::loadPluginIfEnabled("Chat2");
        if (empty($plugin)) {
            return new ApiObject("Plugin disabled");
        }
        if (!User::isLogged()) {
            return new ApiObject("User must be logged");
        }
        $_POST['message'] = @$parameters['message'];
        $_GET['users_id'] = @$parameters['users_id'];
        $_GET['room_users_id'] = @$parameters['room_users_id'];
        include $global['systemRootPath'] . 'plugin/Chat2/sendMessage.json.php';
        exit;
    }

    /**
     * @param array $parameters
     * The sample here will return 10 messages
     * Try this API <a href="../Chat2/api.html">here</a>
     * ['to_users_id'] User's ID where this message was private sent to
     * ['lower_then_id'] Chat message ID to filter the message search. will only return messages before that chat id
     * ['greater_then_id'] Chat message ID to filter the message search. will only return messages after that chat id
     * 'message' URL encoded message
     * 'user' username of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submitted  is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&greater_then_id=88&lower_then_id=98&to_users_id=2&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return string
     */
    public function get_api_chat2_chat($parameters)
    {
        global $global;
        $plugin = AVideoPlugin::loadPluginIfEnabled("Chat2");
        if (empty($plugin)) {
            return new ApiObject("Plugin disabled");
        }
        if (!User::isLogged()) {
            return new ApiObject("User must be logged");
        }
        $_GET['to_users_id'] = @$parameters['to_users_id'];
        $_GET['lower_then_id'] = @$parameters['lower_then_id'];

        if (!empty($parameters['greater_then_id'])) {
            if (empty($_SESSION['chatLog'])) {
                $_SESSION['chatLog'] = [];
            }
            if (empty($_SESSION['chatLog'][$_GET['to_users_id']])) {
                $_SESSION['chatLog'][$_GET['to_users_id']] = [];
            }
            $_SESSION['chatLog'][$_GET['to_users_id']][0]['id'] = $parameters['greater_then_id'];
        }

        include $global['systemRootPath'] . 'plugin/Chat2/getChat.json.php';
        exit;
    }

    /**
     * @param array $parameters
     * The sample here will return 10 messages id greater then 88 and lower then 98
     * Try this API <a href="../Chat2/api.html">here</a>
     * ['room_users_id'] User's ID (channel) where this message was public sent to
     * ['lower_then_id'] Chat message ID to filter the message search. will only return messages before that chat id
     * ['greater_then_id'] Chat message ID to filter the message search. will only return messages after that chat id
     * 'message' URL encoded message
     * 'user' username of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submitted  is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&greater_then_id=88&lower_then_id=98&room_users_id=2&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return string
     */
    public function get_api_chat2_room($parameters)
    {
        global $global;
        $plugin = AVideoPlugin::loadPluginIfEnabled("Chat2");
        if (empty($plugin)) {
            return new ApiObject("Plugin disabled");
        }
        if (!User::isLogged()) {
            return new ApiObject("User must be logged");
        }
        $_GET['room_users_id'] = @$parameters['room_users_id'];
        $_GET['lower_then_id'] = @$parameters['lower_then_id'];

        if (!empty($parameters['greater_then_id'])) {
            if (empty($_SESSION['chatLog'])) {
                $_SESSION['chatLog'] = [];
            }
            if (empty($_SESSION['chatLog'][$_GET['to_users_id']])) {
                $_SESSION['chatLog'][$_GET['to_users_id']] = [];
            }
            $_SESSION['chatLog'][$_GET['to_users_id']][0]['id'] = $parameters['greater_then_id'];
        }

        include $global['systemRootPath'] . 'plugin/Chat2/getRoom.json.php';
        exit;
    }

    public static function getAPISecret()
    {
        $obj = AVideoPlugin::getDataObject("API");
        return $obj->APISecret;
    }

    /**
     * @param array $parameters
     * Return available locales translations
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * @return string
     */
    public function get_api_locales($parameters)
    {
        global $global, $config;
        $langs = new stdClass();
        $langs->default = $config->getLanguage();
        $langs->options = getEnabledLangs();
        $langs->isRTL = isRTL();
        return new ApiObject("", false, $langs);
    }

    /**
     * @param array $parameters
     * 'language' specify what translation array the API should return, for example cn = chinese
     * Return available locales translations
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&language=cn
     * @return string
     */
    public function get_api_locale($parameters)
    {
        global $global, $config;
        $obj = $this->startResponseObject($parameters);

        if (empty($parameters['language'])) {
            return new ApiObject("You must specify a language");
        }
        $parameters['language'] = strtolower($parameters['language']);
        $file = "{$global['systemRootPath']}locale/{$parameters['language']}.php";
        if (!file_exists("{$file}")) {
            return new ApiObject("This language does not exists");
        }
        include $file;
        if (empty($t)) {
            return new ApiObject("This language is empty");
        }

        return new ApiObject("", false, $t);
    }

    /**
     * @param array $parameters
     * ['APISecret' mandatory for security reasons - required]
     * ['user' username of the user - required]
     * ['backgroundImg' URL path of the image - optional]
     * ['profileImg' URL path of the image - optional]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&user=admin
     * @return \ApiObject
     */
    public function set_api_userImages($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        if (self::isAPISecretValid()) {
            $user = new User("", $parameters['user'], false);
            if (empty($user->getUser())) {
                return new ApiObject("User Not defined");
            }

            // UPDATED USER
            $updateUser = $user->updateUserImages($parameters);

            return new ApiObject("", false, $updateUser);
        } else {
            return new ApiObject("API Secret is not valid");
        }
    }

    /**
     *
     * @param array $parameters
     * 'user' username of the user
     * 'pass' password  of the user
     * ['encodedPass' tell the script id the password submitted  is raw or encrypted]
     * ['time' default is today but the options are [today|upcoming|past]]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return string
     */
    public function get_api_meet($parameters)
    {
        global $global;
        $meet = AVideoPlugin::loadPluginIfEnabled('Meet');
        if ($meet) {
            $time = 'today';

            if (!empty($_REQUEST['time'])) {
                $time = $_REQUEST['time'];
            }

            $meets = Meet_schedule::getAllFromUsersId(User::getId(), $time, true, false);

            foreach ($meets as $key => $value) {
                $RoomPassword = '';
                if (self::isAPISecretValid() || Meet::isModerator($value['id']) || Meet::canJoinMeet($value['id'])) {
                    $RoomPassword = $value['password'];
                }

                $meets[$key] = cleanUpRowFromDatabase($value);
                $meets[$key]['RoomPassword'] = $RoomPassword;
            }
            if (empty($meets)) {
                $message = _('You do not have any meetings available. you should set one first');
            } else {
                $message = '';
            }
            //var_dump($meets);
            return new ApiObject($message, false, $meets);
        } else {
            return new ApiObject("Meet Plugin disabled");
        }
        exit;
    }

    /**
     *
     * @param array $parameters
     * 'user' username of the user
     * 'pass' password  of the user
     * 'RoomTopic' The meet title
     * ['id' the meet_schedule_id to delete]
     * ['starts' when the meet will start, the default is now]
     * ['status' a= active | i = inactive]
     * ['public' 2 = public, 1 = (Private) Logged Users Only, 0 = (Private) Specific User Groups [default value is 2]]
     * ['userGroups' user groups array]
     * ['RoomPasswordNew' the meet password]
     * ['encodedPass' tell the script id the password submitted  is raw or encrypted]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true&RoomTopic=APITestMeet
     * @return string
     */
    public function set_api_meet($parameters)
    {
        global $global;
        $meet = AVideoPlugin::loadPluginIfEnabled('Meet');
        if ($meet) {
            if (!User::canCreateMeet()) {
                return new ApiObject("You cannot create a meet");
            } else {
                include $global['systemRootPath'] . 'plugin/Meet/saveMeet.json.php';
                exit;
            }
        } else {
            return new ApiObject("Meet Plugin disabled");
        }
        exit;
    }

    /**
     *
     * @param array $parameters
     * 'user' username of the user
     * 'pass' password  of the user
     * ['encodedPass' tell the script id the password submitted  is raw or encrypted]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return string
     */
    public function get_api_notifications($parameters)
    {
        global $global;
        $plugin = AVideoPlugin::loadPluginIfEnabled('UserNotifications');
        if ($plugin) {
            $url = "{$global['webSiteRootURL']}plugin/UserNotifications/getNotifications.json.php";
            $rows = json_decode(url_get_contents($url, "", 0, false, true));
            $url = "{$global['webSiteRootURL']}plugin/Live/stats.json.php";
            $live = json_decode(url_get_contents($url, "", 0, false, true));
            $rows->live = $live;
            return new ApiObject('', false, $rows);
        } else {
            return new ApiObject("UserNotifications Plugin disabled");
        }
        exit;
    }


    /**
     * @param array $parameters
     * get the roku json
     * 'APISecret' to list all videos
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_app($parameters)
    {
        global $global, $config;
        $name = "get_api_roku" . json_encode($parameters);
        $roku = ObjectYPT::getCacheGlobal($name, 3600);
        if (empty($roku)) {
            if (AVideoPlugin::isEnabledByName("YouPHPFlix2")) {
                $url = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIPlugin=YouPHPFlix2&APIName=firstPage";
            } else {
                $url = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIPlugin=Gallery&APIName=firstPage";
            }
            $content = url_get_contents_with_cache($url);
            //$content = url_get_contents($url);
            $json = _json_decode($content);

            $roku = new stdClass();
            $roku->providerName = $config->getWebSiteTitle();
            $roku->language = "en";
            $roku->lastUpdated = date('c');
            foreach ($json->response->sections as $section) {
                $array = array();
                //var_dump($section->endpointResponse);
                if (!empty($section->endpointResponse->rows)) {
                    foreach ($section->endpointResponse->rows as $row) {
                        $movie = rowToRoku($row);
                        if (!empty($movie)) {
                            $array[] = $movie;
                        }
                    }
                }
                if (!empty($array)) {
                    $roku->{$section->title} = $array;
                }
            }
            //var_dump($roku);exit;
            $roku->cache = ObjectYPT::setCacheGlobal($name, $roku);
            $roku->cached = false;
        } else {
            $roku->cached = true;
        }
        return new ApiObject("", false, $roku);
    }

    /**
     * @param array $parameters
     *
     * Generates a one-time login code for a specific user.
     * The function takes username and password as parameters and creates a unique login code.
     * This code is then saved into a log file within a specified directory.
     * The code along with user details is encrypted before storing.
     * The code expires in 10 minutes
     *
     * ['user' username of the user]
     * ['pass' password  of the user]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b
     * @return \ApiObject Returns an ApiObject containing the encrypted user information, including the generated code.
     */
    public function set_api_login_code($parameters)
    {
        $obj = getActivationCode();
        return new ApiObject('', empty($obj['bytes']), $obj);
    }

    /**
     * @param array $parameters
     *
     * Verifies a one-time login code.
     * The function takes the one-time login code as a parameter. It then fetches and decrypts the associated user information from the log file.
     * If the file or the code does not exist, or the decrypted information is empty, it returns a message indicating the error.
     *
     * 'code' The one-time login code generated in the set_api_login_code function.
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&code=XXXX-XXXX
     * @return \ApiObject Returns an ApiObject containing the decrypted user information, or a message indicating the error.
     */
    public function get_api_login_code($parameters)
    {
        global $global, $config;
        $msg = '';
        $obj = false;
        if (!empty($parameters['code'])) {
            $path = getTmpDir('loginCodes');
            $filename = "{$path}{$parameters['code']}.log";
            if (file_exists($filename)) {
                $content = file_get_contents($filename);
                unlink($filename);
                $string = decryptString($content);
                if (!empty($string)) {
                    $obj = json_decode($string);
                    if ($obj->expires < time()) {
                        $msg = 'Code is expired';
                        $obj = false;
                    } else {
                        $obj->photo = User::getPhoto($obj->users_id);
                        $obj->identification = User::getNameIdentificationById($obj->users_id);
                        $obj->email = User::getEmailDb($obj->users_id);
                        $obj->passhash = User::getUserHash($obj->users_id, $valid = '+1 year');
                    }
                } else {
                    $msg = 'Code is corrupted';
                }
            } else {
                $msg = 'Code not found';
            }
        } else {
            $msg = 'You need to provide a code';
        }

        return new ApiObject($msg, empty($obj), $obj);
    }


    /**
     * @param array $parameters
     * 'birth_date' The birth date in Y-m-d format.
     * ['user' username of the user]
     * ['pass' password  of the user]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&birth_date=1997-06-17
     * @return \ApiObject Returns an ApiObject.
     */
    public function set_api_birth($parameters)
    {
        global $global;
        $users_id = User::getId();
        if (empty($users_id)) {
            return new ApiObject("You must login first");
        }
        $msg = '';
        $obj = new stdClass();

        $user = new User(0);
        $user->loadSelfUser();
        $user->setBirth_date($_REQUEST['birth_date']);
        $obj->users_id = $user->save();
        $obj->error = empty($obj->users_id);
        User::updateSessionInfo();

        return new ApiObject($msg, $obj->error, $obj);
    }

    /**
     * @param array $parameters
     * 'users_id' users id from what user you want the response.
     * 'APISecret' mandatory for security reasons - required
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&users_id=1
     * @return \ApiObject Returns an ApiObject.
     */
    public function get_api_is_verified($parameters)
    {
        global $global;
        if (!self::isAPISecretValid()) {
            return new ApiObject("APISecret is required");
        }
        $obj = new stdClass();
        $obj->users_id = intval($_REQUEST['users_id']);
        if (empty($obj->users_id)) {
            return new ApiObject("Users ID is required");
        }
        $user = new User($obj->users_id);
        $obj->email_verified = !empty($user->getEmailVerified());

        return new ApiObject('', false, $obj);
    }

    /**
     * @param array $parameters
     * 'users_id' users id from what user you want the response.
     * 'APISecret' mandatory for security reasons - required
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&users_id=1
     * @return \ApiObject Returns an ApiObject.
     */
    public function set_api_send_verification_email($parameters)
    {
        global $global;
        if (!self::isAPISecretValid()) {
            return new ApiObject("APISecret is required");
        }
        $obj = new stdClass();
        $obj->users_id = intval($_REQUEST['users_id']);
        if (empty($obj->users_id)) {
            return new ApiObject("Users ID is required");
        }
        $user = new User($obj->users_id);
        $obj->sent = User::sendVerificationLink($obj->users_id);
        return new ApiObject('', false, $obj);
    }

    public static function isAPISecretValid()
    {
        global $global;
        if (!empty($_REQUEST['APISecret'])) {
            $dataObj = AVideoPlugin::getDataObject('API');
            if (trim($dataObj->APISecret) === trim($_REQUEST['APISecret'])) {
                $global['bypassSameDomainCheck'] = 1;
                return true;
            }
        }
        return false;
    }

    /**
     * return true if the secret is valid and false if it is not
     * 'APISecret' mandatory for security reasons - required
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}
     * @return \ApiObject Returns an ApiObject.
     */
    public function get_api_isAPISecretValid()
    {
        global $global;
        if (!self::isAPISecretValid()) {
            return new ApiObject("APISecret is invalid");
        } else {
            return new ApiObject("APISecret is valid", false);
        }
    }

    /**
     * decrypt a string
     * 'string' mandatory
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&string=stringEncryptedToDecrypt
     * @return \ApiObject Returns an ApiObject.
     */
    public function get_api_decryptString()
    {
        $string = decryptString($_REQUEST['string']);
        return new ApiObject($string, empty($string));
    }
}

class ApiObject
{

    public $error;
    public $message;
    public $response;
    public $msg;
    public $users_id;
    public $user_age;
    public $session_id;

    public function __construct($message = "api not started or not found", $error = true, $response = [])
    {
        $response = cleanUpRowFromDatabase($response);

        $this->error = $error;
        $this->msg = $message;
        $this->message = $message;
        $this->response = $response;
        $this->users_id = User::getId();
        $this->user_age = User::getAge();
        $this->session_id = session_id();
    }
}

class SectionFirstPage
{

    public $type;
    public $title;
    public $endpoint;
    public $nextEndpoint;
    public $rowCount;
    public $endpointResponse;
    public $totalRows;
    public $childs;
    public $executionTime;

    // Add constructor, getter, and setter here
    public function __construct($type, $title, $endpoint, $rowCount, $childs = array())
    {
        global $global;
        $endpoint = addQueryStringParameter($endpoint, 'current', 1);
        $endpoint = addQueryStringParameter($endpoint, 'videoType', 'audio_and_video_and_serie');
        $endpoint = addQueryStringParameter($endpoint, 'noRelated', 1);
        $this->type = $type;
        $this->title = $title;
        $this->endpoint = $endpoint;
        $this->nextEndpoint = addQueryStringParameter($endpoint, 'current', 2);
        $this->rowCount = $rowCount;
        $endpointURL = addQueryStringParameter($endpoint, 'rowCount', $rowCount);
        if (User::isLogged()) {

            $endpointURL = addQueryStringParameter($endpointURL, 'user', User::getUserName());
            $endpointURL = addQueryStringParameter($endpointURL, 'pass', User::getUserPass());
            $endpointURL = addQueryStringParameter($endpointURL, 'webSiteRootURL', $global['webSiteRootURL']);

            //$endpointURL = addQueryStringParameter($endpointURL, 'PHPSESSID', session_id());
        }
        $start = microtime(true);
        //$endPointResponse = url_get_contents($endpointURL, '', 5, false, true);
        $endPointResponse = url_get_contents_with_cache($endpointURL, 300, '', 5, false, true);
        $this->executionTime = microtime(true) - $start;
        //_error_log(gettype($endPointResponse).' '.json_encode($endPointResponse));
        if (!empty($endPointResponse)) {
            if (is_string($endPointResponse)) {
                $response = json_decode($endPointResponse);
            } else {
                $response = $endPointResponse;
            }
            /*
              if(User::isLogged()){
              session_id($response->session_id);
              }
             */
            if (!empty($response)) {
                $this->endpointResponse = $response->response;
                $this->totalRows = $this->endpointResponse->totalRows;
            } else {
                $this->endpointResponse = new stdClass();
                $this->totalRows = 0;
            }
        }
        $this->childs = $childs;
    }
}
