<?php
//header('Content-Type: text/xml; charset=UTF8');
//header("Content-Type: application/rss+xml; charset=UTF8");
header("Content-Type: application/rss+xml;");
$cacheFeedName = "feedCacheMRSSxml2" . json_encode($_REQUEST);
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
            <atom:link href="<?php echo htmlspecialchars($global['webSiteRootURL'] . ltrim($_SERVER["REQUEST_URI"], "/"), ENT_QUOTES, 'UTF-8'); ?>" rel="self" type="application/rss+xml" />

            <title><?php echo htmlspecialchars(feedText($title), ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8', false); ?></title>
            <description><?php echo htmlspecialchars(feedText($description), ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8', false); ?></description>
            <link><?php echo htmlspecialchars($global['webSiteRootURL'], ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8'); ?></link>

            <language>en-us</language>
            <itunes:image href="<?php echo htmlspecialchars($logo, ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8'); ?>" />
            <itunes:explicit>no</itunes:explicit>

            <itunes:category text="Technology" />

            <image>
                <title><?php echo htmlspecialchars(feedText($title), ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8', false); ?></title>
                <url><?php echo htmlspecialchars($logo, ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8'); ?></url>
                <link><?php echo htmlspecialchars($link, ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8'); ?></link>
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
                            // Check if MP4 already exists before trying to convert
                            $mp4Path = str_replace('/index.m3u8', '/index.mp4', $value['path']);
                            if(file_exists($mp4Path) && filesize($mp4Path) > 100){
                                // MP4 already exists, use it directly
                                $value['url'] = str_replace('/index.m3u8', '/index.mp4', $value['url']);
                                $value['path'] = $mp4Path;
                                $value['mime'] = "video/mp4";
                            }else{
                                // MP4 doesn't exist or is too small, try to convert
                                $resp = VideoHLS::convertM3U8ToMP4($row['id']);
                                if(!empty($resp)){
                                    $value['url'] = $resp['url'];
                                    $value['path'] = $resp['path'];
                                    $value['mime'] = "video/mp4";
                                }
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
                    <title><?php echo htmlspecialchars(feedText($row['title']), ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8', false); ?></title>
                    <description><?php echo htmlspecialchars(feedText($row['title']), ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8', false); ?></description>
                    <link><![CDATA[<?php echo Video::getLink($row['id'], $row['clean_title']); ?>]]></link>
                    <?php echo $enclosure; ?>
                    <pubDate><?php echo date('r', strtotime($row['created'])); ?></pubDate>
                    <guid isPermaLink="true"><?php echo htmlspecialchars(Video::getLinkToVideo($row['id'], $row['clean_title'], false, "permalink"), ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8'); ?></guid>
                    <media:category><?php echo htmlspecialchars($row["category"]); ?></media:category>
                    <media:content url="<?php echo htmlspecialchars($videoSource, ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8'); ?>" fileSize="<?php echo $video["filesize"]; ?>" bitrate="128"
                                   type="<?php echo mime_content_type_per_filename($videoSource); ?>" expression="full"
                                   duration="<?php echo durationToSeconds($row['duration']); ?>">
                        <media:title type="plain"><?php echo htmlspecialchars($row['title']); ?></media:title>
                        <media:description type="html"><![CDATA[<?php echo Video::htmlDescription($row['title']); ?>]]></media:description>
                        <media:thumbnail url="<?php echo htmlspecialchars(Video::getPoster($row['id']), ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8'); ?>" />
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
