<?php 
//header("Content-Type: application/rss+xml; charset=UTF8");
header('Content-Type: text/xml; charset=UTF8');


require_once '../videos/configuration.php';
require_once '../objects/video.php';

$_POST['sort']["created"] = "DESC";
$_POST['current'] = 1;
$_POST['rowCount'] = 50;

$showOnlyLoggedUserVideos = false;
$title = "RSS ".$config->getWebSiteTitle();
$link = $global['webSiteRootURL'];
$logo = "{$global['webSiteRootURL']}videos/userPhoto/logo.png";

if(!empty($_GET['channelName'])){
    $user = User::getChannelOwner($_GET['channelName']);
    $showOnlyLoggedUserVideos = $user['id'];
    $title = "RSS ".User::getNameIdentificationById($user['id']);
    $link = User::getChannelLink($user['id']);
    $logo = User::getPhoto($user['id']);
}

// send $_GET['catName'] to be able to filter by category
$rows = Video::getAllVideos("viewable", $showOnlyLoggedUserVideos);

echo'<?xml version="1.0" encoding="UTF-8"?>'
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:wfw="http://wellformedweb.org/CommentAPI/"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
     xmlns:slash="http://purl.org/rss/1.0/modules/slash/">
    <channel>
        <atom:link href="<?php echo $global['webSiteRootURL'].ltrim($_SERVER["REQUEST_URI"],"/"); ?>" rel="self" type="application/rss+xml" />
        <title><?php echo $title; ?></title>
        <description>Rss Feed</description>
        <link><?php echo $link; ?></link>
        <sy:updatePeriod>hourly</sy:updatePeriod>
        <sy:updateFrequency>1</sy:updateFrequency>

        <image>
        <title><?php echo $title; ?></title>
        <url><?php echo $logo; ?></url>
        <link><?php echo $link; ?></link>
        <width>144</width>
        <height>40</height>
        <description>YouPHPTube version rss</description>
        </image>

        <?php
        foreach ($rows as $row) {
            $files = getVideosURL($row['filename']);
            $enclosure = "";
            foreach ($files as $value) {
                if ($value["type"] === "video" && file_exists($value['path'])) {
                    $path_parts = pathinfo($value['path']);
                    $value['mime'] = "video/{$path_parts['extension']}";
                    $value['size'] = filesize($value['path']);
                    // replace to validate
                    $value['url'] = str_replace("https://", "http://", $value['url']);
                    $enclosure = '<enclosure url="' . $value['url'] . '" length="' . $value['size'] . '" type="' . $value['mime'] . '" />';
                    break;
                }
            }
            ?>
            <item>
                <title><?php echo htmlspecialchars($row['title']); ?></title>
                <description><![CDATA[<?php echo strip_tags($row['description']); ?>]]></description>
                <link> <?php echo Video::getLink($row['id'], $row['clean_title']); ?></link>
                <?php echo $enclosure; ?>
                <pubDate><?php echo date('r', strtotime($row['created'])); ?></pubDate>
                <guid><?php echo Video::getLinkToVideo($row['id'], $row['clean_title'], false, "permalink"); ?></guid>
            </item>
            <?php
        }
        ?>
    </channel>
</rss>
