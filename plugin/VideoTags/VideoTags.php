<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/Tags.php';
require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/TagsHasVideos.php';
require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/TagsTypes.php';
require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/Tags_subscriptions.php';

class VideoTags extends PluginAbstract {

    static $TagTypePinned = 'pinned';
    static $TagTypePaid = 'paid';
    static $TagTypeStatus = 'status';
    static $TagTypeUserGroups = 'userGroups';
    static $TagTypeCategory = 'category';
    static $TagTypeSource = 'source';

    public function getTags() {
        return array(
            PluginTags::$FREE,
        );
    }

    public function getDescription() {
        $txt = "User interface for managing tags";
        $help = "";
        return $txt . $help;
    }

    public function getName() {
        return "VideoTags";
    }

    public function getUUID() {
        return "tags16e9-e3e5-4a15-8990-f3e9ba32be9c";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->onlyAdminCanCreateTags = false;
        $obj->maxTags = 100;
        $obj->maxChars = 100;
        $obj->disableTagsSubscriptions = false;
        $obj->showTagsOnEmbed = true;
        $obj->showTagsLabels = true;
        return $obj;
    }

    static function saveTags($tagsNameList, $videos_id) {
        TimeLogStart(__FILE__ . "::" . __FUNCTION__);
        // remove all tags from the video
        $tagsSaved = array();
        $deleted = self::removeAllTagFromVideo($videos_id);
        TimeLogEnd(__FILE__ . "::" . __FUNCTION__, __LINE__);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['getVideoTags'][$videos_id]);
        _session_write_close();
        TimeLogEnd(__FILE__ . "::" . __FUNCTION__, __LINE__);
        if ($deleted) {
            foreach ($tagsNameList as $value) {
                if (empty($value['items'])) {
                    continue;
                }
                foreach ($value['items'] as $value2) {
                    $value2 = trim(preg_replace("/[^[:alnum:][:space:]_-]/u", '', $value2));
                    // check if exists
                    // create case do not exists
                    $tag = self::getOrCreateTagFromName($value2, $value['id']);

                    $tag->loadFromName($value2, $value['id']);
                    // add it to the video
                    $tagsSaved[] = $tag->_addVideo($videos_id);
                }
            }
        }
        TimeLogEnd(__FILE__ . "::" . __FUNCTION__, __LINE__);
        //var_dump($tagsSaved, $tagsNameList, $videos_id);
        return $tagsSaved;
    }

    static function getTagFromName($name, $tags_types_id) {
        $tag = new Tags(0);
        $name = trim(preg_replace("/[^[:alnum:][:space:]_-]/u", '', $name));
        $tag->loadFromName($name, $tags_types_id);
        return $tag;
    }

    static function removeAllTagFromVideo($videos_id) {
        return TagsHasVideos::removeAllTagsFromVideo($videos_id);
    }


    static function add($name, $tags_types_id, $videos_id) {
        $tag = VideoTags::getOrCreateTagFromName($name, $tags_types_id);
        $id = TagsHasVideos::getFromTagsIdAndVideosId($tag->getId(), $videos_id);
        if(empty($id)){
            return $tag->_addVideo($videos_id);
        }
        return $id;
    }

    static function getOrCreateTagFromName($name, $tags_types_id) {
        $name = trim(preg_replace("/[^[:alnum:][:space:]_-]/u", '', $name));
        $tag = self::getTagFromName($name, $tags_types_id);
        $id = $tag->getId();
        if (empty($id) && self::canCreateTag()) {
            $tag->setName($name);
            $tag->setTags_types_id($tags_types_id);
            $id = $tag->save();
            $tag = new Tags($id);
        }
        return $tag;
    }

    static function getAll($users_id = 0) {
        return Tags::getAllWithSubscriptionRow($users_id);
    }

    static function getAllFromVideosId($videos_id) {
        return TagsHasVideos::getAllFromVideosId($videos_id);
    }

    static function getArrayFromVideosId($videos_id, $tags_id = 0) {
        $rows = TagsHasVideos::getAllFromVideosId($videos_id);
        $array = array();
        foreach ($rows as $value) {
            if(!empty($tags_id)){
                if($tags_id!=$value['tags_types_id']){
                    continue;
                }
            }
            $array[] = $value['name'];
        }
        //var_dump($videos_id, $tags_id, $rows, $array);exit;
        return $array;
    }

    static function getAllVideosIdFromTagsId($tags_id, $limit = 100, $status = Video::SORT_TYPE_VIEWABLE) {
        return TagsHasVideos::getAllVideosIdFromTagsId($tags_id, $limit, $status);
    }

    static function getTotalVideosFromTagsId($tags_id, $status = Video::SORT_TYPE_VIEWABLE) {
        return TagsHasVideos::getTotalVideosFromTagsId($tags_id, $status);
    }

    static function getVideoIndexFromTagsId($tags_id, $videos_id) {
        if(!empty($videos_id)){
            $pl = self::getAllVideosFromTagsId($tags_id);
            foreach ($pl as $key => $value) {
                if($value['videos_id']==$videos_id){
                    return $key;
                }
            }
        }
        return 0;
    }

    static function getAllVideosFromTagsId($tags_id, $limit = 100) {
        return TagsHasVideos::getAllVideosFromTagsId($tags_id, $limit);
    }

    static function getTagsInputs($colSize = 3, $videos_id=0) {
        $types = TagsTypes::getAll();
        $str = "";
        foreach ($types as $value) {
            $input = self::getTagsInput($value['id'], $videos_id);
            $str .= "<div class=\"col-sm-{$colSize}\"><label for=\"tagTypesId{$value['id']}\">" . __($value['name']) . "</label> {$input}</div> ";
        }
        if(!empty($str)){
            $str = "<div class=\"row\">{$str}</div>";
        }
        return $str;
    }

    static function getTagsInputsJquery() {
        $types = TagsTypes::getAll();
        $array = array();
        foreach ($types as $value) {
            $array[] = "{id:{$value['id']} , items: (function() { var element = $(\"#inputTags{$value['id']}\"); if (element.length === 0) { console.log('Element #inputTags{$value['id']} not found'); return []; } return element.tagsinput('items'); })()} ";
        }
        return "[" . implode(",", $array) . "]";
    }

    static function getTagsInputsJqueryRemoveAll() {
        $types = TagsTypes::getAll();
        $str = "";
        foreach ($types as $value) {
            $str .= "if ($(\"#inputTags{$value['id']}\").length === 0) { console.log('Element #inputTags{$value['id']} not found for removeAll'); } else { $(\"#inputTags{$value['id']}\").tagsinput('removeAll'); } ";
        }
        return $str;
    }

    static function getTagsInput($tagTypesId, $videos_id=0) {
        global $global;

        // Step 1: Query the database for tags associated with videos_id
        $tags = []; // Initialize an array to hold the tags
        if($videos_id > 0) {
            // Assuming you have a function to get tags by video ID
            $tags = self::getArrayFromVideosId($videos_id, $tagTypesId);
        }

        // Step 2: Convert tags into a suitable format (JSON)
        $tagsJson = json_encode($tags);
        //var_dump($tagsJson, $tags);
        $obj = AVideoPlugin::getObjectData("VideoTags");
        $str = '<input type="text" value="" id="inputTags' . $tagTypesId . '"/>
                <script>
                $(document).ready(function () {
                    var videoTags' . $tagTypesId . ' = new Bloodhound({
                        datumTokenizer: Bloodhound.tokenizers.obj.whitespace(\'name\'),
                        queryTokenizer: Bloodhound.tokenizers.whitespace,
                        prefetch: {
                            url: \'' . $global['webSiteRootURL'] . 'plugin/VideoTags/tags.json.php?tags_types_id=' . $tagTypesId . '?\'+Math.random(),
                            filter: function(list) {
                                return $.map(list, function(tagsname) {
                                    return { name: tagsname };
                                });
                            }
                        }
                    });
                    videoTags' . $tagTypesId . '.initialize();

                    $(\'#inputTags' . $tagTypesId . '\').tagsinput({
                        maxTags: ' . $obj->maxTags . ',
                        maxChars: ' . $obj->maxChars . ',
                        trimValue: true,
                        typeaheadjs: {
                            name: \'videoTags\',
                            displayKey: \'name\',
                            valueKey: \'name\',
                            source: videoTags' . $tagTypesId . '.ttAdapter()
                        },
                        freeInput: ' . (self::canCreateTag() ? "true" : "false") . '
                    });

                    // Step 3: Preload and fill the input with all tags from the database
                    var preloadedTags = ' . $tagsJson . ';
                    if(preloadedTags && preloadedTags.length) {
                        preloadedTags.forEach(function(tag) {
                            var element = $(\'#inputTags' . $tagTypesId . '\');
                            if (element.length === 0) {
                                console.log(\'Element #inputTags' . $tagTypesId . ' not found for adding tag: \' + tag);
                            } else {
                                element.tagsinput(\'add\', tag);
                            }
                        });
                    }
                });
                </script>';
        return $str;
    }


    static function canCreateTag() {
        $obj = AVideoPlugin::getObjectData("VideoTags");
        if (empty($obj->onlyAdminCanCreateTags)) {
            return true;
        }
        return User::isAdmin();
    }


    static function isUserSubscribed($users_id, $tags_id) {
        global $_isUserSubscribedTags;

        if(empty($_isUserSubscribedTags)){
            $_isUserSubscribedTags = array();
        }

        if(!isset($_isUserSubscribedTags[$users_id])){
            $UserSubscriptions = Tags_subscriptions::getAllFromUsers_id($users_id);
            $_isUserSubscribedTags[$users_id] = array();
            foreach ($UserSubscriptions as $row) {
                $_isUserSubscribedTags[$users_id][$row['tags_id']] = $row;
            }

        }
        if(empty($_isUserSubscribedTags[$users_id][$tags_id])){
            return false;
        }
        return $_isUserSubscribedTags[$users_id][$tags_id];
    }

    public static function getButton($tags_id, $videos_id = 0, $btnClass = 'btn-xs', $btnClassPrimary = 'btn-primary', $btnClassSuccess = 'btn-success', $btnClassDefault = 'btn-default'){
        if(empty($tags_id)){
            return '';
        }
        global $global, $advancedCustom;
        $rowCount = getRowCount();
        $total = TagsHasVideos::getTotalVideosFromTagsId($tags_id);
        $tag = new Tags($tags_id);
        $btnFile = $global['systemRootPath'] . 'plugin/VideoTags/subscribeBtnOffline.html';

        $notify = '';
        $email = '';
        $subscribe = __("Subscribe");
        $unsubscribe = __("Unsubscribe");
        $tagLink = self::getTagLink($tags_id);

        $playAllLink = '#';
        $playAllClass = 'hidden';
        if(AVideoPlugin::isEnabledByName('PlayLists')){
            $playlist_index = self::getVideoIndexFromTagsId($tags_id, $videos_id);
            //var_dump($videos_id,getVideos_id(), $playlist_index);exit;
            $playAllLink = PlayLists::getTagLink($tags_id, false, $playlist_index);
            $playAllClass = '';
        }

        $subscribeText = $tag->getName();
        $subscribedText = $tag->getName();
        $users_id = User::getId();
        $encryptedIdAndUser = encryptString(array('tags_id'=>$tags_id, 'users_id'=> $users_id));
        $subscribed = '';
        if (User::isLogged()) {
            $btnFile = $global['systemRootPath'] . 'plugin/VideoTags/subscribeBtn.html';
            $email = User::getMail();
            $subs = self::isUserSubscribed($users_id, $tags_id);
            if (!empty($subs)) {
                if (!empty($subs['notify'])) {
                    $notify = 'notify';
                }
                $subscribed = 'subscribed';
            }
        }
        $content = local_get_contents($btnFile);

        $signInBTN = ("<a class='btn btn-primary btn-sm btn-block' href='{$global['webSiteRootURL']}user'>".__("Sign in to subscribe to this tag")."</a>");

        $search = [
            '{btnClass}',
            '{btnClassPrimary}',
            '{btnClassSuccess}',
            '{btnClassDefault}',
            '{playAllClass}',
            '{playAllLink}',
            '{playAllText}',
            '{encryptedIdAndUser}',
            '{tags_id}',
            '{notify}',
            '{tagLink}',
            '{tooltipStop}',
            '{tooltip}',
            '{titleOffline}',
            '{tooltipOffline}',
            '{total}',
            '{subscribe}', '{unsubscribe}', '{subscribeText}', '{subscribedText}', '{subscribed}'
        ];

        $replace = [
            $btnClass,
            $btnClassPrimary,
            $btnClassSuccess,
            $btnClassDefault,
            $playAllClass,
            $playAllLink,
            __("Play All"),
            $encryptedIdAndUser,
            $tags_id,
            $notify,
            $tagLink,
            __("Stop getting notified for every new video"),
            __("Click to get notified for every new video"),
            __("Want to subscribe to this tag?"),
            $signInBTN,
            $total,
            $subscribe, $unsubscribe, $subscribeText, $subscribedText, $subscribed];

        $btnHTML = str_replace($search, $replace, $content);
        //echo $btnHTML;exit;
        return $btnHTML;
    }

    static function getTagLink($tags_id) {
        global $global;
        if (empty($tags_id)) {
            return '';
        }
        $tag = new Tags($tags_id);

        if (empty($tag->getName())) {
            return '';
        }
        return $global['webSiteRootURL'] . 'tag/' . $tags_id . '/' . urlencode($tag->getName());
    }

    static function getTagHTMLLink($tags_id, $total_videos = 0) {
        global $global;
        if (empty($tags_id)) {
            return '';
        }
        $tag = new Tags($tags_id);

        if (empty($tag->getName()) || $tag->getName() === '-') {
            return '';
        }

        if ($total_videos) {
            $tooltipText = "1 " . __("Video");
            if ($total_videos > 1) {
                $tooltipText = "{$total_videos} " . __("Videos");
            }
            $tooltip = "data-toggle=\"tooltip\" title=\"{$tooltipText}\"";
        }

        $strT = '<a ' . $tooltip . ' href="' . VideoTags::getTagLink($tags_id) . '" class="label label-primary">' . __($tag->getName()) . '</a> ';
        return $strT;
    }



    static function getAllSubscribersFromVideosId($videos_id) {
        $tags = TagsHasVideos::getAllFromVideosId($videos_id);
        $users = array();
        foreach ($tags as $value) {
            $subscriptions = Tags_subscriptions::getAllFromTags_id($value['id']);
            foreach ($subscriptions as $user) {
                $users[] = $user;
            }
        }
        return $users;
    }

    static function getLabels($videos_id, $showType = true, $showSubscription = true) {
        global $global;

        $currentPage = getCurrentPage();
        $rowCount = getRowCount();
        unsetCurrentPage();
        $_REQUEST['rowCount'] = 1000;

        $post = $_POST;
        unset($_POST);
        $get = $_GET;
        unset($_GET);
        $types = TagsTypes::getAll();
        //var_dump($videos_id,  $types);
        $obj = AVideoPlugin::getDataObject('VideoTags');
        $tagsStrList = array();
        foreach ($types as $type) {
            $tags = TagsHasVideos::getAllFromVideosIdAndTagsTypesId($videos_id, $type['id']);
            //var_dump($tags);
            $strT = "";
            foreach ($tags as $value) {
                if (empty($value['name']) || $value['name'] === '-') {
                    continue;
                }
                if($obj->disableTagsSubscriptions || empty($showSubscription)){
                    $strT .= self::getTagHTMLLink($value['tags_id'], $value['total']);
                }else{
                    $strT .= self::getButton($value['tags_id'], $videos_id);
                }
            }
            if (!empty($strT)) {
                $label = "";
                if ($obj->showTagsLabels && $showType) {
                    $name = str_replace("_", " ", $type['name']);
                    $label = "<strong class='text-muted tag-label'>" . __($name) . ": </strong> ";
                }
                $tagsStrList[] = "{$label}{$strT}";
            }
        }
        //exit;
        $_POST = $post;
        $_GET = $get;

        $_REQUEST['current'] = $currentPage;
        $_REQUEST['rowCount'] = $rowCount;
        return "<div class='text-muted'>" . implode("</div><div class='text-muted'>", $tagsStrList) . "</div>";
    }

    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/VideoTags/pluginMenu.html';
        return file_get_contents($filename);
    }

    public static function getManagerVideosAddNew() {
        return '"videoTags": ' . self::getTagsInputsJquery() . ',';
    }

    public static function getManagerVideosReset() {
        return self::getTagsInputsJqueryRemoveAll();
    }

    public static function getManagerVideosEdit() {
        $js = "if (typeof row.videoTags !== 'undefined' && row.videoTags.length) {
                                            for (i = 0; i < row.videoTags.length; i++) {
                                                var element = $('#inputTags' + row.videoTags[i].tag_types_id);
                                                if (element.length === 0) {
                                                    console.log('Element #inputTags' + row.videoTags[i].tag_types_id + ' not found for editing, tag: ' + row.videoTags[i].name);
                                                } else {
                                                    element.tagsinput('add', row.videoTags[i].name);
                                                }
                                            }
                                        }";
        return self::getManagerVideosReset() . $js;
    }

    public static function getManagerVideosEditField($type = 'Advanced') {
        if ($type == 'SEO') {
            return self::getTagsInputs();
        }
    }

    public static function getManagerVideosJavaScripts() {
        global $global;
        $js = "<script src=\"" . getURL('plugin/VideoTags/bootstrap-tagsinput/bootstrap-tagsinput.min.js') . "\" type=\"text/javascript\"></script><script src=\"" . getURL('plugin/VideoTags/bootstrap-tagsinput/typeahead.bundle.js') . "\" type=\"text/javascript\"></script>";
        $css = "<style></style>";
        return $css.$js;
    }

    public static function saveVideosAddNew($post, $videos_id) {
        if (empty($post['videoTags'])) {
            return false;
        }
        return self::saveTags($post['videoTags'], $videos_id);
    }

    public static function getAllVideosArray($videos_id) {
        $row = array();
        $row['videoTags'] = Tags::getAllFromVideosId($videos_id);
        $row['videoTagsObject'] = Tags::getObjectFromVideosId($videos_id);
        return $row;
    }

    public function getPluginVersion() {
        return "3.0";
    }

    public function updateScript() {
        global $global;
        //update version 2.0
        if (AVideoPlugin::compareVersion($this->getName(), "2.0") < 0) {
            sqlDal::executeFile($global['systemRootPath'] . 'plugin/VideoTags/install/update.sql');
        }
        if (AVideoPlugin::compareVersion($this->getName(), "3.0") < 0) {
            sqlDal::executeFile($global['systemRootPath'] . 'plugin/VideoTags/install/updateV3.0.sql');
        }
        return true;
    }

    public function getHeadCode(): string {
        $css = '<link href="' .getURL('plugin/VideoTags/View/style.css') . '" rel="stylesheet" type="text/css"/>';
        return $css;
    }
    public function getFooterCode(){
        $js = '';
        $obj = AVideoPlugin::getDataObject('VideoTags');
        if($obj->showTagsOnEmbed && isEmbed() && isVideo()){
            $videos_id = getVideos_id();
            if(!empty($videos_id)){
                $labels = self::getLabels($videos_id, true, false);
                //var_dump($labels);exit;
                if(!empty($labels)){
                    $js .= '<script>videoTagsLabels = '.json_encode($labels).';</script>';
                }
            }
        }
        //var_dump($js);exit;
        $js .= '<script src="' .getURL('plugin/VideoTags/View/script.js') . '" type="text/javascript"></script>';
        return $js;
    }

    public function getMobileInfo() {
        $obj = $this->getDataObject();
        $return = new stdClass();
        $return->videoTagsTypes = TagsTypes::getAll();
        return $return;
    }
}
