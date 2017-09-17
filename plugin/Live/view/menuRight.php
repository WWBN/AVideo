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
    <a href="#" class=" btn btn-default navbar-btn" data-toggle="dropdown"><span class="fa fa-bell"></span> <span class="badge onlineApplications">0</span></a>
    <ul class="dropdown-menu notify-drop" id="availableLive" style="left: -100%;">
        
    </ul>
</li>
<a href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/" class='btn btn-default btn-xs btn-block liveLink hidden liveModel'>
        <i class="fa fa-video-camera"></i> <strong class="liveTitle">Title</strong> <br>
        <span class="label label-success liveUser">User</span> <span class="badge">is live</span>
    </a>
<script>
    function getStatsMenu() {
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/Live/stats.json.php',
            success: function (response) {
                $('.liveViewCount').text(" " + response.nclients);
                $('.onlineApplications').text(response.applications.length);
                $('#availableLive').empty();
                for (i = 0; i < response.applications.length; i++) {
                    var $liveLi = $('.liveModel').clone();
                    $liveLi.removeClass("hidden").removeClass("liveModel");
                    $liveLi.attr("href", "<?php echo $global['webSiteRootURL']; ?>plugin/Live/?u="+response.applications[i].user);   
                    $liveLi.find('.liveTitle').text(response.applications[i].title);
                    $liveLi.find('.liveUser').text(response.applications[i].name);
                    
                    $('#availableLive').append($liveLi);
                }
                setTimeout(function () {
                    getStatsMenu();
                }, 20000);
            }
        });
    }

    $(document).ready(function () {
        getStatsMenu();
    });
</script>