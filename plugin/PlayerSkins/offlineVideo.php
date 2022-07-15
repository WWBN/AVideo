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
$types = Video::getVideoTypeFromId($videos_id);
$video = Video::getVideoLight($videos_id);
$sources = getVideosURL_V2($video['filename']);
$sourcesResolutions = array();
$mainResolution = false;
foreach ($sources as $key => $value) {
    if (preg_match('/^mp4_([0-9]+)/', $key, $matches)) {
        $option = array('url' => $value['url'], 'path' => $value['path'], 'resolution' => $matches[1]);
        $sourcesResolutions[] = $option;
        if ($option['resolution'] == 480) {
            $mainResolution = $option;
        }
    }else if (preg_match('/^m3u8_?([0-9]+)?/', $key, $matches)) {
        $resolution = 'auto';
        if(!empty($matches[1])){
            $resolution = $matches[1];
        }
        $option = array('url' => $value['url'], 'path' => $value['path'], 'resolution' => $resolution);
        $sourcesResolutions[] = $option;
        if ($option['resolution'] == 480) {
            $mainResolution = $option;
        }
    }
}
if (empty($mainResolution) && !empty($sourcesResolutions)) {
    $mainResolution = $sourcesResolutions[0];
}

//var_dump($mainResolution);exit;
function createOfflineDownloadPanel($videos_id, $option, $class = 'col-xs-6') {
    ?>
    <div class="<?php echo $class; ?>">
        <div class="panel panel-default videos_offline_<?php echo $videos_id; ?>_<?php echo $option['resolution']; ?>">
            <div class="panel-heading clearfix">
                <button class="btn btn-danger"  onclick='_deleteOfflineVideo(<?php echo json_encode($option['resolution']); ?>);'>
                    <i class="fas fa-trash"></i> <?php echo __('Delete'); ?> <span class="badge"><?php echo humanFileSize(filesize($option['path'])); ?></span>
                </button>
                <button class="btn btn-warning" onclick='_downloadOfflineVideo(<?php echo json_encode($option['url']); ?>, <?php echo json_encode($option['resolution']); ?>);'>
                    <i class="fas fa-download"></i> <?php echo __('Download'); ?> <span class="badge"><?php echo humanFileSize(filesize($option['path'])); ?></span>
                </button>
                <button class="btn btn-success hidden" onclick='_updateVideo(<?php echo json_encode($videos_id); ?>);'>
                    <i class="fas fa-sync"></i> <?php echo __('Renew'); ?>
                </button>
                <button class="btn btn-primary pull-right" onclick='deleteAllOfflineDatabase();'>
                    <i class="fas fa-times"></i> <?php echo __('Delete All'); ?>
                </button>
            </div>
            <div class="panel-body">
                <ul class="list-group">
                    <li class="list-group-item"><?php echo __('Resolution') ?> <span class="badge"><?php echo $option['resolution']; ?></span></li>
                </ul>
            </div>
            <div class="panel-footer">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped active" role="progressbar"
                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0;">
                        0%
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
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
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo getURL('plugin/PlayerSkins/offlineVideo.css'); ?>" rel="stylesheet" type="text/css"/>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">

            <div class="panel panel-default">
                <div class="panel-heading">
                </div>
                <div class="panel-body offlinevideos tabbable-line">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#offlineVideo"><?php echo __('Offline Video'); ?></a></li>
                        <li><a data-toggle="tab" href="#offlineVideoAdvanced"><?php echo __('Advanced'); ?></a></li>
                        <li><a data-toggle="tab" href="#offlineVideoAll"><?php echo __('All Videos'); ?></a></li>
                    </ul>
                    <div class="tab-content">
                        <div id="offlineVideo" class="tab-pane fade in active">
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="panel panel-default">
                                        <div class="panel-heading clearfix">
                                            <?php
                                            echo Video::getVideosListItem($video['id']);
                                            ?>
                                        </div>
                                        <div class="panel-body">
                                            <ul class="list-group">
                                                <li class="list-group-item">
                                                    <?php echo __('Total Size') ?> <span class="badge"><?php echo humanFileSize($video['filesize']); ?></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <?php
                                    createOfflineDownloadPanel($videos_id, $mainResolution, '');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div id="offlineVideoAdvanced" class="tab-pane fade">
                            <div class="row">
                                <?php
                                foreach ($sourcesResolutions as $key => $option) {
                                    createOfflineDownloadPanel($videos_id, $option);
                                }
                                ?>
                            </div>
                        </div>
                        <div id="offlineVideoAll" class="tab-pane fade">
                            <div class="row">
                                <div class="col-lg-12 col-sm-12 col-xs-12 bottom-border videoListItem hidden" id="offlineVideoTemplate">
                                    <div class="col-lg-5 col-sm-5 col-xs-5 nopadding thumbsImage videoLink h6">
                                        <div class="galleryVideo">
                                            <a href="" class="videoLink">
                                                <img 
                                                    src="" 
                                                    class="thumbsJPG img-responsive text-center" height="130" 
                                                    style="">
                                            </a>
                                            <time class="duration"></time>
                                        </div>
                                    </div>
                                    <div class="col-lg-7 col-sm-7 col-xs-7 videosDetails">
                                        <a href="" class="videoLink">
                                            <div class="text-uppercase row">
                                                <strong class="title"></strong>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                </div>
            </div>
        </div>

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>  
        <script>
            const offlineVideoDbName = 'videos_offlineDb_<?php echo User::getId(); ?>';
            var mediaId = <?php echo $videos_id; ?>;
        </script>
        <script src="<?php echo getURL('node_modules/pouchdb/dist/pouchdb.min.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getURL('plugin/PlayerSkins/offlineVideo.js'); ?>" type="text/javascript"></script>
        <script>
            $(document).ready(function () {
                listAllOfflineVideo();
            });
            
            function deleteAllOfflineDatabase() {
                createOfflineDatabase(true).then(function (resp) {
                    listAllOfflineVideo();
                }).catch(function (e) {
                    console.log("deleteAllOfflineDatabase", e);
                });
            }

            async function listAllOfflineVideoAvailable() {
                var collection = await getAllOfflineVideoAvailable();

                console.log('listAllOfflineVideoAvailable', collection);

                $('#offlineVideoAll .offlineVideoAvailable').remove();

                var model = $('#offlineVideoTemplate').clone();

                model.attr('id', '');
                model.removeClass('hidden');
                model.addClass('offlineVideoAvailable');

                for (var i in collection.rows) {
                    var video = collection.rows[i].doc;
                    if (typeof video !== 'object') {
                        continue;
                    }
                    //console.log("replaceVideoSourcesPerOfflineVersionFromVideosId video: ",video);
                    var fileBlob = await offlineVideoDBPouch.getAttachment(video._id, 'poster');
                    var source = createImageSourceFromBlob(fileBlob, 0);

                    var newVideo = $(model).clone();
                    newVideo.find('.videoLink').attr('href', video.link);
                    newVideo.find('.thumbsJPG').attr('src', source.src);
                    newVideo.find('.duration').html(video.duration);
                    newVideo.find('.title').html(video.title);
                    $('#offlineVideoAll > .row').append(newVideo);
                    console.log('listAllOfflineVideoAvailable video', video, newVideo);
                }
            }

            async function listAllOfflineVideo() {
                videos_id = <?php echo $videos_id; ?>;
                var collection = await getAllOfflineVideoPouch(videos_id);

                console.log('listAllOfflineVideo', collection);

                $('.panel').removeClass('panel-success');
                $('.panel').addClass('panel-default');
                changeProgressBarOfflineVideo('.progress', 0);

                for (var i in collection.rows) {
                    var video = collection.rows[i].doc;
                    if (typeof video !== 'object') {
                        continue;
                    }
                    console.log('listAllOfflineVideo video', video);

                    for (var i in video._attachments) {
                        if (i == 'poster') {
                            continue;
                        }
                        var elemSelector = '.videos_offline_' + video.videos_id + '_' + i;
                        $(elemSelector).removeClass('panel-default');
                        $(elemSelector).addClass('panel-success');
                        changeProgressBarOfflineVideo(elemSelector + ' .progress', 100);
                    }


                }
            }
            async function _downloadOfflineVideo(src, resolution) {
                var elemSelector = ".videos_offline_<?php echo $videos_id; ?>_" + resolution;
                var progressBarSelector = elemSelector + " .progress";
                $(elemSelector).addClass('isDownloading');
                var response = await fetchVideoFromNetwork(src, 'video/mp4', resolution, progressBarSelector).then(function (video) {
                    console.log("_downloadOfflineVideo: ", video);
                    listAllOfflineVideo();
                    socketUpdateOfflineVideoSource(<?php echo json_encode($_REQUEST['socketResourceId']); ?>);
                }).catch(function (e) {
                    console.log("_downloadOfflineVideo Error: ", e);
                });
                $(elemSelector).removeClass('isDownloading');
                return response;
            }
            async function _deleteOfflineVideo(resolution) {
                var elemSelector = ".videos_offline_<?php echo $videos_id; ?>_" + resolution;
                $(elemSelector).addClass('isDeleting');
                var response = await deleteOfflineVideoPouch(<?php echo $videos_id; ?>, resolution).then((video) => {
                    console.log('_deleteOfflineVideo', video);
                    listAllOfflineVideo();
                    socketUpdateOfflineVideoSource(<?php echo json_encode($_REQUEST['socketResourceId']); ?>);
                });
                $(elemSelector).removeClass('isDeleting');
                return response;
            }
        </script>
        <?php
        if(!empty($types->m3u8) && AVideoPlugin::isEnabledByName('VideoHLS')){
            include $global['systemRootPath'].'plugin/VideoHLS/downloadHLS.php';
        }
        ?>
    </body>
</html>