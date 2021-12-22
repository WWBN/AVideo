<?php
$obj = AVideoPlugin::getDataObject("Live");

$buttonTitle = $this->getButtonTitle();
$obj = $this->getDataObject();
if (User::canStream()) {
    if (empty($obj->doNotShowGoLiveButton)) {
        ?>
        <li>
            <button id="TopCopyKeysButton" onclick="avideoModalIframeFull(webSiteRootURL+'plugin/Live')" class="faa-parent animated-hover btn btn-danger navbar-btn" 
               data-toggle="tooltip" title="<?php echo __("Broadcast a Live Stream"); ?>" 
               data-placement="bottom" >
                <i class="fa fa-circle faa-flash"></i> <span class="hidden-md hidden-sm hidden-mdx"><?php echo __($buttonTitle); ?></span>
            </button>
        </li>
        <?php
    }
    if (empty($obj->hideTopCopyKeysButton)) {
        ?>
        <li>
            <button id="TopCopyKeysButton" onclick="copyToClipboard('<?php echo Live::getRTMPLink(User::getId()); ?>')" class="faa-parent animated-hover btn btn-primary navbar-btn" data-toggle="tooltip" title="<?php echo __($obj->topCopyKeysButtonTitle); ?>" data-placement="bottom" >
                <i class="far fa-copy faa-ring"></i> <span class="hidden-md hidden-sm hidden-mdx"><?php echo __($obj->topCopyKeysButtonTitle); ?></span>
            </button>
        </li>
        <?php
    }
}

if (empty($obj->hideTopButton)) {
    ?>

    <style>
        .liveVideo{
            position: relative;
        }
        .liveVideo .liveNow, .liveVideo .liveFuture{
            position: absolute;
            bottom: 5px;
            right: 5px;
        }
        #availableLiveStream{
            width: 350px;
            overflow: hidden;
            max-height: 75vh;
            overflow-y: auto;
        }
        #availableLiveStream li a div{
            overflow: hidden;
        }
    </style>
    <li class="dropdown" onclick="setTimeout(function () {lazyImage();}, 500);setTimeout(function () {lazyImage();}, 1000);setTimeout(function () {lazyImage();}, 1500);">
        <a href="#" class="faa-parent animated-hover btn btn-default navbar-btn" data-toggle="dropdown">
            <span class="fas fa-bell faa-ring"></span>
            <span class="badge onlineApplications" style=" background: rgba(255,0,0,1); color: #FFF;">0</span>
            <b class="caret"></b>
        </a>
        <ul class="dropdown-menu dropdown-menu-right notify-drop" >
            <?php
            if (Live::canStreamWithWebRTC() || Live::canScheduleLive()) {
                ?>
                <div class="btn-group btn-group-justified" style="padding: 5px;">
                    <?php
                    if (Live::canStreamWithWebRTC()) {
                        ?>
                        <button class="btn btn-default btn-sm faa-parent animated-hover " onclick="avideoModalIframeFull(webSiteRootURL + 'plugin/Live/webcamFullscreen.php');" data-toggle="tooltip" title="<?php echo __('Go Live') ?>" >
                            <i class="fas fa-circle faa-flash" style="color:red;"></i> <span class="hidden-sm hidden-xs"><?php echo __($buttonTitle); ?></span>
                        </button>
                        <?php
                    }
                    if (Live::canScheduleLive()) {
                        ?>
                        <button class="btn btn-primary btn-sm" onclick="avideoModalIframeFull(webSiteRootURL + 'plugin/Live/view/Live_schedule/panelIndex.php');" data-toggle="tooltip" title="<?php echo __('Schedule') ?>" >
                            <i class="far fa-calendar"></i> <span class="hidden-sm hidden-xs"><?php echo __('Schedule'); ?></span>
                        </button>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }
            ?>
            <div id="availableLiveStream">

            </div>
        </ul>
    </li>

    <div class="col-lg-12 col-sm-12 col-xs-12 bottom-border hidden extraVideosModel liveVideo">
        <a href="" class="videoLink" class="videoLink galleryLink " >
            <div class="aspectRatio16_9" style="min-height: 70px;" >
                <img src="<?php echo getCDN(); ?>videos/userPhoto/logo.png" class="thumbsJPG img-responsive" height="130" itemprop="thumbnailUrl" alt="Logo" />
                <img src="" style="position: absolute; top: 0; display: none;" class="thumbsGIF img-responsive" height="130" />
                <span class="label label-danger liveNow faa-flash faa-slow animated"><?php echo __("LIVE NOW"); ?></span>
            </div>
        </a>

        <a class="h6 galleryLink " href="_link_" title="_title_" >
            <strong class="title liveTitle"><?php echo __("Title"); ?></strong>
        </a>
        <div class="galeryDetails" style="overflow: hidden;">
            <div>
                <img src="" class="photoImg img img-circle img-responsive" style="max-width: 20px;">
            </div>
            <div class="liveUser"><?php echo __("User"); ?></div>        
            <div class="galleryTags">
                <?php
                if (AVideoPlugin::isEnabledByName("LiveUsers") && method_exists("LiveUsers", "getLabels")) {
                    echo LiveUsers::getLabels('extraVideosModelOnLineLabels');
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        function refreshGetLiveImage(selector) {
            $(selector).find('.thumbsImage img').each(function (index) {
                var src = $(this).attr('src');
                src = addGetParam(src, 'cache', Math.random());
                $(this).attr('src', src);
            });
            setTimeout(function () {
                $(selector).slideDown();
            }, 1000); // give some time to load the new images
        }

        function processLiveStats(response) {
            //console.log('processLiveStats', response);
            if (typeof response !== 'undefined') {
                if (isArray(response)) {
                    for (var i in response) {
                        if (typeof response[i] !== 'object') {
                            continue;
                        }
                        processApplicationLive(response[i]);
                    }
                } else {
                    processApplicationLive(response);
                }
                if (!response.countLiveStream) {
                    availableLiveStreamNotFound();
                } else {
                    $('#availableLiveStream').removeClass('notfound');
                }
                $('.onlineApplications').text(response.countLiveStream);
            }

            setTimeout(function () {
    <?php
    if (!empty($obj->playLiveInFullScreenOnIframe)) {
        echo 'if (typeof linksToFullscreen === \'function\') {linksToFullscreen(\'.liveVideo a, #availableLiveStream a\');}';
    } else if (!empty($obj->playLiveInFullScreen)) {
        echo 'if (typeof linksToEmbed === \'function\') {linksToEmbed(\'.liveVideo a, #availableLiveStream a\');}';
    }
    ?>
            }, 200);
        }

        function getStatsMenu(recurrentCall) {
            if (avideoSocketIsActive()) {
                return false;
            }
            availableLiveStreamIsLoading();
            $.ajax({
                url: webSiteRootURL + 'plugin/Live/stats.json.php?Menu',
                success: function (response) {
                    if (avideoSocketIsActive()) {
                        console.log('getStatsMenu: Socket is enabled we will not process ajax result');
                        return false;
                    }
                    processLiveStats(response);
                    if (recurrentCall) {
                        var timeOut = <?php echo $obj->requestStatsInterval * 1000; ?>;
                        setTimeout(function () {
                            getStatsMenu(true);
                        }, timeOut);
                    }
                }
            });
        }

        function processApplicationLive(response) {
            if (typeof response.applications !== 'undefined') {
                if (response.applications.length) {
                    for (i = 0; i < response.applications.length; i++) {
                        processApplication(response.applications[i]);
                        var selector = '.liveViewStatusClass_' + response.applications[i].live_cleanKey;
                        onlineLabelOnline(selector);
                        selector = '.liveViewStatusClass_' + response.applications[i].key;
                        onlineLabelOnline(selector);
                    }
                    mouseEffect();
                }
            }
            // check for live servers
            var count = 0;
            while (typeof response[count] !== 'undefined') {
                for (i = 0; i < response[count].applications.length; i++) {
                    processApplication(response[count].applications[i]);
                }
                count++;
            }
        }

        function availableLiveStreamIsLoading() {
            if ($('#availableLiveStream').hasClass('notfound')) {
                availableLiveStreamEmpty();
            }
        }

        function availableLiveStreamNotFound() {
            $('#availableLiveStream').addClass('notfound');
            availableLiveStreamEmpty();
        }

        function availableLiveStreamEmpty() {
            $('#availableLiveStream').empty();
        }

        var linksToEmbedTimeout;
        function processApplication(application) {
            href = application.href;
            title = application.title;
            name = application.name;
            user = application.user;
            photo = application.photo;
            key = application.key;
            //console.log('processApplication', application.className);
            callback = '';
            if (typeof application.callback === 'string') {
                callback = application.callback;
            }
            isPrivate = application.isPrivate;
            if (application.type === 'Live') {
                online = application.users.online;
                views = application.users.views;
            } else {
                online = 0;
                views = 0;
            }
            if (typeof application.html != 'undefined') {
                var notificationHTML = $(application.html);
                var notificatioID = notificationHTML.attr('id') + '_notification';
                if (typeof key !== 'undefined') {
                    //console.log('processApplication remove class .live_' + key);
                    $('.live_' + key).remove();
                }
                if (!$('#' + notificatioID).length) {
                    notificationHTML.attr('id', notificatioID);
                    if (application.comingsoon) {
                        $('#availableLiveStream').append(notificationHTML);
                    } else {
                        $('#availableLiveStream').prepend(notificationHTML);
                    }
                    animateChilds('#availableLiveStream', 'animate__bounceInRight', 0.05);
                } else {
                    //console.log('processApplication is already present '+notificatioID, application.className);
                }

                var html;
    <?php
    if (isVideo()) {
        ?>
                    html = application.htmlExtraVideoListItem;
        <?php
    } else if (isLive()) {
        ?>
                    html = application.htmlExtraVideoPage;
        <?php
    } else {
        ?>
                    html = application.htmlExtra;
        <?php
    }
    ?>
                var id = $(html).attr('id');
                if ($('#' + id).length) {
                    //console.log('processApplication key found', id);
                    return false;
                }
                if (application.comingsoon) {
                    $('#liveScheduleVideos .extraVideos').prepend(html);
                    $('#liveScheduleVideos').slideDown();
                } else {
                    $('#liveVideos .extraVideos').prepend(html);
                    $('#liveVideos').slideDown();
                }
                setTimeout(function () {
                    lazyImage();
                }, 1000);
                if (callback) {
                    eval("try {" + callback + ";} catch (e) {console.log('processApplication application.callback error',e.message);}");
                }
            } else {
                console.log('application.html is undefined');
            }
            clearTimeout(linksToEmbedTimeout);
            linksToEmbedTimeout = setTimeout(function () {
    <?php
    if (!empty($obj->playLiveInFullScreenOnIframe)) {
        echo 'if (typeof linksToFullscreen === \'function\') {linksToFullscreen(\'.liveVideo a, #availableLiveStream a\');}';
    } else if (!empty($obj->playLiveInFullScreen)) {
        echo 'if (typeof linksToEmbed === \'function\') {linksToEmbed(\'.liveVideo a, #availableLiveStream a\');}';
    }
    ?>
                avideoSocket();
            }, 500);
            if (application.users && typeof application.users.views !== 'undefined') {
                $('.views_on_total_on_live_' + application.users.transmition_key + '_' + application.users.live_servers_id).text(application.users.views);
            }
        }

        function socketLiveONCallback(json) {
            console.log('socketLiveONCallback', json);
            processLiveStats(json.stats);
            var selector = '.live_' + json.live_servers_id + "_" + json.key;
            $(selector).slideDown();

            if (typeof onlineLabelOnline == 'function') {
                selector = '#liveViewStatusID_' + json.key + '_' + json.live_servers_id;
                onlineLabelOnline(selector);
                selector = '.liveViewStatusClass_' + json.key + '_' + json.live_servers_id;
                onlineLabelOnline(selector);
            }

            // update the chat if the history changes
            var IframeClass = ".yptchat2IframeClass_" + json.key + "_" + json.live_servers_id;
            if ($(IframeClass).length) {
                var src = $(IframeClass).attr('src');
                if (src) {
                    avideoToast('Loading new chat');
                    var newSRC = addGetParam(src, 'live_transmitions_history_id', json.live_transmitions_history_id);
                    $(IframeClass).attr('src', newSRC);
                }
            }
        }
        function socketLiveOFFCallback(json) {
            console.log('socketLiveOFFCallback', json);
            var selector = '.live_' + json.live_servers_id + "_" + json.key;
            selector += ', .liveVideo_live_' + json.live_servers_id + "_" + json.key;
            selector += ', .live_' + json.key;
            //console.log('socketLiveOFFCallback 1', selector);
            $(selector).slideUp("fast", function () {
                $(this).remove();
            });
            if (typeof onlineLabelOffline == 'function') {
                selector = '#liveViewStatusID_' + json.key + '_' + json.live_servers_id;
                //console.log('socketLiveOFFCallback 2', selector);
                onlineLabelOffline(selector);
                selector = '.liveViewStatusClass_' + json.key + '_' + json.live_servers_id;
                //console.log('socketLiveOFFCallback 3', selector);
                onlineLabelOffline(selector);
                selector = '.liveViewStatusClass_' + json.cleanKey;
                //console.log('socketLiveOFFCallback 3', selector);
                onlineLabelOffline(selector);
            }
            setTimeout(function () {
                processLiveStats(json.stats);
                setTimeout(function () {
                    hideExtraVideosIfEmpty();
                }, 500);
            }, 500);
        }

        function hideExtraVideosIfEmpty() {
            $('#liveScheduleVideos .extraVideos').each(function (index, currentElement) {
                var somethingIsVisible = false;
                $(this).children('div').each(function (index2, currentElement2) {
                    if ($(this).is(":visible")) {
                        somethingIsVisible = true;
                        return false;
                    }
                });
                if (!somethingIsVisible) {
                    $('#liveScheduleVideos').slideUp();
                }
            });
            $('#liveVideos .extraVideos').each(function (index, currentElement) {
                var somethingIsVisible = false;
                $(this).children('div').each(function (index2, currentElement2) {
                    if ($(this).is(":visible")) {
                        somethingIsVisible = true;
                        return false;
                    }
                });
                if (!somethingIsVisible) {
                    $('#liveVideos').slideUp();
                }
            });
        }

        $(document).ready(function () {
            if (!avideoSocketIsActive()) {
                availableLiveStreamIsLoading();
                getStatsMenu(true);
            }
    <?php
    if (AVideoPlugin::isEnabledByName('YPTSocket')) {
        echo 'processLiveStats(' . json_encode(getStatsNotifications()) . ');';
    }
    ?>
        });
    </script>
    <?php
}
?>