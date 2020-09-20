
<div class="panel panel-default">
    <div class="panel-heading">
        <?php
        $streamName = $trasnmition['key'];
        include $global['systemRootPath'] . 'plugin/Live/view/onlineLabel.php';
        ?>
        <span class=" pull-right" >
            <button class="btn btn-default btn-xs " id="toogleViewBTN" onclick="" data-toggle="tooltip" title="<?php echo __("Toogle view"); ?>">
                <i class="far fa-window-maximize"></i>
            </button>
            <script>
                $(document).ready(function () {
                    $('#toogleViewBTN').click(function () {
                        if ($(this).find('i').hasClass('far')) {
                            $(this).find('i').removeClass('far');
                            $(this).find('i').addClass('fas');
                            $('#indexCol1').removeClass('col-lg-8');
                            $('#indexCol2').removeClass('col-lg-4');
                        } else {
                            $(this).find('i').removeClass('fas');
                            $(this).find('i').addClass('far');
                            $('#indexCol1').addClass('col-lg-8');
                            $('#indexCol2').addClass('col-lg-4');
                        }
                    });
                });
            </script>
        </span>
        <?php
        if (Live::canStreamWithMeet()) {
            include $global['systemRootPath'] . 'plugin/Live/meet.php';
        }
        ?>
    </div>
    <div class="panel-body">          
        <div class="embed-responsive embed-responsive-16by9">
            <?php
            if (Live::canStreamWithMeet()) {
                ?>
                <div id="divMeetToIFrame"></div> 
                <?php
            }
            ?>
            <video poster="<?php echo $global['webSiteRootURL']; ?><?php echo $poster; ?>?<?php echo filectime($global['systemRootPath'] . $poster); ?>" controls 
                   class="embed-responsive-item video-js vjs-default-skin <?php echo $vjsClass; ?> vjs-big-play-centered" 
                   id="mainVideo" >
                <source src="<?php echo Live::getM3U8File($trasnmition['key']); ?>" type='application/x-mpegURL'>
            </video>
        </div>
    </div>
    <div class="panel-footer" style="display: none;" id="liveControls">
        <?php
        echo Live::getAllControlls($trasnmition['id']);
        ?>
    </div>
    <script>
        $(document).ready(function () {
            setInterval(function () {
                if (isOnlineLabel) {
                    $("#liveControls").slideDown();
                } else {
                    $("#liveControls").slideUp();
                }
            }, 1000);
        });
    </script>
</div>
<?php
include $global['systemRootPath'] . 'plugin/Live/tabs/tabStreamSettings.php';
?>