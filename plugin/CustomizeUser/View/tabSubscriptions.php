<?php
$isVideoTagsEnabled = AVideoPlugin::isEnabledByName('VideoTags');
?>
<div id="<?php echo $tabId; ?>" class="tab-pane fade in" style="padding: 10px 0;">
    <div class="panel panel-default">
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#contentProducersSubs"><i class="fas fa-user"></i> <?php echo __('Users'); ?></a></li>
                <?php
                if ($isVideoTagsEnabled) {
                    ?>
                    <li><a data-toggle="tab" href="#tagsSubs"><i class="fas fa-tags"></i> <?php echo __('Tags'); ?></a></li>                        
                    <?php
                }
                ?>
            </ul>
            <div class="tab-content">
                <div id="contentProducersSubs" class="tab-pane fade in active">
                    <?php
                    include $global['systemRootPath'] . 'plugin/Gallery/view/mainAreaChannels.php';
                    ?>
                </div>
                <?php
                if ($isVideoTagsEnabled) {
                    ?>
                    <div id="tagsSubs" class="tab-pane fade">
                        <?php
                        include $global['systemRootPath'] . 'plugin/VideoTags/View/list.php';
                        ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
    });
</script>

