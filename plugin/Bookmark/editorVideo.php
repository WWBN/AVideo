<?php
require_once '../../videos/configuration.php';

$videos_id = getVideos_id();

if (empty($videos_id)) {
    forbiddenPage('videos_id is required');
}

require_once $global['systemRootPath'] . 'objects/video.php';
$_video = new Video('', '', $videos_id);

if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit this video');
}


$video = Video::getVideo($videos_id, "", true);
$poster = Video::getPathToFile("{$video['filename']}.jpg");
$isModeYouTube = true;
$_page = new Page(array('Edit Bookmark'));
$_page->setExtraStyles(
    array(
        'view/css/DataTables/datatables.min.css',
        'node_modules/video.js/dist/video-js.min.css'
    )
);
$_page->setExtraScripts(
    array(
        'node_modules/video.js/dist/video.min.js',
        'view/js/videojs-persistvolume/videojs.persistvolume.js',
        'view/js/BootstrapMenu.min.js',
        'view/css/DataTables/datatables.min.js',
    )
);
?>
<div class="container">
    <?php
   include $global['systemRootPath'] . 'view/include/video.php';
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

<script>
    var bookmarksArray = [];
    var allBookmarksArray = [];
    var indexEditing = -1;

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
            url: webSiteRootURL+'plugin/Bookmark/page/bookmarkSave.json.php',
            data: {
                "videos_id": <?php echo $_GET['videos_id'] ?>,
                "timeInSeconds": timeToSeconds($('#currentTime').val()),
                "name": $('#subtitle').val()
            },
            type: 'post',
            success: function(response) {
                loadBookmark();
                modal.hidePleaseWait();
            }
        });
    }

    function createList() {
        $("#bookmarksList").empty();

        bookmarksArray.sort(function(a, b) {
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

        $('.editBookmark').click(function() {
            var li = $(this).closest('div');
            var index = $(li).attr('index');
            $('.subtitleItem').removeClass('active');
            $('#subtitleItem' + index).addClass('active');
            setPosition(bookmarksArray[index].timeInSeconds);
            $('#subtitle').val(bookmarksArray[index].name);
            indexEditing = index;
            return false;
        });

        $('.deleteBookmark').click(function() {
            modal.showPleaseWait();
            var li = $(this).closest('div');
            var index = $(li).attr('index');
            var id = $(li).attr('bookmarkId');
            $.ajax({
                url: webSiteRootURL+'plugin/Bookmark/page/bookmarkDelete.json.php',
                data: {
                    "id": id
                },
                type: 'post',
                success: function(response) {
                    loadBookmark();
                    modal.hidePleaseWait();
                }
            });
        });

    }


    function loadBookmark() {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL+'plugin/Bookmark/getBookmarks.json.php?videos_id=<?php echo $_GET['videos_id'] ?>',
            success: function(response) {
                allBookmarksArray = response;
                bookmarksArray = (allBookmarksArray.rows);
                createList();
                modal.hidePleaseWait();
            }
        });
    }


    $(document).ready(function() {

        loadBookmark();

        $('#addBookmarkButton').click(function() {
            addBookmark();
        });

        $('#currentTime').change(function() {
            setPosition(timeToSeconds($('#currentTime').val()));
        });
        $('#subtitle').keydown(function(e) {
            if (e.keyCode == 13) {
                addBookmark();
            } else if ($('#subtitle').val() != '') {
                player.pause();
            } else {
                playerPlay(0);
            }
        });

        if (typeof player === 'undefined' && $('#mainVideo').length) {
            try {                
                player = videojs('mainVideo'<?php echo PlayerSkins::getDataSetup(); ?>);
            } catch (error) {
                player = videojs('mainVideo');
            }
        }
        player.on('timeupdate', function() {
            setTime(this.currentTime());
        });

        setInterval(function() {
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

<?php
$_page->print();
?>