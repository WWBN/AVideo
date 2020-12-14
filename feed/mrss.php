<?php
echo'<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/"
  xmlns:georss="http://www.georss.org/georss"
  xmlns:gml="http://www.opengis.net/gml">
    <channel>
        <title><?php echo $title; ?></title>
        <description><?php echo $description; ?></description>
        <link><?php echo $link; ?></link>

        <image>
        <title><?php echo $title; ?></title>
        <url><?php echo $logo; ?></url>
        <link><?php echo $link; ?></link>
        <width>144</width>
        <height>40</height>
        <description>AVideo version rss</description>
        </image>

        <?php
        foreach ($rows as $row) {
            $video = Video::getVideoFromFileName($row['filename']);
            $files = getVideosURL($row['filename']);
            $enclosure = "";
            $videoSource = Video::getHigestResolution($row['filename']);
            if(empty($videoSource["url"])){
                continue;
            }
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
                <description><![CDATA[<?php echo strip_tags(br2nl($row['description'])); ?>]]></description>
                <link> <?php echo Video::getLink($row['id'], $row['clean_title']); ?></link>
                <?php echo $enclosure; ?>
                <pubDate><?php echo date('r', strtotime($row['created'])); ?></pubDate>
                <guid isPermaLink="true"><?php echo Video::getLinkToVideo($row['id'], $row['clean_title'], false, "permalink"); ?></guid>
                <media:category><?php echo $row["category"]; ?></media:category>
                <media:content url="<?php echo $videoSource["url"]; ?>" fileSize="<?php echo $video["filesize"]; ?>" bitrate="128" 
                               type="<?php echo mime_content_type_per_filename($videoSource["path"]); ?>" expression="full"
                               duration="<?php echo durationToSeconds($row['duration']); ?>">
                    <media:title type="plain"><?php echo htmlspecialchars($row['title']); ?></media:title>
                    <media:description type="html"><![CDATA[<?php echo Video::htmlDescription($row['description']); ?>]]></media:description>
                    <media:thumbnail url="<?php echo Video::getPoster($row['id']); ?>" />
                </media:content>
                <media:embed url="<?php echo Video::getLinkToVideo($row['id'], $row['clean_title'], true); ?>"/>
                <media:status state="active" />
            </item>
            <?php
        }
        ?>
    </channel>
</rss>
