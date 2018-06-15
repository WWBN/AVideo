<?php
header('Content-type: application/xml');

require_once '../../videos/configuration.php';
$server = parse_url($global['webSiteRootURL']);
header('Access-Control-Allow-Origin: '.$server["scheme"].'://imasdk.googleapis.com');
require_once $global['systemRootPath'] . 'objects/video.php';
$ad_server = YouPHPTubePlugin::loadPluginIfEnabled('AD_Server');
$obj = YouPHPTubePlugin::getObjectDataIfEnabled('AD_Server');
if (empty($ad_server)) {
    die("not enabled");
}
$types = array('', '_Low', '_SD', '_HD');

$vastCampaingVideos = new VastCampaignsVideos($_GET['campaign_has_videos_id']);
$video = new Video("", "", $vastCampaingVideos->getVideos_id());
$files = getVideosURL($video->getFilename());
?>
<?xml version="1.0" encoding="UTF-8"?>
<VAST xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="vast.xsd" version="3.0">
    <Ad id="709684336">
        <InLine>
            <AdSystem>GDFP</AdSystem>
            <AdTitle>External NCA1C1L1 Preroll</AdTitle>
            <Description><![CDATA[External NCA1C1L1 Preroll ad]]></Description>
            <Error><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=Error&[ERRORCODE]]]></Error>
            <Impression><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=Impression&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Impression>
            <Creatives>
                <Creative id="57861016576" sequence="1">
                    <Linear skipoffset="<?php echo $obj->skipoffset; ?>">
                        <Duration><?php echo $video->getDuration(); ?></Duration>
                        <TrackingEvents>
                            <Tracking event="start"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=start&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                            <Tracking event="firstQuartile"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=firstQuartile&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                            <Tracking event="midpoint"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=midpoint&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                            <Tracking event="thirdQuartile"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=thirdQuartile&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                            <Tracking event="complete"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=complete&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                            <Tracking event="mute"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=mute&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                            <Tracking event="unmute"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=unmute&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                            <Tracking event="rewind"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=rewind&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                            <Tracking event="pause"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=pause&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                            <Tracking event="resume"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=resume&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                            <Tracking event="fullscreen"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=fullscreen&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                            <Tracking event="creativeView"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=creativeView&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                            <Tracking event="exitFullscreen"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=exitFullscreen&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                            <Tracking event="acceptInvitationLinear"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=acceptInvitationLinear&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                            <Tracking event="closeLinear"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=closeLinear&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                        </TrackingEvents>
                        <?php
                        $campaignVideo = new VastCampaignsVideos($_GET['campaign_has_videos_id']);
                        $link = $campaignVideo->getLink();
                        if (filter_var($link, FILTER_VALIDATE_URL)) {
                            ?>
                            <VideoClicks>
                                <ClickThrough id="GDFP"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=ClickThrough&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></ClickThrough>
                            </VideoClicks>
                            <?php
                        }else{
                            error_log("VastCampaignsVideos has not a valid link: {$link}");
                        }
                        ?>
                        <MediaFiles>
                        <?php
                        foreach ($types as $key => $value) {
                            if (!empty($files["mp4{$value}"])) {
                                echo "\n       " . '<MediaFile id="GDFP" delivery="progressive" type="video/mp4" scalable="true" maintainAspectRatio="true"><![CDATA[' . ($files["mp4{$value}"]['url']) . ']]></MediaFile>';
                            }
                            if (!empty($files["webm{$value}"])) {
                                echo "\n       " . '<MediaFile id="GDFP" delivery="progressive" type="video/mp4" scalable="true" maintainAspectRatio="true"><![CDATA[' . ($files["mp4{$value}"]['url']) . ']]></MediaFile>';
                            }
                        }
                        ?>
                        </MediaFiles>
                    </Linear>
                </Creative>
                <Creative id="<?php echo $_GET['campaign_has_videos_id']; ?>" sequence="1">
                    <CompanionAds>
                        <Companion id="<?php echo $_GET['campaign_has_videos_id']; ?>" width="300" height="250">
                            <StaticResource creativeType="image/png"><![CDATA[<?php echo $global['webSiteRootURL']; ?>view/img/logo.png]]></StaticResource>
                            <TrackingEvents>
                                <Tracking event="creativeView"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=creativeView&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></Tracking>
                            </TrackingEvents>
                            <CompanionClickThrough><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=CompanionClickThrough&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></CompanionClickThrough>
                        </Companion>
                    </CompanionAds>
                </Creative>
            </Creatives>
        </InLine>
    </Ad>
</VAST>
