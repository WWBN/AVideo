<?php
require_once '../../videos/configuration.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugin Audit"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/video.php';

$defaultBookmarkTime = 3;

$video = Video::getVideo($_GET['videos_id'], "", true);
$poster = Video::getPathToFile("{$video['filename']}.jpg");
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">
    <head>
        <?php 
        echo getHTMLTitle( __("Bookmark Editor"));
        ?>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link rel="stylesheet" type="text/css" href="<?php echo getCDN(); ?>view/css/DataTables/datatables.min.css"/>
        <link href="<?php echo getURL('node_modules/video.js/dist/video-js.min.css'); ?>" rel="stylesheet" type="text/css"/>
        <style>
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">

            <?php
            require_once $global['systemRootPath'] . 'view/include/video.php';
            ?>
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">

                        <div class="col-sm-12">
                            <div class="row">
                                <div class="col-sm-2">
                                    <label for="currentTime"><?php echo __("Current Time"); ?>:</label>
                                    <input class="form-control" type="text" id="currentTime">
                                </div>
                                <div class="col-sm-10">
                                    <label for="subtitle"><?php echo __("Text"); ?>:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="<?php echo __("Text"); ?>" id="subtitle">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default" id="addBookmarkButton"><?php echo __("Add"); ?></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="list-group" id="bookmarksList">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script src="<?php echo getURL('node_modules/video.js/dist/video.min.js'); ?>" type="text/javascript"></script>
        <?php
        $videoJSArray = array(
            "view/js/videojs-persistvolume/videojs.persistvolume.js",
            "view/js/BootstrapMenu.min.js");
        $jsURL = combineFiles($videoJSArray, "js");
        ?>
        <script src="<?php echo $jsURL; ?>" type="text/javascript"></script>
        <script type="text/javascript" src="<?php echo getURL('view/css/DataTables/datatables.min.js'); ?>"></script>
        <script>
            var bookmarksArray = [];
            var allBookmarksArray = [];
            var indexEditing = -1;
            function secondsToTime(sec) {
                var rest = parseInt((sec % 1) * 100);
                var date = new Date(null);
                date.setSeconds(sec); // specify value for SECONDS here
                var timeString = date.toISOString().substr(11, 8);
                return (timeString + '.' + rest);
            }

            function timeToSeconds(hms) {
                var a = hms.split(':'); // split it at the colons
// minutes are worth 60 seconds. Hours are worth 60 minutes.
                var seconds = (+a[0]) * 60 * 60 + (+a[1]) * 60 + (+a[2]);
                return (seconds);
            }

            function getTimeIndex(time) {
                for (var i = 0; i < bookmarksArray.length; i++) {
                    if (bookmarksArray[i].start <= time && bookmarksArray[i].end >= time) {
                        return i;
                    }
                }
            }

            function setTime(time) {
                $('#currentTime').val(secondsToTime(time));

            }

            function setPosition(time) {
                player.pause();
                player.currentTime(time);
                setTime(time);
            }

            function addBookmark() {
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/Bookmark/page/bookmarkSave.json.php',
                    data: {"videos_id": <?php echo $_GET['videos_id'] ?>, "timeInSeconds": timeToSeconds($('#currentTime').val()), "name": $('#subtitle').val()},
                    type: 'post',
                    success: function (response) {
                        loadBookmark();
                        modal.hidePleaseWait();
                    }
                });
            }

            function createList() {
                $("#bookmarksList").empty();

                bookmarksArray.sort(function (a, b) {
                    return b.timeInSeconds - a.timeInSeconds;
                });

                for (i = bookmarksArray.length; i > 0; i--) {
                    var index = i - 1;
                    $("#bookmarksList").append('<div class="list-group-item list-group-item-action subtitleItem" bookmarkId="' + bookmarksArray[index].id + '" index="' + index + '" id="subtitleItem' + index + '">' +
                            '<small class=\'text-muted\'>' + secondsToTime(bookmarksArray[index].timeInSeconds) + "</small> - " + bookmarksArray[i - 1].name +
                            '<button class=\'btn btn-danger pull-right deleteBookmark btn-sm btn-xs\'><i class=\'fa fa-trash\'></i></button>' +
                            '<button class=\'btn btn-primary pull-right editBookmark btn-sm btn-xs\'><i class=\'fa fa-edit\'></i></button>' +
                            '</div>');
                }

                $('.editBookmark').click(function () {
                    var li = $(this).closest('div');
                    var index = $(li).attr('index');
                    $('.subtitleItem').removeClass('active');
                    $('#subtitleItem' + index).addClass('active');
                    setPosition(bookmarksArray[index].timeInSeconds);
                    $('#subtitle').val(bookmarksArray[index].name);
                    indexEditing = index;
                    return false;
                });

                $('.deleteBookmark').click(function () {
                    modal.showPleaseWait();
                    var li = $(this).closest('div');
                    var index = $(li).attr('index');
                    var id = $(li).attr('bookmarkId');
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>plugin/Bookmark/page/bookmarkDelete.json.php',
                        data: {"id": id},
                        type: 'post',
                        success: function (response) {
                            loadBookmark();
                            modal.hidePleaseWait();
                        }
                    });
                });

            }


            function loadBookmark() {
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/Bookmark/getBookmarks.json.php?videos_id=<?php echo $_GET['videos_id'] ?>',
                                success: function (response) {
                                    allBookmarksArray = response;
                                    bookmarksArray = (allBookmarksArray.rows);
                                    createList();
                                    modal.hidePleaseWait();
                                }
                            });
                        }


                        $(document).ready(function () {

                            loadBookmark();

                            $('#addBookmarkButton').click(function () {
                                addBookmark();
                            });

                            $('#currentTime').change(function () {
                                setPosition(timeToSeconds($('#currentTime').val()));
                            });
                            $('#subtitle').keydown(function (e) {
                                if (e.keyCode == 13) {
                                    addBookmark();
                                } else if ($('#subtitle').val() != '') {
                                    player.pause();
                                } else {
                                    playerPlay(0);
                                }
                            });

                            if (typeof player === 'undefined' && $('#mainVideo').length) {
                                player = videojs('mainVideo'<?php echo PlayerSkins::getDataSetup(); ?>);
                            }
                            player.on('timeupdate', function () {
                                setTime(this.currentTime());
                            });

                            setInterval(function () {
                                if (!player.paused()) {
                                    var index = getTimeIndex(player.currentTime());
                                    $('.subtitleItem').removeClass('active');
                                    $('#subtitleItem' + index).addClass('active');
                                    if (typeof bookmarksArray[index] !== 'undefined') {
                                        $('#subtitle').val(bookmarksArray[index].name);
                                    }
                                }
                            }, 500);
                        });
        </script>
    </body>
</html>
