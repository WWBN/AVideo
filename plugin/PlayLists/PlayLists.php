<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';

require_once $global['systemRootPath'] . 'plugin/PlayLists/Objects/Playlists_schedules.php';
class PlayLists extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE,
        );
    }

    public function getDescription() {
        return "Playlists or Program Playlists are identified by default as programs of content on the AVideo Platform.<br>"
                . " You can use the Edit Parameters button to rename it to your choosing.<br>  We recommend to keep the Program name "
                . "as it is defaulted to in order to be well indexed in the SearchTube and Other AVideo Platform search and network indexing tools.";
    }

    public function getName() {
        return "Programs";
    }

    public function getUUID() {
        return "plist12345-370-4b1f-977a-fd0e5cabtube";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->name = "Program";
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

        return $obj;
    }

    public function getWatchActionButton($videos_id) {
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

    public function getNetflixActionButton($videos_id) {
        global $global;
        if (!self::canAddVideoOnPlaylist($videos_id)) {
            return "";
        }
        $obj = $this->getDataObject();
        //echo "getNetflixActionButton: ".$videos_id;
        $btnClass = "btn btn-primary";
        include $global['systemRootPath'] . 'plugin/PlayLists/actionButton.php';
    }

    public function getGalleryActionButton($videos_id) {
        global $global;
        if (!self::canAddVideoOnPlaylist($videos_id)) {
            return "";
        }
        $obj = $this->getDataObject();
        //echo "getNetflixActionButton: ".$videos_id;
        $btnClass = "text-primary";
        echo '<div class="">';
        include $global['systemRootPath'] . 'plugin/PlayLists/actionButton.php';
        echo '</div>';
    }

    public function getHeadCode() {
        global $global;
        $obj = $this->getDataObject();

        $css = '<link href="' .getCDN() . 'plugin/PlayLists/style.css" rel="stylesheet" type="text/css"/>';
        $css .= '<style>.epgProgress.progress-bar-primary{opacity: 0.5;}.epgProgress:hover{opacity: 1.0;}.epgProgressText{border-right: 1px solid #FFF; height:100%;}</style>';
        
        if(!empty(getPlaylists_id())){
            $css .= "<link href=\"".getCDN()."plugin/PlayLists/playerButton.css\" rel=\"stylesheet\" type=\"text/css\"/>";
        }

        return $css;
    }

    public function getFooterCode() {
        global $global;
        $obj = $this->getDataObject();
        include $global['systemRootPath'] . 'plugin/PlayLists/footer.php';
        $js = '<script src="' .getCDN() . 'plugin/PlayLists/script.js" type="text/javascript"></script>';
        
        if(isEmbed()){
            if(self::showTVFeatures()){
                $js .= '<script>'. file_get_contents("{$global['systemRootPath']}plugin/PlayLists/showOnTV.js").'</script>';
            }
        }
        
        if(isLive() && self::showTVFeatures()){
            if(!empty($_REQUEST['playlists_id_live']) && 
                    !self::isPlaylistLive($_REQUEST['playlists_id_live']) &&
                    self::canManagePlaylist($_REQUEST['playlists_id_live'])){
                $liveLink = PlayLists::getLiveLink($_REQUEST['playlists_id_live']);
                $js .= '<script>var liveLink = "'.$liveLink.'";'. file_get_contents("{$global['systemRootPath']}plugin/PlayLists/goLiveNow.js").'</script>';
            }
        }
        
        if(!empty(getPlaylists_id())){
            PlayerSkins::getStartPlayerJS(file_get_contents("{$global['systemRootPath']}plugin/PlayLists/playerButton.js"));
        }
        
        return $js;
    }

    static function canAddVideoOnPlaylist($videos_id) {
        if (empty($videos_id)) {
            return false;
        }
        $obj = AVideoPlugin::getObjectData("PlayLists");
        if (!User::isAdmin() && $obj->usersCanOnlyCreatePlayListsFromTheirContent) {
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

    static function isVideoOnFavorite($videos_id, $users_id) {
        return PlayList::isVideoOnFavorite($videos_id, $users_id);
    }

    static function isVideoOnWatchLater($videos_id, $users_id) {
        return PlayList::isVideoOnWatchLater($videos_id, $users_id);
    }

    static function getFavoriteIdFromUser($users_id) {
        return PlayList::getFavoriteIdFromUser($users_id);
    }

    static function getWatchLaterIdFromUser($users_id) {
        return PlayList::getWatchLaterIdFromUser($users_id);
    }

    static function getWatchLaterLink() {
        if (!User::isLogged()) {
            return "";
        }
        global $global;
        return "{$global['webSiteRootURL']}watch-later";
    }

    static function getFavoriteLink() {
        if (!User::isLogged()) {
            return "";
        }
        global $global;
        return "{$global['webSiteRootURL']}favorite";
    }

    public function thumbsOverlay($videos_id) {
        global $global;
        include $global['systemRootPath'] . 'plugin/PlayLists/buttons.php';
    }

    static function isPlayListASerie($serie_playlists_id) {
        global $global, $config;
        $serie_playlists_id = intval($serie_playlists_id);
        $sql = "SELECT * FROM videos WHERE serie_playlists_id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", array($serie_playlists_id), true);
        $video = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return $video;
    }

    static function removeSerie($serie_playlists_id) {
        $video = self::isPlayListASerie($serie_playlists_id);
        if (!empty($video)) {
            $video = new Video("", "", $video['id']);
            return $video->delete();
        }
        return false;
    }

    static function saveSerie($serie_playlists_id) {
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

    public function getStart() {
        global $global;
        $whitelistedFiles = array('VMAP.php');
        $baseName = basename($_SERVER["SCRIPT_FILENAME"]);
        if (!in_array($baseName, $whitelistedFiles)) {
            if (!empty($_GET['videoName'])) {
                $obj = $this->getDataObject();
                if ($obj->usePlaylistPlayerForSeries) {
                    $video = Video::getVideoFromCleanTitle($_GET['videoName']);
                    if ($video['type'] == 'serie' && !empty($video['serie_playlists_id'])) {
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

    static function getLink($playlists_id, $embed = false) {
        global $global;
        $obj = AVideoPlugin::getObjectData("PlayLists");
        if ($embed) {
            return $global['webSiteRootURL'] . "plugin/PlayLists/embed.php?playlists_id=" . $playlists_id;
        } else {
            if (empty($obj->useOldPlayList)) {
                return $global['webSiteRootURL'] . "plugin/PlayLists/player.php?playlists_id=" . $playlists_id;
            } else {
                return $global['webSiteRootURL'] . "program/" . $playlists_id;
            }
        }
    }

    public function navBarButtons() {
        global $global;

        $obj = AVideoPlugin::getObjectData("PlayLists");
        $str = "";

        if ($obj->showWatchLaterOnLeftMenu) {
            $str .= '<li>
                        <div>
                            <a href="' . self::getWatchLaterLink() . '" class="btn btn-default btn-block" style="border-radius: 0;">
                                <i class="fas fa-clock"></i>
                                ' . __("Watch Later") . '
                            </a>
                        </div>
                    </li>';
        }
        if ($obj->showFavoriteOnLeftMenu) {
            $str .= '<li>
                        <div>
                            <a href="' . self::getFavoriteLink() . '" class="btn btn-default btn-block" style="border-radius: 0;">
                                <i class="fas fa-heart"></i>
                                ' . __("Favorite") . '
                            </a>
                        </div>
                    </li>';
        }
        $str .= '<li>
                    <div>
                        <a href="' . "{$global['webSiteRootURL']}plugin/PlayLists/managerPlaylists.php" . '" class="btn btn-default btn-block" style="border-radius: 0;">
                            <i class="fas fa-list"></i>
                            ' . __("Organize") . ' ' .$obj->name . '
                        </a>
                    </div>
                </li>';
        return $str;
    }

    public function navBarProfileButtons() {
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

    static function getLiveLink($playlists_id) {
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

    static function showPlayLiveButton() {
        if (!$obj = AVideoPlugin::getDataObjectIfEnabled("PlayLists")) {
            return false;
        }
        return !empty($obj->showPlayLiveButton);
    }

    static function canPlayProgramsLive() {
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

    static function getOnlyVideosAndAudioIDFromPlaylistLight($playlists_id) {
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
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    static function getLiveEPGLink($playlists_id, $type = 'html') {
        global $global;
        $pl = new PlayList($playlists_id);
        $site = get_domain($global['webSiteRootURL']);
        $channel = $pl->getUsers_id();
        $link = "{$global['webSiteRootURL']}epg.{$type}?site={$site}&channel={$channel}&playlists_id={$playlists_id}";
        return $link;
    }

    static function getLinkToLive($playlists_id) {
        global $global;
        $pl = new PlayList($playlists_id);
        $link = Live::getLinkToLiveFromUsers_idWithLastServersId($pl->getUsers_id());
        return $link . "?playlists_id_live={$playlists_id}";
    }

    static function getImage($playlists_id) {
        global $global;
        if(self::isPlaylistLive($playlists_id)){
            return self::getLivePosterImage($playlists_id);
        }
        
        $serie = self::isPlayListASerie($playlists_id);
        if (!empty($serie)) {
            $tvg_logo = "videos/{$serie['filename']}_tvg.jpg";
            $tvg_logo_path = "{$global['systemRootPath']}{$tvg_logo}";
            if (!file_exists($tvg_logo_path)) {
                $images = Video::getSourceFile($serie['filename']);
                $img = $images["path"];
                im_resizeV2($img, $tvg_logo_path, 150, 150, 80);
            }

            $tvg_logo_url = "{$global['webSiteRootURL']}{$tvg_logo}";
            return $tvg_logo_url;
        } else {
            $pl = new PlayList($playlists_id);
            return User::getPhoto($pl->getUsers_id());
        }
    }
    
    static function getLiveImage($playlists_id) {
        global $global;
        if(self::isPlaylistLive($playlists_id)){
            return self::getLivePosterImage($playlists_id);
        }else{
            return "{$global['webSiteRootURL']}plugin/Live/view/Offline.jpg";
        }
    }

    static function getNameOrSerieTitle($playlists_id) {
        $serie = self::isPlayListASerie($playlists_id);
        if (!empty($serie)) {
            return $serie['title'];
        } else {
            $pl = new PlayList($playlists_id);
            return $pl->getName();
        }
    }

    static function getDescriptionIfIsSerie($playlists_id) {
        $serie = self::isPlayListASerie($playlists_id);
        if (!empty($serie)) {
            return $serie['description'];
        }
        return "";
    }

    static function getLinkToM3U8($playlists_id, $key, $live_servers_id) {
        global $global;
        $_REQUEST['playlists_id_live'] = $playlists_id;
        $_REQUEST['live_servers_id'] = $live_servers_id;
        return Live::getM3U8File($key);
    }
    
    static function getM3U8File($playlists_id) {
        
        $pl = new PlayList($playlists_id);
        $users_id = intval($pl->getUsers_id());
        $key = self::getPlaylistLiveKey($playlists_id, $users_id);
        $live_servers_id = self::getPlaylistLiveServersID($playlists_id, $users_id);
        return self::getLinkToM3U8($playlists_id, $key, $live_servers_id);
    }

    static function showTVFeatures() {
        $obj = AVideoPlugin::getObjectData("PlayLists");
        return !empty($obj->showTVFeatures);
    }

    static function canManagePlaylist($playlists_id) {
        if (!User::isLogged()) {
            return false;
        }
        if (User::isAdmin()) {
            return true;
        }
        $pl = new PlayList($playlists_id);
        if ($pl->getUsers_id() == User::getId()) {
            return true;
        }
        return false;
    }

    static function getShowOnTVSwitch($playlists_id) {
        if (!self::showTVFeatures()) {
            return "";
        }
        if (!self::canManagePlaylist($playlists_id)) {
            return "";
        }
        $input = '<i class="fas fa-tv" style="margin:2px 5px;"></i> <span class="hidden-xs hidden-sm">'.__('Show on TV') . '</span> <div class="material-switch material-small" style="margin:0 10px;">
                                <input class="ShowOnTVSwitch" data-toggle="toggle" type="checkbox" id="ShowOnTVSwitch' . $playlists_id . '" name="ShowOnTVSwitch' . $playlists_id . '" value="1" ' . (self::showOnTV($playlists_id) ? "checked" : "") . ' onchange="saveShowOnTV(' . $playlists_id . ', $(this).is(\':checked\'))" >
                                <label for="ShowOnTVSwitch' . $playlists_id . '" class="label-primary" data-toggle="tooltip" title="'.__('Show on TV').'"></label>
                            </div>';
        return $input;
    }

    static function getPlayListEPG($playlists_id, $users_id = 0) {
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

    static function getUserEPG($users_id) {
        $epg = self::getSiteEPGs();
        if (empty($epg) || empty($epg[$users_id])) {
            return array();
        }
        $epg[$users_id]['generated'] = $epg['generated'];
        return $epg[$users_id];
    }

    static function getSiteEPGs($addPlaylistInfo=false) {
        global $global;
        $siteDomain = get_domain($global['webSiteRootURL']);
        $epg = self::getALLEPGs();
        if (empty($epg[$siteDomain])) {
            return array();
        }
        if($addPlaylistInfo){
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

    static function getALLEPGs() {
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
        if(!is_object($json)){
            return array();
        }
        $getSiteEPGs = object_to_array($json->sites);
        $getSiteEPGs['generated'] = $json->generated;
        //ObjectYPT::setCache($name, $getSiteEPGs);
        return $getSiteEPGs;
    }

    static function epgFromPlayList($playListArray, $generated, $created, $showClock = false, $linkToLive = false, $showTitle = false) {
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
        $searchFor = array('{playListname}', '{showClock}',
            '{linkToLive}', '{totalDuration}',
            '{created}', '{uid}',
            '{percentage_progress}', '{epgBars}',
            '{epgStep}', '{generated}',
            '{implode}');

        $searchForEPGbars = array('{thumbsJpg}', '{represents_percentage}',
            '{className}', '{epgId}', '{title}',
            '{text}', '{uid}', '{percentage_progress}');

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
            $epgBars .= str_replace($searchForEPGbars, array($thumbsJpg, $represents_percentage, $className,
                $epgId, $title,
                $text, $uid, $percentage_progress), $epgBarsTemplate);
            $js[] = " if(currentTime{$uid}>={$value['start']} && currentTime{$uid}<={$value['stop']}){\$('.{$className}').not('#{$epgId}').removeClass('progress-bar-success').addClass('progress-bar-primary');\$('#{$epgId}').addClass('progress-bar-success').removeClass('progress-bar-primary');}";
        }
        $implode = implode("else", $js);
        return str_replace($searchFor, array($playListname, $showClock,
            $linkToLive, $totalDuration_,
            $created, $uid,
            $percentage_progress, $epgBars,
            $epgStep, $generated,
            $implode), $epgTemplate);
    }

    static function showOnTV($playlists_id) {
        if (!self::showTVFeatures()) {
            return false;
        }
        $pl = new PlayList($playlists_id);
        return !empty($pl->getShowOnTV());
    }

    static function getPlayLiveButton($playlists_id) {
        if (!self::showPlayLiveButton()) {
            _error_log("getPlayLiveButton: showPlayLiveButton said no");
            return "";
        }
        if (!self::canManagePlaylist($playlists_id)) {
            _error_log("getPlayLiveButton: canManagePlaylist($playlists_id) said no");
            return "";
        }
        global $global;
        $btnId = "btnId". uniqid();
        $label = __("Play Live");
        $tooltip = __("Play this Program live now");
        $liveLink = PlayLists::getLiveLink($playlists_id);
        $labelLive = __("Is Live");
        $tooltipLive = __("Stop this Program and start over again");
        $isLive = "false";
        if(self::isPlaylistLive($playlists_id)){
            $isLive = "true";
        }
        if (!empty($liveLink)) {
            $template = file_get_contents("{$global['systemRootPath']}plugin/PlayLists/playLiveButton.html");
            return str_replace(array('{isLive}','{liveLink}', '{btnId}', '{label}','{labelLive}', '{tooltip}', '{tooltipLive}'), array($isLive, $liveLink, $btnId, $label, $labelLive, $tooltip, $tooltipLive), $template);
        }else{
            _error_log("getPlayLiveButton: liveLink is empty");
        }
        return '';
    }
    
    static function getVideosIdFromPlaylist($playlists_id){
        return PlayList::getVideosIdFromPlaylist($playlists_id);
    }
    
    static function isPlaylistLive($playlists_id, $users_id = 0){
        global $isPlaylistLive;
        if(!isset($isPlaylistLive)){
            $isPlaylistLive = array();
        }
        if(!isset($isPlaylistLive[$playlists_id])){
            $json = self::getPlayListEPG($playlists_id, $users_id);
            $isPlaylistLive[$playlists_id] = !empty($json['isPIDRunning']);
        }
        return $isPlaylistLive[$playlists_id];
    }
    
    static function getPlaylistLiveServersID($playlists_id, $users_id = 0){
        $json = self::getPlayListEPG($playlists_id, $users_id);
        
        if(!empty($json)){
            return intval($json['live_servers_id']);
        }
        
        if (empty($users_id)) {
            $pl = new PlayList($playlists_id);
            $users_id = ($pl->getUsers_id());
        }
        $last = LiveTransmitionHistory::getLatestFromUser($users_id);
        return $last['live_servers_id'];
    }
    
    static function getPlaylistLiveKey($playlists_id, $users_id = 0){
        $json = self::getPlayListEPG($playlists_id, $users_id);
        if(!empty($json['key'])){
            return $json['key'];
        }
        
        if (empty($users_id)) {
            $pl = new PlayList($playlists_id);
            $users_id = ($pl->getUsers_id());
        }
        $last = LiveTransmitionHistory::getLatestFromUser($users_id);
        return $last['key'];
    }
    
    public function getLivePosterImage($playlists_id) {
        $live = AVideoPlugin::loadPluginIfEnabled("Live");
        if($live){
            $pl = new PlayList($playlists_id);
            $users_id = intval($pl->getUsers_id());
            $live_servers_id = self::getPlaylistLiveServersID($playlists_id, $users_id);
            return $live->getLivePosterImage($users_id, $live_servers_id)."&playlists_id_live={$playlists_id}";
        }
        return "";
        
    }
    
    public function getPluginMenu() {
        global $global;
        return '<a href="plugin/PlayLists/View/editor.php" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> Schedule</a>';
    }
    
}
