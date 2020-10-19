<?php
header('Content-type: application/xml');

require_once '../../videos/configuration.php';
allowOrigin();
require_once $global['systemRootPath'] . 'objects/video.php';
$ad_server = AVideoPlugin::loadPlugin('AD_Server');
$obj = AVideoPlugin::getObjectData('AD_Server');

if(empty($_GET['campaign_has_videos_id'])){
    $video = VastCampaignsVideos::getRandomCampainVideo(intval(@$_GET['campaign_id']));
    $_GET['campaign_has_videos_id'] = $video['id'];
}

$vastCampaingVideos = new VastCampaignsVideos($_GET['campaign_has_videos_id']);
$video = new Video("", "", $vastCampaingVideos->getVideos_id());
?>
<?xml version="1.0" encoding="UTF-8"?>
<VAST xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="vast.xsd" version="3.0">
    <Ad id="709684336">
        <InLine>
            <AdSystem>GDFP</AdSystem>
            <AdTitle><?php echo $vastCampaingVideos->getAd_title(); ?></AdTitle>
            <Description><![CDATA[<?php echo $vastCampaingVideos->getAd_title(); ?>]]></Description>
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
                        if(!empty($campaignVideo)){
                            $link = $campaignVideo->getLink();
                            if (filter_var($link, FILTER_VALIDATE_URL)) {
                                ?>
                                <VideoClicks>
                                    <ClickThrough id="GDFP"><![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?label=ClickThrough&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]></ClickThrough>
                                </VideoClicks>
                                <?php
                            }else{
                                _error_log("VastCampaignsVideos has not a valid link: {$link}");
                            }
                        }
                        ?>
                        <MediaFiles>
                        <?php
                        $adsCount = 0;
                        $files = getVideosURLMP4WEBMOnly($video->getFilename());
                        foreach ($files as $key => $value) {
                            $adsCount++;
                            echo "\n       " . '<MediaFile id="GDFP" delivery="progressive" type="video/mp4" scalable="true" maintainAspectRatio="true"><![CDATA[' . ($value['url']) . ']]></MediaFile>';
                        }
                        if(!$adsCount){
                            echo "\n       " . '<MediaFile id="GDFP" delivery="progressive" type="video/mp4" scalable="true" maintainAspectRatio="true"><![CDATA[' . $global['webSiteRootURL'].'plugin/AD_Server/view/adswarning.mp4]]></MediaFile>';
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
