<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

if (!Permissions::canAdminVideos()) {
    forbiddenPage('You Must be admin');
}

$videos = array();
setRowCount(100);
$row = Video::getAllVideosLight("", false, true, false, '', 0, true);
foreach ($row as $key => $value) {
    $videos[] = array('videos_id' => $value['id'], 'title' => $value['title'], 'order' => $value['order']);
}

$page = new Page('Sort videos');
?>
<style>
    #sortable {
        list-style-type: none;
        margin: 0;
        padding: 0;
        width: 100%;
    }

    #sortable li {
        margin: 0 3px 3px 3px;
        padding: 0.4em;
        padding-left: 1.5em;
        font-size: 1.4em;
        cursor: move;
    }

    #sortable li span {
        margin-left: 15px;
    }

    .ui-state-highlight {
        height: 1.5em;
        line-height: 1.5em;
    }
</style>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Video Sorting</h3>
        </div>
        <div class="panel-body">
            <p>In this page, you can sort the order of videos.</p>
            <p>When you sort the videos by creation date, the videos at the top will appear first.</p>
            <p>To change the order, simply drag and drop the video title.</p>
            <p>To add a new video, use the search bar below and click on the "Add" button.</p>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-10">
                    <?php
                    $autoComplete = Layout::getVideoAutocomplete(0, 'videoAutocomplete');
                    ?>
                </div>
                <div class="col-xs-2">
                    <button class="btn btn-primary btn-block" id="addVideoBtn">
                        <i class="fas fa-plus"></i>
                        <span class="hidden-xs">
                            <?php echo __('Add'); ?>
                        </span>
                    </button>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button class="btn btn-success btn-block saveOrder">
                <i class="fas fa-save"></i>
                <?php echo __('Save'); ?>
            </button>
        </div>
        <div class="panel-body">
            <ul id="sortable">
            </ul>
        </div>
        <div class="panel-footer">
            <button class="btn btn-success btn-block saveOrder">
                <i class="fas fa-save"></i>
                <?php echo __('Save'); ?>
            </button>
        </div>
    </div>

</div>

<script>
    var videos = <?php echo json_encode($videos); ?>;

    function addVideoItem(videos_id, title, order) {
        var newLi = $("<li>", {
            class: "ui-state-default clearfix",
            "data-videos-id": videos_id
        }).append(
            $("<i>", {
                class: "fas fa-arrows-alt-v"
            }),
            $("<span>").text(title),
            $("<button>", {
                class: "btn btn-danger pull-right removeVideoBtn",
                type: "button"
            }).html('<i class="fas fa-trash"></i>')
        );

        if (order === 0) {
            $("#sortable").prepend(newLi);
        } else if (order > 0 && order <= $("#sortable li").length) {
            $("#sortable li:nth-child(" + order + ")").before(newLi);
        } else {
            $("#sortable").append(newLi);
        }
    }

    function saveVideoOrder() {
        var videosList = [];
        $("#sortable li").each(function(index) {
            var videos_id = $(this).data("videos-id");
            var order = index + 1;
            videosList.push({
                videos_id: videos_id,
                order: order
            });
        });

        avideoAjax("objects/videoSaveOrder.json.php", {
            videos: videosList
        });
    }

    $(function() {
        $("#sortable").sortable({
            placeholder: "ui-state-highlight"
        });

        for (const key in videos) {
            if (videos.hasOwnProperty(key)) {
                const video = videos[key];
                addVideoItem(video.videos_id, video.title, video.order);
            }
        }

        $("#addVideoBtn").click(function() {
            var videos_id = $("#videoAutocomplete").val();
            var title = $("#videoAutocompletevideoAutocomplete").val();
            addVideoItem(videos_id, title, 0);
        });

        $(document).on("click", ".removeVideoBtn", function() {
            $(this).closest("li").remove();
        });

        $(".saveOrder").click(function() {
            saveVideoOrder();
        });
    });
</script>


<?php
$page->print();
?>