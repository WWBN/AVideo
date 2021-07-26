<?php
$live_servers_id = Live::getLiveServersIdRequest();
$users_id = User::getId();
?>
<script>autoplay = false;</script>
<div class="panel panel-default">
    <div class="panel-heading">
        <?php
        $streamName = $trasnmition['key'];
        include $global['systemRootPath'] . 'plugin/Live/view/onlineLabel.php';
        if (Live::canStreamWithMeet()) {
            include $global['systemRootPath'] . 'plugin/Live/meet.php';
        }
        include $global['systemRootPath'] . 'plugin/Live/webRTC.php';
        ?>
    </div>
    <div class="panel-body">          
        <div id="divWebcamIFrame" style="display: none;">
            <iframe frameBorder="0" src="about:blank" style="width: 100%; height: 100%;" allowusermedia allow="feature_name allow_list;feature_name allow_list;camera *;microphone *"></iframe>
        </div> 
        <?php
        if (Live::canStreamWithMeet()) {
            ?>
            <div id="divMeetToIFrame" style="display: none;"></div> 
            <?php
        }
        ?>
        <video poster="<?php echo $global['webSiteRootURL'], Live::getPoster($users_id, $live_servers_id); ?>" controls 
               class=" video-js vjs-default-skin vjs-big-play-centered" 
               id="mainVideo" ><!-- indexCol1 -->
            <source src="<?php
            $liveStreamObject2 = new LiveStreamObject($trasnmition['key'], $live_servers_id, @$_REQUEST['live_index'], 0);
            $m3u8URL = $liveStreamObject2->getOnlineM3U8($users_id);
            echo $m3u8URL;
            ?>" type='application/x-mpegURL'>
        </video>
    </div>
    <div class="panel-footer clearfix" id="liveFooterPanel">
        <?php
        echo Live::getAllControlls($liveStreamObject2->getKeyWithIndex(true, true));
        ?>
    </div>
</div>