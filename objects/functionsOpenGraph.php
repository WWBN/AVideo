<?php
function determineOgType($videoType)
{
    switch ($videoType) {
        case Video::$videoTypeAudio:
        case Video::$videoTypeLinkAudio:
            return 'music.song';
        case Video::$videoTypePdf:
        case Video::$videoTypeArticle:
        case Video::$videoTypeImage:
            return 'article';
        case Video::$videoTypeEmbed:
        case Video::$videoTypeSerie:
        case Video::$videoTypeShort:
        case Video::$videoTypeVideo:
            return 'video.other';
        default:
            return 'website';
    }
}

function getMetaTagsContentType($videoType)
{
    switch ($videoType) {
        case Video::$videoTypeAudio:
        case Video::$videoTypeLinkAudio:
            return '<meta property="og:audio:type" content="audio/mpeg" />';
        case Video::$videoTypePdf:
        case Video::$videoTypeArticle:
            return '<meta property="og:type" content="article" />';
        default:
            // Add more cases as needed
            return '<meta property="og:video:type" content="video/mp4" />';
    }
}

function getMetaTagsContentVideoType($sourceFileURL)
{
    if (preg_match('/\.m3u8/', $sourceFileURL)) {
        return "application/x-mpegURL";
    }
    return "video/mp4";
}

function generateMetaTags($videoType, $modifiedDate, $createdDate, $title, $description, $pageURL, $pageURLEmbed,  $duration_in_seconds, $sourceFileURL, $imgPath, $imgURL, $extraMetatags = array(), $canonicalURL='')
{
    global $global, $config, $advancedCustom;
    $ogType = determineOgType($videoType);
    $title = str_replace('"', '', $title);
    $description = str_replace('"', '', $description);
    if (empty($customizePluginDescription)) {
        if (AVideoPlugin::isEnabledByName('Customize')) {
            $ec = new ExtraConfig();
            $customizePluginDescription = $ec->getDescription();
        }
    }

    if (empty($description)) {
        if (empty($description)) {
            $description = $title;
        }
        if (!empty($customizePluginDescription)) {
            $metaTags[] = "<!-- OpenGraph description from customizePluginDescription -->";
            $description = $customizePluginDescription;
        } elseif (!empty($metaDescription)) {
            $metaTags[] = "<!-- OpenGraph description from metaDescription -->";
            $description = $metaDescription;
        }
    }
    $description = getSEODescription($description);
    $metaTags = array();
    $metaTags[] = '<!-- OpenGraph -->';
    $metaTags[] = '<meta property="og:logo" content="'.getURL($config->getLogo()).'" />';
    if (preg_match('/\.m3u8/', $sourceFileURL)) {
        $metaTags[] = '<meta property="og:video:type" content="application/x-mpegURL" />';;
    } else if (!empty($videoType)) {
        $metaTags[] = getMetaTagsContentType($videoType);
    }
    if (!empty($modifiedDate)) {
        $metaTags[] = '<meta http-equiv="last-modified" content="' . $modifiedDate . '">';
        $metaTags[] = '<meta name="revised" content="' . $modifiedDate . '" />';
    }
    if (!empty($createdDate)) {
        $metaTags[] = '<meta property="ya:ovs:upload_date"    content="' . $createdDate . '" />';
    }
    $metaTags[] = '<meta property="ya:ovs:adult"          content="no" />';
    if (!empty($duration_in_seconds)) {
        $metaTags[] = '<meta property="video:duration"    content="' . $duration_in_seconds . '" />';
    }

    if (!empty($imgURL)) {
        $metaTags[] = "<link rel='image_src' href='{$imgURL}' />";
        $metaTags[] = "<meta property='og:image' content='{$imgURL}' />";
        $metaTags[] = "<meta property='og:image:secure_url' content='{$imgURL}' />";
        $metaTags[] = "<meta name=\"twitter:image\" content=\"{$imgURL}\"/>";
        $type = 'image/jpeg';
        $imgw = 1024;
        $imgh = 768;
        if (!empty($imgPath)) {
            $imgSize = @getimgsize($imgPath);
            if ($imgSize) {
                $imgw = $imgSize[0];
                $imgh = $imgSize[1];
                $type = $imgSize['mime'];
            }
        }
        $metaTags[] = "<meta property='og:image:width' content='{$imgw}' />";
        $metaTags[] = "<meta property='og:image:height' content='{$imgh}' />";
        $metaTags[] = "<meta property='og:image:type' content='{$type}' />";
    }

    $metaTags[] = "<meta property='og:type' content='{$ogType}' />";
    if ($title) {
        $title = getSEOTitle(html2plainText($title));
        $metaTags[] = "<meta property=\"og:title\" content=\"{$title}\" />";
        $metaTags[] = "<meta name=\"twitter:title\" content=\"{$title}\"/>";
    }
    if ($description) {
        $metaTags[] = "<meta property=\"og:description\" content=\"{$description}\" />";
        $metaTags[] = "<meta name=\"twitter:description\" content=\"{$description}\"/>";
    }
    if ($pageURL) {
        $metaTags[] = "<meta property='og:url' content='{$pageURL}' />";
        $metaTags[] = "<meta name=\"twitter:url\" content=\"{$pageURL}\"/>";
        if(empty($canonicalURL)){
            $canonicalURL = $pageURL;
        }
    }
    if(!empty($canonicalURL)){
        $metaTags[] = "<link rel=\"canonical\" href=\"{$canonicalURL}\" />";
    }
    if (!empty($advancedCustom->fb_app_id)) {
        $metaTags[] = "<meta property='fb:app_id' content='{$advancedCustom->fb_app_id}' />";
    }
    $SecureVideosDirectoryIsEnabled = AVideoPlugin::isEnabledByName("SecureVideosDirectory");
    if (!$SecureVideosDirectoryIsEnabled && !empty($sourceFileURL)) {
        $metaTags[] = "<meta property=\"og:video\"            content=\"{$sourceFileURL}\" />";
        $metaTags[] = "<meta property=\"og:video:secure_url\" content=\"{$sourceFileURL}\" />";
        $metaTags[] = '<meta property="og:video:type"       content="' . getMetaTagsContentVideoType($sourceFileURL) . '" />';
        $metaTags[] = '<meta property="og:video:width"      content="1024" />';
        $metaTags[] = '<meta property="og:video:height"     content="768" />';
    } else {
        if ($SecureVideosDirectoryIsEnabled) {
            $metaTags[] = '<!-- SecureVideosDirectory plugin is enabled we will not share the video source file -->';
        }
        if (empty($sourceFileURL)) {
            $metaTags[] = '<!-- we could not get the source file -->';
        }

        if (empty($pageURL)) {
            $metaTags[] = '<!-- we could not get the source file -->';
        }
        $metaTags[] = "<meta property=\"og:video\"            content=\"{$pageURL}\" />";
        $metaTags[] = "<meta property=\"og:video:secure_url\" content=\"{$pageURL}\" />";
    }
    if (!empty($advancedCustom->twitter_player) && !empty($pageURLEmbed)) {
        $metaTags[] = '<meta name="twitter:card" content="player" />';
        $metaTags[] = "<meta name=\"twitter:player\" content=\"{$pageURLEmbed}\" />";
        if (!$SecureVideosDirectoryIsEnabled && !empty($sourceFileURL)) {
            $metaTags[] = '<meta name="twitter:player:width" content="1024" />';
            $metaTags[] = '<meta name="twitter:player:height" content="768" />';
            $metaTags[] = '<meta name="twitter:player:stream" content="' . $sourceFileURL . '" />';
            $metaTags[] = '<meta name="twitter:player:stream:content_type" content="' . getMetaTagsContentVideoType($sourceFileURL) . '" />';
        } else {
            $metaTags[] = '<meta name="twitter:player:width" content="480" />';
            $metaTags[] = '<meta name="twitter:player:height" content="480" />';
        }
    } else {
        if (!empty($advancedCustom->twitter_summary_large_image)) {
            $metaTags[] = '<meta name="twitter:card" content="summary_large_image" />';
        } else {
            $metaTags[] = '<meta name="twitter:card" content="summary" />';
        }
    }

    if (!empty($advancedCustom->twitter_site)) {
        $metaTags[] = "<meta property=\"twitter:site\" content=\"{$advancedCustom->twitter_site}\" />";
    }

    $metaTags = array_merge($metaTags, $extraMetatags);

    return implode(PHP_EOL, $metaTags);
}

function getOpenGraphTag($tags_id)
{
    global $global, $config, $advancedCustom;
    $tag = new Tags($tags_id);
    echo PHP_EOL . "<!-- OpenGraph tags_id {$tags_id} -->" . PHP_EOL;
    $videoType = '';
    $modifiedDate = '';
    $createdDate = '';
    $title = $tag->getName();
    $description = '';
    $duration_in_seconds = 0;
    $sourceFileURL = '';
    $pageURL = VideoTags::getTagLink($tags_id);
    $pageURLEmbed = '';
    $imgURL = Configuration::getOGImage();
    $imgPath = Configuration::getOGImagePath();
    $extraMetatags = array('<meta property="og:site_name" content="' . $title . '">');
    //var_dump(debug_backtrace());
    return  generateMetaTags($videoType, $modifiedDate, $createdDate, $title, $description, $pageURL, $pageURLEmbed,  $duration_in_seconds, $sourceFileURL, $imgPath, $imgURL, $extraMetatags);
}

function getOpenGraphCategory($categories_id)
{
    global $global, $config, $advancedCustom;
    echo PHP_EOL . "<!-- OpenGraph Category -->" . PHP_EOL;
    $cat = new Category($categories_id);

    $videoType = '';
    $modifiedDate = '';
    $createdDate = '';
    $title = $cat->getName();
    $description = '';
    $duration_in_seconds = 0;
    $sourceFileURL = '';
    $pageURL = $cat->getLink();
    $pageURLEmbed = '';
    $imgURL = Configuration::getOGImage();
    $imgPath = Configuration::getOGImagePath();
    $extraMetatags = array('<meta property="og:site_name" content="' . $title . '">');
    //var_dump(debug_backtrace());
    return  generateMetaTags($videoType, $modifiedDate, $createdDate, $title, $description, $pageURL, $pageURLEmbed,  $duration_in_seconds, $sourceFileURL, $imgPath, $imgURL, $extraMetatags);
}

function getOpenGraphChannel($users_id)
{
    global $global, $config, $advancedCustom;
    echo PHP_EOL . "<!-- OpenGraph Channel -->" . PHP_EOL;
    $videoType = '';
    $modifiedDate = '';
    $createdDate = '';
    $title = User::getNameIdentificationById($users_id);
    $description = '';
    $duration_in_seconds = 0;
    $sourceFileURL = '';
    $pageURL = User::getChannelLink($users_id);
    $pageURLEmbed = '';
    $imgURL = User::getOGImage($users_id);
    $imgPath = User::getOGImagePath($users_id);
    $extraMetatags = array('<meta property="og:type" content="profile" />', '<meta property="profile:username" content="' . $title . '">');
    //var_dump(debug_backtrace());
    return  generateMetaTags($videoType, $modifiedDate, $createdDate, $title, $description, $pageURL, $pageURLEmbed,  $duration_in_seconds, $sourceFileURL, $imgPath, $imgURL, $extraMetatags);
}

function getOpenGraphSite()
{
    global $global, $config, $advancedCustom;
    echo PHP_EOL . "<!-- OpenGraph Site -->" . PHP_EOL;
    $videoType = '';
    $modifiedDate = '';
    $createdDate = '';
    $title = $config->getWebSiteTitle();
    $description = '';
    $duration_in_seconds = 0;
    $sourceFileURL = '';
    $pageURL = $global['webSiteRootURL'];
    $pageURLEmbed = '';
    $imgURL = Configuration::getOGImage();
    $imgPath = Configuration::getOGImagePath();
    $extraMetatags = array('<meta property="og:site_name" content="' . $title . '">');
    //var_dump(debug_backtrace());
    return  generateMetaTags($videoType, $modifiedDate, $createdDate, $title, $description, $pageURL, $pageURLEmbed,  $duration_in_seconds, $sourceFileURL, $imgPath, $imgURL, $extraMetatags);
}

function getOpenGraphVideo($videos_id)
{
    global $global, $config, $advancedCustom;
    $video = Video::getVideoLight($videos_id);
    $canonicalURL = '';
    if (!empty($_REQUEST['playlists_id'])) {
        echo PHP_EOL . "<!-- OpenGraph Playlist -->" . PHP_EOL;
        $pageURL = PlayLists::getLink($_REQUEST['playlists_id'], false, @$_REQUEST['playlist_index']);
        $pageURLEmbed = PlayLists::getLink($_REQUEST['playlists_id'], true, @$_REQUEST['playlist_index']);
    } else if (!empty($_REQUEST['tags_id']) && isset($_REQUEST['playlist_index'])) {
        echo PHP_EOL . "<!-- OpenGraph Tag Playlist -->" . PHP_EOL;
        $pageURL = PlayLists::getTagLink($_REQUEST['tags_id'], false, @$_REQUEST['playlist_index']);
        $pageURLEmbed = PlayLists::getTagLink($_REQUEST['tags_id'], true, @$_REQUEST['playlist_index']);
    } else {
        echo PHP_EOL . "<!-- OpenGraph Video -->" . PHP_EOL;
        $pageURL = Video::getLinkToVideo($videos_id, '', false, Video::$urlTypeShort, [], true);
        $pageURLEmbed = Video::getLinkToVideo($videos_id, '', true, Video::$urlTypeShort, [], true);
        $canonicalURL = Video::getCanonicalLink($videos_id);
    }

    $sourceFileURL = '';
    $source = Video::getHigestResolution($video['filename']);
    if (empty($source['url'])) {
        if (CustomizeUser::canDownloadVideos()) {
            echo "<!-- you cannot download videos we will not share the video source file -->";
        }
        if (empty($source['url'])) {
            echo "<!-- we could not get the MP4 source file -->";
        }
    } else {
        $source['url'] = str_replace(".m3u8", ".m3u8.mp4", $source['url']);
        $sourceFileURL  = $source['url'];
    }

    $videoType = $video['type'];
    $modifiedDate = $video['modified'];
    $createdDate = $video['created'];
    $title = $video['title'];
    $description = $video['description'];
    $duration_in_seconds = $video['duration_in_seconds'];
    $imgURL = '';
    $imgPath = '';

    $images = Video::getImageFromFilename($video['filename']);
    if (!isImageNotFound($images->posterPortraitThumbs)) {
        $imgURL = $images->posterPortraitThumbs;
        $imgPath = $images->posterPortraitPath;
    } else if (!isImageNotFound($images->posterPortrait)) {
        $imgURL = $images->posterPortrait;
        $imgPath = $images->posterPortraitPath;
    }
    if (!isImageNotFound($images->posterLandscapeThumbs)) {
        $imgURL = $images->posterLandscapeThumbs;
        $imgPath = $images->posterLandscapePath;
    } else if (!isImageNotFound($images->posterLandscape)) {
        $imgURL = $images->posterLandscape;
        $imgPath = $images->posterLandscapePath;
    } else {
        $imgURL = $images->posterPortrait;
        $imgPath = $images->posterPortraitPath;
    }
    //var_dump(debug_backtrace());
    return  generateMetaTags($videoType, $modifiedDate, $createdDate, $title, $description, $pageURL, $pageURLEmbed,  $duration_in_seconds, $sourceFileURL, $imgPath, $imgURL, array(), $canonicalURL);
}

function getOpenGraphLiveLink($liveLink_id)
{
    global $global, $config, $advancedCustom;
    $liveLink = new LiveLinksTable($liveLink_id);
    echo PHP_EOL . "<!-- OpenGraph LiveLink -->" . PHP_EOL;
    $videoType = '';
    $modifiedDate = '';
    $createdDate = '';
    $title = $liveLink->getTitle();
    $description = '';
    $duration_in_seconds = 0;
    $sourceFileURL = $liveLink->getLink();
    $pageURL = LiveLinks::getLinkToLiveFromId($liveLink_id);
    $pageURLEmbed = LiveLinks::getLinkToLiveFromId($liveLink_id, true);    
    $imgURL = LiveLinks::getImageDafaultOrDynamic($liveLink_id);
    $imgPath = '';
    $extraMetatags = array();
    //var_dump(debug_backtrace());
    return  generateMetaTags($videoType, $modifiedDate, $createdDate, $title, $description, $pageURL, $pageURLEmbed,  $duration_in_seconds, $sourceFileURL, $imgPath, $imgURL, $extraMetatags);
}

function getOpenGraphLiveSchedule($live_schedule_id)
{
    global $global, $config, $advancedCustom;
    $liveS = new Live_schedule($live_schedule_id);
    echo PHP_EOL . "<!-- OpenGraph Schedule -->" . PHP_EOL;
    $videoType = '';
    $modifiedDate = '';
    $createdDate = '';
    $title = $liveS->getTitle();
    $description = '';
    $duration_in_seconds = 0;
    $poster = Live_schedule::getPosterURL($live_schedule_id, 0);
    $liveStreamObject = new LiveStreamObject($liveS->getKey(), $liveS->getLive_servers_id(), 0, 0, @$_REQUEST['live_schedule']);
    $sourceFileURL = $liveStreamObject->getM3U8(true);
    $pageURL = $liveStreamObject->getURL();
    $pageURLEmbed = $liveStreamObject->getURLEmbed();
    $imgURL = getURL($poster);
    $imgPath = $global['systemRootPath'] . $poster;
    $extraMetatags = array();
    //var_dump(debug_backtrace());
    return  generateMetaTags($videoType, $modifiedDate, $createdDate, $title, $description, $pageURL, $pageURLEmbed,  $duration_in_seconds, $sourceFileURL, $imgPath, $imgURL, $extraMetatags);
}

function getOpenGraphLive()
{
    global $global, $config, $advancedCustom;
    $isLive = isLive();
    $liveT = LiveTransmition::getFromKey($isLive['cleanKey']);
    $users_id = $liveT['users_id'];
    $poster = Live::getRegularPosterImage($users_id, $isLive['live_servers_id'], $isLive['live_schedule'], 0);
    $liveStreamObject = new LiveStreamObject($isLive['cleanKey'], $isLive['live_servers_id'], $isLive['live_index'], 0);
    echo PHP_EOL . "<!-- OpenGraph Live users_id={$users_id} ".json_encode($isLive)." -->" . PHP_EOL;
    $videoType = '';
    $modifiedDate = '';
    $createdDate = '';
    $title = $liveT['title'];
    if(empty($title)){
        Live::getTitleFromUsers_Id($users_id);
    }

    if(empty($title)){
        echo PHP_EOL . "<!-- OpenGraph title is empty -->" . PHP_EOL;
    }

    $description = $liveT['description'];
    $duration_in_seconds = 0;
    $sourceFileURL = $liveStreamObject->getM3U8(true);
    $pageURL = $liveStreamObject->getURL();
    $pageURLEmbed = $liveStreamObject->getURLEmbed();
    $imgURL = getURL($poster);
    $imgPath = $global['systemRootPath'] . $poster;
    $extraMetatags = array();
    //var_dump(debug_backtrace());
    return  generateMetaTags($videoType, $modifiedDate, $createdDate, $title, $description, $pageURL, $pageURLEmbed,  $duration_in_seconds, $sourceFileURL, $imgPath, $imgURL, $extraMetatags);
}
