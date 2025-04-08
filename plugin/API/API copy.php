<?php

global $global;
require_once __DIR__ . '/../../plugin/Plugin.abstract.php';

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API Plugin",
 *     description="Handle APIs for third party Applications"
 * )
 * @OA\Server(
 *     url="/",
 *     description="Default Server"
 * )
 */
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

    /**
     * @OA\Post(
     *     path="/api/set",
     *     summary="Generic API setter",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="APIName",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The name of the API to call"
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Username to authenticate"
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Password for the user"
     *     ),
     *     @OA\Parameter(
     *         name="encodedPass",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="boolean"),
     *         description="Whether the password is encoded"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="API call result",
     *         @OA\JsonContent(ref="#/components/schemas/ApiObject")
     *     )
     * )
     */
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
     * Retrieves the configuration parameters of a given plugin.
     *
     * @param array $parameters {
     *     @type string $plugin_name Required. The name of the plugin to retrieve the parameters for.
     *     @type string $APISecret   Required. A valid API secret to authorize the request.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&rowCount=3&APISecret={APISecret}
     *
     * @return \ApiObject
     */
    /**
     * @OA\Get(
     *     path="/api/plugin_parameters",
     *     summary="Get Plugin parameters",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * @param array $parameters {
     *     @type int|null    $users_id  Optional. The user ID to retrieve specific ads. Falls back to global ads if none found.
     *     @type int|null    $videos_id Optional. If provided, the owner of the video will be used to retrieve ads.
     *     @type string|null $live_key  Optional. If provided and the Live plugin is enabled, the user ID will be resolved by the live stream key.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&users_id=1
     *
     * @return array|\ApiObject An array with ads data or an ApiObject containing an error message.
     */
    /**
     * @OA\Get(
     *     path="/api/adsInfo",
     *     summary="Get Adsinfo",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Detects if the request is coming from a mobile device based on User-Agent and headers.
     *
     * @param array $parameters {
     *     @type string      $userAgent   Required. Typically the value of $_SERVER["HTTP_USER_AGENT"].
     *     @type string|array $httpHeaders Optional. Typically the value of getallheaders() or $_SERVER["HTTP_X_REQUESTED_WITH"].
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&userAgent=Mozilla%2F5.0+...
     *
     * @return \ApiObject Object with detection result and debug information.
     */
    /**
     * @OA\Get(
     *     path="/api/id",
     *     summary="Get Id",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
     */
    public function get_api_id($parameters)
    {
        global $global;
        $obj = $this->startResponseObject($parameters);
        $obj->id = getPlatformId();
        $obj->isAPISecretValid = self::isAPISecretValid();

        return new ApiObject("", false, $obj);
    }

    /**
     * Checks if the request is coming from a mobile device based on User-Agent and HTTP headers.
     *
     * @param array $parameters {
     *     @type string          $userAgent   Required. Typically the value of $_SERVER["HTTP_USER_AGENT"].
     *     @type string|array|null $httpHeaders Optional. Usually the value of json_encoded getallheaders() or $_SERVER["HTTP_X_REQUESTED_WITH"].
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&userAgent=Mozilla%2F5.0...
     *
     * @return \ApiObject Object containing `userAgent`, `httpHeaders`, and a boolean `isMobile` flag.
     */
    /**
     * @OA\Get(
     *     path="/api/is_mobile",
     *     summary="Get Is mobile",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Retrieves categories with optional filters such as pagination, search, and specific category name.
     *
     * @param array $parameters {
     *     @type array       $sort         Optional. Sorting options, e.g., ['created' => 'DESC'].
     *     @type int|null    $rowCount     Optional. Maximum number of rows to return.
     *     @type int|null    $current      Optional. Current page number.
     *     @type string|null $searchPhrase Optional. Search keyword to filter categories.
     *     @type bool|null   $parentsOnly  Optional. If true, only parent categories will be returned.
     *     @type string|null $catName      Optional. The `clean_name` of a specific category to retrieve.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&rowCount=3&current=1&sort[created]=DESC
     *
     * @return \ApiObject Object containing filtered categories, metadata, and image information.
     */

    /**
     * @OA\Get(
     *     path="/api/category",
     *     summary="Get Category",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Retrieves a specific video from a program (playlist) based on its index.
     *
     * @param array $parameters {
     *     @type string   $APISecret     Required. A valid API secret to authorize the request.
     *     @type int|null $playlists_id  Optional. The ID of the playlist (program) to retrieve the video from.
     *     @type int|null $index         Optional. The index (position) of the video within the playlist.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&playlists_id=1&index=2&APISecret={APISecret}
     *
     * @return \ApiObject Object containing video details and channel metadata.
     */
    /**
     * @OA\Get(
     *     path="/api/video_from_program",
     *     summary="Get Video from program",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Retrieves a specific audio track from a program (playlist) based on its index.
     *
     * @param array $parameters {
     *     @type string   $APISecret     Required. A valid API secret to authorize the request.
     *     @type int|null $playlists_id  Optional. The ID of the playlist (program) to retrieve the audio from.
     *     @type int|null $index         Optional. The index (position) of the audio within the playlist.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&playlists_id=1&index=2&APISecret={APISecret}
     *
     * @return \ApiObject Object containing audio details and channel metadata.
     */
    /**
     * @OA\Get(
     *     path="/api/audio_from_program",
     *     summary="Get Audio from program",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
     */
    public function get_api_audio_from_program($parameters)
    {
        $parameters['audioOnly'] = 1;
        return $this->get_api_video_from_program($parameters);
    }

    /**
     * Retrieves a list of suggested programs (playlists) including a default "Date Added" group.
     *
     * @param array $parameters Currently not used but reserved for future filtering or customization.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     *
     * @return \ApiObject Object containing a list of suggested playlists with channel and video information.
     */
    /**
     * @OA\Get(
     *     path="/api/suggested_programs",
     *     summary="Get Suggested programs",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Returns all tags from the VideoTags plugin and lists the latest videos from the tags the user is subscribed to.
     *
     * @param array $parameters {
     *     @type int|bool|null $audioOnly Optional. If set to 1 or true, the response will include audio-only (MP3) versions of the videos.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     *
     * @return \ApiObject Object containing tag information and related videos, if subscribed.
     */
    /**
     * @OA\Get(
     *     path="/api/tags",
     *     summary="Get Tags",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Returns detailed information and file sources for a specific video.
     *
     * @param array $parameters {
     *     @type string $APISecret Required. A valid API secret to authorize access to video details or list all the videos.
     *     @type int    $videos_id Required. The ID of the video to retrieve.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&APISecret={APISecret}
     *
     * @return \ApiObject Object containing video metadata, duration, file path, sources, and images.
     */
    /**
     * @OA\Get(
     *     path="/api/video_file",
     *     summary="Get Video file",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Checks if a specific user can watch a given video, with or without ads.
     *
     * @param array $parameters {
     *     @type int $videos_id Required. The ID of the video to check.
     *     @type int $users_id  Required. The ID of the user to verify access for.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     *
     * @return \ApiObject Object containing access permission flags for the specified user and video.
     */
    /**
     * @OA\Get(
     *     path="/api/user_can_watch_video",
     *     summary="Get User can watch video",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Verifies whether the provided password is correct for a given video.
     * If the video has no password, it will return true.
     *
     * @param array $parameters {
     *     @type int    $videos_id      Required. The ID of the video to check.
     *     @type string $video_password Required. The password provided by the user.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     *
     * @return \ApiObject Object indicating whether the password is correct for the video.
     */
    /**
     * @OA\Get(
     *     path="/api/video_password_is_correct",
     *     summary="Get Video password is correct",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Retrieves all Pay-Per-View (PPV) plans associated with a specific video.
     *
     * @param array $parameters {
     *     @type int $videos_id Required. The ID of the video to retrieve PPV plans for.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=2
     *
     * @return \ApiObject Object containing the list of PPV plans and formatted pricing.
     */
    /**
     * @OA\Get(
     *     path="/api/ppv_plans",
     *     summary="Get Ppv plans",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Processes the purchase of a Pay-Per-View (PPV) plan for a video and deducts the value from the user's wallet.
     * Verifies if the user is logged in and has sufficient balance, then returns the updated plan status and wallet info.
     *
     * @param array $parameters {
     *     @type int    $plans_id Required. The ID of the PPV plan to be purchased.
     *     @type int    $videos_id Required. The ID of the video to be unlocked.
     *     @type string $user Optional. Username of the user (used for authentication if session is not set).
     *     @type string $pass Optional. Password of the user (used for authentication if session is not set).
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=2&plans_id=4
     *
     * @return \ApiObject Object with purchase result, user info, and plan details.
     */
    /**
     * @OA\Post(
     *     path="/api/ppv_buy",
     *     summary="Set Ppv buy",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * Retrieves all subscription plans associated with a specific video.
     *
     * @param array $parameters {
     *     @type int $videos_id Required. The ID of the video to retrieve subscription plans for.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=2
     *
     * @return \ApiObject Object containing the list of available subscription plans for the video.
     */
    /**
     * @OA\Get(
     *     path="/api/subscription_plans",
     *     summary="Get Subscription plans",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Processes the purchase of a subscription plan for a video and deducts the value from the user's wallet.
     * Verifies if the user is logged in and has sufficient balance, then returns the subscription status.
     *
     * @param array $parameters {
     *     @type int    $plans_id Required. The ID of the subscription plan to be purchased.
     *     @type int    $videos_id Required. The ID of the video to associate with the subscription.
     *     @type string $user Optional. Username of the user (used for authentication if session is not set).
     *     @type string $pass Optional. Password of the user (used for authentication if session is not set).
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&plans_id=2
     *
     * @return \ApiObject Object with subscription result, user info, and plan details.
     */
    /**
     * @OA\Post(
     *     path="/api/subscription_buy",
     *     summary="Set Subscription buy",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * Retrieves a list of videos with advanced filtering and sorting options.
     * Supports filtering by category, tag, user/channel, type, and more.
     * Includes pagination, playlist-compatible format, subtitles, comments, related videos, and advertising data.
     *
     * @param array $parameters {
     *     @type string       $APISecret      Optional. If provided and valid, returns all videos regardless of restrictions.
     *     @type array|string $sort           Optional. Sorting options (e.g., sort[created]=desc, sort[trending]=1).
     *     @type int          $videos_id      Optional. The ID of a specific video to retrieve.
     *     @type string       $clean_title    Optional. The clean title of a specific video to retrieve.
     *     @type int          $rowCount       Optional. Maximum number of rows to return (for pagination).
     *     @type int          $current        Optional. Current page number (required for 'trending' sort to work properly).
     *     @type string       $searchPhrase   Optional. Search phrase to filter categories.
     *     @type int          $tags_id        Optional. ID of a tag to filter videos by.
     *     @type string|array $catName        Optional. Clean name(s) of category(ies) to include.
     *     @type string|array $doNotShowCats  Optional. Clean name(s) of category(ies) to exclude.
     *     @type string       $channelName    Optional. Filter videos by channel name.
     *     @type int|bool     $playlist       Optional. If set to 1, returns data in playlist-compatible format.
     *     @type string       $videoType      Optional. Type of video (e.g., 'video', 'audio', 'serie', 'embed', etc.).
     *     @type int|bool     $is_serie       Optional. 0 to return only videos, 1 for series, unset for all.
     *     @type int|bool     $noRelated      Optional. If set, disables fetching related videos.
     * }
     *
     * @example {webSiteRootURL}plugin/API/get.json.php?APIName={APIName}&catName=default&rowCount=10
     * @example Suggested  → {webSiteRootURL}plugin/API/get.json.php?APIName={APIName}&rowCount=10&sort[suggested]=1
     * @example DateAdded  → {webSiteRootURL}plugin/API/get.json.php?APIName={APIName}&rowCount=10&sort[created]=desc
     * @example Trending   → {webSiteRootURL}plugin/API/get.json.php?APIName={APIName}&rowCount=10&sort[trending]=1&current=1
     * @example Shorts     → {webSiteRootURL}plugin/API/get.json.php?APIName={APIName}&rowCount=10&sort[shorts]=1
     * @example MostWatched→ {webSiteRootURL}plugin/API/get.json.php?APIName={APIName}&rowCount=10&sort[views_count]=desc
     *
     * @return \ApiObject Object containing video rows, metadata, and related details such as comments, subtitles, ads, and images.
     */
    /**
     * @OA\Get(
     *     path="/api/video",
     *     summary="Get Video",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Updates video metadata such as title, description, category, privacy options, and more.
     * Requires proper authentication (APISecret or valid user credentials with permission to edit the video).
     *
     * @param array $parameters {
     *     @type int          $videos_id      Required. The ID of the video to update.
     *     @type string|null  $user           Optional. Username for authentication.
     *     @type string|null  $pass           Optional. Password for authentication.
     *     @type string|null  $APISecret      Optional. Valid API secret key for authentication.
     *     @type int|null     $next_videos_id Optional. ID of the next suggested video.
     *     @type string|null  $title          Optional. New video title.
     *     @type string|null  $status         Optional. Video status (e.g., 'public', 'private').
     *     @type string|null  $description    Optional. Video description.
     *     @type int|null     $categories_id  Optional. ID of the new category.
     *     @type int|null     $can_download   Optional. 1 to allow download, 0 to disallow.
     *     @type int|null     $can_share      Optional. 1 to allow sharing, 0 to disallow.
     *     @type int|null     $only_for_paid  Optional. 1 for paid-only access, 0 otherwise.
     *     @type string|null  $video_password Optional. Password required to watch the video.
     *     @type string|null  $trailer1       Optional. URL for the trailer.
     *     @type string|null  $rrating        Optional. Rating ('g', 'pg', 'pg-13', 'r', 'nc-17', 'ma').
     *     @type string|null  $created        Optional. Custom creation date (requires admin or valid APISecret).
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b
     *
     * @return \ApiObject Object containing the result of the update operation or an error message.
     */
    /**
     * @OA\Post(
     *     path="/api/video_save",
     *     summary="Set Video save",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * Retrieves the total count of videos, with optional filters based on categories, tags, and channel.
     * If APISecret is valid, it lists all videos regardless of restrictions.
     *
     * @param array $parameters {
     *     @type string $APISecret   Optional. If provided and valid, returns all videos.
     *     @type string $searchPhrase Optional. Phrase to search within the categories.
     *     @type int    $tags_id     Optional. ID of the tag to filter the videos.
     *     @type string $catName     Optional. Clean API name of the category to filter videos by.
     *     @type string $channelName Optional. Name of the channel to filter videos by.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}
     *
     * @return \ApiObject Object containing the total count of videos.
     */
    /**
     * @OA\Get(
     *     path="/api/videosCount",
     *     summary="Get Videoscount",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Deletes a video by its ID if the authenticated user has permission or a valid APISecret is provided.
     *
     * @param array $parameters {
     *     @type int    $videos_id Required. The ID of the video to delete.
     *     @type string $APISecret Optional. If provided and valid, bypasses user authentication.
     *     @type string $user      Optional. Username for authentication (if APISecret is not used).
     *     @type string $pass      Optional. Password for authentication (if APISecret is not used).
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123&APISecret={APISecret}
     *
     * @return \ApiObject Object indicating success or failure of the delete operation.
     */
    /**
     * @OA\Get(
     *     path="/api/video_delete",
     *     summary="Get Video delete",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Creates or updates a comment on a specific video. Requires user authentication or a valid APISecret.
     *
     * @param array $parameters {
     *     @type string $comment     Required. The content of the comment.
     *     @type int    $videos_id   Required. The ID of the video that will receive the comment.
     *     @type int    $id          Optional. The ID of the comment to edit.
     *     @type string $APISecret   Optional. If provided and valid, bypasses user authentication.
     *     @type string $user        Optional. Username of the user posting the comment.
     *     @type string $pass        Optional. Password of the user posting the comment.
     *     @type int    $comments_id Optional. The parent comment ID (for replies).
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123&APISecret={APISecret}
     *
     * @return \ApiObject Object containing the result and the new or updated comment ID.
     */
    /**
     * @OA\Post(
     *     path="/api/comment",
     *     summary="Set Comment",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * Retrieves all comments for a given video, with optional authentication and pagination.
     *
     * @param array $parameters {
     *     @type int    $videos_id Required. The ID of the video to retrieve comments for.
     *     @type string $APISecret Optional. If provided and valid, bypasses user authentication.
     *     @type string $user      Optional. Username for authentication.
     *     @type string $pass      Optional. Password for authentication.
     *     @type int    $rowCount  Optional. Maximum number of comments to return.
     *     @type int    $current   Optional. Current page number for pagination.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123&APISecret={APISecret}
     *
     * @return \ApiObject Object containing the list of comments with user metadata.
     */
    /**
     * @OA\Get(
     *     path="/api/comment",
     *     summary="Get Comment",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Retrieves live schedule information. If a specific ID is provided, returns only that record.
     * Requires user authentication.
     *
     * @param array $parameters {
     *     @type int|null    $live_schedule_id Optional. The ID of the live schedule to retrieve.
     *     @type string      $user             Required. Username for authentication.
     *     @type string      $pass             Required. Password for authentication.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     *
     * @return \ApiObject Object containing the live schedule record(s).
     */
    /**
     * @OA\Get(
     *     path="/api/live_schedule",
     *     summary="Get Live schedule",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Retrieves one or more live schedule records. If `live_schedule_id` is provided, returns only the specified record.
     * Requires user authentication.
     *
     * @param array $parameters {
     *     @type int|null $live_schedule_id Optional. The ID of the specific live schedule to retrieve.
     *     @type string   $user             Required. Username of the user for authentication.
     *     @type string   $pass             Required. Password of the user for authentication.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     *
     * @return \ApiObject Object containing one or more live schedule entries.
     */
    /**
     * @OA\Post(
     *     path="/api/live_schedule_delete",
     *     summary="Set Live schedule delete",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * Creates or updates a live schedule record. Allows uploading poster images and setting metadata.
     * Requires user authentication with streaming permissions.
     *
     * @param array $parameters {
     *     @type int|null    $live_schedule_id         Optional. If provided, updates the existing record.
     *     @type int|null    $live_servers_id          Optional. Live server ID (default is 0).
     *     @type string|null $base64PNGImageRegular    Optional. Base64-encoded regular poster image.
     *     @type string|null $base64PNGImagePreRoll    Optional. Base64-encoded pre-roll poster image.
     *     @type string|null $base64PNGImagePostRoll   Optional. Base64-encoded post-roll poster image.
     *     @type string      $title                    Required when creating. Title of the live schedule.
     *     @type string|null $description              Optional. Description of the live stream.
     *     @type string      $scheduled_time           Required when creating. Date and time in 'YYYY-mm-dd HH:ii:ss' format.
     *     @type string      $status                   Required. 'a' for active or 'i' for inactive.
     *     @type string|null $scheduled_password       Optional. Password to restrict access to the live stream.
     *     @type string      $user                     Required. Username of the user.
     *     @type string      $pass                     Required. Password of the user.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     *
     * @return \ApiObject Object containing the created or updated live_schedule_id.
     */
    /**
     * @OA\Post(
     *     path="/api/live_schedule",
     *     summary="Set Live schedule",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * Retrieves live stream statistics using the Live plugin's `stats.json.php` endpoint.
     * This will immediately return the JSON response and terminate execution.
     *
     * @param array $parameters Not used in this endpoint.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     *
     * @return \ApiObject This method exits early and does not return an ApiObject. The response is handled by `stats.json.php`.
     */
    /**
     * @OA\Get(
     *     path="/api/livestreams",
     *     summary="Get Livestreams",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
     */
    public function get_api_livestreams($parameters)
    {
        global $global;
        require_once $global['systemRootPath'] . 'plugin/Live/stats.json.php';
        exit;
    }

    /**
     * Creates or updates a livestream record for a user. Can optionally reset the livestream key.
     * Requires authentication using either APISecret or user credentials.
     *
     * @param array $parameters {
     *     @type string|null $title     Optional. The title of the livestream.
     *     @type int|null    $public    Optional. 1 for public listing, 0 for private/unlisted.
     *     @type string|null $APISecret Optional. If provided and valid, bypasses user authentication.
     *     @type int|null    $users_id  Optional. The user ID. Required if APISecret is used.
     *     @type int|null    $resetKey  Optional. Set to 1 to reset the livestream key.
     *     @type string|null $user      Optional. Username for authentication (if APISecret is not used).
     *     @type string|null $pass      Optional. Password for authentication (if APISecret is not used).
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&users_id=1
     *
     * @return \ApiObject Object containing the updated livestream data or an error message.
     */
    /**
     * @OA\Post(
     *     path="/api/livestream_save",
     *     summary="Set Livestream save",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
                if ($parameters['resetKey']) {
                    LiveTransmition::resetTransmitionKey($parameters['users_id']);
                }
                $trans = LiveTransmition::getFromDb($obj->id, true);
                return new ApiObject("", false, $trans);
            } else {
                return new ApiObject("Error on save");
            }
        } else {
            return new ApiObject("API Secret is not valid");
        }
    }

    /**
     * Returns livestream and wallet information for a specific user.
     * Requires authentication using either APISecret or user credentials.
     *
     * @param array $parameters {
     *     @type string|null $APISecret Optional. If provided and valid, bypasses user authentication.
     *     @type int|null    $users_id  Optional. The ID of the user. If not set, uses the logged-in user.
     *     @type string|null $user      Optional. Username for authentication (if APISecret is not used).
     *     @type string|null $pass      Optional. Password for authentication (if APISecret is not used).
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&users_id=1
     *
     * @return \ApiObject Object containing user info, livestream settings, active/live/scheduled streams, and wallet info.
     */
    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Get User",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Returns a filtered list of users. Requires a valid APISecret.
     *
     * @param array $parameters {
     *     @type string $APISecret   Required. API secret key for authentication.
     *     @type int|null $rowCount  Optional. Maximum number of users to return.
     *     @type int|null $current   Optional. Current page number for pagination.
     *     @type string|null $searchPhrase Optional. Search term to filter by username or name.
     *     @type string|null $status       Optional. Filter by user status: 'a' (active) or 'i' (inactive).
     *     @type int|null $isAdmin         Optional. Filter to return only admin users.
     *     @type int|null $isCompany       Optional. Filter to return only company users.
     *     @type int|null $canUpload       Optional. Filter to return only users who can upload.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&status=a&rowCount=3&searchPhrase=test
     *
     * @return \ApiObject Object containing the list of users matching the filters.
     */
    /**
     * @OA\Get(
     *     path="/api/users_list",
     *     summary="Get Users list",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Returns the total number of videos and their combined views count.
     *
     * @param array $parameters {
     *     @type string|null $APISecret     Optional. If provided and valid, grants access to all videos.
     *     @type string|null $sort          Optional. Sort by column name (e.g., views_count).
     *     @type int|null    $videos_id     Optional. Specific video ID to retrieve.
     *     @type string|null $clean_title   Optional. Clean title to retrieve a specific video.
     *     @type int|null    $rowCount      Optional. Maximum number of rows to return.
     *     @type int|null    $current       Optional. Current page number for pagination.
     *     @type string|null $searchPhrase  Optional. Search term for filtering.
     *     @type int|null    $tags_id       Optional. Filter by tag ID.
     *     @type string|null $catName       Optional. Filter by category clean name.
     *     @type string|null $channelName   Optional. Filter by channel name.
     * }
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}
     *
     * @return \ApiObject Object containing totalRows and viewsCount for the filtered video set.
     */
    /**
     * @OA\Get(
     *     path="/api/videosViewsCount",
     *     summary="Get Videosviewscount",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Returns a list of all channels on the site.
     *
     * @param array $parameters Currently unused.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     *
     * @return \ApiObject List of channels with basic information:
     *                    - id: Channel ID
     *                    - photo: Channel photo URL
     *                    - channelLink: Channel link URL
     *                    - name: User Display name
     *                    - channelName: Unique channel identifier
     */
    /**
     * @OA\Get(
     *     path="/api/channels",
     *     summary="Get Channels",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Returns a single Program (Playlist) from the site.
     *
     * @param array $parameters
     * - playlists_id (int) Optional. The playlist ID. Required if videos_id is not provided.
     * - videos_id (int) Optional. If provided, retrieves the program associated with the video (if it's part of a series).
     *
     * Behavior:
     * - If `playlists_id` is provided, the program is returned only if it belongs to the logged-in user.
     * - If `videos_id` is provided, the function will resolve the associated playlist and ensure the user has permission to watch it.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&playlists_id=12
     *
     * @return \ApiObject Object with the list of videos in the program (property: videos).
     */
    /**
     * @OA\Get(
     *     path="/api/program",
     *     summary="Get Program",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Returns all Programs (Playlists) available for the authenticated user.
     *
     * @param array $parameters
     * - onlyWithVideos (int) Optional. 0 or 1. If 1, only returns programs that contain videos. Default is 1.
     * - returnFavoriteAndWatchLater (int) Optional. 0 or 1. If 1, includes "favorite" and "watch later" programs. Default is 0.
     *
     * Notes:
     * - The user must be logged in.
     * - Each program includes metadata such as ID, photo, username, link, and associated videos.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     *
     * @return \ApiObject List of programs with metadata.
     */
    /**
     * @OA\Get(
     *     path="/api/programs",
     *     summary="Get Programs",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Create a new Program (Playlist).
     *
     * @param array $parameters
     * - name (string, required) The name of the new program.
     * - status (string, optional) The program visibility status. Accepted values: 'public', 'private', 'unlisted', 'favorite', 'watch_later'.
     *
     * Notes:
     * - The user must be logged in.
     * - The PlayLists plugin must be enabled.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&name=NewPL&status=unlisted
     *
     * @return \ApiObject Success or error status.
     */
    /**
     * @OA\Post(
     *     path="/api/create_programs",
     *     summary="Set Create programs",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * Delete an existing Program (Playlist).
     *
     * @param array $parameters
     * - playlists_id (int, required) The ID of the program to delete.
     *
     * Notes:
     * - The user must be logged in and must own the playlist.
     * - The PlayLists plugin must be enabled.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&playlists_id=12
     *
     * @return \ApiObject Success or error status.
     */
    /**
     * @OA\Post(
     *     path="/api/delete_programs",
     *     summary="Set Delete programs",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * Add or remove a video from a Program (Playlist).
     *
     * @param array $parameters
     * - videos_id (int, required) The ID of the video.
     * - playlists_id (int, required) The ID of the playlist (program).
     * - add (int, required) Use 1 to add the video to the playlist or 0 to remove it.
     *
     * Notes:
     * - The user must be logged in.
     * - The PlayLists plugin must be enabled.
     * - The user must be the owner of the playlist.
     * - The user must have permission to add the video to the playlist.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=11&playlists_id=10&add=1
     *
     * @return \ApiObject Success or error status.
     */
    /**
     * @OA\Post(
     *     path="/api/programs",
     *     summary="Set Programs",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * Retrieve all subscribers for a specific user.
     *
     * @param array $parameters
     * - users_id (int, required) The user ID to retrieve the subscribers for.
     * - APISecret (string, required) A valid API secret to authenticate the request.
     *
     * Notes:
     * - Caches the result for 1 hour (3600 seconds).
     * - Requires a valid APISecret.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?users_id=1&APIName={APIName}&APISecret={APISecret}
     *
     * @return \ApiObject A list of subscribers or an error object.
     */
    /**
     * @OA\Get(
     *     path="/api/subscribers",
     *     summary="Get Subscribers",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Retrieve all categories available on the site.
     *
     * @param array $parameters
     * (No parameters are required for this request)
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     *
     * @return \ApiObject A list of all site categories with basic information such as ID, icon class, name, and totals.
     */
    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Get Categories",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Retrieve the number of likes for a specific video.
     *
     * @param array $parameters
     * @param int $parameters['videos_id'] (required) The ID of the video for which you want to retrieve the like count.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1
     *
     * @return \ApiObject An object containing the total number of likes for the specified video.
     */
    /**
     * @OA\Get(
     *     path="/api/likes",
     *     summary="Get Likes",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Register a "like" for a specific video from a logged-in user.
     *
     * @param array $parameters (all parameters are mandatory)
     * @param int $parameters['videos_id'] The ID of the video to like.
     * @param string $parameters['user'] The username of the user performing the like action.
     * @param string $parameters['pass'] The password of the user.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123
     *
     * @return \ApiObject Result of the like operation.
     */
    /**
     * @OA\Post(
     *     path="/api/like",
     *     summary="Set Like",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function set_api_like($parameters)
    {
        return $this->like($parameters, 1);
    }

    /**
     * Register a "dislike" for a specific video from a logged-in user.
     *
     * @param array $parameters (all parameters are mandatory)
     * @param int $parameters['videos_id'] The ID of the video to dislike.
     * @param string $parameters['user'] The username of the user performing the dislike action.
     * @param string $parameters['pass'] The password of the user.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123
     *
     * @return \ApiObject Result of the dislike operation.
     */
    /**
     * @OA\Post(
     *     path="/api/dislike",
     *     summary="Set Dislike",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function set_api_dislike($parameters)
    {
        return $this->like($parameters, -1);
    }

    /**
     * Remove a previously registered like/dislike from a specific video by a logged-in user.
     *
     * @param array $parameters (all parameters are mandatory)
     * @param int $parameters['videos_id'] The ID of the video to remove the like/dislike.
     * @param string $parameters['user'] The username of the user performing the action.
     * @param string $parameters['pass'] The password of the user.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123
     *
     * @return \ApiObject Result of the like removal operation.
     */
    /**
     * @OA\Post(
     *     path="/api/removelike",
     *     summary="Set Removelike",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function set_api_removelike($parameters)
    {
        return $this->like($parameters, 0);
    }

    /**
     * Authenticate a user and return a session token or error.
     *
     * @param array $parameters
     * @param string $parameters['user'] The username of the user.
     * @param string $parameters['pass'] The user's password (either raw or encrypted).
     * @param bool [$parameters['encodedPass']] Optional. Set to true if the password is already encrypted.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     *
     * @return string JSON encoded authentication response or error message.
     */
    /**
     * @OA\Get(
     *     path="/api/signIn",
     *     summary="Get Signin",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
     */
    public function get_api_signIn($parameters)
    {
        global $global;
        $this->getToPost();
        require_once $global['systemRootPath'] . 'objects/login.json.php';
        exit;
    }

    /**
     * Register a new user on the platform.
     *
     * @param array $parameters
     * @param string $parameters['user'] The desired username of the user.
     * @param string $parameters['pass'] The password for the user.
     * @param string $parameters['email'] The email address of the user.
     * @param string $parameters['name'] The full name of the user.
     * @param int [$parameters['emailVerified']] Optional. Set to 1 if the user's email is already verified.
     * @param int [$parameters['canCreateMeet']] Optional. Set to 1 if the user is allowed to create meetings.
     * @param int [$parameters['canStream']] Optional. Set to 1 if the user is allowed to start live streams.
     * @param int [$parameters['canUpload']] Optional. Set to 1 if the user is allowed to upload videos.
     * @param string $parameters['APISecret'] Required. Secret key to authorize the API request.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&user=admin&pass=123&email=me@mysite.com&name=Yeshua
     *
     * @return string JSON encoded success or error response.
     */
    /**
     * @OA\Post(
     *     path="/api/signUp",
     *     summary="Set Signup",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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

    /**
     * Handle like/dislike/removal actions for a video.
     *
     * @param array $parameters
     * @param int $like The like value to apply:
     *                  1 = like,
     *                  -1 = dislike,
     *                  0 = remove like/dislike.
     *
     * @param int $parameters['videos_id'] The ID of the video to apply the like action.
     *
     * @return \ApiObject The updated like status object or an error message.
     */
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
     * Returns a VMAP (Video Multiple Ad Playlist) XML response for ads playback.
     *
     * If no user credentials are provided, ads will always be shown.
     * If valid user credentials are passed, the script will decide based on user permissions whether to show ads.
     *
     * @param array $parameters
     * @param int    $parameters['videos_id']        Required. The video ID used to calculate the ads.
     * @param int    [$parameters['optionalAdTagUrl']] Optional. A tag identifier (1, 2, 3, or 4) to select a different ad tag. Defaults to the default tag if not passed.
     * @param string [$parameters['user']]           Optional. Username to check permissions for ad display.
     * @param string [$parameters['pass']]           Optional. Password of the user.
     * @param bool   [$parameters['encodedPass']]    Optional. Indicates if the password is encrypted.
     *
     * @example XML Response:
     *     {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=secret&encodedPass=true&optionalAdTagUrl=2
     * @example JSON Response:
     *     {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=secret&encodedPass=true&optionalAdTagUrl=2&json=1
     *
     * @return string XML or JSON response depending on the request.
     */
    /**
     * @OA\Get(
     *     path="/api/vmap",
     *     summary="Get Vmap",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Returns a VAST (Video Ad Serving Template) XML response for ad playback.
     *
     * If no user credentials are provided, ads will always be shown.
     * If valid user credentials are passed, the system will determine whether ads should be shown to the user.
     *
     * @param array $parameters
     * @param int    $parameters['videos_id']        Required. The video ID used to determine ad eligibility.
     * @param int    [$parameters['optionalAdTagUrl']] Optional. Tag identifier (1, 2, 3, or 4) to select a different ad tag. Defaults to the default tag if not provided.
     * @param string [$parameters['user']]           Optional. Username of the user.
     * @param string [$parameters['pass']]           Optional. Password of the user.
     * @param bool   [$parameters['encodedPass']]    Optional. If true, indicates that the password is encrypted.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true&optionalAdTagUrl=2
     *
     * @return string XML response (VAST format)
     */
    /**
     * @OA\Get(
     *     path="/api/vast",
     *     summary="Get Vast",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Return the location based on the provided IP address.
     *
     * @param array $parameters
     * @param string $parameters['APISecret'] Required. Secret key for API access.
     * @param string $parameters['ip']        Required. The IP address to retrieve location data for.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&ip=2.20.147.123
     *
     * @return \ApiObject Location data including country, city, latitude, longitude, etc., or an error message if not available.
     */
    /**
     * @OA\Get(
     *     path="/api/IP2Location",
     *     summary="Get Ip2location",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Return all favorite videos from a specific user.
     *
     * @param array $parameters
     * @param string $parameters['user']        Required. Username of the user.
     * @param string $parameters['pass']        Required. Password of the user.
     * @param bool   $parameters['encodedPass'] Optional. If true, indicates the password is encrypted.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     *
     * @return string JSON-encoded list of favorite playlists and videos.
     */
    /**
     * @OA\Get(
     *     path="/api/favorite",
     *     summary="Get Favorite",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Add a video to the user's favorite playlist.
     *
     * @param array $parameters
     * @param int    $parameters['videos_id']   Required. ID of the video to add.
     * @param string $parameters['user']        Required. Username of the user.
     * @param string $parameters['pass']        Required. Password of the user.
     * @param bool   $parameters['encodedPass'] Optional. If true, indicates the password is encrypted.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     *
     * @return string JSON result indicating success or failure.
     */
    /**
     * @OA\Post(
     *     path="/api/favorite",
     *     summary="Set Favorite",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function set_api_favorite($parameters)
    {
        $this->favorite($parameters, true);
    }

    /**
     * Remove a video from the user's favorite playlist.
     *
     * @param array $parameters
     * @param int    $parameters['videos_id']   Required. ID of the video to remove.
     * @param string $parameters['user']        Required. Username of the user.
     * @param string $parameters['pass']        Required. Password of the user.
     * @param bool   $parameters['encodedPass'] Optional. If true, indicates the password is encrypted.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     *
     * @return string JSON result indicating success or failure.
     */
    /**
     * @OA\Post(
     *     path="/api/removeFavorite",
     *     summary="Set Removefavorite",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * Return all videos in the "watch later" playlist for a user.
     *
     * @param array $parameters
     * @param string $parameters['user']        Required. Username of the user.
     * @param string $parameters['pass']        Required. Password of the user.
     * @param bool   $parameters['encodedPass'] Optional. Indicates if the password is encrypted.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     *
     * @return string JSON encoded list of videos marked as watch later.
     */
    /**
     * @OA\Get(
     *     path="/api/watch_later",
     *     summary="Get Watch later",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Add a video to the user's "watch later" playlist.
     *
     * @param array $parameters
     * @param int    $parameters['videos_id']    Required. ID of the video to be added.
     * @param string $parameters['user']         Required. Username of the user.
     * @param string $parameters['pass']         Required. Password of the user.
     * @param bool   $parameters['encodedPass']  Optional. Indicates if the password is encrypted.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     *
     * @return string JSON encoded result message.
     */
    /**
     * @OA\Post(
     *     path="/api/watch_later",
     *     summary="Set Watch later",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function set_api_watch_later($parameters)
    {
        $this->watch_later($parameters, true);
    }

    /**
     * Remove a video from the user's "watch later" playlist.
     *
     * @param array $parameters
     * @param int    $parameters['videos_id']    Required. ID of the video to be removed.
     * @param string $parameters['user']         Required. Username of the user.
     * @param string $parameters['pass']         Required. Password of the user.
     * @param bool   $parameters['encodedPass']  Optional. Indicates if the password is encrypted.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     *
     * @return string JSON encoded result message.
     */
    /**
     * @OA\Post(
     *     path="/api/removeWatch_later",
     *     summary="Set Removewatch later",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * Send a message using the Chat2 plugin.
     *
     * @param array $parameters
     * @param string $parameters['message']         Required. The message content, must be URL encoded.
     * @param int    $parameters['users_id']        Optional. The ID of the user to send the message to.
     * @param int    $parameters['room_users_id']   Optional. The ID of the channel or room to send the message to.
     * @param string $parameters['user']            Required. Username for authentication.
     * @param string $parameters['pass']            Required. Password for authentication.
     * @param bool   $parameters['encodedPass']     Optional. Indicates whether the password is encrypted.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&message=HelloWorld&users_id=2&room_users_id=4&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return string JSON encoded result message.
     */
    /**
     * @OA\Post(
     *     path="/api/chat2_message",
     *     summary="Set Chat2 message",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * Retrieve chat messages using the Chat2 plugin.
     *
     * @param array $parameters
     * @param int    $parameters['to_users_id']       Optional. User ID for whom the private messages are intended.
     * @param int    $parameters['lower_then_id']     Optional. Only messages with ID less than this value will be returned.
     * @param int    $parameters['greater_then_id']   Optional. Only messages with ID greater than this value will be returned.
     * @param string $parameters['user']              Required. Username for authentication.
     * @param string $parameters['pass']              Required. Password for authentication.
     * @param bool   $parameters['encodedPass']       Optional. Indicates whether the password is encrypted.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&greater_then_id=88&lower_then_id=98&to_users_id=2&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return string JSON encoded chat messages.
     */
    /**
     * @OA\Get(
     *     path="/api/chat2_chat",
     *     summary="Get Chat2 chat",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Retrieve public chat messages from a specific room using the Chat2 plugin.
     *
     * @param array $parameters
     * @param int    $parameters['room_users_id']     Required. User ID of the room/channel from which to retrieve messages.
     * @param int    $parameters['lower_then_id']     Optional. Only messages with ID less than this will be returned.
     * @param int    $parameters['greater_then_id']   Optional. Only messages with ID greater than this will be returned.
     * @param string $parameters['user']              Required. Username for authentication.
     * @param string $parameters['pass']              Required. Password for authentication.
     * @param bool   $parameters['encodedPass']       Optional. Whether the password is encrypted.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&greater_then_id=88&lower_then_id=98&room_users_id=2&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return string JSON encoded chat messages from the specified room.
     */
    /**
     * @OA\Get(
     *     path="/api/chat2_room",
     *     summary="Get Chat2 room",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Retrieve available locale translations on the site.
     *
     * @param array $parameters Not used in this request.
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     *
     * @return \ApiObject
     * {
     *   "default": "en_US",             // Default language configured in the system
     *   "options": ["en_US", "pt_BR"],  // List of enabled language options
     *   "isRTL": false                  // Boolean indicating if the current language is RTL
     * }
     */
    /**
     * @OA\Get(
     *     path="/api/locales",
     *     summary="Get Locales",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Retrieve translations for a specific language.
     *
     * @param array $parameters
     *  'language' (required) ISO code of the language to retrieve translations for (e.g., 'cn' for Chinese).
     *
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&language=cn
     *
     * @return \ApiObject
     * Returns a PHP array `$t` containing key-value translation pairs for the requested language.
     *
     * Error handling:
     * - Returns error if 'language' parameter is missing.
     * - Returns error if the language file does not exist.
     * - Returns error if the translation array `$t` is empty.
     */
    /**
     * @OA\Get(
     *     path="/api/locale",
     *     summary="Get Locale",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Update user profile and/or background images using URLs.
     *
     * @param array $parameters
     *  - 'APISecret' (string, required): Secret key for API authentication.
     *  - 'user' (string, required): Username of the user to update.
     *  - 'backgroundImg' (string, optional): URL to the new background image.
     *  - 'profileImg' (string, optional): URL to the new profile image.
     *
     * @example
     * {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&user=admin
     * &backgroundImg=https%3A%2F%2Fexample.com%2Fbackground.jpg&profileImg=https%3A%2F%2Fexample.com%2Fprofile.jpg
     *
     * @return \ApiObject
     *  Returns an object with updated image info or error messages.
     *
     * Notes:
     * - Both image parameters are optional; if omitted, the corresponding image will not be updated.
     * - Requires a valid API secret.
     * - If user does not exist, returns an error.
     */
    /**
     * @OA\Post(
     *     path="/api/userImages",
     *     summary="Set Userimages",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * Returns the list of scheduled meetings for the authenticated user.
     *
     * @param array $parameters
     *  - 'user' (string, required): Username of the user.
     *  - 'pass' (string, required): Password of the user.
     *  - 'encodedPass' (bool, optional): Indicates if the password is encrypted (default: false).
     *  - 'time' (string, optional): Meeting timeframe filter. Accepted values: [today|upcoming|past]. Default: today.
     *
     * @example
     * {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=abc123&encodedPass=true
     * {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=abc123&time=past
     *
     * @return \ApiObject
     *  Returns an array of meetings with their details. If user is a moderator or has access, the room password is included.
     *  Returns a message if there are no meetings scheduled.
     *
     * Notes:
     * - Requires the Meet plugin to be enabled.
     * - Requires valid user login credentials.
     * - Meeting room password is only included if the user has permission to view it.
     */
    /**
     * @OA\Get(
     *     path="/api/meet",
     *     summary="Get Meet",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Create or delete a scheduled meeting (Meet plugin required).
     *
     * @param array $parameters
     *  - 'user' (string, required): Username of the user.
     *  - 'pass' (string, required): Password of the user.
     *  - 'RoomTopic' (string, required): The title of the meeting.
     *  - ['id'] (int, optional): If provided, deletes the meeting with this ID.
     *  - ['starts'] (string, optional): Start datetime of the meeting (default is now).
     *  - ['status'] (string, optional): 'a' for active, 'i' for inactive.
     *  - ['public'] (int, optional): 2 = public, 1 = logged-in users only, 0 = specific user groups (default: 2).
     *  - ['userGroups'] (array, optional): Array of user group IDs.
     *  - ['RoomPasswordNew'] (string, optional): Meeting password.
     *  - ['encodedPass'] (bool, optional): If true, treats the password as already encoded.
     *
     * @example
     * {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=abc123&encodedPass=true&RoomTopic=MyMeeting
     *
     * @return string
     *  - On success: returns meeting data or confirmation of deletion.
     *  - On error: returns descriptive message.
     *
     * Notes:
     * - Requires Meet plugin enabled.
     * - User must have permission to create meetings.
     * - If 'id' is passed, the function will delete the meeting instead of creating one.
     */
    /**
     * @OA\Post(
     *     path="/api/meet",
     *     summary="Set Meet",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * Return user notifications and current live streaming stats.
     *
     * @param array $parameters
     *  - 'user' (string, required): Username of the user.
     *  - 'pass' (string, required): Password of the user.
     *  - ['encodedPass'] (bool, optional): If true, indicates the password is already encrypted.
     *
     * @example
     * {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=abc123&encodedPass=true
     *
     * @return string
     *  - JSON-encoded response containing:
     *    - notifications (from UserNotifications plugin)
     *    - live (from Live plugin stats)
     *
     * Notes:
     * - Requires the **UserNotifications** plugin enabled.
     * - Also fetches live stream stats via `Live/stats.json.php`.
     * - Returns an error if the plugin is not enabled.
     */
    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     summary="Get Notifications",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * Return the Roku-compatible JSON feed for the app.
     *
     * @param array $parameters
     *  - 'APISecret' (string, required): Required to access all videos.
     *
     * @example
     * {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}
     *
     * @return \ApiObject
     *  - Returns an object with the following structure:
     *    - providerName: Website title
     *    - language: Feed language (default: 'en')
     *    - lastUpdated: ISO 8601 date string
     *    - Sections with video entries (from Gallery or YouPHPFlix2 plugin)
     *    - cache: Whether the response was newly cached
     *    - cached: Boolean indicating if the response was served from cache
     *
     * Notes:
     * - Uses `YouPHPFlix2` plugin if enabled; falls back to `Gallery`.
     * - Caches result globally for 1 hour.
     * - Requires `rowToRoku()` function to format video rows.
     */
    /**
     * @OA\Get(
     *     path="/api/app",
     *     summary="Get App",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     *
     * Description:
     * - Accepts a username and password.
     * - Generates a unique login code (one-time use).
     * - Stores the encrypted code and user data in a log file.
     * - The code expires after 10 minutes.
     *
     * Parameters:
     * - 'user' (string, required): Username of the user.
     * - 'pass' (string, required): Password of the user.
     *
     * @example
     * {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b
     *
     * @return \ApiObject
     * - success: true if code generated
     * - response: contains 'code', 'user', 'bytes' and other encrypted user data
     */
    /**
     * @OA\Post(
     *     path="/api/login_code",
     *     summary="Set Login code",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     *
     * Description:
     * - Takes a one-time login code as input.
     * - Attempts to locate and decrypt the corresponding log file.
     * - If valid, returns the user’s data.
     * - The login code is valid for a limited time and is deleted after use.
     *
     * Parameters:
     * - 'code' (string, required): The login code previously generated by `set_api_login_code`.
     *
     * @example
     * {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&code=XXXX-XXXX
     *
     * @return \ApiObject
     * - success: true if the code is valid and not expired
     * - response: object containing user ID, name, email, photo URL, and user hash
     * - error: returns appropriate message if the code is invalid, expired, not found, or corrupted
     */
    /**
     * @OA\Get(
     *     path="/api/login_code",
     *     summary="Get Login code",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     *
     * Updates the birth date of the currently logged-in user.
     *
     * Required Parameters:
     * - 'birth_date' (string): The user's birth date in Y-m-d format (e.g., "1997-06-17").
     *
     * Optional Authentication:
     * - 'user' (string): Username for authentication (if not already logged in).
     * - 'pass' (string): Password for authentication.
     *
     * @example
     * {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&birth_date=1997-06-17
     *
     * @return \ApiObject
     * - success: true if the birth date is updated successfully.
     * - response: object containing the `users_id` and `error` flag.
     * - error: true if the user is not logged in or update fails.
     */
    /**
     * @OA\Post(
     *     path="/api/birth",
     *     summary="Set Birth",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     *
     * Check if a user's email is verified.
     *
     * Required Parameters:
     * - 'users_id' (int): The ID of the user you want to check.
     * - 'APISecret' (string): Required for authentication.
     *
     * @example
     * {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&users_id=1&APISecret=YOUR_SECRET
     *
     * @return \ApiObject
     * - response: object with:
     *     - users_id: The user ID provided
     *     - email_verified: true if the user's email is verified, false otherwise
     * - error: true if the APISecret or users_id is invalid or missing
     */
    /**
     * @OA\Get(
     *     path="/api/is_verified",
     *     summary="Get Is verified",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     *
     * Send a verification email to the specified user.
     *
     * Required Parameters:
     * - 'users_id' (int): The ID of the user to whom the verification email should be sent.
     * - 'APISecret' (string): Required for authentication.
     *
     * @example
     * {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&users_id=1&APISecret=YOUR_SECRET
     *
     * @return \ApiObject
     * - response: object with:
     *     - users_id: The user ID provided
     *     - sent: true if the email was sent successfully, false otherwise
     * - error: true if APISecret is invalid or users_id is missing
     */
    /**
     * @OA\Post(
     *     path="/api/send_verification_email",
     *     summary="Set Send verification email",
     *     tags={"API"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * Check if the provided APISecret is valid.
     *
     * Required Parameters:
     * - 'APISecret' (string): The secret key to validate.
     *
     * @example
     * {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret=YOUR_SECRET
     *
     * @return \ApiObject
     * - message: "APISecret is valid" or "APISecret is invalid"
     * - error: false if valid, true if invalid
     */
    /**
     * @OA\Get(
     *     path="/api/isAPISecretValid",
     *     summary="Get Isapisecretvalid",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/decrypt",
     *     summary="Decrypt a string encrypted by the system",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="APIName",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The name of the API to call"
     *     ),
     *     @OA\Parameter(
     *         name="string",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The encrypted string to decrypt"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Decrypted string",
     *         @OA\JsonContent(ref="#/components/schemas/ApiObject")
     *     )
     * )
     */
    /**
     * @OA\Get(
     *     path="/api/decryptString",
     *     summary="Get Decryptstring",
     *     tags={"API"},
     *     @OA\Parameter(
     *         name="parameters",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="object"),
     *         description="Optional parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
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
