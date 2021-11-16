<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

if (!Permissions::canAdminVideos()) {
    forbiddenPage("Cannot edit videos likes");
}

if (empty($_REQUEST['videos_id'])) {
    forbiddenPage("videos_id is empty");
}

$video = Video::getVideoLight($_REQUEST['videos_id']);
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title>Edit Likes</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php
                echo $video['title'];
                ?>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-6">
                        <div class="input-group input-group-lg">
                            <span class="input-group-addon"><i class="far fa-thumbs-up"></i></span>
                            <input id="editLikes" type="number" step="1" class="form-control" placeholder="<?php echo __('Likes'); ?>" aria-label="<?php echo __('Likes'); ?>" value="<?php echo $video['likes']; ?>">
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="input-group input-group-lg">
                            <span class="input-group-addon"><i class="far fa-thumbs-down"></i></span>
                            <input id="editDislikes" type="number" step="1" class="form-control" placeholder="<?php echo __('Dislikes'); ?>" aria-label="<?php echo __('Dislikes'); ?>" value="<?php echo $video['dislikes']; ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn btn-success btn-lg btn-block" onclick="saveLikes()">
                    <i class="fas fa-save"></i> <?php echo __('Save'); ?>
                </button>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            function saveLikes() {
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL +'objects/likes.edit.json.php',
                    data: {"videos_id": <?php echo $video['id']; ?>, likes:$('#editLikes').val(), dislikes:$('#editDislikes').val()},
                    type: 'post',
                    success: function (response) {
                        modal.hidePleaseWait();
                        avideoResponse(response);
                    }
                });
            }
        </script>
    </body>
</html>
