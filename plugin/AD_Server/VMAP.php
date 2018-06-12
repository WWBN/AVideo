<?php
header('Access-Control-Allow-Origin: *'); 
header('Content-type: application/xml');

require_once '../../videos/configuration.php';
$ad_server = YouPHPTubePlugin::loadPluginIfEnabled('AD_Server');
if(empty($ad_server)){
    die("not enabled");
}

if(!empty($_GET['vmap_id']) && !empty($_SESSION['vmap'][$_GET['vmap_id']])){
    $vmaps = unserialize($_SESSION['vmap'][$_GET['vmap_id']]);
}else{
    $vmaps = $ad_server->getVMAPs($_GET['video_length']);
    $_SESSION['vmap'][$_GET['vmap_id']] = serialize($vmaps);
}
//var_dump($vmaps);exit;
?>
<?xml version="1.0" encoding="UTF-8"?>
<vmap:VMAP xmlns:vmap="http://www.iab.net/videosuite/vmap" version="1.0">
    <?php
    foreach ($vmaps as $value) { 
        ?>
        <vmap:AdBreak timeOffset="<?php echo $value->timeOffset; ?>" breakType="linear" breakId="preroll">
            <vmap:AdSource id="<?php echo $value->idTag; ?>" allowMultipleAds="false" followRedirects="true">
                <vmap:AdTagURI templateType="vast3"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/VAST.php?campaign_has_videos_id=<?php echo $value->VAST->campaing; ?>&vmap_id=<?php echo @$_GET['vmap_id']; ?>]]></vmap:AdTagURI>
            </vmap:AdSource>
        </vmap:AdBreak>    
        <?php
    }
    ?> 
</vmap:VMAP>
