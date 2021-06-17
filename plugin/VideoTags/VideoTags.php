<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/Tags.php';
require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/TagsHasVideos.php';
require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/TagsTypes.php';

class VideoTags extends PluginAbstract {

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
        return $obj;
    }

    static function saveTags($tagsNameList, $videos_id) {
        TimeLogStart(__FILE__."::".__FUNCTION__);
        // remove all tags from the video
        $tagsSaved = array();
        $deleted = self::removeAllTagFromVideo($videos_id);
        TimeLogEnd(__FILE__."::".__FUNCTION__, __LINE__);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['getVideoTags'][$videos_id]);
        session_write_close();
        TimeLogEnd(__FILE__."::".__FUNCTION__, __LINE__);
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
        TimeLogEnd(__FILE__."::".__FUNCTION__, __LINE__);
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

    static function getAllFromVideosId($videos_id) {
        return TagsHasVideos::getAllFromVideosId($videos_id);
    }

    static function getAllVideosIdFromTagsId($tags_id) {
        return TagsHasVideos::getAllVideosIdFromTagsId($tags_id);
    }

    static function getTagsInputs() {
        $types = TagsTypes::getAll();
        $str = "";
        foreach ($types as $value) {
            $input = self::getTagsInput($value['id']);
            $str .= "<label for=\"tagTypesId{$value['id']}\">".__($value['name'])."</label><div class=\"clear clearfix\">{$input}</div> ";
        }
        return $str;
    }

    static function getTagsInputsJquery() {
        $types = TagsTypes::getAll();
        $array = array();
        foreach ($types as $value) {
            $array[] = "{id:{$value['id']} , items: $(\"#inputTags{$value['id']}\").tagsinput('items')} ";
        }
        return "[" . implode(",", $array) . "]";
    }

    static function getTagsInputsJqueryRemoveAll() {
        $types = TagsTypes::getAll();
        $str = "";
        foreach ($types as $value) {
            $str .= "$(\"#inputTags{$value['id']}\").tagsinput('removeAll'); ";
        }
        return $str;
    }

    static function getTagsInput($tagTypesId) {
        global $global;
        $obj = AVideoPlugin::getObjectData("VideoTags");
        $str = '<input type="text" value="" id="inputTags' . $tagTypesId . '"/>
                <script>
                $(document).ready(function () {
var citynames' . $tagTypesId . ' = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace(\'name\'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  prefetch: {
    url: \'' . $global['webSiteRootURL'] . 'plugin/VideoTags/tags.json.php?tags_types_id=' . $tagTypesId . '?\'+Math.random(),
    filter: function(list) {
      return $.map(list, function(cityname) {
        return { name: cityname }; });
    }
  }
});
citynames' . $tagTypesId . '.initialize();

$(\'#inputTags' . $tagTypesId . '\').tagsinput({
    maxTags: ' . $obj->maxTags . ',
    maxChars: ' . $obj->maxChars . ',
    trimValue: true,
    typeaheadjs: {
      name: \'citynames\',
      displayKey: \'name\',
      valueKey: \'name\',
      source: citynames' . $tagTypesId . '.ttAdapter()
    },
    freeInput: ' . (self::canCreateTag() ? "true" : "false") . '
});

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

    static function getLabels($videos_id, $showType=true) {
        global $global;

        $currentPage = getCurrentPage();
        $rowCount = getRowCount();
        $_REQUEST['current'] = 1;
        $_REQUEST['rowCount'] = 1000;           

        $post = $_POST;
        unset($_POST);
        $get = $_GET;
        unset($_GET);
        $types = TagsTypes::getAll();

        $tagsStrList = array();
        foreach ($types as $type) {
            $tags = TagsHasVideos::getAllFromVideosIdAndTagsTypesId($videos_id, $type['id']);
            $strT = "";
            foreach ($tags as $value) {
                $tooltip = "1 " . __("Video");
                if ($value['total'] > 1) {
                    $tooltip = "{$value['total']} " . __("Videos");
                }
                $strT .= '<a data-toggle="tooltip" title="' . $tooltip . '" href="' . $global['webSiteRootURL'] . 'tag/' . $value['tags_id'] . '/' . urlencode($value['name']) . '" class="label label-primary">' . __($value['name']) . '</a> ';
            }
            if (!empty($strT)) {
                $label = "";
                if($showType){
                    $name = str_replace("_", " ", $type['name']);
                    $label = "<strong class='label text-muted'>".__($name).": </strong> ";
                }
                $tagsStrList[] = "{$label}{$strT}";
            }
        }
        $_POST = $post;
        $_GET = $get;
        
        $_REQUEST['current'] = $currentPage;
        $_REQUEST['rowCount'] = $rowCount;  
        return "<div class='text-muted'>".implode("</div><div class='text-muted'>", $tagsStrList)."</div>";
    }

    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/VideoTags/pluginMenu.html';
        return file_get_contents($filename);
    }
    
    
    public static function getManagerVideosAddNew(){
        return '"videoTags": ' . self::getTagsInputsJquery() . ',';
    }
    
    public static function getManagerVideosReset(){
        return self::getTagsInputsJqueryRemoveAll();
    }    
    
    public static function getManagerVideosEdit(){
        $js = "if (typeof row.videoTags !== 'undefined' && row.videoTags.length) {
                                            for (i = 0; i < row.videoTags.length; i++) {
                                                $('#inputTags' + row.videoTags[i].tag_types_id).tagsinput('add', row.videoTags[i].name);
                                            }
                                        }";
        return self::getManagerVideosReset().$js;
    }
    
    public static function getManagerVideosEditField(){
        return self::getTagsInputs();
    }
    
    public static function getManagerVideosJavaScripts(){
        global $global;
        return "<script src=\"".getCDN()."plugin/VideoTags/bootstrap-tagsinput/bootstrap-tagsinput.min.js\" type=\"text/javascript\"></script><script src=\"".getCDN()."plugin/VideoTags/bootstrap-tagsinput/typeahead.bundle.js\" type=\"text/javascript\"></script>";
    }
    
    public static function saveVideosAddNew($post, $videos_id){
        if(empty($post['videoTags'])){
            return false;
        }
        return self::saveTags($post['videoTags'], $videos_id);
    }
    
    public static function getAllVideosArray($videos_id){
        $row = array();
        $row['videoTags'] = Tags::getAllFromVideosId($videos_id);
        $row['videoTagsObject'] = Tags::getObjectFromVideosId($videos_id);
        return $row;
    }   
    
    
    public function getPluginVersion() {
        return "2.0";   
    }
    
    public function updateScript() {
        global $global;
        //update version 2.0        
        if(AVideoPlugin::compareVersion($this->getName(), "2.0")<0){
            sqlDal::executeFile($global['systemRootPath'] . 'plugin/VideoTags/install/update.sql');
        }    
        return true;
    }

}
