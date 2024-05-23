<?php
global $advancedCustom;
if (empty($playlists_id)) {
    echo 'empty playlist id';
    return '';
}

$program = PlayList::getFromDbFromId($playlists_id);
if (empty($program)) {
    echo 'program not found';
    return;
}

$plObj = AVideoPlugin::getDataObject('PlayLists');
$playListButtons = AVideoPlugin::getPlayListButtons($playlists_id);
$link = PlayLists::getLink($program['id']);

$isASerie = PlayLists::isPlayListASerie($program['id']);
if (empty($isASerie)) {
    $currentSerieVideos_id = 0;
} else {
    $currentSerieVideos_id = $isASerie['id'];
}

getSharePopupButton(0, "{$global['webSiteRootURL']}viewProgram/{$program['id']}/" . urlencode($program['name']), $program['name'], 'btn-xs');
?>
<a href="<?php echo $link; ?>" class="btn btn-xs btn-default playAll hrefLink" data-toggle="tooltip" title="<?php echo __("Play All"); ?>">
    <span class="fa fa-play"></span> <span class="hidden-sm hidden-xs"><?php echo __("Play All"); ?></span>
</a>
<?php echo $playListButtons; ?>
<?php
//echo PlayLists::getPlayLiveButton($program['id']);
echo PlayLists::scheduleLiveButton($program['id']);
if (PlayLists::canManagePlaylist($playlists_id)) {
    if (!isMobile()) {
?>
        <script>
            $(function() {
                $("#sortable<?php echo $program['id']; ?>").sortable({
                    items: "li",
                    stop: function(event, ui) {
                        modal.showPleaseWait();
                        saveSortable(this, <?php echo $program['id']; ?>);
                    }
                });
                $("#sortable<?php echo $program['id']; ?>").disableSelection();
            });
        </script>
    <?php
    }
    ?>
    <div class="dropdown" style="display: inline-block;">
        <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
            <i class="fas fa-sort-amount-down"></i>
            <?php echo __("Auto Sort"); ?>
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><a href="<?php echo $global['webSiteRootURL']; ?>objects/playlistSort.php?playlist_id=<?php echo $program['id']; ?>&sort=1"><i class="fas fa-sort-alpha-down"></i> <?php echo __("Alphabetical"); ?> A-Z</a></li>
            <li><a href="<?php echo $global['webSiteRootURL']; ?>objects/playlistSort.php?playlist_id=<?php echo $program['id']; ?>&sort=2"><i class="fas fa-sort-alpha-down-alt"></i> <?php echo __("Alphabetical"); ?> Desc Z-A</a></li>
            <li><a href="<?php echo $global['webSiteRootURL']; ?>objects/playlistSort.php?playlist_id=<?php echo $program['id']; ?>&sort=3"><i class="fas fa-sort-numeric-down"></i> <?php echo __("Created Date"); ?> 0-9</a></li>
            <li><a href="<?php echo $global['webSiteRootURL']; ?>objects/playlistSort.php?playlist_id=<?php echo $program['id']; ?>&sort=4"><i class="fas fa-sort-numeric-down-alt"></i> <?php echo __("Created Date"); ?> Desc 9-0</a></li>
        </ul>
    </div>
    <?php
    if (!empty($plObj->showFeed)) {
        $rss =  "{$global['webSiteRootURL']}feed/?program_id={$program['id']}";
        $mrss =  "{$global['webSiteRootURL']}mrss/?program_id={$program['id']}";
        $roku =  "{$global['webSiteRootURL']}roku.json?program_id={$program['id']}";
        echo getFeedButton($rss, $mrss, $roku);
    }
    ?>
    <div class="pull-right btn-group" style="display: inline-flex;">
        <?php
        echo PlayLists::getShowOnTVSwitch($program['id']);
        if ($program['status'] != "favorite" && $program['status'] != "watch_later") {
            if (AVideoPlugin::isEnabledByName("PlayLists")) {
        ?>
                <button class="btn btn-xs btn-default" onclick="copyToClipboard($('#playListEmbedCode<?php echo $program['id']; ?>').val()); setTextEmbedCopied();" data-toggle="tooltip" title="<?php echo __("Copy Embed code"); ?>">
                    <span class="fa fa-copy"></span> <span id="btnEmbedText" class="hidden-sm hidden-xs"><?php echo __("Embed code"); ?></span>
                </button>
                <input type="hidden" id="playListEmbedCode<?php echo $program['id']; ?>" value='<?php
                                                                                                $code = str_replace("{embedURL}", "{$global['webSiteRootURL']}plugin/PlayLists/embed.php?playlists_id={$program['id']}", $advancedCustom->embedCodeTemplate);
                                                                                                $code = str_replace("{videoLengthInSeconds}", 0, $code);
                                                                                                echo ($code);
                                                                                                ?>' />
                <button class="btn btn-xs btn-default" onclick="copyToClipboard($('#playListEmbedGallery<?php echo $program['id']; ?>').val()); setTextGalleryCopied();" data-toggle="tooltip" title="<?php echo __("Copy Embed code"); ?>">
                    <span class="fa fa-copy"></span> <span id="btnEmbedGalleryText" class="hidden-sm hidden-xs"><?php echo __("Embed Gallery"); ?></span>
                </button>
                <input type="hidden" id="playListEmbedGallery<?php echo $program['id']; ?>" value='<?php
                                                                                                    $code = str_replace("{embedURL}", "{$global['webSiteRootURL']}plugin/PlayLists/playerEmbed.php?playlists_id={$program['id']}", $advancedCustom->embedCodeTemplate);
                                                                                                    $code = str_replace("{videoLengthInSeconds}", 0, $code);
                                                                                                    echo ($code);
                                                                                                    ?>' />
            <?php
            }
            if (User::canUpload()) {
            ?>
                <button class="btn btn-xs btn-info seriePlaylist" playlist_id="<?php echo $program['id']; ?>" data-toggle="tooltip" title="<?php echo __('Add this playlist in your video library'); ?>">
                    <i class="fas fa-film"></i> <span class="hidden-xs hidden-sm"><?php echo __("Serie"); ?></span>
                </button>
                <div id="seriePlaylistModal" class="modal fade" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document" style="width: 90%; margin: auto;">
                        <div class="modal-content">
                            <div class="modal-body">
                                <iframe style="width: 100%; height: 80vh;" src="about:blank">

                                </iframe>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
                <script>
                    $(function() {
                        $('.seriePlaylist').click(function() {
                            $($('#seriePlaylistModal').find('iframe')[0]).attr('src', 'about:blank');
                            var playlist_id = $(this).attr('playlist_id');
                            $($('#seriePlaylistModal').find('iframe')[0]).attr('src', '<?php echo $global['webSiteRootURL']; ?>plugin/PlayLists/playListToSerie.php?playlist_id=' + playlist_id);
                            $('#seriePlaylistModal').modal();
                            //$('#seriePlaylistModal').modal('hide');
                        });
                    });
                </script>

            <?php }
            ?>
            <button class="btn btn-xs btn-danger deletePlaylist" playlist_id="<?php echo $program['id']; ?>" data-toggle="tooltip" title="<?php echo __('Delete'); ?>"><i class="fas fa-trash"></i> <span class="hidden-xs hidden-sm"><?php echo __("Delete"); ?></span></button>
            <button class="btn btn-xs btn-primary renamePlaylist" playlist_id="<?php echo $program['id']; ?>" data-toggle="tooltip" title="<?php echo __('Rename'); ?>"><i class="fas fa-edit"></i> <span class="hidden-xs hidden-sm"><?php echo __("Rename"); ?></span></button>
            <button class="btn btn-xs btn-success" onclick="openVideoSearch(<?php echo $currentSerieVideos_id; ?>)" playlist_id="<?php echo $program['id']; ?>" data-toggle="tooltip" title="<?php echo __('Add to Program'); ?>"><span class="fas fa-plus"></span> <span class="hidden-xs hidden-sm"><?php echo __("Add"); ?></span></button>
            <button class="btn btn-xs btn-success" onclick="encodeNewVideo()" playlist_id="<?php echo $program['id']; ?>" data-toggle="tooltip" title="<?php echo __('Encode a new video'); ?>"><i class="fas fa-cog"></i> <span class="hidden-xs hidden-sm"><?php echo __("Encode"); ?></span></button>
            <button class="btn btn-xs btn-default statusPlaylist statusPlaylist<?php echo $program['id']; ?>" playlist_id="<?php echo $program['id']; ?>" style="">
                <span class="fa fa-lock" id="statusPrivate<?php echo $program['id']; ?>" style="color: red; <?php
                                                                                                            if ($program['status'] !== 'private') {
                                                                                                                echo ' display: none;';
                                                                                                            }
                                                                                                            ?> " data-toggle="tooltip" title="<?php echo __('This playlist is private, click to make it public'); ?>"></span>
                <span class="fa fa-globe" id="statusPublic<?php echo $program['id']; ?>" style="color: green; <?php
                                                                                                                if ($program['status'] !== 'public') {
                                                                                                                    echo ' display: none;';
                                                                                                                }
                                                                                                                ?>" data-toggle="tooltip" title="<?php echo __('This playlist is public, click to make it unlisted'); ?>"></span>
                <span class="fa fa-eye-slash" id="statusUnlisted<?php echo $program['id']; ?>" style="color: gray;   <?php
                                                                                                                        if ($program['status'] !== 'unlisted') {
                                                                                                                            echo ' display: none;';
                                                                                                                        }
                                                                                                                        ?>" data-toggle="tooltip" title="<?php echo __('This playlist is unlisted, click to make it private'); ?>"></span>
            </button>
        <?php
            include_once $global['systemRootPath'] . 'plugin/PlayLists/addVideoModal.php';
        }
        if ($showMore) {
        ?>
            <a class="btn btn-xs btn-default" href="<?php echo $global['webSiteRootURL']; ?>viewProgram/<?php echo $program['id']; ?>/<?php echo urlencode(cleanURLName($program['name'])); ?>/" data-toggle="tooltip" title="<?php echo __("More"); ?>">
                <?php echo __('More'); ?> <i class="fas fa-ellipsis-h"></i>
            </a>
        <?php
        }
        ?>
        <script>
            function encodeNewVideo() {
                <?php
                $config = new AVideoConf();
                $params = new stdClass();
                $params->webSiteRootURL = $global['webSiteRootURL'];
                $params->user = User::getUserName();
                $params->pass = User::getUserPass();
                $params->playlists_id = $playlists_id;
                ?>
                postFormToTarget("<?php echo $config->getEncoderURL(); ?>", "encoder", <?php echo json_encode($params); ?>);
                <?php
                ?>
            }
        </script>
    </div>
<?php }
?>