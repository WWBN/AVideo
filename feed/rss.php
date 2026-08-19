<?php

/**
 * Renders the channel RSS/Podcast feed. Always included from feed/index.php, which defines
 * the variables below before requiring this file - never accessed directly by a browser.
 * @var array $global
 * @var string $title channel/host display name
 * @var string $link channel link
 * @var string $description channel description
 * @var string $author site contact email (from AVideoConf::getContactEmail())
 * @var string $logo channel logo URL
 * @var array $rows video rows to render as <item>
 * @var stdClass $advancedCustom CustomizeAdvanced plugin settings object
 */
header('Content-Type: application/rss+xml; charset=UTF-8');
$cacheFeedName = "feedCacheRSS" . json_encode($_REQUEST);
$lifetime = 43200;
$feed = ObjectYPT::getCache($cacheFeedName, $lifetime);
if (empty($feed)) {
    _ob_start();
    // itunes:author must be a human-readable name (the podcaster/host), never an email
    // address; $title is already the channel/host display name (or the site title as a
    // fallback), $author (site contact email) stays reserved for <itunes:owner><itunes:email>
    $itunesAuthor = feedText(!empty($title) ? $title : $author);
    // Apple Podcasts/Spotify reject the feed if this isn't a real address (e.g. the
    // "undefined@youremail.com" placeholder install.php writes when no contact email was
    // set); skip the whole <itunes:owner> block instead of shipping an invalid one
    $itunesOwnerEmail = filter_var($author, FILTER_VALIDATE_EMAIL) ? $author : false;
    if (empty($itunesOwnerEmail)) {
        _error_log("feed/rss.php: invalid or missing site contact email, skipping <itunes:owner> (set a valid Contact Email in Admin > General Settings)");
    }
    // configurable per install (CustomizeAdvanced plugin), since this same codebase powers sites with very different content
    $itunesCategory = !empty($advancedCustom->rssItunesCategory->value) ? $advancedCustom->rssItunesCategory->value : 'Society & Culture';
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    // lets browsers render the feed as a readable page instead of raw XML
    echo '<?xml-stylesheet type="text/xsl" href="' . $global['webSiteRootURL'] . 'feed/rss-style.xsl"?>'
?>
    <rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"
        xmlns:wfw="http://wellformedweb.org/CommentAPI/"
        xmlns:dc="http://purl.org/dc/elements/1.1/"
        xmlns:atom="http://www.w3.org/2005/Atom"
        xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
        xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
        xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">
        <channel>
            <atom:link href="<?php echo $global['webSiteRootURL'] . ltrim($_SERVER["REQUEST_URI"], "/"); ?>" rel="self" type="application/rss+xml" />
            <title>
                <![CDATA[ <?php echo feedText($title); ?> ]]>
            </title>
            <description>
                <![CDATA[ <?php echo feedHtmlDescription($description); ?> ]]>
            </description>
            <link><?php echo $link; ?></link>
            <sy:updatePeriod>hourly</sy:updatePeriod>
            <sy:updateFrequency>1</sy:updateFrequency>
            <language>en</language>
            <itunes:category text="<?php echo htmlspecialchars($itunesCategory, ENT_QUOTES, 'UTF-8'); ?>" />
            <itunes:explicit>false</itunes:explicit>
            <!-- required by Apple Podcasts/Spotify for the channel-level cover art -->
            <itunes:image href="<?php echo $logo; ?>" />
            <itunes:author>
                <![CDATA[ <?php echo $itunesAuthor; ?> ]]>
            </itunes:author>
            <itunes:summary>
                <![CDATA[ <?php echo feedHtmlDescription($description); ?> ]]>
            </itunes:summary>
            <?php if (!empty($itunesOwnerEmail)): ?>
            <itunes:owner>
                <itunes:name>
                    <![CDATA[ <?php echo feedText($title); ?> ]]>
                </itunes:name>
                <itunes:email>
                    <![CDATA[ <?php echo feedText($itunesOwnerEmail); ?> ]]>
                </itunes:email>
            </itunes:owner>
            <?php endif; ?>

            <image>
                <title>
                    <![CDATA[ <?php echo feedText($title); ?> ]]>
                </title>
                <url><?php echo $logo; ?></url>
                <link><?php echo $link; ?></link>
                <width>144</width>
                <height>40</height>
                <description>RSS</description>
            </image>

            <?php
            foreach ($rows as $row) {
                $files = getVideosURL($row['filename']);
                $selectedEnclosure = '';

                // Initialize available enclosure slots by type
                $enclosureOptions = ['mp3' => null, 'mp4' => null, 'other' => null];

                foreach ($files as $value) {
                    if (
                        ($value["type"] === Video::$videoTypeVideo || $value["type"] === Video::$videoTypeAudio)
                        && !empty($value['url'])
                    ) {
                        $path_parts = pathinfo($value['path']);
                        $ext = strtolower($path_parts['extension']);

                        // Determine the correct MIME type based on file extension
                        switch ($ext) {
                            case 'mp3':
                                $value['mime'] = 'audio/mpeg';
                                break;
                            case 'mp4':
                                $value['mime'] = 'video/mp4';
                                break;
                            case 'm3u8':
                                $value['mime'] = 'application/x-mpegURL';
                                break;
                            default:
                                $value['mime'] = "video/{$ext}";
                        }

                        // Get file size cheaply: a local stat() when the file is on disk, otherwise
                        // the already-loaded `videos.filesize` column (no extra query/HTTP call, so
                        // this stays fast even for hundreds of remotely-stored/CDN episodes).
                        // The ">1000" guard skips length=10, a dummy local placeholder written when
                        // the real file lives only on remote storage; we don't fetch the remote size
                        // here (too slow for hundreds of episodes), so we just omit the length instead.
                        $value['size'] = file_exists($value['path']) ? @filesize($value['path']) : false;
                        if ($value['size'] === false || $value['size'] < 1) {
                            $dbFilesize = intval($row['filesize'] ?? 0);
                            $value['size'] = $dbFilesize > 1000 ? $dbFilesize : 0;
                        }
                        $value['url'] = str_replace("http://", "https://", $value['url']);

                        // Prepare the enclosure tag
                        $enclosureTag = '<enclosure url="' . $value['url'] . '" length="' . $value['size'] . '" type="' . $value['mime'] . '" />';

                        // Store the enclosure based on priority
                        if ($ext === 'mp3' && !$enclosureOptions['mp3']) {
                            $enclosureOptions['mp3'] = $enclosureTag;
                        } elseif ($ext === 'mp4' && !$enclosureOptions['mp4']) {
                            $enclosureOptions['mp4'] = $enclosureTag;
                        } elseif (!$enclosureOptions['other']) {
                            $enclosureOptions['other'] = $enclosureTag;
                        }
                    }
                }

                // Choose the enclosure according to priority: mp3 > mp4 > other
                if ($enclosureOptions['mp3']) {
                    $selectedEnclosure = $enclosureOptions['mp3'];
                } elseif ($enclosureOptions['mp4']) {
                    $selectedEnclosure = $enclosureOptions['mp4'];
                } elseif ($enclosureOptions['other']) {
                    $selectedEnclosure = $enclosureOptions['other'];
                }
            ?>

                <item>
                    <title>
                        <![CDATA[ <?php echo feedText($row['title']); ?> ]]>
                    </title>
                    <description>
                        <![CDATA[ <?php echo feedHtmlDescription($row['description']); ?> ]]>
                    </description>
                    <link><?php echo Video::getLink($row['id'], $row['clean_title']); ?></link>
                    <?php echo $selectedEnclosure; ?>
                    <pubDate><?php echo date('r', strtotime($row['created'])); ?></pubDate>
                    <guid><?php echo Video::getLinkToVideo($row['id'], $row['clean_title'], false, "permalink"); ?></guid>
                </item>

            <?php } ?>

        </channel>
    </rss>
<?php
    $feed = ob_get_contents();
    _ob_end_clean();
    ObjectYPT::setCache($cacheFeedName, $feed);
} else {
    //echo '<!-- cache -->';
}
if (!is_string($feed)) {
    $feed = json_encode($feed);
}
echo $feed;
