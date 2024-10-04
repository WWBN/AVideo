<?php
require_once $global['systemRootPath'] . 'objects/playlist.php';
global $isSerie;
$isSerie = 1;
$playlist = new PlayList($playlist_id);

$rowCount = getRowCount();
$_REQUEST['rowCount'] = 1000;

$playlistVideos = PlayList::getVideosFromPlaylist($playlist_id, true, true);

$videoSerie = Video::getVideoFromSeriePlayListsId($playlist_id);

//var_dump($videoSerie);

$_REQUEST['rowCount'] = $rowCount;

$users_id = $playlist->getUsers_id();
$name = $playlist->getName();

if (!empty($videoSerie)) {
    $users_id = $videoSerie['users_id'];
    $name = $videoSerie['title'];
    $playListObject = AVideoPlugin::getObjectData("PlayLists");
    $vid = Video::getVideo($videoSerie["id"], "", true);
    if (!empty($vid)) {
        $videoSerie = $vid;
    }
    if (!empty($playListObject->showTrailerInThePlayList) && !empty($videoSerie["trailer1"]) && filter_var($videoSerie["trailer1"], FILTER_VALIDATE_URL) !== false) {
        $videoSerie["type"] = "embed";
        $videoSerie["videoLink"] = $videoSerie["trailer1"];
        array_unshift($playlistVideos, $videoSerie);
    }
}
?>
<style>
    .playlistList .videoLink {
        display: inline-flex;
        padding: 12px 12px 12px 2px;
        width: 100%;
    }

    .noPaddingButtons button,
    .noPaddingButtons a {
        padding: 1px 5px !important;
    }

    .playlist-nav>.navbar-inverse.playlistList>ul {
        width: 100%;
    }

    .plRemoveBtn {
        position: absolute;
        right: 0;
        top: 5px;
    }

    .plIndicator {
        width: 25px;
    }

    .playlist-nav>.navbar-inverse.playlistList .videosDetails {
        width: 70%;
        margin-right: 10px;
        overflow: hidden;
    }

    .plImage {
        width: 250px;
        height: 100px;
        margin-left: 5px;
        position: relative;    
        display: flex;
        justify-content: center;
    }

    .plImage>img {
        max-height: 100%;
    }

    .plImage .progress {
        position: absolute;
        bottom: 0;
        width: 100%;
        margin: 0 !important;
    }
</style>
<div class="playlist-nav">
    <nav class="navbar navbar-inverse">
        <ul class="nav">
            <li class="navbar-header" style="padding: 5px;">
                <div class="pull-right noPaddingButtons">
                    <?php
                    //echo PlayLists::getPlayLiveButton($playlist_id);
                    echo '<!-- scheduleLiveButton start -->';
                    echo PlayLists::scheduleLiveButton($playlist_id);
                    echo '<!-- scheduleLiveButton end -->';
                    if (!empty($videoSerie)) {
                        $videos_id = $videoSerie["id"];
                        $btnClass = 'btn btn-xs btn-default';
                        echo '<!-- PlayLists/actionButton start -->';
                        include $global['systemRootPath'] . 'plugin/PlayLists/actionButton.php';
                        echo '<!-- PlayLists/actionButton end -->';
                    }
                    ?>
                </div>
                <h3>
                    <?php
                    echo $name;
                    ?>
                    (<?php
                        echo User::getNameIdentificationById($users_id);
                        ?>)
                </h3>
                <small class="pull-right">
                    <?php
                    echo ($playlist_index + 1), "/", count($playlistVideos), " ", __("Videos");
                    ?>
                </small>
            </li>
        </ul>
    </nav>
    <nav class="navbar navbar-inverse playlistList">
        <ul class="nav navbar-nav">
            <?php
            $count = 0;
            foreach ($playlistVideos as $value) {
                $value = object_to_array($value);
                $class = '';
                $indicator = $count + 1;
                if ($count == $playlist_index) {
                    $class .= " active";
                    $indicator = '<span class="fa fa-play text-danger"></span>';
                }
                $uid = 'pl_' . uniqid();
                $plURL = PlayLists::getURL($playlist_id, $count, $value["channelName"], $playlist->getName(), $value['clean_title']);
            ?>
                <li class="<?php echo $class; ?>" id="<?php echo $uid; ?>">
                    <a href="<?php echo $plURL; ?>" title="<?php echo str_replace('"', '', $value['title']); ?>" class="videoLink" style="    padding: 12px 12px 12px 2px;">
                        <div class="pull-left plIndicator">
                            <?php echo $indicator; ?>
                        </div>
                        <div class="pull-left plImage">
                            <?php
                            if (($value['type'] !== "audio") && ($value['type'] !== "linkAudio")) {
                                if (empty($value['images']['poster'])) {
                                    $img = Video::getPoster($value['videos_id']);
                                } else {
                                    $img = $value['images']['poster'];
                                }
                            } else {
                                $img = ImagesPlaceHolders::getAudioLandscape(ImagesPlaceHolders::$RETURN_URL);
                            } ?>
                            <img src="<?php echo $img; ?>" alt="<?php echo str_replace('"', '', $value['title']); ?>" class="img img-responsive" itemprop="thumbnail" />

                            <?php
                            if ($value['type'] !== 'pdf' && $value['type'] !== 'article' && $value['type'] !== 'serie') {
                            ?>
                                <time class="duration"><?php echo Video::getCleanDuration(@$value['duration']); ?></time>
                                <div class="progress" style="height: 3px; margin-bottom: 2px;">
                                    <div class="progress-bar progress-bar-danger" role="progressbar" style="width: <?php echo $value['progress']['percent'] ?>%;" aria-valuenow="<?php echo $value['progress']['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            <?php
                            } ?>
                        </div>
                        <div class="pull-left videosDetails">
                            <div class="text-uppercase row"><strong itemprop="name" class="title"><?php echo $value['title']; ?></strong></div>
                            <div class="details row" itemprop="description">
                                <div>
                                    <span class="<?php echo @$value['iconClass']; ?>"></span>
                                </div>

                                <?php
                                if (empty($advancedCustom->doNotDisplayViews)) {
                                ?>
                                    <div>
                                        <strong class=""><?php echo empty($value['views_count']) ? 0 : number_format($value['views_count'], 0); ?></strong> <?php echo __("Views"); ?>
                                    </div>
                                <?php
                                } ?>
                            </div>
                        </div>
                    </a>
                    <button class="btn btn-link btn-xs plRemoveBtn" type="button" onclick="removeFromPlayList(<?php echo $value['id']; ?>, '<?php echo $uid; ?>')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </li>
            <?php
                $count++;
            }
            ?>
        </ul>
    </nav>
</div>
<script>
    $(function() {
        var ul = $(".playlistList ul");
        var li = ul.find("li.active");
        ul.scrollTop(ul.scrollTop() + li.position().top);
    });

    async function removeFromPlayList(videos_id, liID) {
        // Confirm before removing the video from the playlist
        const confirmed = await avideoConfirm('Are you sure you want to remove this video from the playlist?');

        if (confirmed) {
            modal.showPleaseWait();

            $.ajax({
                url: webSiteRootURL + 'objects/playListAddVideo.json.php',
                method: 'POST',
                data: {
                    'videos_id': videos_id,
                    'add': 0,
                    'playlists_id': <?php echo $playlist_id; ?>
                },
                success: function(response) {
                    fetchPlayLists(1);
                    if (response.error) {
                        var msg = __('Error on playlist');
                        if (!empty(response.msg)) {
                            msg = response.msg;
                        }
                        avideoAlertError(msg);
                    } else {
                        $('#' + liID).slideUp();
                        console.log('addVideoToPlayList success', response);
                        setTimeout(function() {
                            setPlaylistStatus(response.videos_id, response.add, playlists_id, response.type, true);
                        }, 100);
                    }

                    modal.hidePleaseWait();
                }
            });
        }
    }
</script>