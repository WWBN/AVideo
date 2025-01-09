<?php
header('Content-type: application/xml');

require_once '../../videos/configuration.php';
allowOrigin();
header('Access-Control-Allow-Credentials: true');
require_once $global['systemRootPath'] . 'objects/video.php';
$ad_server = AVideoPlugin::loadPlugin('AD_Server');
$obj = AVideoPlugin::getObjectData('AD_Server');

if (empty($_GET['campaign_has_videos_id'])) {
    $video = VastCampaignsVideos::getRandomCampainVideo(intval(@$_GET['campaign_id']));
    $_GET['campaign_has_videos_id'] = $video['id'];
}

$vastCampaingVideos = new VastCampaignsVideos($_GET['campaign_has_videos_id']);
$video = new Video("", "", $vastCampaingVideos->getVideos_id());

$videos_id = getVideos_id();

$adsCount = 0;
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<VAST xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="vast.xsd" version="4.0">
    <Ad id="<?php echo date('YmdHis'); ?>">
        <InLine>
            <AdSystem>AdSense</AdSystem>
            <AdTitle><?php echo $vastCampaingVideos->getAd_title(); ?></AdTitle>
            <Description>
                <![CDATA[<?php echo $vastCampaingVideos->getAd_title(); ?>]]>
            </Description>
            <Error>
                <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=Error&[ERRORCODE]]]>
            </Error>
            <Impression>
                <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=Impression&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
            </Impression>
            <Creatives>
                <?php
                if (empty($_REQUEST['imagesOnly'])) {
                    $files = getVideosURLMP4WEBMOnly($video->getFilename());
                    if (empty($files)) {
                        $files2 = getVideosURL($video->getFilename());
                        //var_dump($files2);exit;
                        if (!empty($files2['m3u8'])) {
                            $files = array();
                            foreach ($files2 as $key => $value) {
                                if (preg_match('/m3u8_/', $key)) {
                                    $files[] = $value;
                                }
                            }
                            $files[] = $files2['m3u8'];
                        } else if (!empty($files2['jpg'])) {
                            $files = $files2;
                            $_REQUEST['imagesOnly'] = 1;
                        }
                    }
                    if (empty($_REQUEST['imagesOnly'])) {
                        $logo = 'view/img/logo.png';
                        $image_info = getimagesize("{$global['systemRootPath']}{$logo}");
                ?>
                        <Creative id="Linear_<?php echo $_GET['campaign_has_videos_id']; ?>" sequence="1">
                            <Linear skipoffset="<?php echo $obj->skipoffset->value; ?>">
                                <Duration><?php echo $video->getDuration(); ?></Duration>
                                
                                <TrackingEvents>
                                    <Tracking event="start">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_STARTED; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="firstQuartile">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_FIRST_QUARTILE; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="midpoint">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_MIDPOINT; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="thirdQuartile">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_THIRD_QUARTILE; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="complete">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_COMPLETED; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="mute">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_MUTED; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="unmute">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_UNMUTED; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="rewind">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_REWIND; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="pause">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_PAUSED; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="resume">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_RESUMED; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="fullscreen">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_FULLSCREEN; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="creativeView">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_CREATIVE_VIEW; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="exitFullscreen">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_EXIT_FULLSCREEN; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="acceptInvitationLinear">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_ACCEPT_INVITATION_LINEAR; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="closeLinear">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_CLOSE_LINEAR; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                </TrackingEvents>
                                <?php
                                if (!empty($vastCampaingVideos)) {
                                    $link = $vastCampaingVideos->getLink();
                                    if (filter_var($link, FILTER_VALIDATE_URL)) {
                                ?>
                                        <VideoClicks>
                                            <ClickThrough id="AdSense">
                                                <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_CLICKED; ?>&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                            </ClickThrough>
                                        </VideoClicks>
                                <?php
                                    } else {
                                        _error_log("VastCampaignsVideos has not a valid link: {$link}");
                                        echo "<!-- VideoClicks VastCampaignsVideos has not a valid link: {$link} -->" . PHP_EOL;
                                    }
                                } else {
                                    echo '<!-- VideoClicks empty vastCampaingVideos -->' . PHP_EOL;
                                }
                                ?>
                                <MediaFiles>
                                    <?php
                                    foreach ($files as $key => $value) {
                                        $adsCount++;
                                        $type = ' type="video/mp4" delivery="progressive" ';
                                        if (preg_match('/m3u8/', $value['url'])) {
                                            $type = ' type="application/x-mpegURL" delivery="streaming" minBitrate="49" maxBitrate="258" ';
                                        }
                                        echo PHP_EOL . '<MediaFile id="AdSense' . ($key) . '" ' . $type . ' scalable="true" maintainAspectRatio="true"><![CDATA[' . ($value['url']) . ']]></MediaFile>';
                                        //echo PHP_EOL . '<MediaFile id="AdSense' . ($key) . '" type="video/mp4" delivery="progressive" scalable="true" maintainAspectRatio="true"><![CDATA[' . ($value['url']) . ']]></MediaFile>';
                                        //echo PHP_EOL . '<MediaFile id="AdSense' . ($key) . '" type="application/vnd.apple.mpegurl" minBitrate="49" maxBitrate="258"  delivery="streaming" scalable="true" maintainAspectRatio="true"><![CDATA[' . ($value['url']) . ']]></MediaFile>';
                                    }
                                    if (!$adsCount) {
                                        echo PHP_EOL . '<MediaFile id="AdSense' . ($key) . '" delivery="progressive" type="video/mp4" scalable="true" maintainAspectRatio="true"><![CDATA[' . $global['webSiteRootURL'] . 'plugin/AD_Server/view/adswarning.mp4]]></MediaFile>';
                                    }
                                    ?>
                                </MediaFiles>
                            </Linear>
                        </Creative>
                        <Creative id="CompanionAds_<?php echo $_GET['campaign_has_videos_id']; ?>" sequence="1">
                            <CompanionAds>
                                <Companion id="<?php echo $_GET['campaign_has_videos_id']; ?>" <?php echo $image_info[3]; ?>>
                                    <StaticResource creativeType="image/png">
                                        <![CDATA[<?php echo getURL($logo); ?>]]>
                                    </StaticResource>
                                    <TrackingEvents>
                                        <Tracking event="creativeView">
                                            <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_CREATIVE_VIEW; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                        </Tracking>
                                    </TrackingEvents>
                                    <CompanionClickThrough>
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_CLICKED; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </CompanionClickThrough>
                                </Companion>
                            </CompanionAds>
                        </Creative>
                    <?php
                    }
                }
                if (!empty($_REQUEST['imagesOnly'])) {
                    $sources = getVideosURL($video->getFilename());
                    $image_info = getimagesize($sources['jpg']['path']);
                    $Duration = '00:00:30';
                    if (!empty($image_info[3])) {
                    ?>
                        <Creative id="NonLinearAds_<?php echo $_GET['campaign_has_videos_id']; ?>" sequence="1">
                            <NonLinearAds>
                                <TrackingEvents>
                                    <Tracking event="start">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_STARTED; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="firstQuartile">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_FIRST_QUARTILE; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="midpoint">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_MIDPOINT; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="thirdQuartile">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_THIRD_QUARTILE; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="complete">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_COMPLETED; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="mute">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_MUTED; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="unmute">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_UNMUTED; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="rewind">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_REWIND; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="pause">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_PAUSED; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="resume">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_RESUMED; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="fullscreen">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_FULLSCREEN; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="creativeView">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_CREATIVE_VIEW; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="exitFullscreen">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_EXIT_FULLSCREEN; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="acceptInvitationLinear">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_ACCEPT_INVITATION_LINEAR; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                    <Tracking event="closeLinear">
                                        <![CDATA[<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/log.php?videos_id=<?php echo $videos_id; ?>&label=<?php echo AD_Server::AD_CLOSE_LINEAR; ?>&ad_mt=[AD_MT]&campaign_has_videos_id=<?php echo $_GET['campaign_has_videos_id']; ?>]]>
                                    </Tracking>
                                </TrackingEvents>

                                <NonLinear <?php echo $image_info[3]; ?> minSuggestedDuration="<?php echo $Duration; ?>" scalable="true" maintainAspectRatio="false" skipoffset="<?php echo $obj->skipoffset->value; ?>">
                                    <?php
                                    foreach ($sources as $key => $img) {
                                        if (preg_match('/^jpg/i', $key) && !empty($img['url'])) {
                                    ?>
                                            <StaticResource creativeType="image/jpg">
                                                <![CDATA[<?php echo $img['url']; ?>]]>
                                            </StaticResource>
                                    <?php
                                            break;
                                        }
                                    }
                                    ?>
                                    <NonLinearClickThrough>
                                        <![CDATA[<?php echo $vastCampaingVideos->getLink(); ?>]]>
                                    </NonLinearClickThrough>
                                </NonLinear>
                            </NonLinearAds>
                        </Creative>
                <?php
                    }
                }
                ?>
            </Creatives>
            <Extensions>
                <Extension type="AdSense"></Extension>
            </Extensions>
        </InLine>
    </Ad>
</VAST>