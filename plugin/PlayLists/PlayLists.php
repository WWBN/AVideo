<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';

class PlayLists extends PluginAbstract {

    public function getDescription() {
        return "A playlist video picker for youphptube for embed";
    }

    public function getName() {
        return "PlayLists";
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
        $obj->playOnSelect = true;
        $obj->autoadvance = true;
        $obj->usersCanOnlyCreatePlayListsFromTheirContent = false;
        $obj->useOldPlayList = false;
        $obj->expandPlayListOnChannels = false;
        $obj->usePlaylistPlayerForSeries = true;

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
        if(empty($videos_id)){
            return false;
        }
        $obj = YouPHPTubePlugin::getObjectData("PlayLists");
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
                    $link = PlayLists::getLink($video['serie_playlists_id']);
                    header("Location: {$link}");
                    exit;
                }
            }
        }
    }

    static function getLink($playlists_id) {
        global $global;
        $obj = YouPHPTubePlugin::getObjectData("PlayLists");
        if (empty($obj->useOldPlayList)) {
            return $global['webSiteRootURL'] . "plugin/PlayLists/player.php?playlists_id=" . $playlists_id;
        } else {
            return $global['webSiteRootURL'] . "playlist/" . $playlists_id;
        }
    }

}
