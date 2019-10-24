<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class API extends PluginAbstract {

    public function getDescription() {
        return "Handle APIs for third party Applications";
    }

    public function getName() {
        return "API";
    }

    public function getUUID() {
        return "1apicbec-91db-4357-bb10-ee08b0913778";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->APISecret = md5($global['systemRootPath']);
        return $obj;
    }

    public function getPluginMenu() {
        global $global;
        $fileAPIName = $global['systemRootPath'] . 'plugin/API/pluginMenu.html';
        return file_get_contents($fileAPIName);
    }

    public function set($parameters) {
        if (empty($parameters['APIName'])) {
            $object = new ApiObject("Parameter APIName can not be empty");
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
                $object = new ApiObject();
            }
        }
        return $object;
    }

    public function get($parameters) {
        if (empty($parameters['APIName'])) {
            $object = new ApiObject("Parameter APIName can not be empty");
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
                $object = new ApiObject();
            }
        }
        return $object;
    }

    private function startResponseObject($parameters) {
        $obj = new stdClass();
        if (empty($parameters['sort']) && !empty($parameters['order'][0]['dir'])) {
            $index = intval($parameters['order'][0]['column']);
            $parameters['sort'][$parameters['columns'][$index]['data']] = $_GET['order'][0]['dir'];
        }
        $array = array('sort', 'rowCount', 'current', 'searchPhrase');
        foreach ($array as $value) {
            if (!empty($parameters[$value])) {
                $obj->$value = $parameters[$value];
                $_POST[$value] = $parameters[$value];
            }
        }

        return $obj;
    }

    private function getToPost() {
        foreach ($_GET as $key => $value) {
            $_POST[$key] = $value;
        }
    }

    /**
     * @param type $parameters 
     * ['sort' database sort column]
     * ['rowCount' max numbers of rows]
     * ['current' current page]
     * ['searchPhrase' to search on the categories]
     * ['parentsOnly' will bring only the parents, not children categories]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&rowCount=3&current=1&sort[created]=DESC
     * @return \ApiObject
     */
    public function get_api_category($parameters) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/category.php';
        $obj = $this->startResponseObject($parameters);
        $rows = Category::getAllCategories();
        $totalRows = Category::getTotalCategories();
        $obj->totalRows = $totalRows;
        $obj->rows = $rows;
        return new ApiObject("", false, $obj);
    }

    /**
     * @param type $parameters 
     * ['APISecret' to list all videos]
     * ['sort' database sort column]
     * ['videos_id' the video id (will return only 1 or 0 video)]
     * ['clean_title' the video clean title (will return only 1 or 0 video)]
     * ['rowCount' max numbers of rows]
     * ['current' current page]
     * ['searchPhrase' to search on the categories]
     * ['tags_id' the ID of the tag you want to filter]
     * ['catName' the clean_APIName of the category you want to filter]
     * ['channelName' the channelName of the videos you want to filter]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&catName=default&rowCount=10
     * @return \ApiObject
     */
    public function get_api_video($parameters) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $obj = $this->startResponseObject($parameters);
        $dataObj = $this->getDataObject();
        if ($dataObj->APISecret === @$_GET['APISecret']) {
            $rows = Video::getAllVideos("viewable", false, true);
            $totalRows = Video::getTotalVideos("viewable", false, true);
        } else if (!empty($parameters['videos_id'])) {
            $rows = array(Video::getVideo($parameters['videos_id']));
            $totalRows = empty($rows) ? 0 : 1;
        } else if (!empty($parameters['clean_title'])) {
            $rows = Video::getVideoFromCleanTitle($parameters['clean_title']);
            $totalRows = empty($rows) ? 0 : 1;
        } else {
            $rows = Video::getAllVideos();
            $totalRows = Video::getTotalVideos();
        }
        $objMob = YouPHPTubePlugin::getObjectData("MobileManager");
        $SubtitleSwitcher = YouPHPTubePlugin::loadPluginIfEnabled("SubtitleSwitcher");
        foreach ($rows as $key => $value) {
            if (is_object($value)) {
                $value = object_to_array($value);
            }
            if (empty($value['filename'])) {
                continue;
            }
            if($value['type']=='serie'){
                require_once $global['systemRootPath'] . 'objects/playlist.php';
                $rows[$key]['playlist'] = PlayList::getVideosFromPlaylist($value['serie_playlists_id']);
            }
            $images = Video::getImageFromFilename($rows[$key]['filename'], $rows[$key]['type']);
            $rows[$key]['images'] = $images;
            $rows[$key]['videos'] = Video::getVideosPaths($value['filename'], true);

            $rows[$key]['Poster'] = !empty($objMob->portraitImage) ? $images->posterPortrait : $images->poster;
            $rows[$key]['Thumbnail'] = !empty($objMob->portraitImage) ? $images->posterPortraitThumbs : $images->thumbsJpg;
            $rows[$key]['imageClass'] = !empty($objMob->portraitImage) ? "portrait" : "landscape";
            $rows[$key]['createdHumanTiming'] = humanTiming(strtotime($rows[$key]['created']));
            $rows[$key]['pageUrl'] = "{$global['webSiteRootURL']}video/" . $rows[$key]['clean_title'];
            $rows[$key]['embedUrl'] = "{$global['webSiteRootURL']}videoEmbeded/" . $rows[$key]['clean_title'];
            $rows[$key]['UserPhoto'] = User::getPhoto($rows[$key]['users_id']);

            if ($SubtitleSwitcher) {
                $rows[$key]['subtitles'] = getVTTTracks($value['filename'], true);
            }

            if (!empty($_REQUEST['complete'])) {
                require_once $global['systemRootPath'] . 'objects/comment.php';
                require_once $global['systemRootPath'] . 'objects/subscribe.php';
                unset($_POST['sort']);
                unset($_POST['current']);
                unset($_POST['searchPhrase']);
                $_POST['rowCount'] = 10;
                $_POST['sort']['created'] = "desc";
                $rows[$key]['comments'] = Comment::getAllComments($rows[$key]['id']);
                $rows[$key]['commentsTotal'] = Comment::getTotalComments($rows[$key]['id']);
                foreach ($rows[$key]['comments'] as $key2 => $value2) {
                    $user = new User($value2['users_id']);
                    $rows[$key]['comments'][$key2]['userPhotoURL'] = User::getPhoto($rows[$key]['comments'][$key2]['users_id']);
                    $rows[$key]['comments'][$key2]['userName'] = $user->getNameIdentificationBd();
                }
                $rows[$key]['subscribers'] = Subscribe::getTotalSubscribes($rows[$key]['users_id']);
            }
        }
        $obj->totalRows = $totalRows;
        $obj->rows = $rows;
        return new ApiObject("", false, $obj);
    }
    
    /**
     * @param type $parameters 
     * ['APISecret' to list all videos]
     * ['searchPhrase' to search on the categories]
     * ['tags_id' the ID of the tag you want to filter]
     * ['catName' the clean_APIName of the category you want to filter]
     * ['channelName' the channelName of the videos you want to filter]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_videosCount($parameters) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $obj = $this->startResponseObject($parameters);
        $dataObj = $this->getDataObject();
        if ($dataObj->APISecret === @$_GET['APISecret']) {
            $totalRows = Video::getTotalVideos("viewable", false, true);
        } else {
            $totalRows = Video::getTotalVideos();
        }
        $objMob = YouPHPTubePlugin::getObjectData("MobileManager");
        $SubtitleSwitcher = YouPHPTubePlugin::loadPluginIfEnabled("SubtitleSwitcher");
        $obj->totalRows = $totalRows;
        return new ApiObject("", false, $obj);
    }
    
    /**
     * @param type $parameters 
     * ['APISecret' to list all videos]
     * ['sort' database sort column]
     * ['videos_id' the video id (will return only 1 or 0 video)]
     * ['clean_title' the video clean title (will return only 1 or 0 video)]
     * ['rowCount' max numbers of rows]
     * ['current' current page]
     * ['searchPhrase' to search on the categories]
     * ['tags_id' the ID of the tag you want to filter]
     * ['catName' the clean_APIName of the category you want to filter]
     * ['channelName' the channelName of the videos you want to filter]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_videosViewsCount($parameters) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $obj = $this->startResponseObject($parameters);
        $dataObj = $this->getDataObject();
        if ($dataObj->APISecret === @$_GET['APISecret']) {
            $rows = Video::getAllVideos("viewable", false, true);
            $totalRows = Video::getTotalVideos("viewable", false, true);
        } else if (!empty($parameters['videos_id'])) {
            $rows = array(Video::getVideo($parameters['videos_id']));
            $totalRows = empty($rows) ? 0 : 1;
        } else if (!empty($parameters['clean_title'])) {
            $rows = Video::getVideoFromCleanTitle($parameters['clean_title']);
            $totalRows = empty($rows) ? 0 : 1;
        } else {
            $rows = Video::getAllVideos();
            $totalRows = Video::getTotalVideos();
        }
        $objMob = YouPHPTubePlugin::getObjectData("MobileManager");
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
     * @param type $parameters
     * 'videos_id' the video ID what you want to get the likes 
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1
     * @return \ApiObject
     */
    public function get_api_likes($parameters) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/like.php';
        if (empty($parameters['videos_id'])) {
            return new ApiObject("Videos ID can not be empty");
        }
        return new ApiObject("", false, Like::getLikes($parameters['videos_id']));
    }

    /**
     * @param type $parameters (all parameters are mandatories)
     * 'videos_id' the video ID what you want to send the like
     * 'user' usename of the user that will like the video
     * 'pass' password  of the user that will like the video
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123
     * @return \ApiObject
     */
    public function set_api_like($parameters) {
        return $this->like($parameters, 1);
    }

    /**
     * @param type $parameters (all parameters are mandatories)
     * 'videos_id' the video ID what you want to send the like
     * 'user' usename of the user that will like the video
     * 'pass' password  of the user that will like the video
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123
     * @return \ApiObject
     */
    public function set_api_dislike($parameters) {
        return $this->like($parameters, -1);
    }

    /**
     * @param type $parameters (all parameters are mandatories)
     * 'videos_id' the video ID what you want to send the like
     * 'user' usename of the user that will like the video
     * 'pass' password  of the user that will like the video
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123
     * @return \ApiObject
     */
    public function set_api_removelike($parameters) {
        return $this->like($parameters, 0);
    }

    /**
     * 
     * @param type $parameters
     * 'user' usename of the user
     * 'pass' password  of the user
     * ['encodedPass' tell the script id the password submited is raw or encrypted]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return type
     */
    public function get_api_signIn($parameters) {
        global $global;
        $this->getToPost();
        require_once $global['systemRootPath'] . 'objects/login.json.php';
        exit;
    }

    /**
     * 
     * @param type $parameters
     * 'user' usename of the user 
     * 'pass' password  of the user
     * 'email' email of the user
     * 'name' real name of the user
     * 'APISecret' mandatory for security reasons
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&user=admin&pass=123&email=me@mysite.com&name=Yeshua
     * @return type
     */
    public function set_api_signUp($parameters) {
        global $global;
        $this->getToPost();
        $obj = $this->getDataObject();
        if ($obj->APISecret !== @$_GET['APISecret']) {
            return new ApiObject("APISecret Not valid");
        }
        $ignoreCaptcha = 1;
        require_once $global['systemRootPath'] . 'objects/userCreate.json.php';
        exit;
    }

    private function like($parameters, $like) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/like.php';
        if (empty($parameters['videos_id'])) {
            return new ApiObject("Videos ID can not be empty");
        }
        if (!User::isLogged()) {
            return new ApiObject("User must be logged");
        }
        new Like($like, $parameters['videos_id']);
        return new ApiObject("", false, Like::getLikes($parameters['videos_id']));
    }

    /**
     * If you do not pass the user and password, it will always show ads, if you pass it the script will check if will display ads or not
     * @param type $parameters
     * 'videos_id' the video id to calculate the ads length
     * ['optionalAdTagUrl' a tag number 1 or 2 or 3 or 4 to use another tag, if do not pass it will use the default tag]
     * ['user' usename of the user]
     * ['pass' password  of the user]
     * ['encodedPass' tell the script id the password submited is raw or encrypted]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true&optionalAdTagUrl=2
     * @return type
     */
    public function get_api_vmap($parameters) {
        global $global;
        $this->getToPost();
        require_once $global['systemRootPath'] . 'plugin/GoogleAds_IMA/VMAP.php';
        exit;
    }

    /**
     * Return the location based on the provided IP
     * @param type $parameters
     * 'APISecret' mandatory for security reasons
     * 'ip' Ip to verify
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&ip=2.20.147.123
     * @return type
     */
    public function get_api_IP2Location($parameters) {
        global $global;
        $this->getToPost();
        $obj = $this->getDataObject();
        if ($obj->APISecret !== @$_GET['APISecret']) {
            return new ApiObject("APISecret Not valid");
        }
        if (YouPHPTubePlugin::isEnabledByName("User_Location")) {
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
     * @param type $parameters
     * 'user' usename of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submited is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return type
     */
    public function get_api_favorite($parameters) {
        $plugin = YouPHPTubePlugin::loadPluginIfEnabled("PlayLists");
        if (empty($plugin)) {
            return new ApiObject("Plugin disabled");
        }
        if (!User::isLogged()) {
            return new ApiObject("User must be logged");
        }
        $row = PlayList::getAllFromUser(User::getId(), false, 'favorite');
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
     * add a video into a user favorite play list
     * @param type $parameters
     * 'videos_id' the video id that you want to add
     * 'user' usename of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submited is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return type
     */
    public function set_api_favorite($parameters) {
        $this->favorite($parameters, true);
    }

    /**
     * @param type $parameters
     * 'videos_id' the video id that you want to remove
     * 'user' usename of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submited is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return type
     */
    public function set_api_removeFavorite($parameters) {
        $this->favorite($parameters, false);
    }

    private function favorite($parameters, $add) {
        global $global;
        $plugin = YouPHPTubePlugin::loadPluginIfEnabled("PlayLists");
        if (empty($plugin)) {
            return new ApiObject("Plugin disabled");
        }
        if (!User::isLogged()) {
            return new ApiObject("Wrong user or password");
        }
        $_POST['videos_id'] = $parameters['videos_id'];
        $_POST['add'] = $add;
        $_POST['playlists_id'] = PlayLists::getFavoriteIdFromUser(User::getId());
        require_once $global['systemRootPath'] . 'objects/playListAddVideo.json.php';
        exit;
    }

}

class ApiObject {

    public $error;
    public $message;
    public $response;

    function __construct($message = "api not started or not found", $error = true, $response = array()) {
        $this->error = $error;
        $this->message = $message;
        $this->response = $response;
    }

}
