<?php
$obj = AVideoPlugin::getDataObject("Live");
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
    .liveVideo .liveNow{
        background-color: rgba(255,0,0,0.7);
    }
    #availableLiveStream{
        max-width: 400px;
        overflow: hidden;
        max-height: 75vh;
        overflow-y: auto;
    }
    #availableLiveStream li a div{
        overflow: hidden;
    }
</style>
<?php
if (empty($obj->doNotShowGoLiveButton) && User::canStream()) {
    ?>
    <li>
        <a href="<?php echo $global['webSiteRootURL']; ?>plugin/Live"  class="faa-parent animated-hover btn btn-danger navbar-btn" data-toggle="tooltip" title="<?php echo __("Broadcast a Live Stream"); ?>" data-placement="bottom" >
            <span class="fa fa-circle faa-flash"></span>  <span class="hidden-md hidden-sm hidden-mdx"><?php echo __($buttonTitle); ?></span>
        </a>
    </li>
    <?php
}
?>
<li class="dropdown">
    <a href="#" class="faa-parent animated-hover btn btn-default navbar-btn" data-toggle="dropdown">
        <span class="fas fa-bell faa-ring"></span>
        <span class="badge onlineApplications" style=" background: rgba(255,0,0,1); color: #FFF;">0</span>
        <b class="caret"></b>
    </a>
    <ul class="dropdown-menu dropdown-menu-right notify-drop" id="availableLiveStream"></ul>
</li>
<li class="hidden liveModel"  style="margin-right: 0;">
    <a href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/" class='liveLink '>
        <div class="pull-left">
            <img src="" class="img img-circle img-responsive" style="max-width: 38px;">
        </div>
        <div style="margin-left: 40px;white-space: nowrap;
             overflow: hidden;
             text-overflow: ellipsis;">
            <i class="fas fa-video"></i> <strong class="liveTitle"><?php echo __("Title"); ?></strong> <br>
            <span class="label label-success liveUser"><?php echo __("User"); ?></span> <span class="label label-danger liveNow faa-flash faa-slow animated hidden"><?php echo __("LIVE NOW"); ?></span>
        </div>
    </a>
</li>
<div class="col-lg-12 col-sm-12 col-xs-12 bottom-border hidden extraVideosModel liveVideo">
    <a href="" class="h6 videoLink">
        <div class="col-lg-5 col-sm-5 col-xs-5 nopadding thumbsImage" style="min-height: 70px; position:relative;" >
            <img src="<?php echo getCDN(); ?>videos/userPhoto/logo.png" class="thumbsJPG img-responsive" height="130" itemprop="thumbnailUrl" alt="Logo" />
            <span itemprop="uploadDate" content="<?php echo date("Y-m-d h:i:s"); ?>" />
            <img src="" style="position: absolute; top: 0; display: none;" class="thumbsGIF img-responsive" height="130" />
            <span class="label label-danger liveNow faa-flash faa-slow animated"><?php echo __("LIVE NOW"); ?></span>
        </div>
        <div class="col-lg-7 col-sm-7 col-xs-7 videosDetails">
            <div class="text-uppercase row"><strong itemprop="name" class="title liveTitle"><?php echo __("Title"); ?></strong></div>
            <div class="details row" itemprop="description">
                <div class="pull-left">
                    <img src="" class="photoImg img img-circle img-responsive" style="max-width: 20px;">
                </div>
                <div style="margin-left: 25px;">
                    <div class="liveUser"><?php echo __("User"); ?></div>
                </div>
            </div>
            <div class="clearfix"></div>
            <?php
            if (AVideoPlugin::isEnabledByName("LiveUsers") && method_exists("LiveUsers", "getLabels")) {
                echo LiveUsers::getLabels('extraVideosModelOnLineLabels');
            }
            ?>
        </div>
    </a>
</div>
<script>
    var loadedExtraVideos = [];
    /* Use this funtion to display live videos dynamic on pages*/
    function afterExtraVideos($liveLi) {
        return $liveLi
    }

    function createLiveItem(href, title, name, photo, offline, online, views, key, isPrivate, callback) {
        var $liveLi = $('.liveModel').clone();
        $($liveLi).find('a').removeClass('linksToFullscreen');
        if (offline) {
            $liveLi.find('.fa-video').removeClass("fa-video").addClass("fa-ban");
            $liveLi.find('.liveUser').removeClass("label-success").addClass("label-danger");
            $liveLi.find('.badge').text("offline");
            //$('#mainVideo.liveVideo').find('.vjs-poster').css({'background-image': 'url(<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Offline.jpg)'});
        } else {
            //$('#mainVideo.liveVideo').find('.vjs-poster').css({'background-image': 'url(<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/OnAir.jpg)'});
        }
        $liveLi.removeClass("hidden").removeClass("liveModel");
        if (isPrivate) {
            $liveLi.find('.fa-video').removeClass('fa-video').addClass('fa-lock');
        }
        $liveLi.find('a').attr("href", href);
        $liveLi.find('.liveTitle').text(title);
        $liveLi.find('.liveUser').text(name);
        $liveLi.find('.img').attr("src", photo);

        if (typeof callback == 'string' && callback) {
            eval("try {console.log('createLiveItem application.callback');$liveLi = " + callback + ";} catch (e) {console.log('createLiveItem application.callback error',e.message);}");
        }

        $('#availableLiveStream').append($liveLi);

        if (href != "#") {
            $liveLi.find('.liveNow').removeClass("hidden");
        }
        if (!avideoSocketIsActive()) {
            $('.liveUsersOnline_' + key).text(online);
            $('.liveUsersViews_' + key).text(views);
        }

    }
    var limitLiveOnVideosListCount = 0;
    function createExtraVideos(href, title, name, photo, user, online, views, key, disableGif, live_servers_id, live_index) {
        if (typeof key !== 'string') {
            return false;
        }
        $('#liveVideos').slideDown();
        limitLiveOnVideosListCount++;
        if (limitLiveOnVideosListCount ><?php echo intval($obj->limitLiveOnVideosList); ?>) {
            console.log("Max live videos on first page reached");
            return false;
        }

        var matches = key.match(/.*_([0-9]+)/);
        var playlists_id_live = "";
        if (matches && matches[1]) {
            playlists_id_live = "&playlists_id_live=" + matches[1];
        }

        var id = 'extraVideo' + user + "_" + live_servers_id + "_" + key;
        var _class = 'live' + "_" + live_servers_id + "_" + key;
        id = id.replace(/\W/g, '');
        if ($(".extraVideos").length && $("#" + id).length == 0) {
            var $liveLi = $('.extraVideosModel').clone();
            $($liveLi).find('a').removeClass('linksToFullscreen');
            $liveLi.removeClass("hidden").removeClass("extraVideosModel");

            var counterClassName = "total_on_live_" + key + "_" + live_servers_id;
            $liveLi.find('.extraVideosModelOnLineLabels').addClass(counterClassName);
            $liveLi.find('.views_on_extraVideosModelOnLineLabels').addClass('views_on_' + counterClassName);

            $liveLi.css({'display': 'none'})
            $liveLi.attr('id', id);
            $liveLi.addClass(_class);
            $liveLi.find('.videoLink').attr("href", href);
            $liveLi.find('.liveTitle').text(title);
            $liveLi.find('.liveUser').text(name);
            $liveLi.find('.photoImg').attr("src", photo);
            if (!avideoSocketIsActive()) {
                $liveLi.find('.liveUsersOnline').text(online);
                $liveLi.find('.liveUsersViews').text(views);
                $liveLi.find('.liveUsersOnline').addClass("liveUsersOnline_" + key);
                $liveLi.find('.liveUsersViews').addClass("liveUsersViews_" + key);
            }

            var getImageURL = webSiteRootURL + "plugin/Live/getImage.php?live_servers_id=" + live_servers_id + "&live_index=" + live_index + "&u=" + user + playlists_id_live;

            $liveLi.find('.thumbsJPG').attr("src", getImageURL + "&format=jpg" + ('&' + Math.random()));
            if (!disableGif) {
                $liveLi.find('.thumbsGIF').attr("src", getImageURL + "&format=webp" + ('&' + Math.random()));
            } else {
                $liveLi.find('.thumbsGIF').remove();
            }
            $liveLi = afterExtraVideos($liveLi);
            $('.extraVideos').append($liveLi);
            $liveLi.slideDown();
            setTimeout(function () {
                refreshGetLiveImage("#" + id)
            }, 5000);
        } else if ($("#" + id).length) {
            refreshGetLiveImage("#" + id);
        }
    }

    function refreshGetLiveImage(selector) {
        $(selector).find('.thumbsImage img').each(function (index) {
            $(this).attr('src', $(this).attr('src') + ('&' + Math.random()));
        });
        setTimeout(function () {
            $(selector).slideDown();
        }, 1000); // give some time to load the new images
    }

    function processLiveStats(response) {
        //console.log('processLiveStats', response);
        limitLiveOnVideosListCount = 0;
        if (typeof response !== 'undefined') {
            $('#availableLiveStream').empty();
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
                disableGif = response.disableGif;
                for (i = 0; i < response.applications.length; i++) {
                    //console.log('processApplicationLive', response.applications[i]);
                    var live_index = 0;
                    if (typeof response.applications[i].live_index !== 'undefined') {
                        live_index = response.applications[i].live_index;
                    }
                    var live_servers_id = 0;
                    if (typeof response.applications[i].live_servers_id !== 'undefined') {
                        live_servers_id = response.applications[i].live_servers_id;
                    }
                    processApplication(response.applications[i], disableGif, live_servers_id, live_index);
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
                disableGif = response[count].disableGif;
                processApplication(response[count].applications[i], disableGif, response[count].live_servers_id, response[count].live_index);
            }
            count++;
        }
    }

    function availableLiveStreamIsLoading() {
        if ($('#availableLiveStream').hasClass('notfound')) {
            $('#availableLiveStream').empty();
            createLiveItem("#", "<?php echo __("Please Wait, we are checking the lives"); ?>", "", "", true, false, '');
            $('#availableLiveStream').find('.fa-ban').removeClass("fa-ban").addClass("fa-sync fa-spin");
            $('#availableLiveStream').find('.liveLink div').attr('style', '');
        }
    }

    function availableLiveStreamNotFound() {
        $('#availableLiveStream').addClass('notfound');
        $('#availableLiveStream').empty();
        createLiveItem("#", "<?php echo __("There is no streaming now"); ?>", "", "", true, false, '');
        $('#availableLiveStream').find('.liveLink div').attr('style', '');
    }

    function processApplication(application, disableGif, live_servers_id, live_index) {

        href = application.href;
        title = application.title;
        name = application.name;
        user = application.user;
        photo = application.photo;
        key = application.key;
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
            $('#availableLiveStream').append(application.html);
            if (typeof application.htmlExtra != 'undefined') {
                var id = $(application.htmlExtra).attr('id');
                if (loadedExtraVideos.indexOf(id) == -1) {
                    loadedExtraVideos.push(id)
<?php
if (isVideo()) {
    ?>
                        $('.extraVideos').append(application.htmlExtraVideoListItem);
    <?php
} else if (isLive()) {
    ?>
                        $('.extraVideos').append(application.htmlExtraVideoPage);
    <?php
} else {
    ?>
                        $('.extraVideos').append(application.htmlExtra);
    <?php
}
?>

                } else {
                    $('#' + id).slideDown();
                }
            }
            $('#liveVideos').slideDown();
            if (callback) {
                eval("try {$liveLi = " + callback + ";} catch (e) {console.log('processApplication application.callback error',e.message);}");
            }
        } else {

            createLiveItem(href, title, name, photo, false, online, views, key, isPrivate, callback);
<?php
if (empty($obj->doNotShowLiveOnVideosList)) {
    ?>
                createExtraVideos(href, title, name, photo, user, online, views, key, disableGif, live_servers_id, live_index);
    <?php
}
?>
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
        if(application.users && typeof application.users.views !== 'undefined'){
            $('.views_on_total_on_live_'+application.users.transmition_key+'_'+application.users.live_servers_id).text(application.users.views);
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
        processLiveStats(json.stats);
        var selector = '.live_' + json.live_servers_id + "_" + json.key;
        //console.log('socketLiveOFFCallback 1', selector);
        $(selector).slideUp();
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
        setTimeout(function(){hideExtraVideosIfEmpty();},500);
    }

    function hideExtraVideosIfEmpty() {
        $('.extraVideos').each(function (index, currentElement) {
            var somethingIsVisible = false;
            $(this).children('div').each(function (index2, currentElement2) {
                if($(this).is(":visible")){
                    somethingIsVisible = true;
                    return false;
                }
            });
            if(!somethingIsVisible){
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
