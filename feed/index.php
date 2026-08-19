<?php

// buffer everything from the very first line so a stray warning/notice/BOM emitted by the
// legacy bootstrap below (configuration.php, video.php) never leaks before header() runs or
// before the XML declaration - either of which breaks the feed's Content-Type/XML rendering
ob_start();

$global['ignoreUserMustBeLoggedIn'] = 1;
require_once '../videos/configuration.php';
require_once '../objects/video.php';
$global['ignoreUserMustBeLoggedIn'] = 1;
$_POST['sort']["created"] = "DESC";
$_POST['current'] = 1;
$_REQUEST['rowCount'] = getRowCount();

set_time_limit(300); // 5 minutes
ini_set('max_execution_time', 300);

$advancedCustom = AVideoPlugin::getDataObject('CustomizeAdvanced');

if(!empty($advancedCustom->disableFeeds)){
    forbiddenPage('Feeds are disabled');
}

if(empty($config)){
    require_once $global['systemRootPath'] . 'objects/configuration.php';
    $config = new AVideoConf();
}

$showOnlyLoggedUserVideos = false;
$title = $config->getWebSiteTitle();
$link = $global['webSiteRootURL'];
$author = $config->getContactEmail();
$logo = getURL("videos/userPhoto/logo.png");
$description = '';

$extraPluginFile = $global['systemRootPath'] . 'plugin/Customize/Objects/ExtraConfig.php';
if (file_exists($extraPluginFile) && AVideoPlugin::isEnabledByName("Customize")) {
    require_once $extraPluginFile;
    $ec = new ExtraConfig();
    $description = $ec->getDescription();
}

if (!empty($_GET['channelName'])) {
    $user = User::getChannelOwner($_GET['channelName']);
    $showOnlyLoggedUserVideos = $user['id'];
    $title = User::getNameIdentificationById($user['id']);
    $about = User::getDescriptionById($user['id'], true);
    // do not disclose the channel owner's personal email to unauthenticated callers; keep the site contact email set above
    if (!isHTMLEmpty($about)) {
        $description = $about;
    }
    $link = User::getChannelLink($user['id']);
    $logo = User::getPhoto($user['id']);
}

$cacheName = "feedCache" . md5(json_encode($_REQUEST));
$rows = ObjectYPT::getCache($cacheName, 0);
if (empty($rows)) {
    // send $_REQUEST['catName'] to be able to filter by category
    $sort = @$_POST['sort'];
    if (empty($_REQUEST['program_id'])) {
        if (empty($_POST['sort'])) {
            $_POST['sort'] = array('created' => 'DESC');
        }
        $rows = Video::getAllVideos("viewable", $showOnlyLoggedUserVideos);
    } else {
        unset($_POST['sort']);
        $playlists_id = intval($_REQUEST['program_id']);
        $videosArrayId = PlayList::getVideosIdFromPlaylist($playlists_id);
        $rows = Video::getAllVideos("viewable", false, true, $videosArrayId, false, true);
        $rows = PlayList::sortVideos($rows, $videosArrayId);
    }
    $_POST['sort'] = $sort;
    ObjectYPT::setCache($cacheName, $rows);
} else {
    $rows = object_to_array($rows);
}


if (!empty($_REQUEST['program_id'])) {
    $playlists_id = intval($_REQUEST['program_id']);
    if (!PlayList::canSee($playlists_id, User::getId())) {
        forbiddenPage('Permission denied');
    }
    $pl = new PlayList($playlists_id);
    $videosArrayId = PlayList::getVideosIdFromPlaylist($playlists_id);
    $title = PlayLists::getNameOrSerieTitle($playlists_id);
    $link = PlayLists::getLink($playlists_id);
    // do not disclose the playlist owner's personal email to unauthenticated callers; keep the site contact email set above
    $description = PlayLists::getDescriptionIfIsSerie($playlists_id);
    //var_dump($videosArrayId);foreach ($rows as $value) {var_dump($value['id']);}exit;
}

if (empty($description)) {
    $description = $title;
}
//var_dump($title, $cacheName, $_REQUEST);exit;
// discard anything buffered so far (warnings/whitespace from the bootstrap above) so the
// response body starts exactly with the feed handler's own header()/XML declaration
ob_clean();
if (!empty($_REQUEST['roku'])) {
    include $global['systemRootPath'] . 'feed/roku.json.php';
} elseif (!empty($_REQUEST['rokuSearch'])) {
    include $global['systemRootPath'] . 'feed/roku.search.json.php';
} elseif (!empty($_REQUEST['vizio'])) {
    include $global['systemRootPath'] . 'feed/vizio.json.php';
} elseif (empty($_REQUEST['mrss'])) {
    include $global['systemRootPath'] . 'feed/rss.php';
} else {
    include $global['systemRootPath'] . 'feed/mrss.php';
}
ob_end_flush();

// Plain-text fields (title, itunes:author, etc). Always used inside <![CDATA[ ]]>,
// so no entity-escaping is needed here - CDATA already protects &, < and >.
// html_entity_decode() runs first so text that is already HTML-encoded in the DB
// (e.g. "&lt;p&gt;") is not re-escaped into "&amp;lt;p&amp;gt;" further down.
function feedText($text) {
    if ($text === null || $text === '') {
        return '';
    }
    $decoded = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $plain = strip_tags(br2nl($decoded));
    $plain = str_replace('&nbsp;', ' ', $plain);
    // collapse repeated spaces/tabs/newlines (e.g. "Bible Study  on...") into a single space
    $plain = preg_replace('/\s+/u', ' ', $plain);
    // guard against a literal "]]>" ever closing the CDATA section early
    return trim(str_replace(']]>', ']]&gt;', $plain));
}

// Rich-text fields (episode/channel <description> and <itunes:summary>). Keeps the
// underlying HTML clean and literal (e.g. <p class="...">) instead of stripping or
// re-encoding it, but removes inline style="..." attributes and any script/event
// handlers before it gets echoed inside a <![CDATA[ ]]> block.
function feedHtmlDescription($text) {
    if ($text === null || $text === '') {
        return '';
    }
    // some descriptions are stored already HTML-entity-encoded (e.g. "&lt;p&gt;text&lt;/p&gt;");
    // decode repeatedly until stable so nothing gets double-encoded later on
    $decoded = (string) $text;
    for ($i = 0; $i < 3; $i++) {
        $next = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($next === $decoded) {
            break;
        }
        $decoded = $next;
    }

    // drop <script>/<style> blocks entirely
    $decoded = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $decoded);

    // strip heavy/inline CSS (style="...") that breaks rendering in podcast apps
    $decoded = preg_replace('/\s+style\s*=\s*("[^"]*"|\'[^\']*\')/i', '', $decoded);

    // strip inline event handlers (onclick="...", onerror="...", etc.)
    $decoded = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\')/i', '', $decoded);

    // guard against a literal "]]>" ever closing the CDATA section early
    return trim(str_replace(']]>', ']]&gt;', $decoded));
}
