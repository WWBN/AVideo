<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';

require_once $global['systemRootPath'] . 'plugin/PlayLists/Objects/Playlists_schedules.php';
require_once $global['systemRootPath'] . 'plugin/PlayLists/PlayListElement.php';

class PlayLists extends PluginAbstract
{

    const PERMISSION_CAN_MANAGE_ALL_PLAYLISTS = 0;

    public function getTags()
    {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE,
        );
    }

    public function getDescription()
    {
        return __("Playlists or Program Playlists are identified by default as programs of content on the AVideo Platform.<br>")
            . __(" You can use the Edit Parameters button to rename it to your choosing.<br>  We recommend to keep the Program name ")
            . __("as it is defaulted to in order to be well indexed in the SearchTube and Other AVideo Platform search and network indexing tools.");
    }

    public function getName()
    {
        return 'PlayLists';
    }

    public function getUUID()
    {
        return "plist12345-370-4b1f-977a-fd0e5cabtube";
    }

    function getPermissionsOptions(): array
    {
        $permissions = array();
        $permissions[] = new PluginPermissionOption(self::PERMISSION_CAN_MANAGE_ALL_PLAYLISTS, __("Can Manage All Playlists"), __("Can Manage All Playlists"), 'PlayLists');
        return $permissions;
    }

    static function canManageAllPlaylists(): bool
    {
        if (User::isAdmin()) {
            return true;
        }
        return Permissions::hasPermission(self::PERMISSION_CAN_MANAGE_ALL_PLAYLISTS, 'PlayLists');
    }

    static function canManagePlaylist($playlists_id)
    {
        if (!User::isLogged()) {
            return false;
        }
        if (self::canManageAllPlaylists()) {
            return true;
        }
        $pl = new PlayList($playlists_id);
        if ($pl->getUsers_id() == User::getId()) {
            return true;
        }
        return false;
    }

    public function getPluginVersion()
    {
        return "3.0";
    }

    public function getEmptyDataObject()
    {
        global $global;
        $obj = new stdClass();
        $obj->name = __("Program");
        $obj->playOnSelect = true;
        $obj->autoadvance = true;
        $obj->usersCanOnlyCreatePlayListsFromTheirContent = false;
        $obj->useOldPlayList = false;
        $obj->expandPlayListOnChannels = false;
        $obj->usePlaylistPlayerForSeries = true;
        $obj->showWatchLaterOnLeftMenu = true;
        $obj->showFavoriteOnLeftMenu = true;
        $obj->showWatchLaterOnProfileMenu = true;
        $obj->showFavoriteOnProfileMenu = true;
        $obj->showPlayLiveButton = true;
        $obj->showTrailerInThePlayList = true;
        $obj->showTVFeatures = false;
        $obj->showFeed = true;

        return $obj;
    }

    public function getWatchActionButton($videos_id)
    {
        global $global, $livet;
        if (isLive() && empty($videos_id) && !empty($livet)) {
            include $global['systemRootPath'] . 'plugin/PlayLists/actionButtonLive.php';
        } else {
            if (!self::canAddVideoOnPlaylist($videos_id)) {
                return "";
            }
            $obj = $this->getDataObject();
            //echo "getNetflixActionButton: ".$videos_id;
            $btnClass = "btn btn-default no-outline";
            include $global['systemRootPath'] . 'plugin/PlayLists/actionButton.php';
        }
    }

    public function getNetflixActionButton($videos_id)
    {
        global $global;
        if (!self::canAddVideoOnPlaylist($videos_id)) {
            return "";
        }
        $obj = $this->getDataObject();
        //echo "getNetflixActionButton: ".$videos_id;
        $btnClass = "btn btn-primary";
        include $global['systemRootPath'] . 'plugin/PlayLists/actionButton.php';
    }

    public function getGalleryActionButton($videos_id)
    {
        global $global;
        if (!self::canAddVideoOnPlaylist($videos_id)) {
            return "";
        }
        $obj = $this->getDataObject();
        //echo "getNetflixActionButton: ".$videos_id;
        $btnClass = "";
        echo '<div class="">';
        include $global['systemRootPath'] . 'plugin/PlayLists/actionButton.php';
        echo '</div>';
    }

    public function getHeadCode()
    {
        global $global, $nonCriticalCSS;
        $obj = $this->getDataObject();

        $css = '<link href="' . getURL('plugin/PlayLists/style.css') . '" rel="stylesheet" type="text/css" />';

        if (!empty(getPlaylists_id()) && isEmbed()) {
            $css .= "<link href=\"" . getURL('plugin/PlayLists/playerButton.css') . "\" rel=\"stylesheet\" type=\"text/css\"/>";
        }

        return $css;
    }

    public static function loadScripts()
    {
        global $global;
        $global['laodPlaylistScript'] = 1;
    }

    public function getFooterCode()
    {
        global $global;
        $obj = $this->getDataObject();
        $js = '';
        include_once $global['systemRootPath'] . 'plugin/PlayLists/footer.php';
        if (!empty($global['laodPlaylistScript'])) {
            $js .= '<script src="' . getURL('plugin/PlayLists/script.js') . '" type="text/javascript"></script>';
        }

        if (isEmbed()) {
            if (self::showTVFeatures()) {
                $js .= '<script>' . file_get_contents("{$global['systemRootPath']}plugin/PlayLists/showOnTV.js") . '</script>';
            }
        }

        if (isLive() && self::showTVFeatures()) {
            if (
                !empty($_REQUEST['playlists_id_live']) &&
                !self::isPlaylistLive($_REQUEST['playlists_id_live']) &&
                self::canManagePlaylist($_REQUEST['playlists_id_live'])
            ) {
                $liveLink = PlayLists::getLiveLink($_REQUEST['playlists_id_live']);
                $js .= '<script>var liveLink = "' . $liveLink . '";' . file_get_contents("{$global['systemRootPath']}plugin/PlayLists/goLiveNow.js") . '</script>';
            }
        }

        if (!empty(getPlaylists_id()) && isEmbed()) {
            PlayerSkins::getStartPlayerJS(file_get_contents("{$global['systemRootPath']}plugin/PlayLists/playerButton.js"));
        }

        return $js;
    }

    static function canAddVideoOnPlaylist($videos_id)
    {
        if (empty($videos_id)) {
            return false;
        }
        $obj = AVideoPlugin::getObjectData("PlayLists");
        if (!PlayLists::canManageAllPlaylists() && $obj->usersCanOnlyCreatePlayListsFromTheirContent) {
            if (User::isLogged()) {
                $users_id = Video::getOwner($videos_id);
                if (User::getId() == $users_id) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    static function isVideoOnFavorite($videos_id, $users_id)
    {
        return PlayList::isVideoOnFavorite($videos_id, $users_id);
    }

    static function isVideoOnWatchLater($videos_id, $users_id)
    {
        return PlayList::isVideoOnWatchLater($videos_id, $users_id);
    }

    static function getFavoriteIdFromUser($users_id)
    {
        return PlayList::getFavoriteIdFromUser($users_id);
    }

    static function getWatchLaterIdFromUser($users_id)
    {
        return PlayList::getWatchLaterIdFromUser($users_id);
    }

    static function getWatchLaterLink()
    {
        if (!User::isLogged()) {
            return "";
        }
        global $global;
        return "{$global['webSiteRootURL']}watch-later";
    }

    static function getFavoriteLink()
    {
        if (!User::isLogged()) {
            return "";
        }
        global $global;
        return "{$global['webSiteRootURL']}favorite";
    }

    public function thumbsOverlay($videos_id)
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/PlayLists/buttons.php';
    }

    static function isPlayListASerie($serie_playlists_id)
    {
        global $global, $config;
        $serie_playlists_id = intval($serie_playlists_id);
        $sql = "SELECT * FROM videos WHERE serie_playlists_id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", array($serie_playlists_id));
        $video = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return $video;
    }

    static function removeSerie($serie_playlists_id)
    {
        $video = self::isPlayListASerie($serie_playlists_id);
        if (!empty($video)) {
            $video = new Video("", "", $video['id']);
            return $video->delete();
        }
        return false;
    }

    static function saveSerie($serie_playlists_id)
    {
        $playlist = new PlayList($serie_playlists_id);

        if (empty($playlist)) {
            return false;
        }

        $video = self::isPlayListASerie($serie_playlists_id);
        if (!empty($video)) {
            $filename = $video['filename'];
            $v = new Video("", "", $video['id']);
        } else {
            $filename = 'serie_playlists_' . uniqid();
            $v = new Video("", $filename);
        }
        $v->setTitle($playlist->getName());
        $v->setSerie_playlists_id($serie_playlists_id);
        $v->setUsers_id($playlist->getUsers_id());
        $v->setStatus('u');
        $v->setFilename($filename);
        $v->setType("serie");
        return $v->save();
    }

    public function getStart()
    {
        global $global;
        $whitelistedFiles = array('VMAP.php');
        $baseName = basename($_SERVER["SCRIPT_FILENAME"]);
        if (!in_array($baseName, $whitelistedFiles)) {
            if (!empty($_GET['videoName'])) {
                $obj = $this->getDataObject();
                if ($obj->usePlaylistPlayerForSeries) {
                    $video = Video::getVideoFromCleanTitle($_GET['videoName']);
                    if (!empty($video) && $video['type'] == 'serie' && !empty($video['serie_playlists_id'])) {
                        if (basename($_SERVER["SCRIPT_FILENAME"]) == "videoEmbeded.php") {
                            $link = PlayLists::getLink($video['serie_playlists_id'], true);
                        } else {
                            $link = PlayLists::getLink($video['serie_playlists_id']);
                        }
                        header("Location: {$link}");
                        exit;
                    }
                }
            }
        }
    }

    static function getLink($playlists_id, $embed = false, $playlist_index = null)
    {
        global $global;
        $obj = AVideoPlugin::getObjectData("PlayLists");
        if ($embed) {
            //$url = $global['webSiteRootURL'] . "plugin/PlayLists/embed.php?playlists_id=" . $playlists_id;
            $url = $global['webSiteRootURL'] . "playEmbed/" . $playlists_id;
        } else {
            if (empty($obj->useOldPlayList)) {
                //$url = $global['webSiteRootURL'] . "plugin/PlayLists/player.php?playlists_id=" . $playlists_id;
                $url = $global['webSiteRootURL'] . "play/" . $playlists_id;
            } else {
                $url = PlayLists::getURL($playlists_id);
            }
        }
        if (isset($playlist_index)) {
            $url = addLastSlash($url);
            //$url = addQueryStringParameter($url, 'playlist_index', $playlist_index);
            $url .= "$playlist_index";
        }
        return $url;
    }

    static function getTagLink($tags_id, $embed = false, $playlist_index = null)
    {
        global $global;
        $obj = AVideoPlugin::getObjectData("PlayLists");
        if ($embed) {
            // $url = $global['webSiteRootURL'] . "plugin/PlayLists/embed.php?tags_id=" . $tags_id;
            $url = $global['webSiteRootURL'] . "playTagEmbed/" . $tags_id;
        } else {
            //$url = $global['webSiteRootURL'] . "plugin/PlayLists/player.php?tags_id=" . $tags_id;
            $url = $global['webSiteRootURL'] . "playTag/" . $tags_id;
        }
        if (isset($playlist_index)) {
            //$url = addQueryStringParameter($url, 'playlist_index', $playlist_index);
            $url .= "/$playlist_index";
        }
        return $url;
    }

    public function navBarButtons()
    {
        global $global;

        $obj = AVideoPlugin::getObjectData("PlayLists");
        $str = "";

        if ($obj->showWatchLaterOnLeftMenu) {
            $str .= '<li>
                        <div>
                            <a href="' . self::getWatchLaterLink() . '" class="btn btn-default btn-block" style="border-radius: 0;">
                                <i class="fas fa-clock"></i>
                                <span class="menuLabel">
                                ' . __("Watch Later") . '
                                </span>
                            </a>
                        </div>
                    </li>';
        }
        if ($obj->showFavoriteOnLeftMenu) {
            $str .= '<li>
                        <div>
                            <a href="' . self::getFavoriteLink() . '" class="btn btn-default btn-block" style="border-radius: 0;">
                                <i class="fas fa-heart"></i>
                                <span class="menuLabel">
                                ' . __("Favorite") . '
                                </span>
                            </a>
                        </div>
                    </li>';
        }
        $str .= '<li>
                    <div>
                        <a href="' . "{$global['webSiteRootURL']}plugin/PlayLists/managerPlaylists.php" . '" class="btn btn-default btn-block" style="border-radius: 0;">
                            <i class="fas fa-list"></i>
                            <span class="menuLabel">
                            ' . __("Organize") . ' ' . __($obj->name) . '
                            </span>
                        </a>
                    </div>
                </li>';
        return $str;
    }

    public function navBarProfileButtons()
    {
        global $global;
        $obj = AVideoPlugin::getObjectData("PlayLists");
        $str = "";

        if ($obj->showWatchLaterOnProfileMenu) {
            $str .= '<li>
                            <a href="' . self::getWatchLaterLink() . '" class="" style="border-radius: 0;">
                                <i class="fas fa-clock"></i>
                                ' . __("Watch Later") . '
                            </a>
                    </li>';
        }
        if ($obj->showFavoriteOnProfileMenu) {
            $str .= '<li>
                            <a href="' . self::getFavoriteLink() . '" class="" style="border-radius: 0;">
                                <i class="fas fa-heart"></i>
                                ' . __("Favorite") . '
                            </a>
                    </li>';
        }
        return $str;
    }

    static function getLiveLink($playlists_id)
    {
        global $global;
        if (!self::canPlayProgramsLive()) {
            _error_log("PlayLists:getLiveLink canPlayProgramsLive() said no");
            return false;
        }
        // does it has videos?
        $videosArrayId = PlayLists::getOnlyVideosAndAudioIDFromPlaylistLight($playlists_id);
        if (empty($videosArrayId)) {
            _error_log("PlayLists:getLiveLink getOnlyVideosAndAudioIDFromPlaylistLight($playlists_id) said no");
            return false;
        }

        return "{$global['webSiteRootURL']}plugin/PlayLists/playProgramsLive.json.php?playlists_id=" . $playlists_id;
    }

    static function showPlayLiveButton()
    {
        if (!$obj = AVideoPlugin::getDataObjectIfEnabled("PlayLists")) {
            return false;
        }
        return !empty($obj->showPlayLiveButton);
    }

    static function canPlayProgramsLive()
    {
        // can the user live?
        if (!User::canStream()) {
            _error_log("Playlists:canPlayProgramsLive this user cannon stream");
            return false;
        }
        // Is API enabled
        if (!AVideoPlugin::isEnabledByName("API")) {
            _error_log("Playlists:canPlayProgramsLive you need to enable the API plugin to be able to play live programs", AVideoLog::$WARNING);
            return false;
        }
        return true;
    }

    static function getOnlyVideosAndAudioIDFromPlaylistLight($playlists_id)
    {
        global $global;
        $sql = "SELECT * FROM  playlists_has_videos p "
            . " LEFT JOIN videos v ON videos_id = v.id "
            . " WHERE playlists_id = ? AND v.status IN ('" . implode("','", Video::getViewableStatus(true)) . "')"
            . " AND (`type` = 'video' OR `type` = 'audio' ) ORDER BY p.`order` ";
        cleanSearchVar();
        $sort = @$_POST['sort'];
        $_POST['sort'] = array();
        $_POST['sort']['p.`order`'] = 'ASC';
        $_POST['sort'] = $sort;
        $res = sqlDAL::readSql($sql, "i", array($playlists_id));
        $fullData = sqlDAL::fetchAllAssoc($res);
        reloadSearchVar();
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    static function getLiveEPGLink($playlists_id, $type = 'html')
    {
        global $global;
        $pl = new PlayList($playlists_id);
        $site = get_domain($global['webSiteRootURL']);
        $channel = $pl->getUsers_id();
        $link = "{$global['webSiteRootURL']}epg.{$type}?site={$site}&channel={$channel}&playlists_id={$playlists_id}";
        return $link;
    }

    static function videosToPlaylist($videos, $index = 0, $audioOnly = false)
    {
        $parameters = array();
        $parameters['index'] = intval($index);

        while (empty($videoPath) && !empty($videos)) {

            if (empty($videos[$parameters['index']])) {
                $video = $videos[0];
            } else {
                $video = $videos[$parameters['index']];
            }

            $videoPath = Video::getHigherVideoPathFromID($video['id']);

            if (!empty($videoPath)) {
                $parameters['nextIndex'] = $parameters['index'] + 1;
                if (empty($videos[$parameters['nextIndex']])) {
                    $parameters['nextIndex'] = 0;
                }
                break;
            } else {
                unset($videos[$parameters['index']]);
                $parameters['index']++;
            }
        }
        $parameters['videos'] = array_values($videos);
        $parameters['totalPlaylistDuration'] = 0;
        $parameters['currentPlaylistTime'] = 0;
        foreach ($parameters['videos'] as $key => $value) {
            $parameters['videos'][$key]['path'] = Video::getHigherVideoPathFromID($value['id']);
            if ($key && $key <= $parameters['index']) {
                $parameters['currentPlaylistTime'] += durationToSeconds($parameters['videos'][$key - 1]['duration']);
            }
            $parameters['totalPlaylistDuration'] += durationToSeconds($parameters['videos'][$key]['duration']);

            $parameters['videos'][$key]['info'] = Video::getTags($value['id']);
            $parameters['videos'][$key]['category'] = Category::getCategory($value['categories_id']);
            $parameters['videos'][$key]['media_session'] = Video::getMediaSession($value['id']);
            $parameters['videos'][$key]['images'] = Video::getImageFromFilename_($value['filename'], $value['type']);

            if (!empty($audioOnly)) {
                $parameters['videos'][$key]['mp3'] = convertVideoToMP3FileIfNotExists($value['id']);
            }
        }
        if (empty($parameters['totalPlaylistDuration'])) {
            $parameters['percentage_progress'] = 0;
        } else {
            $parameters['percentage_progress'] = ($parameters['currentPlaylistTime'] / $parameters['totalPlaylistDuration']) * 100;
        }
        $parameters['title'] = $video['title'];
        $parameters['videos_id'] = $video['id'];
        $parameters['path'] = $videoPath;
        $parameters['duration'] = $video['duration'];
        $parameters['duration_seconds'] = durationToSeconds($parameters['duration']);

        return $parameters;
    }

    static function getLinkToLive($playlists_id)
    {
        global $global;
        $pl = new PlayList($playlists_id);
        $link = Live::getLinkToLiveFromUsers_idWithLastServersId($pl->getUsers_id());
        return $link . "?playlists_id_live={$playlists_id}";
    }

    static function getImage($playlists_id)
    {
        global $global;
        if (self::isPlaylistLive($playlists_id)) {
            return self::getLivePosterImage($playlists_id);
        }

        $serie = self::isPlayListASerie($playlists_id);
        if (!empty($serie)) {
            $tvg_logo = "{$serie['filename']}_tvg.jpg";
            $tvg_logo_path = Video::getPathToFile($tvg_logo);
            if (!file_exists($tvg_logo_path)) {
                $images = Video::getSourceFile($serie['filename']);
                $img = $images["path"];
                im_resize($img, $tvg_logo_path, 150, 150, 80);
            }

            $tvg_logo_url = Video::getURLToFile($tvg_logo);
            return $tvg_logo_url;
        } else {
            $pl = new PlayList($playlists_id);
            return User::getPhoto($pl->getUsers_id());
        }
    }

    static function getLiveImage($playlists_id)
    {
        global $global;
        if (self::isPlaylistLive($playlists_id)) {
            return self::getLivePosterImage($playlists_id);
        } else {
            return "{$global['webSiteRootURL']}plugin/Live/view/Offline.jpg";
        }
    }

    static function getNameOrSerieTitle($playlists_id)
    {
        $serie = self::isPlayListASerie($playlists_id);
        if (!empty($serie)) {
            return $serie['title'];
        } else {
            $pl = new PlayList($playlists_id);
            return $pl->getName();
        }
    }

    static function getDescriptionIfIsSerie($playlists_id)
    {
        $serie = self::isPlayListASerie($playlists_id);
        if (!empty($serie)) {
            return $serie['description'];
        }
        return "";
    }

    static function getTrailerIfIsSerie($playlists_id)
    {
        $serie = self::isPlayListASerie($playlists_id);
        if (!empty($serie)) {
            return $serie['trailer1'];
        }
        return "";
    }

    static function getLinkToM3U8($playlists_id, $key, $live_servers_id)
    {
        global $global;
        $_REQUEST['playlists_id_live'] = $playlists_id;
        $_REQUEST['live_servers_id'] = $live_servers_id;
        return Live::getM3U8File($key);
    }

    static function getM3U8File($playlists_id)
    {

        $pl = new PlayList($playlists_id);
        $users_id = intval($pl->getUsers_id());
        $key = self::getPlaylistLiveKey($playlists_id, $users_id);
        $live_servers_id = self::getPlaylistLiveServersID($playlists_id, $users_id);
        return self::getLinkToM3U8($playlists_id, $key, $live_servers_id);
    }

    static function showTVFeatures()
    {
        $obj = AVideoPlugin::getObjectData("PlayLists");
        return !empty($obj->showTVFeatures);
    }

    static function getShowOnTVSwitch($playlists_id)
    {
        if (!self::showTVFeatures()) {
            return "";
        }
        if (!self::canManagePlaylist($playlists_id)) {
            return "";
        }
        $input = '<i class="fas fa-tv" style="margin:2px 5px;"></i> <span class="hidden-xs hidden-sm">' . __('Show on TV') . '</span> <div class="material-switch material-small" style="margin:0 10px;">
                                <input class="ShowOnTVSwitch" data-toggle="toggle" type="checkbox" id="ShowOnTVSwitch' . $playlists_id . '" name="ShowOnTVSwitch' . $playlists_id . '" value="1" ' . (self::showOnTV($playlists_id) ? "checked" : "") . ' onchange="saveShowOnTV(' . $playlists_id . ', $(this).is(\':checked\'))" >
                                <label for="ShowOnTVSwitch' . $playlists_id . '" class="label-primary" data-toggle="tooltip" title="' . __('Show on TV') . '"></label>
                            </div>';
        return $input;
    }

    static function getPlayListEPG($playlists_id, $users_id = 0)
    {
        if (empty($users_id)) {
            $pl = new PlayList($playlists_id);
            $users_id = ($pl->getUsers_id());
        }
        $epg = self::getUserEPG($users_id);
        if (empty($epg["playlists"]) || empty($epg["playlists"][$playlists_id])) {
            return array();
        }
        $epg["playlists"][$playlists_id]['generated'] = $epg['generated'];
        return $epg["playlists"][$playlists_id];
    }

    static function getUserEPG($users_id)
    {
        $epg = self::getSiteEPGs();
        if (empty($epg) || empty($epg[$users_id])) {
            return array();
        }
        $epg[$users_id]['generated'] = $epg['generated'];
        return $epg[$users_id];
    }

    static function getSiteEPGs($addPlaylistInfo = false)
    {
        global $global;
        $siteDomain = get_domain($global['webSiteRootURL']);
        $epg = self::getALLEPGs();
        if (empty($epg[$siteDomain])) {
            return array();
        }
        if ($addPlaylistInfo) {
            foreach ($epg[$siteDomain]["channels"] as $key => $value) {
                foreach ($value['playlists'] as $key2 => $value2) {
                    $pl = new PlayList($value2['playlist_id']);
                    $epg[$siteDomain]["channels"][$key]['playlists'][$key2]['title'] = $pl->getName();
                    $epg[$siteDomain]["channels"][$key]['playlists'][$key2]['image'] = PlayLists::getImage($value2['playlist_id']);
                    $epg[$siteDomain]["channels"][$key]['playlists'][$key2]['m3u8'] = PlayLists::getLinkToM3U8($value2['playlist_id'], $value2['key'], $value2['live_servers_id']);
                }
            }
        }
        $epg[$siteDomain]["channels"]['generated'] = $epg['generated'];
        return $epg[$siteDomain]["channels"];
    }

    static function getALLEPGs()
    {
        global $config, $global, $getSiteEPGs;
        if (!empty($getSiteEPGs)) {
            return $getSiteEPGs;
        }
        $encoder = $config->_getEncoderURL();
        $url = "{$encoder}view/videosListEPG.php";
        $content = url_get_contents($url);
        $name = "getALLEPGs_" . md5($url);
        //$cache = ObjectYPT::getCache($name, 15);
        //if (!empty($cache)) {
        //    return object_to_array($cache);
        //}

        $json = _json_decode($content);
        if (!is_object($json)) {
            return array();
        }
        $getSiteEPGs = object_to_array($json->sites);
        $getSiteEPGs['generated'] = $json->generated;
        //ObjectYPT::setCache($name, $getSiteEPGs);
        return $getSiteEPGs;
    }

    static function epgFromPlayList($playListArray, $generated, $created, $showClock = false, $linkToLive = false, $showTitle = false)
    {
        if (empty($playListArray) || empty($created)) {
            return '';
        }
        global $global;
        $uid = uniqid();
        $totalDuration = 0;
        foreach ($playListArray as $value) {
            $totalDuration += $value['duration_seconds'];
        }
        $playlists_id = $playListArray[0]['id'];
        $current = $generated - $created;
        $endTime = ($created + $totalDuration);
        $durationLeft = $endTime - $generated;
        $percentage_progress = ($current / $totalDuration) * 100;
        $percentage_left = 100 - floatval($percentage_progress);
        $epgStep = number_format($percentage_left / $durationLeft, 2);
        $searchFor = array(
            '{playListname}', '{showClock}',
            '{linkToLive}', '{totalDuration}',
            '{created}', '{uid}',
            '{percentage_progress}', '{epgBars}',
            '{epgStep}', '{generated}',
            '{implode}'
        );

        $searchForEPGbars = array(
            '{thumbsJpg}', '{represents_percentage}',
            '{className}', '{epgId}', '{title}',
            '{text}', '{uid}', '{percentage_progress}'
        );

        $epgTemplate = file_get_contents($global['systemRootPath'] . 'plugin/PlayLists/epg.template.html');
        $epgBarsTemplate = file_get_contents($global['systemRootPath'] . 'plugin/PlayLists/epg.template.bar.html');

        $html = "";
        if ($showTitle) {
            $pl = new PlayList($playlists_id);
            $playListname = " <strong>" . $pl->getName() . "</strong>";
        } else {
            $playListname = "";
        }
        if ($showClock) {
            $showClock = " <div class='label label-primary'><i class=\"far fa-clock\"></i> " . getServerClock() . "</div>";
        } else {
            $showClock = "";
        }
        if ($linkToLive) {
            $link = PlayLists::getLinkToLive($playlists_id);
            $linkToLive = " <a href='{$link}' class='btn btn-xs btn-primary'>" . __("Watch Live") . "</a>";
        } else {
            $linkToLive = "";
        }
        $totalDuration_ = secondsToDuration($totalDuration);
        $created = humanTimingAgo($created);
        $js = array();
        $per = 0;
        $className = "class_{$uid}";
        $epgBars = "";
        foreach ($playListArray as $key => $value) {
            $epgId = "epg_" . uniqid();
            $represents_percentage = number_format(($value['duration_seconds'] / $totalDuration) * 100, 2);
            $images = Video::getImageFromFilename($value['filename']);
            $per += $represents_percentage;
            $thumbsJpg = $images->thumbsJpg;
            if ($per > 100) {
                $represents_percentage -= $per - 100;
            }
            $img = "<img src='{$images->thumbsJpg}' class='img img-responsive' style='height: 60px; padding: 2px;'><br>";
            $title = addcslashes("{$img} {$value['title']} {$value['duration']}<br>{$value['start_date']}", '"');
            $text = "{$value['title']}";
            $epgBars .= str_replace($searchForEPGbars, array(
                $thumbsJpg, $represents_percentage, $className,
                $epgId, $title,
                $text, $uid, $percentage_progress
            ), $epgBarsTemplate);
            $js[] = " if(currentTime{$uid}>={$value['start']} && currentTime{$uid}<={$value['stop']}){\$('.{$className}').not('#{$epgId}').removeClass('progress-bar-success').addClass('progress-bar-primary');\$('#{$epgId}').addClass('progress-bar-success').removeClass('progress-bar-primary');}";
        }
        $implode = implode("else", $js);
        return str_replace($searchFor, array(
            $playListname, $showClock,
            $linkToLive, $totalDuration_,
            $created, $uid,
            $percentage_progress, $epgBars,
            $epgStep, $generated,
            $implode
        ), $epgTemplate);
    }

    static function showOnTV($playlists_id)
    {
        if (!self::showTVFeatures()) {
            return false;
        }
        $pl = new PlayList($playlists_id);
        return !empty($pl->getShowOnTV());
    }

    static function getPlayLiveButton($playlists_id)
    {
        if (!self::showPlayLiveButton()) {
            _error_log("getPlayLiveButton: showPlayLiveButton said no");
            return "";
        }
        if (!self::canManagePlaylist($playlists_id)) {
            _error_log("getPlayLiveButton: canManagePlaylist($playlists_id) said no");
            return "";
        }
        global $global;
        $btnId = "btnId" . uniqid();
        $label = __("Play Live");
        $tooltip = __("Play this Program live now");
        $liveLink = PlayLists::getLiveLink($playlists_id);
        $labelLive = __("Is Live");
        $tooltipLive = __("Stop this Program and start over again");
        $isLive = "false";
        if (self::isPlaylistLive($playlists_id)) {
            $isLive = "true";
        }
        if (!empty($liveLink)) {
            $template = file_get_contents("{$global['systemRootPath']}plugin/PlayLists/playLiveButton.html");
            return str_replace(array('{isLive}', '{liveLink}', '{btnId}', '{label}', '{labelLive}', '{tooltip}', '{tooltipLive}'), array($isLive, $liveLink, $btnId, $label, $labelLive, $tooltip, $tooltipLive), $template);
        } else {
            _error_log("getPlayLiveButton: liveLink is empty");
        }
        return '';
    }

    static function scheduleLiveButton($playlists_id, $showLabel = true, $class = 'btn btn-xs btn-default')
    {
        if (!self::showPlayLiveButton()) {
            _error_log("Playlists:scheduleLiveButton: showPlayLiveButton said no");
            return '<!-- Playlists:scheduleLiveButton: showPlayLiveButton said no -->';
        }
        // can the user live?
        if (!User::canStream()) {
            _error_log("Playlists:scheduleLiveButton this user cannot stream");
            return '<!-- This user cannot stream -->';
        }
        if (!self::canManagePlaylist($playlists_id)) {
            _error_log("Playlists:scheduleLiveButton canManagePlaylist($playlists_id) said no");
            return "<!-- This user canManagePlaylist $playlists_id -->";
        }

        if (!AVideoPlugin::isEnabledByName('Rebroadcaster')) {
            _error_log("Playlists:scheduleLiveButton Rebroadcaster not enabled");
            return '<!-- Rebroadcaster not enabled -->';
        }
        global $global;
        $label = __("Play Live");

        $liveLink = "{$global['webSiteRootURL']}plugin/PlayLists/View/Playlists_schedules/schedule.php";
        $liveLink = addQueryStringParameter($liveLink, 'program_id', $playlists_id);

        $labelText = $label;
        if (empty($showLabel)) {
            $labelText = '';
        }

        $btn = "<button class=\"{$class}\" onclick=\"avideoModalIframe('$liveLink');\" data-toggle=\"tooltip\" title=\"$label\" ><i class=\"fas fa-broadcast-tower\"></i> $labelText</button>";
        if(AVideoPlugin::isEnabledByName('VideoPlaylistScheduler')){
            $liveLink = "{$global['webSiteRootURL']}plugin/VideoPlaylistScheduler/playLiveInLoop.php";
            $liveLink = addQueryStringParameter($liveLink, 'playlists_id', $playlists_id);
            $btn .= "<button class=\"{$class}\" onclick=\"avideoModalIframe('{$liveLink}');\" data-toggle=\"tooltip\" title=\"$label Loop\" ><i class=\"fa-solid fa-infinity\"></i> $labelText</button>";
        }
        return $btn;
    }

    static function getVideosIdFromPlaylist($playlists_id)
    {
        return PlayList::getVideosIdFromPlaylist($playlists_id);
    }

    static function isPlaylistLive($playlists_id, $users_id = 0)
    {
        global $isPlaylistLive;
        if (!isset($isPlaylistLive)) {
            $isPlaylistLive = array();
        }
        if (!isset($isPlaylistLive[$playlists_id])) {
            $json = self::getPlayListEPG($playlists_id, $users_id);
            $isPlaylistLive[$playlists_id] = !empty($json['isPIDRunning']);
        }
        return $isPlaylistLive[$playlists_id];
    }

    static function getPlaylistLiveServersID($playlists_id, $users_id = 0)
    {
        $json = self::getPlayListEPG($playlists_id, $users_id);

        if (!empty($json)) {
            return intval($json['live_servers_id']);
        }

        if (empty($users_id)) {
            $pl = new PlayList($playlists_id);
            $users_id = ($pl->getUsers_id());
        }
        $last = LiveTransmitionHistory::getLatestFromUser($users_id);
        return $last['live_servers_id'];
    }

    static function getPlaylistLiveKey($playlists_id, $users_id = 0)
    {
        $json = self::getPlayListEPG($playlists_id, $users_id);
        if (!empty($json['key'])) {
            return $json['key'];
        }

        if (empty($users_id)) {
            $pl = new PlayList($playlists_id);
            $users_id = ($pl->getUsers_id());
        }
        $last = LiveTransmitionHistory::getLatestFromUser($users_id);
        return $last['key'];
    }

    public static function getLivePosterImage($playlists_id)
    {
        $live = AVideoPlugin::loadPluginIfEnabled("Live");
        if ($live) {
            $pl = new PlayList($playlists_id);
            $users_id = intval($pl->getUsers_id());
            $live_servers_id = self::getPlaylistLiveServersID($playlists_id, $users_id);
            return $live->getLivePosterImage($users_id, $live_servers_id) . "&playlists_id_live={$playlists_id}";
        }
        return "";
    }

    public function getPluginMenu()
    {
        global $global;
        return '';
        //return '<a href="plugin/PlayLists/View/editor.php" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> Schedule</a>';
    }

    public static function setAutoAddPlaylist($users_id, $playlists_id)
    {
        $playlists_id = intval($playlists_id);
        $user = new User($users_id);
        $paramName = 'autoadd_playlist';
        return $user->addExternalOptions($paramName, $playlists_id);
    }

    public static function getAutoAddPlaylist($users_id)
    {
        $user = new User($users_id);
        $paramName = 'autoadd_playlist';
        return $user->getExternalOption($paramName);
    }

    public static function getPLButtons($playlists_id, $showMore = true)
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/PlayLists/View/getPlaylistButtons.php';
    }

    public function getMyAccount($users_id)
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/PlayLists/getMyAccount.php';
    }

    public function onNewVideo($videos_id)
    {
        if (empty($videos_id)) {
            return false;
        }
        $video = new Video('', '', $videos_id);
        $users_id = $video->getUsers_id();
        $playlists_id = PlayLists::getAutoAddPlaylist($users_id);
        if (!empty($playlists_id)) {
            return self::addVideo($videos_id, $playlists_id);
        }
        return false;
    }

    static public function addVideo($videos_id, $playlists_id, $add = true, $order = 0)
    {
        $pl = new PlayList($playlists_id);
        return $pl->addVideo($videos_id, $add, $order);
    }

    static public function getPlaylistNotFoundMessage($playlistId)
    {
        if (is_numeric($playlistId)) {
            return "The playlist with ID {$playlistId} is empty or does not exist.";
        } elseif ($playlistId === 'favorite') {
            return __("Your Favorites playlist is empty.");
        } elseif ($playlistId === 'watch_later') {
            return __("Your 'Watch Later' playlist is empty.");
        } else {
            return "Playlist not found (ID: {$playlistId}).";
        }
    }

    function executeEveryMinute()
    {
        global $global;
        $file = "{$global['systemRootPath']}plugin/PlayLists/run.php";
        require_once $file;
    }

    static function thereIsARebroadcastPlaying($key){
        $parts = Rebroadcaster::isKeyARebroadcast($key);
        $stats = getStatsNotifications();
        foreach ($stats["applications"] as $key => $value) {
            if(preg_match("/{$parts['cleankey']}-RB-([0-9]+)-{$parts['index']}/i", $value['key'])){
                return true;
            }
        }
        return false;
    }

    public function on_publish_done($live_transmitions_history_id, $users_id, $key, $live_servers_id)
    {
        $lt = new LiveTransmitionHistory($live_transmitions_history_id);
        $key = $lt->getKey();
        _error_log("on_publish_done key={$key} live_transmitions_history_id={$live_transmitions_history_id} ");
        $isPlayListScheduled = Playlists_schedules::iskeyPlayListScheduled($key);
        if (!empty($isPlayListScheduled['playlists_schedules'])) {
            if(!self::thereIsARebroadcastPlaying($key)){
                $pls = new Playlists_schedules($isPlayListScheduled['playlists_schedules']);
                if ($pls->getFinish_datetime() > time()) {
                    $ps = Playlists_schedules::getPlaying($isPlayListScheduled['playlists_schedules']);
                    $pl = new PlayList($ps->playlists_id);
                    $title = $pl->getName() . ' [' . $ps->msg . ']';
                    Rebroadcaster::rebroadcastVideo(
                        $ps->current_videos_id,
                        $pl->getUsers_id(),
                        Playlists_schedules::getPlayListScheduledIndex($isPlayListScheduled['playlists_schedules']),
                        $title
                    );
                } else {
                    _error_log("on_publish_done is complete {$pls->getFinish_datetime()} < " . time() . " | " . date('Y/m/d H:i:s', $pls->getFinish_datetime()) . ' < ' . date('Y/m/d H:i:s', time()));
                    self::setScheduleStatus($key, Playlists_schedules::STATUS_COMPLETE);
                }
            }else{
                _error_log("on_publish_done  Playlists::thereIsARebroadcastPlaying($key) ");
            }
        } else {
            _error_log("on_publish_done is complete isPlayListScheduled=" . json_encode($isPlayListScheduled));
        }
    }

    public function on_publish_denied($key)
    {
        return self::setScheduleStatus($key, Playlists_schedules::STATUS_FAIL);
    }

    static public function setScheduleStatus($key, $status)
    {
        if (!empty($key) && $isPlayListScheduled = Playlists_schedules::iskeyPlayListScheduled($key)) {
            _error_log("setFail Playlists_schedules " . json_encode($isPlayListScheduled));
            $ps = new Playlists_schedules($isPlayListScheduled['playlists_schedules']);
            $ps->setStatus($status);
            return  $ps->save();
        }
        return false;
    }

    static function getURL($playlist_id, $count= null, $PLChannelName = '', $plName = '', $current_video_clean_title = '')
    {
        global $global, $total_get_playlists_urls;
        $plURL = "{$global['webSiteRootURL']}program/{$playlist_id}/";
        if(isset($count) && is_numeric($count)){
            $plURL .= "{$count}/";
        }
        return $plURL ;
        /*
        if(empty($total_get_playlists_urls)){
            $total_get_playlists_urls = 0;
        }

        if (empty($PLChannelName)) {
            $total_get_playlists_urls++;
            if($total_get_playlists_urls>3){
                $PLChannelName = '-';
            }else{
                $pl = new PlayList($playlist_id);
                $users_id = $pl->getUsers_id();
                $user = new User($users_id);
                $PLChannelName = $user->getChannelName();
            }
        }
        if (empty($plName)) {
            $total_get_playlists_urls++;
            if($total_get_playlists_urls>3){
                $plName = '-';
            }else{
                if (empty($pl)) {
                    $pl = new PlayList($playlist_id);
                }
                $plName = $pl->getName();
            }
        }
        if (empty($current_video_clean_title)) {
            $total_get_playlists_urls++;
            if($total_get_playlists_urls>3){
                $current_video_clean_title = '-';
            }else{
                $playlistVideos = PlayList::getVideosFromPlaylist($playlist_id);
                $current_video_clean_title = $playlistVideos[$count]['clean_title'];
            }
        }
        $plURL = "{$global['webSiteRootURL']}program/{$playlist_id}/{$count}/" . urlencode(cleanURLName($PLChannelName)) . '/' . urlencode(cleanURLName($plName)) . '/' . urlencode(cleanURLName($current_video_clean_title));

        return $plURL;
        */
    }
}

class PlayListPlayer
{

    private $name;
    private $videos;
    private $isAdmin;
    private $index;
    private $users_id;
    private $playlists_id;
    private $tags_id;
    private $ObjectData;

    public function getPlaylists_id()
    {
        return $this->playlists_id;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVideos()
    {
        return $this->videos;
    }

    public function canSee()
    {
        return !empty($this->playlists_id) && PlayList::canSee($this->playlists_id, $this->users_id);
    }

    public function canNotSeeReason()
    {
        $obj = new PlayList($this->playlists_id);
        $status = $obj->getStatus();
        $reasons = array();
        if ($status == 'favorite') {
            $reasons[] = __('Favorite is always private');
        } else if ($status == 'watch_later') {
            $reasons[] = __('Watch later is always private');
        } else if ($status !== 'public' && $status !== 'unlisted') {
            $reasons[] = 'Status = ' . $status;
            if ($this->users_id !== $obj->getUsers_id()) {
                $reasons[] = __('Playlist is private');
            }
        }
        return $reasons;
    }
    public function __construct($playlists_id, $tags_id, $checkPlayMode = false)
    {
        $this->users_id = User::getId();
        if (!empty($playlists_id)) {
            if (preg_match("/^[0-9]+$/", $playlists_id)) {
                $this->playlists_id = $playlists_id;
            } elseif (!empty($this->users_id)) {
                if ($playlists_id == "favorite") {
                    $this->playlists_id = PlayList::getFavoriteIdFromUser($this->users_id);
                } else {
                    $this->playlists_id = PlayList::getWatchLaterIdFromUser($this->users_id);
                }
            }
        }
        $this->tags_id = $tags_id;
        if ($checkPlayMode) {
            $this->checkPlayMode();
        }
        $this->index = getPlayListIndex();
        $this->isAdmin = User::isAdmin();
        $this->name = $this->_getName();
        $this->ObjectData = AVideoPlugin::getObjectData("PlayLists");
        $this->videos = $this->_getVideos();
    }

    private function _getName()
    {
        global $global;
        if (!empty($this->playlists_id)) {
            $video = PlayLists::isPlayListASerie($this->playlists_id);
            if (!empty($video['id'])) {
                return $video['title'];
            } else {
                $playListObj = new PlayList($this->playlists_id);
                return $playListObj->getName();
            }
        } else if (!empty($this->tags_id)) {
            //require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/Tags.php';
            AVideoPlugin::getObjectData("VideoTags");
            $tag = new Tags($this->tags_id);
            return $tag->getName();
        }
        return '';
    }

    private function _getVideos()
    {
        $videos = array();
        if (!empty($this->playlists_id)) {
            global $_pl_getVideos;
            if (!isset($_pl_getVideos)) {
                $_pl_getVideos = array();
            }
            //_error_log("PlayList::getVideosFromPlaylist($this->playlists_id) " . json_encode(debug_backtrace()));
            if (isset($_pl_getVideos[$this->playlists_id])) {
                return $_pl_getVideos[$this->playlists_id];
            }
            $_pl_getVideos[$this->playlists_id] = array();
            $videos = PlayList::getVideosFromPlaylist($this->playlists_id);
            //var_dump($videos, $this->playlists_id);
            $_pl_getVideos[$this->playlists_id] = $videos;
            /*
              if (!empty($this->ObjectData->showTrailerInThePlayList)) {
              $videoSerie = PlayLists::isPlayListASerie($this->playlists_id);
              if (!empty($videoSerie['id'])) {
              $videoSerie["type"] = "embed";
              $videoSerie["videoLink"] = $videoSerie["trailer1"];
              array_unshift($videos, $videoSerie);
              }
              }
             *
             */
        } else if (!empty($this->tags_id)) {
            global $_plt_getVideos;
            if (!isset($_plt_getVideos)) {
                $_plt_getVideos = array();
            }
            if (isset($_plt_getVideos[$this->playlists_id])) {
                return $_plt_getVideos[$this->playlists_id];
            }
            $_plt_getVideos[$this->playlists_id] = array();
            $videos = VideoTags::getAllVideosFromTagsId($this->tags_id);
            $_plt_getVideos[$this->playlists_id] = $videos;
        }
        //var_dump($this->tags_id, $videos);exit;
        return self::fixRows($videos);
    }

    private function fixRows($playList)
    {
        $videos = array();
        foreach ($playList as $key => $value) {
            $videos[$key] = $value;
            if (!empty($value['videos_id'])) {
                $videos[$key]['id'] = $value['videos_id'];
            }
            if (!$this->isAdmin && !Video::userGroupAndVideoGroupMatch($this->users_id, $videos[$key]['id'])) {
                unset($videos[$key]);
                continue;
            }
            if (!empty($this->playlists_id)) {
                $videos[$key]['alternativeLink'] = PlayLists::getLink($this->playlists_id, 1, $key);
            } else if (!empty($this->tags_id)) {
                $videos[$key]['alternativeLink'] = PlayLists::getTagLink($this->tags_id, 1, $key);
            } else {
                die('error on playlist definition');
            }
        }
        return array_values($videos);
    }

    public function getPlayListData()
    {
        global $playListData, $messagesFromPlayList;
        if (!isset($playListData)) {
            $playListData = array();
        }
        $messagesFromPlayList = array();
        foreach ($this->videos as $key => $video) {
            if ($video['type'] === Video::$videoTypeEmbed) {
                $sources[0]['type'] = 'video';
                $sources[0]['url'] = @$video["videoLink"];
            } else {
                $sources = getVideosURL($video['filename']);
            }
            $images = Video::getImageFromFilename($video['filename'], $video['type']);
            $externalOptions = _json_decode($video['externalOptions']);
            $src = new stdClass();
            $src->src = $images->thumbsJpg;
            $thumbnail = array($src);
            $playListSources = array();
            foreach ($sources as $value2) {
                if ($value2['type'] !== Video::$videoTypeVideo && $video['type'] !== Video::$videoTypeAudio && $video['type'] !== Video::$videoTypeSerie) {
                    $messagesFromPlayList[] = "Playlist getPlayListData videos_id={$video['id']} [{$video['title']}] invalid type {$video['type']} filename={$video['filename']}";
                    continue;
                }
                $messagesFromPlayList[] = "Playlist playListSource videos_id={$video['id']} [{$video['title']}] type {$video['type']} filename={$video['filename']}";
                //var_dump($value2);
                $playListSources[] = new playListSource($value2['url'], $value2['videos_id'], $video['type'] === Video::$videoTypeEmbed);
            }
            if (empty($playListSources)) {
                $messagesFromPlayList[] = "videos_id={$video['videos_id']} [{$value2['title']}]  empty playlist source ";
                continue;
            }
            $playListData[] = new PlayListElement(@$video['title'], @$video['description'], @$video['duration'], $playListSources, $thumbnail, $images->poster, parseDurationToSeconds(@$externalOptions->videoStartSeconds), @$video['created'], @$video['likes'], @$video['views_count'], $video['videos_id']);
        }
        return $playListData;
    }

    public function getCurrentVideo()
    {
        global $global;
        $key = $this->getIndex();
        if (empty($this->videos[$key])) {
            return false;
        }
        $video = $this->videos[$key];
        $video['url'] = $global['webSiteRootURL'] . "playlist/{$this->playlists_id}/" . ($key);

        if (!isValidURL(@$video['trailer1'])) {
            $video['trailer1'] = PlayLists::getTrailerIfIsSerie($this->playlists_id);
        }
        //$_GET['v'] = $video['id'];
        //setVideos_id($video['id']);
        //var_dump($key, $video, $_GET);exit;
        return $video;
    }

    public function getNextVideo()
    {
        global $global;
        $key = $this->getIndex();
        $autoplayIndex = $key + 1;
        if (empty($this->videos[$autoplayIndex])) {
            $autoplayIndex = 0;
        }
        $autoPlayVideo = $this->videos[$autoplayIndex];
        $autoPlayVideo['url'] = $global['webSiteRootURL'] . "playlist/{$this->playlists_id}/" . ($autoplayIndex);
        return $autoPlayVideo;
    }

    public function checkPlayMode()
    {
        global $global;
        if (!empty($this->playlists_id)) {
            $video = PlayLists::isPlayListASerie($this->playlists_id);
            if (!empty($video)) {
                $video = Video::getVideo($video['id']);
                include $global['systemRootPath'] . 'view/modeYoutube.php';
                exit;
            }
        }
    }
}
