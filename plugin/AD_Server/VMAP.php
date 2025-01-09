<?php
header('Content-type: application/xml');

require_once '../../videos/configuration.php';
allowOrigin();
header('Access-Control-Allow-Credentials: true');
$ad_server = AVideoPlugin::loadPluginIfEnabled('AD_Server');
if (empty($ad_server)) {
    die("not enabled");
}
if (empty($_GET['video_length'])) {
    $_GET['video_length'] = 300;
}

if (empty($_GET['vmap_id'])) {
    $_GET['vmap_id'] = uniqid();
}

$videos_id = getVideos_id();

$vmaps = AD_Server::getVMAPSFromRequest();
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<vmap:VMAP xmlns:vmap="http://www.iab.net/videosuite/vmap" version="1.0">
    <?php
    if(empty($vmaps)){
        if (!empty($_REQUEST['vmaps'])) {
            $vmaps = _json_decode(base64_decode($_REQUEST['vmaps']));
        } else{
            echo '<!-- $_REQUEST[vmaps] is empty -->'.PHP_EOL;
        }
        if(empty($vmaps)) {
            if(!empty($_REQUEST['video_length'])){
                $video_length = intval($_REQUEST['video_length']);
            }else{
                $video_length = self::getVideoLength();
            }
            $ad_server = AVideoPlugin::loadPlugin('AD_Server');
            $vmaps = $ad_server->getVMAPs($video_length);
        }
    }
    if(empty($vmaps)) {
        echo '<!-- $vmaps is empty -->'.PHP_EOL;
    }
    foreach ($vmaps as $key => $value) {
        if (empty($value['VAST']['campaing'])) {
            echo '<!-- campaing is empty '.json_encode($value).' -->'.PHP_EOL;
            continue;
        }
        $AdTagURI = "{$global['webSiteRootURL']}plugin/AD_Server/VAST.php";
        $AdTagURI = addQueryStringParameter($AdTagURI, 'campaign_has_videos_id', $value['VAST']['campaing']);
        $AdTagURI = addQueryStringParameter($AdTagURI, 'vmap_id', $_GET['vmap_id'] ?? '');
        $AdTagURI = addQueryStringParameter($AdTagURI, 'key', $key);
        $AdTagURI = addQueryStringParameter($AdTagURI, 'videos_id', $videos_id);
        
        $AdTagURI = AVideoPlugin::replacePlaceHolders($AdTagURI, $videos_id);
        ?>
        <vmap:AdBreak timeOffset="<?php echo $value['timeOffset']; ?>">
            <vmap:AdSource id="<?php echo $value['idTag']; ?>" allowMultipleAds="true" followRedirects="true" breakId="<?php echo $value['idTag']; ?>-break">
                <vmap:AdTagURI templateType="vast3"><![CDATA[<?php echo $AdTagURI; ?>]]></vmap:AdTagURI>
            </vmap:AdSource>
        </vmap:AdBreak>
        <?php
    }
    ?>
</vmap:VMAP>
<!-- AD_Server -->