<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';

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

        return $obj;
    }

    public function getWatchActionButton($videos_id) {
        global $global;
        if (!self::canAddVideoOnPlaylist($videos_id)) {
            return "";
        }
        $obj = $this->getDataObject();
        //echo "getNetflixActionButton: ".$videos_id;
        $btnClass = "btn btn-default no-outline";
        include $global['systemRootPath'] . 'plugin/PlayLists/actionButton.php';
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

        $css = '<link href="' . $global['webSiteRootURL'] . 'plugin/PlayLists/style.css" rel="stylesheet" type="text/css"/>';

        return $css;
    }

    public function getFooterCode() {
        global $global;
        $obj = $this->getDataObject();

        include $global['systemRootPath'] . 'plugin/PlayLists/footer.php';
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
            $video->delete();
        }
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
        
        $obj = AVideoPlugin::getObjectData("PlayLists");
        $str = "";
        
        if($obj->showWatchLaterOnLeftMenu){
            $str .= '<li>
                        <div>
                            <a href="' . self::getWatchLaterLink() . '" class="btn btn-default btn-block" style="border-radius: 0;">
                                <i class="fas fa-clock"></i>
                                ' . __("Watch Later") . '
                            </a>
                        </div>
                    </li>';
        }
        if($obj->showFavoriteOnLeftMenu){
            $str .= '<li>
                        <div>
                            <a href="' . self::getFavoriteLink() . '" class="btn btn-default btn-block" style="border-radius: 0;">
                                <i class="fas fa-heart"></i>
                                ' . __("Favorite") . '
                            </a>
                        </div>
                    </li>';
        }
        return $str;
    }
    
    
    public function navBarProfileButtons() {
        
        $obj = AVideoPlugin::getObjectData("PlayLists");
        $str = "";
        
        if($obj->showWatchLaterOnProfileMenu){
            $str .= '<li>
                            <a href="' . self::getWatchLaterLink() . '" class="" style="border-radius: 0;">
                                <i class="fas fa-clock"></i>
                                ' . __("Watch Later") . '
                            </a>
                    </li>';
        }
        if($obj->showFavoriteOnProfileMenu){
            $str .= '<li>
                            <a href="' . self::getFavoriteLink() . '" class="" style="border-radius: 0;">
                                <i class="fas fa-heart"></i>
                                ' . __("Favorite") . '
                            </a>
                    </li>';
        }
        return $str;
    }
    
    static function getLiveLink($playlists_id){
        global $global;
        if(!self::canPlayProgramsLive()){
            return false;
        }
        // does it has videos?
        $videosArrayId = PlayLists::getOnlyVideosAndAudioIDFromPlaylistLight($playlists_id);
        if(empty($videosArrayId)){
            return false;
        }
        
        return "{$global['webSiteRootURL']}plugin/PlayLists/playProgramsLive.json.php?playlists_id=" . $playlists_id;
    }
    
    static function showPlayLiveButton(){ 
        if(!$obj = AVideoPlugin::getDataObjectIfEnabled("PlayLists")){
            return false;
        }
        return !empty($obj->showPlayLiveButton);
    }
    
    static function canPlayProgramsLive(){
        // can the user live?
        if(!User::canStream()){
            return false;
        }
        // Is API enabled
        if(!AVideoPlugin::isEnabledByName("API")){
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

}
