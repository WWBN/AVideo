<link href="<?php echo $global['webSiteRootURL']; ?>view/css/font-awesome-animation.min.css" rel="stylesheet" type="text/css"/>
<style>
    .liveVideo{
        position: relative;
    }
    .liveVideo .liveNow{
        position: absolute;
        bottom: 5px;
        right: 5px;
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
        <a href="<?php echo $global['webSiteRootURL']; ?>plugin/Live"  class="btn btn-danger navbar-btn" data-toggle="tooltip" title="<?php echo __("Broadcast a Live Stream"); ?>" data-placement="bottom" >
            <span class="fa fa-circle"></span>  <span class="hidden-md hidden-sm hidden-mdx"><?php echo $buttonTitle; ?></span>
        </a>
    </li>
    <?php
}
?>
<li class="dropdown">
    <a href="#" class=" btn btn-default navbar-btn" data-toggle="dropdown">
        <span class="fa fa-bell"></span>
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
        <div style="margin-left: 40px;">
            <i class="fas fa-video"></i> <strong class="liveTitle"><?php echo __("Title"); ?></strong> <br>
            <span class="label label-success liveUser"><?php echo __("User"); ?></span> <span class="label label-danger liveNow faa-flash faa-slow animated hidden"><?php echo __("LIVE NOW"); ?></span>
        </div>
    </a>
</li>
<div class="col-lg-12 col-sm-12 col-xs-12 bottom-border hidden extraVideosModel liveVideo">
    <a href="" class="h6 videoLink">
        <div class="col-lg-5 col-sm-5 col-xs-5 nopadding thumbsImage" style="min-height: 70px; position:relative;" >
            <img src="<?php echo $global['webSiteRootURL']; ?>videos/userPhoto/logo.png" class="thumbsJPG img-responsive" height="130" itemprop="thumbnailUrl" alt="Logo" />
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
            <?php
            require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
            // the live users plugin
            $lu = AVideoPlugin::getObjectDataIfEnabled("LiveUsers");
            if (!empty($lu) && !$lu->doNotDisplayCounter) {
                ?>
                <span class="label label-primary"  data-toggle="tooltip" title="<?php echo __("Watching Now"); ?>" data-placement="bottom" ><i class="fa fa-user"></i> <b class="liveUsersOnline">0</b></span>
                <span class="label label-default"  data-toggle="tooltip" title="<?php echo __("Total Views"); ?>" data-placement="bottom" ><i class="fa fa-eye"></i> <b class="liveUsersViews">0</b></span>
                <?php
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

    function createLiveItem(href, title, name, photo, offline, online, views, key, isPrivate) {
        var $liveLi = $('.liveModel').clone();
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
        $('#availableLiveStream').append($liveLi);

        if (href != "#") {
            $liveLi.find('.liveNow').removeClass("hidden");
        }

        $('.liveUsersOnline_' + key).text(online);
        $('.liveUsersViews_' + key).text(views);
    }
    var limitLiveOnVideosListCount = 0;
    function createExtraVideos(href, title, name, photo, user, online, views, key, disableGif, live_servers_id) {
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
        id = id.replace(/\W/g, '');
        if ($(".extraVideos").length && $("#" + id).length == 0) {
            var $liveLi = $('.extraVideosModel').clone();
            $liveLi.removeClass("hidden").removeClass("extraVideosModel");
            $liveLi.css({'display': 'none'})
            $liveLi.attr('id', id);
            $liveLi.find('.videoLink').attr("href", href);
            $liveLi.find('.liveTitle').text(title);
            $liveLi.find('.liveUser').text(name);
            $liveLi.find('.photoImg').attr("src", photo);
            $liveLi.find('.liveUsersOnline').text(online);
            $liveLi.find('.liveUsersViews').text(views);
            $liveLi.find('.liveUsersOnline').addClass("liveUsersOnline_" + key);
            $liveLi.find('.liveUsersViews').addClass("liveUsersViews_" + key);
            $liveLi.find('.thumbsJPG').attr("src", "<?php echo $global['webSiteRootURL']; ?>plugin/Live/getImage.php?live_servers_id=" + live_servers_id + "&u=" + user + "&format=jpg" + playlists_id_live+'&'+Math.random());
            if (!disableGif) {
                $liveLi.find('.thumbsGIF').attr("src", "<?php echo $global['webSiteRootURL']; ?>plugin/Live/getImage.php?live_servers_id=" + live_servers_id + "&u=" + user + "&format=gif" + playlists_id_live+'&'+Math.random());
            } else {
                $liveLi.find('.thumbsGIF').remove();
            }
            $liveLi = afterExtraVideos($liveLi);
            $('.extraVideos').append($liveLi);
            $liveLi.slideDown();
        }
    }

    function getStatsMenu(recurrentCall) {
        availableLiveStreamIsLoading();
        $.ajax({
            url: webSiteRootURL + 'plugin/Live/stats.json.php?Menu<?php echo (!empty($_GET['videoName']) ? "&requestComesFromVideoPage=1" : "") ?>',
            success: function (response) {
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
                    if (!response.total) {
                        availableLiveStreamNotFound();
                    } else {
                        $('#availableLiveStream').removeClass('notfound');
                    }
                    $('.onlineApplications').text(response.total);
                }
                if (recurrentCall) {
                    setTimeout(function () {
                        getStatsMenu(true);
                    }, <?php echo $obj->requestStatsInterval * 1000; ?>);
                }
            }
        });
    }

    function processApplicationLive(response) {
        if (typeof response.applications !== 'undefined') {
            if (response.applications.length) {
                disableGif = response.disableGif;
                for (i = 0; i < response.applications.length; i++) {
                    processApplication(response.applications[i], disableGif, 0);
                }
                mouseEffect();
            }
        }
        // check for live servers
        var count = 0;
        while (typeof response[count] !== 'undefined') {
            for (i = 0; i < response[count].applications.length; i++) {
                disableGif = response[count].disableGif;
                processApplication(response[count].applications[i], disableGif, response[count].live_servers_id);
            }
            count++;
        }
    }

    function availableLiveStreamIsLoading() {
        if ($('#availableLiveStream').hasClass('notfound')) {
            $('#availableLiveStream').empty();
            createLiveItem("#", "<?php echo __("Please Wait, we are checking the lives"); ?>", "", "", true, false);
            $('#availableLiveStream').find('.fa-ban').removeClass("fa-ban").addClass("fa-sync fa-spin");
            $('#availableLiveStream').find('.liveLink div').attr('style', '');
        }
    }

    function availableLiveStreamNotFound() {
        $('#availableLiveStream').addClass('notfound');
        $('#availableLiveStream').empty();
        createLiveItem("#", "<?php echo __("There is no streaming now"); ?>", "", "", true, false);
        $('#availableLiveStream').find('.liveLink div').attr('style', '');
    }

    function processApplication(application, disableGif, live_servers_id) {
        if (typeof application.html != 'undefined') {
            $('#availableLiveStream').append(application.html);
            if (typeof application.htmlExtra != 'undefined') {
                var id = $(application.htmlExtra).attr('id');
                if (loadedExtraVideos.indexOf(id) == -1) {
                    loadedExtraVideos.push(id)
<?php
if (isLive()) {
    ?>
                        $('.extraVideos').append(application.htmlExtraVideoPage);
    <?php
} else {
    ?>
                        $('.extraVideos').append(application.htmlExtra);
    <?php
}
?>

                }
            }
            $('#liveVideos').slideDown();
        } else {
            //href = "<?php echo $global['webSiteRootURL']; ?>plugin/Live/?live_servers_id=" + live_servers_id + "&c=" + application.channelName;
            href = application.href;
            title = application.title;
            name = application.name;
            user = application.user;
            photo = application.photo;
            online = application.users.online;
            views = application.users.views;
            key = application.key;
            live_servers_id = live_servers_id;
            isPrivate = application.isPrivate;

            createLiveItem(href, title, name, photo, false, online, views, key, isPrivate);
<?php
if (empty($obj->doNotShowLiveOnVideosList)) {
    ?>
                createExtraVideos(href, title, name, photo, user, online, views, key, disableGif, live_servers_id);
    <?php
}
?>
        }
    }

    $(document).ready(function () {
        availableLiveStreamIsLoading();
        getStatsMenu(true);
    });
</script>
