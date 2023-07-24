<?php
if (User::isLogged()) {
    ?>
    <li>
        <div>
            <button onclick="avideoModalIframe(webSiteRootURL + 'plugin/VideosStatistics/history.php');" class="btn btn-default btn-block"   style="border-radius: 0;">
                <i class="fas fa-history"></i>
                <?php echo __("Videos History"); ?>
            </button>
        </div>
    </li>      
    <?php
}
?>
