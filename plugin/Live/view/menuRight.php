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
                        photo = response.applications[i].photo;
                        createLiveItem(href, title, name, "<?php echo $global['webSiteRootURL']; ?>"+photo, false);
                    }
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