<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/LiveLinks/Objects/LiveLinksTable.php';
require_once $global['systemRootPath'] . 'plugin/Live/Live.php';

class LiveLinks extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$LIVE,
            PluginTags::$FREE,
            PluginTags::$PLAYER,
        );
    }

    public function getDescription() {
        $desc = "Register Livestreams external Links from any HLS provider, Wowza and others";
        $desc .= $this->isReadyLabel(array('Live'));
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/LiveLinks-Plugin' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";

        return $desc . $help;
    }

    public function getName() {
        return "LiveLinks";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->onlyAdminCanAddLinks = true;
        $obj->buttonTitle = "Add a Live Link";
        $obj->disableGifThumbs = false;
        $obj->disableLiveThumbs = false;
        $obj->doNotShowLiveLinksLabel = false;
        $obj->disableProxy = false;
        $obj->hideTopButton = true;
        $obj->hideIsRebroadcastOption = true;
        self::addDataObjectHelper('hideTopButton', 'Hide Top Button', 'This will hide the button on the top menu bar');
        return $obj;
    }

    public function getUUID() {
        return "39d3b5fe-9702-4f1d-9ffd-fe1cd22a4dc7";
    }

    public function getPluginVersion() {
        return "4.3";
    }

    public function canAddLinks() {
        $obj = $this->getDataObject();
        if (!User::isLogged()) {
            return false;
        }
        if ($obj->onlyAdminCanAddLinks && !User::isAdmin()) {
            return false;
        }
        return User::canStream();
    }

    public function getHTMLMenuRight() {
        global $global;
        $obj = $this->getDataObject();
        if($obj->hideTopButton){
            return '';
        }
        if (!$this->canAddLinks()) {
            return '';
        }

        include $global['systemRootPath'] . 'plugin/LiveLinks/view/menuRight.php';
    }

    static function getAllActive($future = false, $activeOnly = true, $notStarted = false, $users_id=0, $categories_id=0) {
        global $global;
        _mysql_connect();
        $sql = "SELECT * FROM  LiveLinks WHERE 1=1 ";

        if (!empty($future)) {
            $sql .= " AND end_php_time >= ".time().' ';
        }

        if (!empty($activeOnly)) {
            $sql .= " AND status='a' ";
        }

        if (!empty($notStarted)) {
            $sql .= " AND (start_php_time >= ".time().') ';
        }

        if (!empty($users_id)) {
            $sql .= " AND users_id = " . intval($users_id);
        }

        if (!empty($categories_id)) {
            $sql .= " AND categories_id = " . intval($categories_id);
        }

        $sql .= " ORDER BY start_php_time";
        //echo $sql;//exit;
        
        /**
         * 
         * @var array $global
         * @var object $global['mysqli'] 
         */
        $res = $global['mysqli']->query($sql);
        $rows = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                if(empty($row['start_php_time'])){
                    LiveLinksTable::updatePhpTimestampById($row['id']);
                }
                $row['link'] = str_replace('&amp;', '&', $row['link']);
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    static function getImageDafaultOrDynamic($liveLink_id) {
        global $global;
        $relative = "videos/LiveLinks/img_{$liveLink_id}.png";
        $filename = $global['systemRootPath'] . $relative ;
        if(file_exists($filename)){
            $imgURL = getURL($relative);
        }else{
            $imgURL = LiveLinks::getImage($liveLink_id);
        }
        return $imgURL;
    }

    static function getImage($id) {
        global $global;
        return "{$global['webSiteRootURL']}plugin/LiveLinks/getImage.php?id={$id}&format=jpg";
    }

    static function getImageGif($id) {
        global $global;
        return "{$global['webSiteRootURL']}plugin/LiveLinks/getImage.php?id={$id}&format=webp";
    }

    /**
     * 
     * @return string array(array("key"=>'live key', "users"=>false, "name"=>$userName, "user"=>$user, "photo"=>$photo, "UserPhoto"=>$UserPhoto, "title"=>''));
     */
    public function getLiveApplicationArray() {
        global $global;
        
        $liveUsers = AVideoPlugin::isEnabledByName('LiveUsers');        
        $rows = LiveLinks::getAllActive(true, true);
        $array = array();
        foreach ($rows as $value) {

            if ($value['type'] == 'unlisted') {
                continue;
            }
            if ($value['type'] == 'logged_only') {
                if (!User::isLogged()) {
                    continue;
                }
            }
                        
            $label = ($liveUsers ? getLiveUsersLabelLiveLinks($value['id']) : '');
            //var_dump( self::getPosterToLiveFromId($value['id']),$value['id'] );exit;
            
            $_array = array(
                'users_id'=>$value['users_id'],
                'title'=>$value['title'],
                'link'=>self::getLinkToLiveFromId($value['id']),
                'imgJPG'=>self::getPosterToLiveFromId($value['id']),
                'imgGIF'=>self::getPosterToLiveFromId($value['id'], 'webp'),
                'type'=>'LiveLink',
                'LiveUsersLabelLive'=>$label,
                'uid'=>'liveLink_'.$value['id'],
                'callback'=>'',
                'startsOnDate'=>date('Y-m-d H:i:s', $value['start_php_time']),
                'class'=>'',
                'description'=>$value['description']
            );
            
            $row = Live::getLiveApplicationModelArray($_array);
            //var_dump($row);exit;
            $row['categories_id'] = $value['categories_id'];
            $row['liveLinks_id'] = $value['id'];
            $row['start_date'] = $value['start_date'];
            $row['end_date'] = $value['end_date'];
            $row['end_date_my_timezone'] = date('Y-m-d H:i:s', $value['end_php_time']);
            $row['expires'] = strtotime($row['end_date_my_timezone']);
            $row['isRebroadcast'] = !empty($value['isRebroadcast']);
            $array[] = $row;
            
        }
        //var_dump($rows, $array);exit;
        return $array;
    }

    public function getHeadCode() {
        global $global;
        $obj = $this->getDataObject();
        // preload image
        $js = "";
        $css = '';
        if (!empty($obj->doNotShowLiveLinksLabel)) {
            $css .= '<style>.livelinksLabel{display: none;}</style>';
        }
        if(isLiveLink()){
            $js .= '<link href="'.getURL('plugin/Live/view/live.css').'" rel="stylesheet" type="text/css"/>';
        }

        return $js . $css;
    }
    /**
     * @param int $id
     * @param boolean $embed
     * @return string
     */
    public static function getLinkToLiveFromId($id, $embed = false) {
        return self::getLink($id, $embed);
    }

    public static function getLink($id, $embed = false) {
        global $global;
        //return "{$global['webSiteRootURL']}plugin/LiveLinks/view/Live.php?link={$id}".($embed?"&embed=1":"");
        $ll = new LiveLinksTable($id);
        if (!$embed) {
            return "{$global['webSiteRootURL']}liveLink/{$id}/" . urlencode(cleanURLName($ll->getTitle()));
        } else {
            return "{$global['webSiteRootURL']}liveLinkEmbed/{$id}/" . urlencode(cleanURLName($ll->getTitle()));
        }
    }

    public static function getSourceLink($id) {
        global $global;
        if (empty($id)) {
            return false;
        }
        $ll = new LiveLinksTable($id);
        if (empty($ll->getLink())) {
            return false;
        }
        $liveLink = 'Invalid livelink' . $id;
        if (filter_var($ll->getLink(), FILTER_VALIDATE_URL)) {
            $url = parse_url($ll->getLink());
            if ($url['scheme'] == 'https') {
                $liveLink = $ll->getLink();
            } else {
                $liveLink = "{$global['webSiteRootURL']}plugin/LiveLinks/proxy.php?livelink=" . urlencode($ll->getLink());
            }
        }
        return $liveLink;
    }

    public function getPosterToLiveFromId($id, $format = 'jpg') {
        global $global;
        return "{$global['webSiteRootURL']}plugin/LiveLinks/getImage.php?id={$id}&format={$format}";
    }

    public static function isLiveThumbsDisabled() {
        $obj = AVideoPlugin::getDataObject("LiveLinks");
        if (!empty($obj->disableLiveThumbs)) {
            return true;
        }
        return false;
    }

    public function getPosterThumbsImage($users_id, $live_servers_id) {
        global $global;
        $file = Live::_getPosterThumbsImage($users_id, $live_servers_id);

        if (!file_exists($global['systemRootPath'] . $file)) {
            $file = "plugin/Live/view/OnAir.jpg";
        }

        return $file;
    }

    public function getUploadMenuButton() {
        global $global;
        if (!$this->canAddLinks()) {
            return '';
        }
        $obj = $this->getDataObject();
        $buttonTitle = $obj->buttonTitle;
        include $global['systemRootPath'] . 'plugin/LiveLinks/getUploadMenuButton.php';
    }

    public static function getAllVideos($status = "", $showOnlyLoggedUserVideos = false, $activeUsersOnly = true) {
        global $global, $config, $advancedCustom;
        if (AVideoPlugin::isEnabledByName("VideoTags")) {
            if (!empty($_GET['tags_id']) && empty($videosArrayId)) {
                TimeLogStart("video::getAllVideos::getAllVideosIdFromTagsId({$_GET['tags_id']})");
                $videosArrayId = VideoTags::getAllVideosIdFromTagsId($_GET['tags_id']);
                TimeLogEnd("video::getAllVideos::getAllVideosIdFromTagsId({$_GET['tags_id']})", __LINE__);
            }
        }
        $status = str_replace("'", "", $status);

        $sql = "SELECT STRAIGHT_JOIN  u.*, v.*, c.iconClass, c.name as category, c.clean_name as clean_category,c.description as category_description, v.created as videoCreation, v.modified as videoModified "
                . " FROM LiveLinks as v "
                . " LEFT JOIN categories c ON categories_id = c.id "
                . " LEFT JOIN users u ON v.users_id = u.id "
                . " WHERE 1=1 ";

        if ($showOnlyLoggedUserVideos === true && !Permissions::canModerateVideos()) {
            $uid = intval(User::getId());
            $sql .= " AND v.users_id = '{$uid}'";
        } elseif (!empty($showOnlyLoggedUserVideos)) {
            $uid = intval($showOnlyLoggedUserVideos);
            $sql .= " AND v.users_id = '{$uid}'";
        } elseif (!empty($_GET['channelName'])) {
            $user = User::getChannelOwner($_GET['channelName']);
            $uid = intval($user['id']);
            $sql .= " AND v.users_id = '{$uid}' ";
        }

        if ($activeUsersOnly) {
            $sql .= " AND u.status = 'a' ";
        }

        if ($status == Video::SORT_TYPE_PUBLICONLY) {
            $sql .= " AND v.`type` = 'public' ";
        } elseif (!empty($status)) {
            $sql .= " AND v.`status` = '{$status}'";
        }

        if (!empty($_REQUEST['catName'])) {
            $catName = ($_REQUEST['catName']);
            $sql .= " AND (c.clean_name = '{$catName}' OR c.parentId IN (SELECT cs.id from categories cs where cs.clean_name =  '{$catName}' ))";
        }

        if (!empty($_GET['modified'])) {
            $_GET['modified'] = str_replace("'", "", $_GET['modified']);
            $sql .= " AND v.modified >= '{$_GET['modified']}'";
        }

        $sql .= AVideoPlugin::getVideoWhereClause();

        if (strpos(strtolower($sql), 'limit') === false) {
            if (!empty($_GET['limitOnceToOne'])) {
                $sql .= " LIMIT 1";
                unset($_GET['limitOnceToOne']);
            } else {
                $_REQUEST['rowCount'] = getRowCount();
                if (!empty($_REQUEST['rowCount'])) {
                    $sql .= " LIMIT {$_REQUEST['rowCount']}";
                } else {
                    _error_log("getAllVideos without limit " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
                    if (empty($global['limitForUnlimitedVideos'])) {
                        $global['limitForUnlimitedVideos'] = 100;
                    }
                    if ($global['limitForUnlimitedVideos'] > 0) {
                        $sql .= " LIMIT {$global['limitForUnlimitedVideos']}";
                    }
                }
            }
        }

        //echo $sql;exit;
        //_error_log("getAllVideos($status, $showOnlyLoggedUserVideos , $ignoreGroup , ". json_encode($videosArrayId).")" . $sql);
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);

        sqlDAL::close($res);
        $videos = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $row = cleanUpRowFromDatabase($row);
                $row['link'] = str_replace('&amp;', '&', $row['link']);
                if (empty($otherInfo)) {
                    $otherInfo = array();
                    $otherInfo['category'] = xss_esc_back($row['category']);
                    //$otherInfo['groups'] = UserGroups::getVideosAndCategoriesUserGroups($row['id']);
                    //$otherInfo['title'] = UTF8encode($row['title']);
                    $otherInfo['description'] = UTF8encode($row['description']);
                    $otherInfo['descriptionHTML'] = Video::htmlDescription($otherInfo['description']);
                    $otherInfo['filesize'] = 0;
                }

                foreach ($otherInfo as $key => $value) {
                    $row[$key] = $value;
                }

                $row['rotation'] = 0;
                $row['filename'] = '';
                $row['type'] = 'livelinks';
                $row['duration'] = '';
                $row['isWatchLater'] = 0;
                $row['isFavorite'] = 0;
                $row['views_count'] = 0;

                $videos[] = $row;
            }
            //$videos = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            //die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $videos;
    }

    static function notifySocketToRemoveLiveLinks($liveLinks_id) {
        $array = array();
        $array['stats'] = getStatsNotifications();
        $array['autoEvalCodeOnHTML'] = '$(".liveLink_' . $liveLinks_id . '").slideUp();';
        $socketObj = sendSocketMessageToAll($array, 'socketRemoveLiveLinks');
        return $socketObj;
    }

    public function getFooterCode() {
        $obj = $this->getDataObject();
        global $global;

        include $global['systemRootPath'] . 'plugin/LiveLinks/view/footer.php';
        return '<!-- LiveLinks Footer Code -->';
    }

    static function userCanWatch($users_id, $livelinks_id) {
        if (empty($livelinks_id)) {
            return false;
        }

        if (User::isAdmin()) {
            return true;
        }

        $livelinks = new LiveLinksTable($livelinks_id);
        if ($livelinks->getUsers_id() == $users_id) {
            return true;
        }

        $user_groups_ids = LiveLinksTable::getUserGorupsIds($livelinks_id);
        if (empty($user_groups_ids)) {
            return true;
        }
        
        if (empty($users_id)) {
            return false;
        }

        return LiveLinksTable::userGroupsMatch($livelinks_id, $users_id);
    }

    public static function getDinamicVideoLink($videoLink, $title, $owner_users_id) {
        global $global;
        $video = new stdClass();
        $video->videoLink = $videoLink;
        $video->title = $title;
        $video->users_id = $owner_users_id;

        $hash = encryptString(_json_encode($video));

        return "{$global['webSiteRootURL']}liveLink/0/?hash={$hash}";
    }

    public static function decodeDinamicVideoLink() {

        if (empty($_REQUEST['hash'])) {
            return false;
        }

        $string = decryptString($_REQUEST['hash']);
        $video = _json_decode($string);
        //var_dump($video);exit;
        $t = array();
        $t['id'] = -1;
        $t['users_id'] = $video->users_id;
        $t['title'] = $video->title;
        $t['link'] = $video->videoLink;
        $t['description'] = @$video->description;
        return $t;
    }
    
    public static function getMediaSession($id) {
        $ll = new LiveLinksTable($id);
        
        if(empty($ll->getUsers_id())){
            return false;
        }
        
        $posters = array();
        //var_dump($posters);exit;
        $category = Category::getCategory($ll->getCategories_id());
        $MediaMetadata = new stdClass();

        $MediaMetadata->title = $ll->getTitle();
        $MediaMetadata->artist = User::getNameIdentificationById($ll->getUsers_id());
        $MediaMetadata->album = $category['name'];
        $MediaMetadata->artwork = array();
        
        $poster = LiveLinks::getImage($id);
        $MediaMetadata->artwork[] = array('src' => $poster, 'sizes' => "512x512", 'type' => 'image/jpg');
        /*
        foreach ($posters as $key => $value) {
            $MediaMetadata->artwork[] = array('src' => $value['url'], 'sizes' => "{$key}x{$key}", 'type' => 'image/jpg');
        }
         * 
         */
        return $MediaMetadata;
    }

    static function getImagesPaths($livelinks_id){
        global $global;
        $relativeDir = 'videos/LiveLinks/';
        $pathDir = "{$global['systemRootPath']}{$relativeDir}";
        $pathURL = "{$global['webSiteRootURL']}{$relativeDir}";
        if(!is_dir($pathDir)){
            mkdir($pathDir);
        }
        $imgName = "img_{$livelinks_id}.png";
        $path = "{$pathDir}{$imgName}";
        $url = "{$pathURL}{$imgName}";
        $relative = "{$relativeDir}{$imgName}";
        $exists = file_exists($path);
        $showURL = $url;
        if(!$exists){
            $showURL = "{$global['webSiteRootURL']}plugin/Live/view/OnAir.jpg";
        }
        return array('path'=>$path, 'url'=>$url, 'relative'=>$relative, 'exists'=>$exists, 'showURL'=>$showURL);
    }

    static function isLiveLinkDateValid($livelinks_id){
        $liveLink = new LiveLinksTable($livelinks_id);

        if($liveLink->getStart_php_time() > time()){
            return false;
        }

        if($liveLink->getEnd_php_time() < time()){
            return false;
        }

        return true;
    }

}
