<link href="<?php echo $global['webSiteRootURL']; ?>css/font-awesome-animation.min.css" rel="stylesheet" type="text/css"/>
<style>
.liveVideo{
    position: relative;
    border: 2px solid red;
    border-radius: 5px;
}
.liveVideo .liveNow{
    position: absolute;
    bottom: 5px;
    right: 5px;
    background-color: rgba(255,0,0,0.5);
}
</style>
<?php
if (User::canUpload()) {
    ?>
    <li>
        <a href="<?php echo $global['webSiteRootURL']; ?>plugin/Live"  class="btn btn-danger navbar-btn pull-left" data-toggle="tooltip" title="<?php echo __("Broadcast a Live Streaming"); ?>" data-placement="bottom" >
            <span class="fa fa-circle"></span> <?php echo $buttonTitle; ?>
        </a>
    </li>
    <?php
}
?>
<li class="dropdown">
    <a href="#" class=" btn btn-default navbar-btn" data-toggle="dropdown">
        <span class="fa fa-bell"></span> 
        <span class="badge onlineApplications" style=" background: rgba(255,0,0,1); color: #FFF;">0</span></span>
</a>
<ul class="dropdown-menu notify-drop" id="availableLive" style="left: -100%;"></ul>
</li>
<a href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/" class='btn btn-default btn-xs btn-block liveLink hidden liveModel'>
    <div class="pull-left">
        <img src="" class="img img-circle img-responsive" style="max-width: 38px;">
    </div>
    <div style="margin-left: 40px;">
        <i class="fa fa-video-camera"></i> <strong class="liveTitle">Title</strong> <br>
        <span class="label label-success liveUser">User</span> <span class="badge">is live</span>
    </div>
</a>

<div class="col-lg-12 col-sm-12 col-xs-12 bottom-border hidden extraVideosModel liveVideo" itemscope itemtype="http://schema.org/VideoObject">
    <a href="" class="videoLink">
        <div class="col-lg-5 col-sm-5 col-xs-5 nopadding thumbsImage" >
            <img src="https://demo.youphptube.com/plugin/Live/getImage.php?u=danielneto.com@gmail.com&format=jpg" class="thumbsJPG img-responsive" height="130" />
            <img src="https://demo.youphptube.com/plugin/Live/getImage.php?u=danielneto.com@gmail.com&format=gif" style="position: absolute; top: 0; display: none;" class="thumbsGIF img-responsive" height="130" />
            <span class="label label-danger liveNow faa-flash faa-slow animated">LIVE NOW</span>
        </div>
        <div class="col-lg-7 col-sm-7 col-xs-7 videosDetails">
            <div class="text-uppercase row"><strong itemprop="name" class="title liveTitle">Title</strong></div>
            <div class="details row" itemprop="description">
                <div class="pull-left">
                    <img src="" class="photoImg img img-circle img-responsive" style="max-width: 38px;">
                </div>
                <div style="margin-left: 50px;">
                    <div class="liveUser">User</div>
                </div>
            </div>
        </div>
    </a>
</div>
<script>

    function createLiveItem(href, title, name, photo, offline) {
        var $liveLi = $('.liveModel').clone();
        if (offline) {
            $liveLi.find('.fa-video-camera').removeClass("fa-video-camera").addClass("fa-ban");
            $liveLi.find('.liveUser').removeClass("label-success").addClass("label-danger");
            $liveLi.find('.badge').text("offline");
        }
        $liveLi.removeClass("hidden").removeClass("liveModel");
        $liveLi.attr("href", href);
        $liveLi.find('.liveTitle').text(title);
        $liveLi.find('.liveUser').text(name);
        $liveLi.find('.img').attr("src", photo);
        $('#availableLive').append($liveLi);
    }

    function createExtraVideos(href, title, name, photo, user) {
        var id = 'extraVideo' + user;
        id = id.replace(/\W/g, '');
        if ($("#" + id).length == 0) {
            var $liveLi = $('.extraVideosModel').clone();
            $liveLi.removeClass("hidden").removeClass("extraVideosModel");
            $liveLi.css({'display':'none'})
            $liveLi.attr('id', id);
            $liveLi.find('.videoLink').attr("href", href);
            $liveLi.find('.liveTitle').text(title);
            $liveLi.find('.liveUser').text(name);
            $liveLi.find('.photoImg').attr("src", photo);
            $liveLi.find('.thumbsJPG').attr("src", "<?php echo $global['webSiteRootURL']; ?>plugin/Live/getImage.php?u=" + user + "&format=png");
            $liveLi.find('.thumbsGIF').attr("src", "<?php echo $global['webSiteRootURL']; ?>plugin/Live/getImage.php?u=" + user + "&format=gif");
            $('.extraVideos').append($liveLi);
            $liveLi.slideDown();
        }
    }

    function getStatsMenu() {
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/Live/stats.json.php?Menu',
            success: function (response) {
                $('.onlineApplications').text(response.applications.length);
                $('#availableLive').empty();
                if (response.applications.length) {
                    for (i = 0; i < response.applications.length; i++) {
                        href = "<?php echo $global['webSiteRootURL']; ?>plugin/Live/?u=" + response.applications[i].user;
                        title = response.applications[i].title;
                        name = response.applications[i].name;
                        user = response.applications[i].user;
                        photo = response.applications[i].photo;
                        createLiveItem(href, title, name, "<?php echo $global['webSiteRootURL']; ?>" + photo, false);
                        createExtraVideos(href, title, name, "<?php echo $global['webSiteRootURL']; ?>" + photo, user);
                    }
                    mouseEffect();
                } else {
                    createLiveItem("#", "There is no streaming now", "", "", true);
                }
                setTimeout(function () {
                    getStatsMenu();
                }, 2000);
            }
        });
    }

    $(document).ready(function () {
        getStatsMenu();
    });
</script>