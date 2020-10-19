<?php
header('Content-type: application/xml');

require_once '../../videos/configuration.php';
allowOrigin();
$ad_server = AVideoPlugin::loadPluginIfEnabled('AD_Server');
if(empty($ad_server)){
    die("not enabled");
}
if(empty($_GET['video_length'])){
    $_GET['video_length'] = 300;
}

if(empty($_GET['vmap_id'])){
    $_GET['vmap_id'] = uniqid();
}

if(!empty($_GET['vmap_id']) && !empty($_SESSION['user']['vmap'][$_GET['vmap_id']])){
    $vmaps = unserialize($_SESSION['user']['vmap'][$_GET['vmap_id']]);
}else{
    $vmaps = $ad_server->getVMAPs($_GET['video_length']);
    $_SESSION['user']['vmap'][$_GET['vmap_id']] = serialize($vmaps);
}
unset($_SESSION['user']['vmap'][$_GET['vmap_id']]);
//var_dump($vmaps);exit;
?>
<?xml version="1.0" encoding="UTF-8"?>
<vmap:VMAP xmlns:vmap="http://www.iab.net/videosuite/vmap" version="1.0">
    <?php
    foreach ($vmaps as $value) { 
        if(empty($value->VAST->campaing)){
            continue;
        }
        ?>
        <vmap:AdBreak timeOffset="<?php echo $value->timeOffset; ?>" breakType="linear">
            <vmap:AdSource id="<?php echo $value->idTag; ?>" allowMultipleAds="true" followRedirects="true" breakId="<?php echo $value->idTag; ?>-break">
                <vmap:AdTagURI templateType="vast3"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/VAST.php?campaign_has_videos_id=<?php echo $value->VAST->campaing; ?>&vmap_id=<?php echo @$_GET['vmap_id']; ?>]]></vmap:AdTagURI>
            </vmap:AdSource>
        </vmap:AdBreak>    
        <?php
    }
    ?> 
</vmap:VMAP>
