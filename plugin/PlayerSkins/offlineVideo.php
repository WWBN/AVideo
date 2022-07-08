<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

$videos_id = intval(@$_REQUEST['videos_id']);

if (empty($videos_id)) {
    forbiddenPage('Videos ID is required');
}

if (!User::canWatchVideo($videos_id)) {
    forbiddenPage('you cannot watch this video');
}

$video = Video::getVideoLight($videos_id);
$sources = getVideosURL_V2($video['filename']);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="view/img/favicon.ico">
        <title>Download Video</title>
        <?php
        //echo AVideoPlugin::getHeadCode();
        ?>

        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <h1><?php echo $video['title']; ?></h1>
            <h2><?php echo humanFileSize($video['filesize']); ?></h2>

            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#home">Home</a></li>
                <li><a data-toggle="tab" href="#menu1">Menu 1</a></li>
            </ul>

            <div class="tab-content">
                <div id="home" class="tab-pane fade in active">
                    <h3>HOME</h3>
                    <p>Some content.</p>
                </div>
                <div id="menu1" class="tab-pane fade">
                    <h3>Menu 1</h3>
                    <p>Some content in menu 1.</p>
                </div>
            </div>


            <div class="row">
                <?php
                foreach ($sources as $key => $value) {
                    if (preg_match('/^mp4_([0-9]+)/', $key, $matches)) {
                        ?>
                        <div class="col-sm-3">
                            <div class="panel panel-default" id="videos_offline_<?php echo $videos_id; ?>_<?php echo $matches[1]; ?>">
                                <div class="panel-heading">
                                    <button class="btn btn-danger"  onclick='_deleteOfflineVideo(<?php echo json_encode($matches[1]); ?>);'>
                                        <i class="fas fa-trash"></i> <?php echo __('Delete'); ?>
                                    </button>
                                    <button class="btn btn-warning" onclick='_downloadOfflineVideo(<?php echo json_encode($value['url']); ?>, <?php echo json_encode($matches[1]); ?>, "downloadProgressBar<?php echo json_encode($videos_id); ?>");'>
                                        <i class="fas fa-download"></i> <?php echo __('Download'); ?>
                                    </button>
                                    <button class="btn btn-success" onclick='_updateVideo(<?php echo json_encode($videos_id); ?>);'>
                                        <i class="fas fa-sync"></i> <?php echo __('Renew'); ?>
                                    </button>
                                </div>
                                <div class="panel-body">
                                    Resolution: <?php echo $matches[1]; ?>p<br>
                                </div>
                                <div class="panel-footer">
                                    <?php echo humanFileSize(filesize($value['path'])); ?>
                                    <div class="progress" id="downloadProgressBar<?php echo json_encode($videos_id); ?>">
                                        <div class="progress-bar progress-bar-striped active" role="progressbar"
                                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0;">
                                            0%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        //var_dump($matches, $value);
                    }
                }
                //var_dump($video, $sources);
                ?>

            </div>
        </div>

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>  
        <script>
            const offlineVideoDbName = 'videos_offlineDb_<?php echo User::getId(); ?>';
            var mediaId = <?php echo $videos_id; ?>;
        </script>
        <script src="<?php echo getURL('node_modules/dexie/dist/dexie.min.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getURL('plugin/PlayerSkins/offlineVideo.js'); ?>" type="text/javascript"></script>
        <script>
            $(document).ready(function () {
                listAllOfflineVideo();
            });
            function listAllOfflineVideo() {
                videos_id = <?php echo $videos_id; ?>;
                var collection = offlineDbRequest.offline_videos.where('videos_id').equals(videos_id);

                $('.panel').removeClass('panel-success');
                $('.panel').addClass('panel-default');
                collection.each(function (video) {
                    var elemSelector = '#videos_offline_' + video.videos_id_resolution;
                    $(elemSelector).removeClass('panel-default');
                    $(elemSelector).addClass('panel-success');
                });

            }
            async function _downloadOfflineVideo(src, resolution, progressBarSelector) {
                return await fetchVideoFromNetwork(src, 'video/mp4', resolution, progressBarSelector).then(function (video) {
                    console.log("_downloadOfflineVideo: ", video);
                    listAllOfflineVideo();
                }).catch(function (e) {
                    console.log("_downloadOfflineVideo Error: ", e);
                });
            }
            function _deleteOfflineVideo(resolution) {
                return deleteOfflineVideo(<?php echo $videos_id; ?>, resolution).then((video) => {
                    console.log('_deleteOfflineVideo', video);
                    listAllOfflineVideo();
                });
            }
        </script>
        <?php
        //echo AVideoPlugin::getFooterCode();
        ?>

    </body>
</html>