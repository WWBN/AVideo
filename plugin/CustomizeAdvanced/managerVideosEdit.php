<?php
if (Permissions::canAdminVideos()) {
    ?>
    <br>
    <div class="clearfix"></div>
    <ul class="list-group">
        <li class="list-group-item"> 
            <i class="fas fa-photo-video"></i>
            <?php echo __('Do NOT Show Video Ads on this video'); ?>  
            <div class="material-switch pull-right">
                <input id="doNotShowAdsOnThisVideo" type="checkbox" value="">
                <label for="doNotShowAdsOnThisVideo" class="label-danger"></label>
            </div>
        </li>
    </ul>
    <?php
}
?>