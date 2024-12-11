<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../../../videos/configuration.php';
}

if (!User::canStream()) {
    forbiddenPage("You cant livestream");
}

$ppv_schedule_id = intval($_REQUEST['ppv_schedule_id'] ?? 0);
$live_schedule_id = intval($_REQUEST['live_schedule_id'] ?? 0);
$live_servers_id = intval($_REQUEST['live_servers_id'] ?? 0);

$posterFor = 'Live';

$callBackJSFunction = 'saveLivePoster';
if (!empty($live_schedule_id)) {
    $row = new Live_schedule($live_schedule_id);
    if (!User::isAdmin() && $row->getUsers_id() != User::getId()) {
        forbiddenPage("You cant do this 2");
    }
    $callBackJSFunction = 'saveSchedulePoster';
}

if (!empty($ppv_schedule_id)) {
    $p = AVideoPlugin::loadPluginIfEnabled('PayPerViewLive');

    if(empty($p)){
        forbiddenPage("PayPerViewLive is disabled");
    }

    $row = new Ppvlive_schedule($ppv_schedule_id);
    if (!User::isAdmin() && $row->getUsers_id() != User::getId()) {
        forbiddenPage("You cant do this");
    }
    $callBackJSFunction = 'saveSchedulePosterPPV';
    $posterFor = 'Live PPV';
    
}
//var_dump(User::getId(), $live_servers_id ?? '', $live_schedule_id, $ppv_schedule_id);
$poster = Live::getRegularPosterImage(User::getId(), $live_servers_id ?? '', $live_schedule_id, $ppv_schedule_id );
//var_dump($poster, User::getId(), $live_servers_id ?? '', $live_schedule_id);exit;
$image = getURL($poster);
$poster = Live::getPrerollPosterImage(User::getId(), $live_servers_id ?? '', $live_schedule_id, $ppv_schedule_id);
//var_dump($poster, User::getId(), $live_servers_id ?? '', $live_schedule_id, Live::$posterType_preroll);exit;
$image_preroll = getURL($poster);
$poster = Live::getPostrollPosterImage(User::getId(), $live_servers_id ?? '', $live_schedule_id, $ppv_schedule_id);
//var_dump($poster, User::getId(), $live_servers_id ?? '', $live_schedule_id);exit;
$image_postroll = getURL($poster);

$defaultTIme = 30;
$defaultCloseTIme = 10;
$_page = new Page(array('Upload Poster'));
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
                            ?>
                            <optgroup label="<?php echo $seconds; ?>">
                                <?php
                                for ($i = 0; $i < 10; $i++) {
                                    echo "<option value=\"{$i}\">{$i} {$seconds}</option>";
                                }
                                ?>
                                <?php
                                for ($i = 10; $i < 60; $i += 5) {
                                    $selected = '';
                                    if ($i == $defaultTIme) {
                                        $selected = 'selected';
                                    }
                                    echo "<option value=\"{$i}\" {$selected}>{$i} {$seconds}</option>";
                                }
                                ?>

                            </optgroup>
                            <?php
                            $minutes = __('Minutes');
                            ?>
                            <optgroup label="<?php echo $minutes; ?>">
                                <?php
                                for ($i = 60; $i < 600; $i += 60) {
                                    $selected = '';
                                    if ($i == $defaultTIme) {
                                        $selected = 'selected';
                                    }
                                    $min = intval($i / 60);
                                    echo "<option value=\"{$i}\" {$selected}>{$min} {$minutes}</option>";
                                }
                                ?>
                                <?php
                                for ($i = 600; $i <= 3600; $i += 300) {
                                    $selected = '';
                                    if ($i == $defaultTIme) {
                                        $selected = 'selected';
                                    }
                                    $min = intval($i / 60);
                                    echo "<option value=\"{$i}\" {$selected}>{$min} {$minutes}</option>";
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="liveImgCloseTimeInSeconds"><?php echo __('Close Button Delay'); ?></label>
                        <select class="form-control" id="liveImgCloseTimeInSeconds">
                            <option value="-1" selected><?php echo __('Do not allow close'); ?></option>
                            <?php
                            $seconds = __('Seconds');
                            for ($i = 0; $i < 10; $i++) {
                                echo "<option value=\"{$i}\">{$i} {$seconds}</option>";
                            }
                            ?>
                            <?php
                            for ($i = 10; $i < 600; $i += 5) {
                                $selected = '';
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
                    (<?php echo $posterFor; ?>)
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
                ppv_schedule_id: <?php echo $ppv_schedule_id; ?>,
                live_schedule_id: <?php echo $live_schedule_id; ?>,
                live_servers_id: <?php echo $live_servers_id; ?>,
                image: image,
            },
            type: 'post',
            success: function(response) {
                modal.hidePleaseWait();
                avideoResponse(response);
                if (response && !response.error) {
                    if (closeWindowAfterImageSave) {
                        var scheduleElem = $('#schedule_poster_<?php echo $live_schedule_id; ?>_<?php echo $ppv_schedule_id; ?>', window.parent.document);
                        $(scheduleElem).attr('src', addGetParam($(scheduleElem).attr('src'), 'cache', Math.random()));
                        avideoModalIframeClose();
                    }
                }
            }
        });

    }

    $(document).ready(function() {
        <?php
        echo $croppie1['createCroppie'] . "('{$image}');";
        ?>

        $('.posterTypeBtn').click(function() {
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
            var liveImgCloseTimeInSeconds = -1;
            var liveImgTimeInSeconds = <?php echo $defaultTIme; ?>;
            if (jsonFile) {
                modal.showPleaseWait();
                $.getJSON(jsonFile, function(data) {
                    if (data) {
                        liveImgCloseTimeInSeconds = data.liveImgCloseTimeInSeconds;
                        liveImgTimeInSeconds = data.liveImgTimeInSeconds;
                    }
                }).always(function() {
                    modal.hidePleaseWait();
                    $('#liveImgCloseTimeInSeconds').val(liveImgCloseTimeInSeconds);
                    $('#liveImgTimeInSeconds').val(liveImgTimeInSeconds);
                });
            } else {
                $('#liveImgCloseTimeInSeconds').val(liveImgCloseTimeInSeconds);
                $('#liveImgTimeInSeconds').val(liveImgTimeInSeconds);
            }
        });
    });
</script>
<?php
$_page->print();
?>