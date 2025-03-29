<?php
//header('Content-Type: text/xml; charset=UTF8');
//header("Content-Type: application/rss+xml; charset=UTF8");
header("Content-Type: application/rss+xml;");
$cacheFeedName = "feedCacheMRSS" . json_encode($_REQUEST);
$lifetime = 43200;
$feed = ObjectYPT::getCache($cacheFeedName, $lifetime);
$link = "{$link}/mrss";
$recreate = recreateCache();
if (empty($feed) || $recreate) {
    _ob_start();
    echo'<?xml version="1.0" encoding="UTF-8"?>'; ?>
    <rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/"
         xmlns:georss="http://www.georss.org/georss"
         xmlns:gml="http://www.opengis.net/gml"
         xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
         xmlns:atom="http://www.w3.org/2005/Atom" >
        <channel>
            <atom:link href="<?php echo $global['webSiteRootURL'] . ltrim($_SERVER["REQUEST_URI"], "/"); ?>" rel="self" type="application/rss+xml" />

            <title><?php echo feedText($title); ?></title>
            <description><?php echo feedText($description); ?></description>
            <link><?php echo $global['webSiteRootURL']; ?></link>

            <language>en-us</language>
            <itunes:image href="<?php echo $logo; ?>" />
            <itunes:explicit>no</itunes:explicit>

            <itunes:category text="Technology" />

            <image>
                <title><?php echo feedText($title); ?></title>
                <url><?php echo $logo; ?></url>
                <link><?php echo $link; ?></link>
                <width>144</width>
                <height>40</height>
                <description>AVideo version rss</description>
            </image>
            <?php
            foreach ($rows as $row) {
                $video = Video::getVideoFromFileName($row['filename']);
                $files = getVideosURL($row['filename'], $recreate);
                $enclosure = '';
                $videoSource = Video::getSourceFileURL($row['filename'], true);
                if (empty($videoSource)) {
                    continue;
                }
                foreach ($files as $value) {
                    if ($value["type"] === "video" && file_exists($value['path'])) {
                        $path_parts = pathinfo($value['path']);
                        if($path_parts['extension'] === 'm3u8'){
                            $resp = VideoHLS::convertM3U8ToMP4($row['id']);
                            if(!empty($resp)){
                                $value['url'] = $resp['url'];
                                $value['path'] = $resp['path'];
                                $value['mime'] = "video/mp4";
                            }
                        }else{
                            $value['mime'] = "video/{$path_parts['extension']}";
                        }

                        $value['size'] = filesize($value['path']);
                        // replace to validate
                        $value['url'] = str_replace("http://", "https://", $value['url']);
                        $enclosure = '<enclosure url="' . str_replace('&', '&amp;', $value['url']) . '" length="' . $value['size'] . '" type="' . $value['mime'] . '" />';
                        break;
                    }
                } ?>
                <item>
                    <title><?php echo feedText($row['title']); ?></title>
                    <description><?php echo feedText($row['title']); ?></description>
                    <link><![CDATA[<?php echo Video::getLink($row['id'], $row['clean_title']); ?>]]></link>
                    <?php echo $enclosure; ?>
                    <pubDate><?php echo date('r', strtotime($row['created'])); ?></pubDate>
                    <guid isPermaLink="true"><?php echo Video::getLinkToVideo($row['id'], $row['clean_title'], false, "permalink"); ?></guid>
                    <media:category><?php echo htmlspecialchars($row["category"]); ?></media:category>
                    <media:content url="<?php echo $videoSource; ?>" fileSize="<?php echo $video["filesize"]; ?>" bitrate="128"
                                   type="<?php echo mime_content_type_per_filename($videoSource); ?>" expression="full"
                                   duration="<?php echo durationToSeconds($row['duration']); ?>">
                        <media:title type="plain"><?php echo htmlspecialchars($row['title']); ?></media:title>
                        <media:description type="html"><![CDATA[<?php echo Video::htmlDescription($row['title']); ?>]]></media:description>
                        <media:thumbnail url="<?php echo Video::getPoster($row['id']); ?>" />
                    </media:content>
                </item>
                <?php
            } ?>
        </channel>
    </rss>
    <?php
    $feed = ob_get_contents();
    _ob_end_clean();
    //var_dump($cacheFeedName, $feed);exit;
    ObjectYPT::setCache($cacheFeedName, $feed);
    $feedLast = '<!-- NO cache -->';
} else {
    $feedLast = '<!-- cache -->';
}
if (!is_string($feed)) {
    $feed = json_encode($feed);
}
echo $feed.PHP_EOL;
echo $feedLast;
