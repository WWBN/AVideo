<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12 AdsLeaderBoard AdsLeaderBoardTop">
        <center style="margin:5px;">
            <?php
            $getAdsLeaderBoardTop = getAdsLeaderBoardTop();
            if (!empty($getAdsLeaderBoardTop)) {
                ?>
                <style>
                    .compress {
                        top: 100px !important;
                    }
                </style>
                <?php
                echo $getAdsLeaderBoardTop;
            }
            ?>
        </center>
    </div>
</div>
<?php
$vType = $video['type'];
if ($vType == "linkVideo") {
    $vType = "video";
} else if ($vType == "live") {
    $vType = "../../plugin/Live/view/liveVideo";
} else if ($vType == "linkAudio") {
    $vType = "audio";
}
if (!in_array($vType, Video::$typeOptions)) {
    $vType = 'video';
}
require "{$global['systemRootPath']}view/include/{$vType}.php";
$modeYouTubeTimeLog['After include video ' . $vType] = microtime(true) - $modeYouTubeTime;
$modeYouTubeTime = microtime(true);
?>
<div class="row">
    <div class="col-sm-1 col-md-1"></div>
    <div class="col-sm-10 col-md-10 AdsLeaderBoard AdsLeaderBoardTop2">
        <center style="margin:5px;">
            <?php echo getAdsLeaderBoardTop2(); ?>
        </center>
    </div>
</div>