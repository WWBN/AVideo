<?php
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
    require_once $global['systemRootPath'] . 'objects/playlist.php';
    require_once $global['systemRootPath'] . 'objects/configuration.php';
}

$PlayListChannels = PlayLists::getSiteEPGs();
$tabindex = 1;
foreach ($PlayListChannels as $users_id => $PlayListChannel) {
    if ($users_id === 'generated') {
        continue;
    }
    ?>
    <div class="row">
        <hr>
        <div class="col-sm-12">
            <h3 style="display: inline-flex;">
                <img src="<?php echo $PlayListChannel['icon']; ?>" class="img img-rounded img-responsive" style="height: 30px; margin: 5px 10px;"/>
                <?php echo $PlayListChannel['name']; ?>
            </h3>
        </div>
        <?php
        $itemsPerRow = 6;
        $count = 0;
        $tabindexCol = 0;
        foreach ($PlayListChannel["playlists"] as $key_playlists_id => $playlist) {
            $count++;
            ?>
            <div class="col-sm-<?php echo 12 / $itemsPerRow; ?> videoLinkCol">
                <div class="videoLinkDiv" tabindex="<?php echo $tabindex++; ?>"  tabindexCol="<?php echo $tabindexCol++; ?>" channelNumber="<?php echo $count; ?>" >
                    <a tabindex="-1"
                       href="<?php echo $playlist['embedlink']; ?>" 
                       link="<?php echo $playlist['link']; ?>" 
                       source="<?php echo PlayLists::getM3U8File($key_playlists_id); ?>" 
                       class="videoLink <?php echo PlayLists::isPlaylistLive($key_playlists_id) ? "playListIsLive" : ""; ?>">
                        <img src="<?php echo PlayLists::getLiveImage($key_playlists_id); ?>" 
                             originalSrc="<?php echo PlayLists::getLiveImage($key_playlists_id); ?>" 
                             class="img img-rounded img-responsive" style="margin: 0 auto;"/>
                        <h5>
                            <i class="fas fa-broadcast-tower "></i> 
                            <span class="badge"><?php echo sprintf('%03d', $count); ?> </span>
                            <?php echo PlayLists::getNameOrSerieTitle($key_playlists_id); ?>
                        </h5>
                    </a>
                </div>
            </div>
            <?php
            if ($count % $itemsPerRow === 0) {
                $tabindexCol = 0;
                echo "<div class='clearfix'></div>";
            }
        }
        ?>
    </div>
    <?php
}
?>
</div>