<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../../../videos/configuration.php';
}
if (!User::canStream()) {
    forbiddenPage("You cant do this 1");
}

$live_schedule_id = intval(@$_REQUEST['live_schedule_id']);
$callBackJSFunction = 'saveLivePoster';
if (!empty($live_schedule_id)) {
    $row = new Live_schedule($live_schedule_id);
    if (!User::isAdmin() && $row->getUsers_id() != User::getId()) {
        forbiddenPage("You cant do this 2");
    }
    $callBackJSFunction = 'saveSchedulePoster';
}

if (!User::canStream()) {
    forbiddenPage("You cant livestream");
}
$poster = Live::getPosterImage(User::getId(), @$_REQUEST['live_servers_id'], $live_schedule_id);
//var_dump($poster, User::getId(), @$_REQUEST['live_servers_id'], $live_schedule_id);exit;
$image = getURL($poster);
$poster = Live::getPrerollPosterImage(User::getId(), @$_REQUEST['live_servers_id'], $live_schedule_id);
//var_dump($poster, User::getId(), @$_REQUEST['live_servers_id'], $live_schedule_id, Live::$posterType_preroll);exit;
$image_preroll = getURL($poster);
$poster = Live::getPostrollPosterImage(User::getId(), @$_REQUEST['live_servers_id'], $live_schedule_id);
//var_dump($poster, User::getId(), @$_REQUEST['live_servers_id'], $live_schedule_id);exit;
$image_postroll = getURL($poster);

$defaultTIme = 30;
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
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php echo __('Poster Type'); ?>
                        </div>
                        <div class="panel-body">
                            <ul class="nav nav-pills nav-stacked">
                                <li class="active posterTypeBtn" posterType="<?php echo Live::$posterType_regular; ?>"><a href="#"><i class="fas fa-photo-video"></i> <?php echo __("Regular Poster"); ?></a></li>
                                <li class="posterTypeBtn" posterType="<?php echo Live::$posterType_preroll; ?>"><a href="#"><i class="fas fa-step-backward"></i> <?php echo __("Preroll Poster"); ?></a></li>
                                <li class="posterTypeBtn" posterType="<?php echo Live::$posterType_postroll; ?>"><a href="#"><i class="fas fa-step-forward"></i> <?php echo __("Postroll Poster"); ?></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="panel panel-default" id="PosterConfiguration" style="display: none;">
                        <div class="panel-heading">
                            <?php echo __('Poster Configuration'); ?>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="liveImgTimeInSeconds"><?php echo __('Poster Display Time'); ?></label>
                                <select class="form-control" id="liveImgTimeInSeconds">
                                    <?php
                                    $seconds = __('Seconds');
                                    for ($i = 0; $i < 10; $i++) {
                                        echo "<option value=\"{$i}\">{$i} {$seconds}</option>";
                                    }
                                    ?>
                                    <?php
                                    for ($i = 10; $i < 600; $i += 5) {
                                        $selected = '';
                                        if ($i == $defaultTIme) {
                                            $selected = 'selected';
                                        }
                                        echo "<option value=\"{$i}\" {$selected}>{$i} {$seconds}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="liveImgCloseTimeInSeconds"><?php echo __('Close Button Delay'); ?></label>
                                <select class="form-control" id="liveImgCloseTimeInSeconds">
                                    <?php
                                    $seconds = __('Seconds');
                                    for ($i = 0; $i < 10; $i++) {
                                        echo "<option value=\"{$i}\">{$i} {$seconds}</option>";
                                    }
                                    ?>
                                    <?php
                                    for ($i = 10; $i < 600; $i += 5) {
                                        $selected = '';
                                        if ($i == $defaultTIme) {
                                            $selected = 'selected';
                                        }
                                        echo "<option value=\"{$i}\" {$selected}>{$i} {$seconds}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-10">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php echo __('Save Poster'); ?>
                        </div>
                        <div class="panel-body">
                            <?php
                            $croppie1 = getCroppie(__("Upload Poster"), $callBackJSFunction);
                            //var_dump($croppie1);exit;
                            echo $croppie1['html'];
                            ?>
                        </div>
                        <div class="panel-footer">
                            <button class="btn btn-success btn-lg btn-block" onclick="closeWindowAfterImageSave = true;<?php echo $croppie1['getCroppieFunction']; ?>"><i class="fas fa-save"></i> <?php echo __('Save'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>  
        <script>
                                var closeWindowAfterImageSave = false;
                                var posterType = 0;
                                function <?php echo $callBackJSFunction; ?>(image) {
                                    modal.showPleaseWait();
                                    $.ajax({
                                        url: webSiteRootURL + 'plugin/Live/uploadPoster.json.php',
                                        data: {
                                            posterType: posterType,
                                            liveImgCloseTimeInSeconds: $('#liveImgCloseTimeInSeconds').val(),
                                            liveImgTimeInSeconds: $('#liveImgTimeInSeconds').val(),
                                            live_schedule_id: <?php echo $live_schedule_id; ?>,
                                            image: image,
                                        },
                                        type: 'post',
                                        success: function (response) {
                                            modal.hidePleaseWait();
                                            avideoResponse(response);
                                            if (response && !response.error) {
                                                if (closeWindowAfterImageSave) {
                                                    var scheduleElem = $('#schedule_poster_<?php echo $live_schedule_id; ?>', window.parent.document);
                                                    $(scheduleElem).attr('src', addGetParam($(scheduleElem).attr('src'), 'cache', Math.random()));
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

                                    $('.posterTypeBtn').click(function () {
                                        posterType = parseInt($(this).attr('posterType'));
                                        $('.posterTypeBtn').removeClass('active');
                                        $('.posterTypeBtn[posterType="' + posterType + '"]').addClass('active');
                                        var jsonFile = false;
                                        switch (posterType) {
                                            case <?php echo Live::$posterType_preroll; ?>:
                                                $('#PosterConfiguration').slideDown();
                                                imageToRelaod = '<?php echo $image_preroll; ?>';
                                                jsonFile = imageToRelaod.replace('.jpg', '.json');
                                                break;
                                            case <?php echo Live::$posterType_postroll; ?>:
                                                $('#PosterConfiguration').slideDown();
                                                imageToRelaod = '<?php echo $image_postroll; ?>';
                                                jsonFile = imageToRelaod.replace('.jpg', '.json');
                                                break;

                                            default:
                                                $('#PosterConfiguration').slideUp();
                                                imageToRelaod = '<?php echo $image; ?>';
                                                break;
                                        }
                                        console.log('posterTypeBtn click', posterType, imageToRelaod);
<?php
echo $croppie1['restartCroppie'] . "(imageToRelaod);";
?>      
                                        var liveImgCloseTimeInSeconds = <?php echo $defaultTIme; ?>;
                                        var liveImgTimeInSeconds = <?php echo $defaultTIme; ?>;
                                        if (jsonFile) {
                                            modal.showPleaseWait();
                                            $.getJSON(jsonFile, function (data) {
                                                if(data){
                                                    liveImgCloseTimeInSeconds = data.liveImgCloseTimeInSeconds;
                                                    liveImgTimeInSeconds = data.liveImgTimeInSeconds;
                                                }
                                            }).always(function() { 
                                                modal.hidePleaseWait();
                                                $('#liveImgCloseTimeInSeconds').val(liveImgCloseTimeInSeconds);
                                                $('#liveImgTimeInSeconds').val(liveImgTimeInSeconds);
                                            });
                                        }else{
                                            $('#liveImgCloseTimeInSeconds').val(liveImgCloseTimeInSeconds);
                                            $('#liveImgTimeInSeconds').val(liveImgTimeInSeconds);
                                        }
                                    });
                                });
        </script>
    </body>
</html>
