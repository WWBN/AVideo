<?php 
header("Content-Type: application/rss+xml; charset=UTF8");


require_once '../videos/configuration.php';
require_once '../objects/video.php';

$_POST['sort']["created"] = "DESC";
$_POST['current'] = 1;
$_POST['rowCount'] = 50;

// send $_GET['catName'] to be able to filter by category
$rows = Video::getAllVideos("viewable");

echo'<?xml version="1.0" encoding="UTF-8"?>'
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:wfw="http://wellformedweb.org/CommentAPI/"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
     xmlns:slash="http://purl.org/rss/1.0/modules/slash/">
    <channel>
        <atom:link href="<?php echo str_replace("//", "/", $global['webSiteRootURL'].$_SERVER["REQUEST_URI"]); ?>" rel="self" type="application/rss+xml" />
        <title>RSS <?php echo $config->getWebSiteTitle(); ?></title>
        <description>Rss Feed</description>
        <link><?php echo $global['webSiteRootURL']; ?></link>
        <sy:updatePeriod>hourly</sy:updatePeriod>
        <sy:updateFrequency>1</sy:updateFrequency>

        <image>
        <title>RSS <?php echo $config->getWebSiteTitle(); ?></title>
        <url><?php echo $global['webSiteRootURL']; ?>videos/userPhoto/logo.png</url>
        <link><?php echo $global['webSiteRootURL']; ?></link>
        <width>144</width>
        <height>40</height>
        <description>YouPHPTube versione rss</description>
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
                <description><![CDATA[<?php echo $row['description']; ?>]]></description>
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
