<?php
/*
 * http://wiki.xmltv.org/index.php/XMLTVFormat
 * https://en.f-player.ru/xmltv-format-description
 */


global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
allowOrigin();
header("Content-type: text/xml");
$domain = get_domain($global['webSiteRootURL']);
?><?xml version="1.0" encoding="ISO-8859-1"?>
<!DOCTYPE tv SYSTEM "xmltv.dtd">
<tv source-info-url="<?php echo $global['webSiteRootURL']; ?>" 
    source-info-name="<?php echo addcslashes($config->getWebSiteTitle(), '"'); ?>" 
    generator-info-name="<?php echo $domain, " " . date("Y/m/d H:i:s"); ?>" 
    generator-info-url="<?php echo $global['webSiteRootURL']; ?>">
        <?php
        $channels = PlayLists::getSiteEPGs();
        foreach ($channels as $channel) {
            if (empty($channel['playlists'])) {
                continue;
            }
            if (!empty($channel['playlists'])) {
                foreach ($channel['playlists'] as $json) {
                    if (empty($json['playlists_id']) || !PlayLists::showOnTV($json['playlists_id'])) {
                        continue;
                    }
                    $id = "{$json['playlists_id']}.{$channel['users_id']}.$domain";
                    ?>
                <channel id="<?php echo $id; ?>">
                    <display-name><?php echo htmlspecialchars(PlayLists::getNameOrSerieTitle($json['playlists_id'])); ?></display-name>
                    <icon src="<?php echo htmlspecialchars(PlayLists::getImage($json['playlists_id'])); ?>" />
                </channel>    
                <?php
                if (!empty($json['programme'])) {
                    foreach ($json['programme'] as $programme) {
                        ?>
                        <programme start="<?php echo date("YmdHis", $programme["start"]); ?>" stop="<?php echo date("YmdHis", $programme["stop"]); ?>" channel="<?php echo $id; ?>">
                            <title><?php echo htmlspecialchars(strip_tags($programme['title'])); ?></title>
                            <desc><?php echo htmlspecialchars(strip_tags(br2nl($programme['desc']))); ?></desc>
                            <date><?php echo date("Ymd", $programme["start"]); ?></date>
                            <category><?php echo strip_tags(@$programme['category']); ?></category>
                            <audio>
                                <stereo>stereo</stereo>
                            </audio>
                        </programme>        
                        <?php
                    }
                }
            }
        }
    }
    ?>
</tv>