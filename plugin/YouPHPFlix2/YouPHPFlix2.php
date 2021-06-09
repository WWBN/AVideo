<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
class YouPHPFlix2 extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$NETFLIX,
            PluginTags::$FREE,
        );
    }
    public function getDescription() {
        $txt = "Make the first page looks like a Netflix site";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/Configure-a-Netflix-Clone-Page' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $txt.$help;
    }

    public function getName() {
        return "YouPHPFlix2";
    }

    public function getUUID() {
        return "e3a568e6-ef61-4dcc-aad0-0109e9be8e36";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->hidePrivateVideos = false;
        $obj->pageDots = true;
        $obj->Suggested = true;
        $obj->SuggestedAutoPlay = true;
        $obj->PlayList = true;
        $obj->PlayListAutoPlay = true;
        $obj->Channels = true;
        $obj->ChannelsAutoPlay = true;
        $obj->Trending = true;
        $obj->TrendingAutoPlay = true;
        $obj->DateAdded = true;
        $obj->DateAddedAutoPlay = true;
        $obj->MostPopular = true;
        $obj->MostPopularAutoPlay = true;
        $obj->MostWatched = true;
        $obj->MostWatchedAutoPlay = true;
        $obj->SortByName = false;
        $obj->SortByNameAutoPlay = true;
        $obj->Categories = true;
        $obj->CategoriesAutoPlay = true;
        $obj->maxVideos = 20;
        $obj->BigVideo = true;
        $obj->RemoveBigVideoDescription = false;
        $obj->BigVideoPlayIcon = true;
        $obj->BigVideoMarginBottom = "-350px";
        $obj->backgroundRGB = "20,20,20";
        $obj->landscapePosters = true;
        $obj->playVideoOnFullscreen = true;
        $obj->playVideoOnFullscreenOnIframe = true;
        $obj->youtubeModeOnFullscreen = false;
        $obj->paidOnlyLabelOverPoster = false;
        $obj->titleLabel = true;
        $obj->titleLabelOverPoster = false;
        $obj->titleLabelCSS = "";
        $obj->hidePlayButtonIfCannotWatch = false;
        return $obj;
    }

    public function getHelp(){
        if(User::isAdmin()){
            return "<h2 id='YouPHPFlix help'>YouPHPFlix options (admin)</h2><table class='table'><thead><th>Option-name</th><th>Default</th><th>Description</th></thead><tbody><tr><td>DefaultDesign</td><td>checked</td><td>The original style, for each category, one row with the newest videos</td></tr><tr><td>DateAdded,MostPopular,MostWatched,SortByName</td><td>checked,checked,checked,unchecked</td><td>Metacategories</td></tr><tr><td>LiteDesign</td><td>unchecked</td> <td>All categories in one row</td></tr><tr><td>separateAudio</td><td>unchecked</td><td>Create a own row for audio</td></tr></tbody></table>";
        }
        return "";
    }

    public function getFirstPage(){
        global $global;

        if(!AVideoPlugin::isEnabledByName("CombineSites")){
            return $global['systemRootPath'].'plugin/YouPHPFlix2/view/modeFlix.php';
        }
    }

    public function getHeadCode() {
        global $global, $isEmbed;
        $obj = $this->getDataObject();
        $baseName = basename($_SERVER["SCRIPT_FILENAME"]);
        if($baseName == 'channel.php'){
            return "";
        }
        $css = "";
        //$css .= "<link href=\"".getCDN()."view/css/custom/".$obj->theme.".css\" rel=\"stylesheet\" type=\"text/css\"/>";
        $css .= "<link href=\"".getCDN()."plugin/YouPHPFlix2/view/css/style.css?".  filectime("{$global['systemRootPath']}plugin/YouPHPFlix2/view/css/style.css")."\" rel=\"stylesheet\" type=\"text/css\"/>";
        if(!empty($obj->youtubeModeOnFullscreen) && canFullScreen()){
            $isEmbed = 1;
            $css .= '<link href="' .getCDN() . 'plugin/YouPHPFlix2/view/css/fullscreen.css" rel="stylesheet" type="text/css"/>';
            $css .= '<style>.container-fluid {overflow: visible;padding: 0;}#mvideo{padding: 0 !important; position: absolute; top: 0;}</style>';
            $css .= '<style>body.fullScreen{overflow: hidden;}</style>';
        }
        return $css;
    }

    static function getLinkToVideo($videos_id, $ignoreEmbed = false){
        $obj = AVideoPlugin::getObjectData("YouPHPFlix2");
        $link = Video::getLinkToVideo($videos_id);
        if(!empty($obj->playVideoOnFullscreen)){
            if(!Video::isSerie($videos_id) && empty($ignoreEmbed)){
                $link = parseVideos($link, 1, 0, 0, 0, 1);
            }
        }
        return $link;
    }

    public function getFooterCode() {
        $obj = $this->getDataObject();
        global $global;

        $js = '';
        
        if (!empty($obj->playVideoOnFullscreenOnIframe) && !isSerie()) {
            $js .= '<script>$(function () { if(typeof linksToFullscreen === \'function\'){ linksToFullscreen(\'a.galleryLink\'); } });</script>';
            $js .= '<script>var playVideoOnFullscreen = 1</script>';
        } else
        if (!empty($obj->playVideoOnFullscreen) && !isSerie()) {
            $js .= '<script>$(function () { if(typeof linksToEmbed === \'function\'){ linksToEmbed(\'a.galleryLink\'); } });</script>';
            $js .= '<script>var playVideoOnFullscreen = 2</script>';
        } else{
            $js .= '<script>var playVideoOnFullscreen = false</script>';
        }
        $js .= '<script src="' .getCDN() . 'plugin/YouPHPFlix2/view/js/fullscreen.js"></script>';
        return $js;
    }

}
