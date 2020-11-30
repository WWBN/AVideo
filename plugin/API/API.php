<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class API extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE,
            PluginTags::$MOBILE
        );
    }
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
     * 'plugin_name' The plugin name that you want to retreive the parameters
     * 'APISecret' to list all videos
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&rowCount=3&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_plugin_parameters($parameters) {
        global $global;
        $name = "get_api_plugin_parameters" . json_encode($parameters);
        $obj = ObjectYPT::getCache($name, 3600);
        if (empty($obj)) {
            $obj = $this->startResponseObject($parameters);
            $dataObj = $this->getDataObject();
            if (!empty($parameters['plugin_name'])) {
                if ($dataObj->APISecret === @$_GET['APISecret']) {
                    $obj->response = AVideoPlugin::getDataObject($parameters['plugin_name']);
                } else {
                    return new ApiObject("APISecret is required");
                }
            } else {
                return new ApiObject("Plugin name Not found");
            }
            ObjectYPT::setCache($name, $obj);
        }
        return new ApiObject("", false, $obj);
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
        //array_multisort(array_column($rows, 'hierarchyAndName'), SORT_ASC, $rows);
        $totalRows = Category::getTotalCategories();
        $obj->totalRows = $totalRows;
        $obj->rows = $rows;
        return new ApiObject("", false, $obj);
    }

    /**
     * @param type $parameters 
     * 'APISecret' to list all videos
     * 'playlists_id' the program id
     * 'index' the position of the video
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&playlists_id=1&index=2&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_video_from_program($parameters) {
        global $global;
        $playlists = AVideoPlugin::loadPlugin("PlayLists");
        if (empty($parameters['playlists_id'])) {
            return new ApiObject("Playlist ID is empty", true, $parameters);
        }
        $videos = PlayLists::getOnlyVideosAndAudioIDFromPlaylistLight($parameters['playlists_id']);

        if (empty($videos)) {
            return new ApiObject("There are no videos for this playlist", true, $parameters);
        }

        if (empty($parameters['index'])) {
            $parameters['index'] = 0;
        }

        if (empty($videos[$parameters['index']])) {
            $video = $videos[0];
        } else {
            $video = $videos[$parameters['index']];
        }

        $parameters['nextIndex'] = $parameters['index'] + 1;

        if (empty($videos[$parameters['nextIndex']])) {
            $parameters['nextIndex'] = 0;
        }
        
        $playlist = new PlayList($parameters['playlists_id']);
        $user = new User($playlist->getUsers_id());
        
        $videoPath = Video::getHigherVideoPathFromID($video['id']);
        $parameters['videos'] = $videos;
        $parameters['playlist_name'] = $playlist->getName();
        $parameters['modified'] = $playlist->getModified();
        $parameters['modified_timestamp'] = strtotime($parameters['modified']);
        $parameters['users_id'] = $playlist->getUsers_id();
        $parameters['channel_name'] = $user->getChannelName();
        $parameters['channel_photo'] = $user->getPhotoDB();
        $parameters['channel_bg'] = $user->getBackground();
        $parameters['channel_link'] = $user->getChannelLink();
        $parameters['totalPlaylistDuration'] = 0;
        $parameters['currentPlaylistTime'] = 0;
        foreach ($parameters['videos'] as $key => $value) {
            
            $parameters['videos'][$key]['path'] = Video::getHigherVideoPathFromID($value['id']);
            if($key && $key<=$parameters['index']){
                $parameters['currentPlaylistTime'] += durationToSeconds($parameters['videos'][$key-1]['duration']);
            }
            $parameters['totalPlaylistDuration'] += durationToSeconds($parameters['videos'][$key]['duration']);
            
            $parameters['videos'][$key]['info'] = Video::getTags($value['id']);
            $parameters['videos'][$key]['category'] = Category::getCategory($value['categories_id']);
        }
        if(empty($parameters['totalPlaylistDuration'])){
            $parameters['percentage_progress'] = 0;
        }else{
            $parameters['percentage_progress'] = ($parameters['currentPlaylistTime']/$parameters['totalPlaylistDuration'])*100;
        }
        $parameters['title'] = $video['title'];
        $parameters['videos_id'] = $video['id'];
        $parameters['path'] = $videoPath;
        $parameters['duration'] = $video['duration'];
        $parameters['duration_seconds'] = durationToSeconds($parameters['duration']);

        return new ApiObject("", false, $parameters);
    }

    /**
     * @param type $parameters 
     * 'APISecret' to list all videos
     * 'videos_id' the video id
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_video_file($parameters) {
        global $global;
        $obj = $this->startResponseObject($parameters);
        $obj->video_file = Video::getHigherVideosPathsFromID($videos_id);
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
        if (!empty($parameters['videos_id'])) {
            $status = "viewable";
            $ignoreGroup = false;
            if ($dataObj->APISecret === @$_GET['APISecret']) {
                $status = "";
                $ignoreGroup = true;
            }
            $rows = array(Video::getVideo($parameters['videos_id'], $status, $ignoreGroup));
            $totalRows = empty($rows) ? 0 : 1;
        } else if ($dataObj->APISecret === @$_GET['APISecret']) {
            $rows = Video::getAllVideos("viewable", false, true);
            $totalRows = Video::getTotalVideos("viewable", false, true);
        } else if (!empty($parameters['clean_title'])) {
            $rows = Video::getVideoFromCleanTitle($parameters['clean_title']);
            $totalRows = empty($rows) ? 0 : 1;
        } else {
            $rows = Video::getAllVideos();
            $totalRows = Video::getTotalVideos();
        }
        unsetSearch();
        $objMob = AVideoPlugin::getObjectData("MobileManager");
        $SubtitleSwitcher = AVideoPlugin::loadPluginIfEnabled("SubtitleSwitcher");
        foreach ($rows as $key => $value) {
            if (is_object($value)) {
                $value = object_to_array($value);
            }
            if (empty($value['filename'])) {
                continue;
            }
            if ($value['type'] == 'serie') {
                require_once $global['systemRootPath'] . 'objects/playlist.php';
                $rows[$key]['playlist'] = PlayList::getVideosFromPlaylist($value['serie_playlists_id']);
            }
            $images = Video::getImageFromFilename($rows[$key]['filename'], $rows[$key]['type']);
            $rows[$key]['images'] = $images;
            $rows[$key]['videos'] = Video::getVideosPaths($value['filename'], true);
            if (empty($rows[$key]['videos'])) {
                $rows[$key]['videos'] = new stdClass();
            }
            $rows[$key]['Poster'] = !empty($objMob->portraitImage) ? $images->posterPortrait : $images->poster;
            $rows[$key]['Thumbnail'] = !empty($objMob->portraitImage) ? $images->posterPortraitThumbs : $images->thumbsJpg;
            $rows[$key]['imageClass'] = !empty($objMob->portraitImage) ? "portrait" : "landscape";
            $rows[$key]['createdHumanTiming'] = humanTiming(strtotime($rows[$key]['created']));
            $rows[$key]['pageUrl'] = Video::getLink($rows[$key]['id'], $rows[$key]['clean_title'], false);
            $rows[$key]['embedUrl'] = Video::getLink($rows[$key]['id'], $rows[$key]['clean_title'], true);
            $rows[$key]['UserPhoto'] = User::getPhoto($rows[$key]['users_id']);
            $rows[$key]['isSubscribed'] = false;
            if(User::isLogged()){
                require_once $global['systemRootPath'] . 'objects/subscribe.php';
                $rows[$key]['isSubscribed'] = Subscribe::isSubscribed($rows[$key]['users_id']);
            }

            if ($SubtitleSwitcher) {
                $rows[$key]['subtitles'] = getVTTTracks($value['filename'], true);
                foreach ($rows[$key]['subtitles'] as $key2 => $value) {
                    $rows[$key]['subtitlesSRT'][] = convertSRTTrack($value);
                }
            }

            
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
            $rows[$key]['wwbnDescription'] = $rows[$key]['description'];
            //$rows[$key]['wwbnChannel'] = User::getChannelLink($rows[$key]['users_id']);
            $rows[$key]['wwbnChannelURL'] = User::getChannelLink($rows[$key]['users_id']);
            $rows[$key]['wwbnImgChannel'] = $rows[$key]['UserPhoto'];
            //$rows[$key]['wwbnProgram'] = $rows[$key]['pageUrl'];
            //$rows[$key]['wwbnProgramURL'] = $rows[$key]['pageUrl'];
            $rows[$key]['wwbnType'] = $rows[$key]['type'];
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
        //$objMob = AVideoPlugin::getObjectData("MobileManager");
        //$SubtitleSwitcher = AVideoPlugin::loadPluginIfEnabled("SubtitleSwitcher");
        $obj->totalRows = $totalRows;
        return new ApiObject("", false, $obj);
    }

    /**
     * @param type $parameters 
     * 'videos_id' the video id that will be deleted
     * ['APISecret' if passed will not require user and pass]
     * ['user' usename of the user that will like the video]
     * ['pass' password  of the user that will like the video]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_video_delete($parameters) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $obj = $this->startResponseObject($parameters);
        $dataObj = $this->getDataObject();
        if (!empty($parameters['videos_id'])) {
            if (!User::canUpload()) {
                return new ApiObject("Access denied");
            }
            if (!empty($_GET['APISecret']) && $dataObj->APISecret !== $_GET['APISecret']) {
                return new ApiObject("Secret does not match");
            }
            $obj = new Video("", "", $parameters['videos_id']);
            if (!$obj->userCanManageVideo()) {
                return new ApiObject("User cannot manage the video");
            }
            $id = $obj->delete();
            return new ApiObject("", !$id, $id);
        } else {
            return new ApiObject("Video ID is required");
        }
    }

    /**
     * @param type $parameters 
     * 'comment' String with the comment
     * 'videos_id' the video that will receive the comment
     * ['id' the comment id if you will edit some]
     * ['APISecret' if passed will not require user and pass]
     * ['user' usename of the user that will like the video]
     * ['pass' password  of the user that will like the video]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123&APISecret={APISecret}
     * @return \ApiObject
     */
    public function set_api_comment($parameters) {
        global $global;
        $obj = $this->startResponseObject($parameters);
        $dataObj = $this->getDataObject();
        if (!empty($parameters['videos_id'])) {
            if (!empty($_GET['APISecret']) && $dataObj->APISecret !== $_GET['APISecret']) {
                return new ApiObject("Secret does not match");
            } else if (!User::canComment()) {
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
            $id = $obj->save();
            return new ApiObject("", !$id, $id);
        } else {
            return new ApiObject("Video ID is required");
        }
    }

    /**
     * @param type $parameters 
     * 'videos_id' the video id that will be deleted
     * 'title' the video title
     * 'status' the video status
     * ['APISecret' if passed will not require user and pass]
     * ['user' usename of the user that will like the video]
     * ['pass' password  of the user that will like the video]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123&APISecret={APISecret}
     * @return \ApiObject
     */
    public function set_api_video_save($parameters) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $obj = $this->startResponseObject($parameters);
        $dataObj = $this->getDataObject();
        if (!empty($parameters['videos_id'])) {
            if (!User::canUpload()) {
                return new ApiObject("Access denied");
            }
            if (!empty($_GET['APISecret']) && $dataObj->APISecret !== $_GET['APISecret']) {
                return new ApiObject("Secret does not match");
            }
            $obj = new Video("", "", $parameters['videos_id']);
            if (!$obj->userCanManageVideo()) {
                return new ApiObject("User cannot manage the video");
            }
            if (!empty($parameters['title'])) {
                $obj->setTitle($parameters['title']);
            }
            $id = $obj->save();
            // set status must be after save videos parameters
            if (!empty($parameters['status'])) {
                $obj->setStatus($parameters['status']);
            }
            return new ApiObject("", !$id, $id);
        } else {
            return new ApiObject("Video ID is required");
        }
    }

    /**
     * @param type $parameters 
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * @return \ApiObject
     */
    public function get_api_livestreams($parameters) {
        global $global;
        require_once $global['systemRootPath'] . 'plugin/Live/stats.json.php';
        exit;
    }

    /**
     * Return a user livestream information
     * @param type $parameters 
     * ['title' Livestream title]
     * ['public' 1 = live is listed; 0 = not listed]
     * ['APISecret' if passed will not require user and pass]
     * ['users_id' the user ID]
     * ['user' usename if does not have the APISecret]
     * ['pass' password  if does not have the APISecret]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&users_id=1
     * @return \ApiObject
     */
    public function set_api_livestream_save($parameters) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $obj = $this->startResponseObject($parameters);
        if (empty($parameters['title']) && !isset($parameters['public'])) {
            return new ApiObject("Invalid parameters");
        }
        $dataObj = $this->getDataObject();
        if ($dataObj->APISecret === @$_GET['APISecret'] || User::isLogged()) {
            if (!empty($parameters['users_id'])) {
                if ($dataObj->APISecret !== @$_GET['APISecret']) {
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
     * @param type $parameters 
     * ['APISecret' if passed will not require user and pass]
     * ['users_id' the user ID]
     * ['user' usename if does not have the APISecret]
     * ['pass' password  if does not have the APISecret]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&users_id=1
     * @return \ApiObject
     */
    public function get_api_user($parameters) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $obj = $this->startResponseObject($parameters);
        $dataObj = $this->getDataObject();
        if ($dataObj->APISecret === @$_GET['APISecret'] || User::isLogged()) {

            if (!empty($parameters['users_id'])) {
                if ($dataObj->APISecret !== @$_GET['APISecret']) {
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

            $obj->user = User::getUserFromID($user->getBdId());
            $obj->livestream = LiveTransmition::getFromDbByUser($user->getBdId());
            $obj->livestream["live_servers_id"] = Live::getCurrentLiveServersId();
            $obj->livestream["server"] = $p->getServer($obj->livestream["live_servers_id"]) . "?p=" . $user->getPassword();
            $obj->livestream["poster"] = $global['webSiteRootURL'] . $p->getPosterImage($user->getBdId(), $obj->livestream["live_servers_id"]);
            $obj->livestream["joinURL"] = Live::getLinkToLiveFromUsers_idAndLiveServer($user->getBdId(), $obj->livestream["live_servers_id"]);

            return new ApiObject("", false, $obj);
        } else {
            return new ApiObject("API Secret is not valid");
        }
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
     * @param type $parameters
     * Return all channels on this site
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * @return \ApiObject
     */
    public function get_api_channels($parameters) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/Channel.php';
        $channels = Channel::getChannels();
        $list = array();
        foreach ($channels as $value) {
            $obj = new stdClass();
            $obj->id = $value['id'];
            $obj->photo = User::getPhoto($value['id']);
            $obj->channelLink = User::getChannelLink($value['id']);
            $obj->name = User::getNameIdentificationById($value['id']);

            $list[] = $obj;
        }
        return new ApiObject("", false, $list);
    }

    /**
     * @param type $parameters
     * Return all Programs (Playlists) on this site
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * @return \ApiObject
     */
    public function get_api_programs($parameters) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/playlist.php';
        $playlists = PlayList::getAll();
        $list = array();
        foreach ($playlists as $value) {
            $videosArrayId = PlayList::getVideosIdFromPlaylist($value['id']);
            if (empty($videosArrayId) || $value['status'] == "favorite" || $value['status'] == "watch_later") {
                continue;
            }
            $obj = new stdClass();
            $obj->id = $value['id'];
            $obj->photo = User::getPhoto($value['users_id']);
            $obj->channelLink = User::getChannelLink($value['users_id']);
            $obj->username = User::getNameIdentificationById($value['users_id']);
            $obj->name = $value['name'];
            $obj->link = PlayLists::getLink($value['id']);
            $list[] = $obj;
        }
        return new ApiObject("", false, $list);
    }

    /**
     * @param type $parameters
     * Return all Subscribers from an user
     * 'users_id' users ID
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?users_id=1&APIName={APIName}&APISecret={APISecret}
     * @return \ApiObject
     */
    public function get_api_subscribers($parameters) {
        global $global;

        $name = "get_api_subscribers" . json_encode($parameters);
        $subscribers = ObjectYPT::getCache($name, 3600);
        if (empty($subscribers)) {
            $obj = $this->startResponseObject($parameters);
            $dataObj = $this->getDataObject();
            if ($dataObj->APISecret !== @$_GET['APISecret']) {
                return new ApiObject("Invalid APISecret");
            }
            if (empty($parameters['users_id'])) {
                return new ApiObject("User ID can not be empty");
            }
            require_once $global['systemRootPath'] . 'objects/subscribe.php';
            $subscribers = Subscribe::getAllSubscribes($parameters['users_id']);
            ObjectYPT::setCache($name, $subscribers);
        }
        return new ApiObject("", false, $subscribers);
    }

    /**
     * @param type $parameters
     * Return all categories on this site
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * @return \ApiObject
     */
    public function get_api_categories($parameters) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/category.php';
        $categories = Category::getAllCategories();
        array_multisort(array_column($categories, 'hierarchyAndName'), SORT_ASC, $categories);
        $list = array();
        foreach ($categories as $value) {
            $obj = new stdClass();
            $obj->id = $value['id'];
            $obj->iconClass = $value['iconClass'];
            $obj->hierarchyAndName = $value['hierarchyAndName'];
            $obj->name = $value['name'];
            $obj->fullTotal = $value['fullTotal'];
            $obj->total = $value['total'];
            $list[] = $obj;
        }
        return new ApiObject("", false, $list);
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
     * @example for XML response: {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true&optionalAdTagUrl=2
     * @example for JSON response: {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=3&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true&optionalAdTagUrl=2&json=1
     * @return type
     */
    public function get_api_vmap($parameters) {
        global $global;
        $this->getToPost();
        require_once $global['systemRootPath'] . 'plugin/GoogleAds_IMA/VMAP.php';
        exit;
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
    public function get_api_vast($parameters) {
        global $global;
        $this->getToPost();
        $vastOnly = 1;
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
     * @param type $parameters
     * 'user' usename of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submited is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return type
     */
    public function get_api_favorite($parameters) {
        $plugin = AVideoPlugin::loadPluginIfEnabled("PlayLists");
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
        $plugin = AVideoPlugin::loadPluginIfEnabled("PlayLists");
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

    /**
     * @param type $parameters
     * Try this API <a href="../Chat2/api.html">here</a>
     * 'message' the message for the chat
     * ['users_id'] User's ID to what this message will be sent to (send the users_id or room_users_id)
     * ['room_users_id'] User's ID from the channel where this message will be sent to (send the users_id or room_users_id)
     * 'message' URL encoded message
     * 'user' usename of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submited is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&message=HelloWorld&users_id=2&room_users_id=4&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return type
     */
    public function set_api_chat2_message($parameters) {
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
     * @param type $parameters
     * The sample here will return 10 messages
     * Try this API <a href="../Chat2/api.html">here</a>
     * ['to_users_id'] User's ID where this message was private sent to
     * ['lower_then_id'] Chat message ID to filter the message search. will only return messages before that chat id
     * ['greater_then_id'] Chat message ID to filter the message search. will only return messages after that chat id
     * 'message' URL encoded message
     * 'user' usename of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submited is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&greater_then_id=88&lower_then_id=98&to_users_id=2&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return type
     */
    public function get_api_chat2_chat($parameters) {
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
                $_SESSION['chatLog'] = array();
            }
            if (empty($_SESSION['chatLog'][$_GET['to_users_id']])) {
                $_SESSION['chatLog'][$_GET['to_users_id']] = array();
            }
            $_SESSION['chatLog'][$_GET['to_users_id']][0]['id'] = $parameters['greater_then_id'];
        }

        include $global['systemRootPath'] . 'plugin/Chat2/getChat.json.php';
        exit;
    }

    /**
     * @param type $parameters
     * The sample here will return 10 messages id greater then 88 and lower then 98
     * Try this API <a href="../Chat2/api.html">here</a>
     * ['room_users_id'] User's ID (channel) where this message was public sent to
     * ['lower_then_id'] Chat message ID to filter the message search. will only return messages before that chat id
     * ['greater_then_id'] Chat message ID to filter the message search. will only return messages after that chat id
     * 'message' URL encoded message
     * 'user' usename of the user
     * 'pass' password  of the user
     * 'encodedPass' tell the script id the password submited is raw or encrypted
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&greater_then_id=88&lower_then_id=98&room_users_id=2&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return type
     */
    public function get_api_chat2_room($parameters) {
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
                $_SESSION['chatLog'] = array();
            }
            if (empty($_SESSION['chatLog'][$_GET['to_users_id']])) {
                $_SESSION['chatLog'][$_GET['to_users_id']] = array();
            }
            $_SESSION['chatLog'][$_GET['to_users_id']][0]['id'] = $parameters['greater_then_id'];
        }

        include $global['systemRootPath'] . 'plugin/Chat2/getRoom.json.php';
        exit;
    }

    static function getAPISecret() {
        $obj = AVideoPlugin::getDataObject("API");
        return $obj->APISecret;
    }

    /**
     * @param type $parameters
     * Return available locales translations
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}
     * @return type
     */
    public function get_api_locales($parameters) {
        global $global, $config;
        $langs = new stdClass();
        $langs->default = $config->getLanguage();
        $langs->options = getEnabledLangs();
        $langs->isRTL = isRTL();
        return new ApiObject("", false, $langs);
    }

    /**
     * @param type $parameters
     * 'language' specify what translation array the API should return, for example cn = chinese
     * Return available locales translations
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&language=cn
     * @return type
     */
    public function get_api_locale($parameters) {
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
     * @param type $parameters
     * ['APISecret' mandatory for security reasons - required]
     * ['user' usename of the user - required]
     * ['backgroundImg' URL path of the image - optional]
     * ['profileImg' URL path of the image - optional]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&APISecret={APISecret}&user=admin
     * @return \ApiObject
     */
    public function set_api_userImages($parameters) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        // $obj = $this->startResponseObject($parameters);
        $dataObj = $this->getDataObject();
        if ($dataObj->APISecret === @$_GET['APISecret']) {

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
