<?php
header("Content-Type: audio/x-mpegurl");
session_start();
$dirURL = "";
if (!empty($global['webSiteRootURL'])) {
    $dirURL = "{$global['webSiteRootURL']}plugin/Live/view/loopBGHLS/";
}
if (empty($_GET['res'])) {
    ?>#EXTM3U
#EXT-X-VERSION:3
#EXT-X-STREAM-INF:BANDWIDTH=300000
<?php echo $dirURL; ?>res240/index.m3u8.php?res=240
#EXT-X-STREAM-INF:BANDWIDTH=600000
<?php echo $dirURL; ?>res360/index.m3u8.php?res=360
#EXT-X-STREAM-INF:BANDWIDTH=1000000
<?php echo $dirURL; ?>res480/index.m3u8.php?res=480
#EXT-X-STREAM-INF:BANDWIDTH=2000000
<?php echo $dirURL; ?>res720/index.m3u8.php?res=720
    <?php
    $_SESSION['EXT-X-DISCONTINUITY-SEQUENCE'] = 0;
    exit;
}

?>#EXTM3U
#EXT-X-VERSION:3
#EXT-X-MEDIA-SEQUENCE:<?php echo ++$_SESSION['EXT-X-DISCONTINUITY-SEQUENCE'], PHP_EOL; ?>
#EXT-X-DISCONTINUITY-SEQUENCE:<?php echo $_SESSION['EXT-X-DISCONTINUITY-SEQUENCE'], PHP_EOL; ?>
#EXT-X-DISCONTINUITY
#EXT-X-TARGETDURATION:8
#EXT-X-KEY:METHOD=AES-128,URI="../enc_5efe4da35485d.key"
#EXTINF:8.341667,
index0.ts?seq=<?php echo $_SESSION['EXT-X-DISCONTINUITY-SEQUENCE'],PHP_EOL; ?>
#EXTINF:1.668333,
index1.ts?seq=<?php echo $_SESSION['EXT-X-DISCONTINUITY-SEQUENCE'],PHP_EOL; ?>

