<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../../../videos/configuration.php';
}
if (!User::canStream()) {
    forbiddenPage("You cant do this 1");
}

$live_schedule_id = intval($_REQUEST['live_schedule_id']);

if (empty($live_schedule_id)) {
    forbiddenPage("Invalid schedule ID");
}

$row = new Live_schedule($live_schedule_id);

if (!User::isAdmin() && $row->getUsers_id() == User::getId()) {
    forbiddenPage("You cant do this 2");
}

$image = Live_schedule::getPosterURL($live_schedule_id);

?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Upload Poster</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        $croppie1 = getCroppie(__("Upload Poster"), "saveSchedulePoster");
        echo $croppie1['html'];
        ?>
        <hr>
        <button class="btn btn-success btn-lg btn-block" onclick="closeWindowAfterImageSave = true;<?php echo $croppie1['getCroppieFunction']; ?>"><i class="fas fa-save"></i> <?php echo __('Save'); ?></button>

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>  
        <script>
            var closeWindowAfterImageSave = false;
            function saveSchedulePoster(image) {
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL + 'plugin/Live/uploadPoster.json.php',
                    data: {
                        live_schedule_id: <?php echo $live_schedule_id; ?>,
                        image: image
                    },
                    type: 'post',
                    success: function (response) {
                        modal.hidePleaseWait();
                        avideoResponse(response);
                        if (response && !response.error) {
                            if (closeWindowAfterImageSave) {
                                var scheduleElem = $('#schedule_poster_<?php echo $live_schedule_id; ?>', window.parent.document);
                                $(scheduleElem).attr('src', addGetParam($(scheduleElem).attr('src'), 'cache', Math.random()) );
                                avideoModalIframeClose();
                            }
                        }
                    }
                });

            }

            $(document).ready(function () {

<?php
echo $croppie1['createCroppie'] . "('{$image}');";
?>
            });
        </script>
    </body>
</html>
