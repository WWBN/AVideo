<?php
if (User::isLogged()) {
?>
    <li>
        <div>
            <button onclick="avideoModalIframe(webSiteRootURL + 'plugin/VideosStatistics/history.php');" class="btn btn-default btn-block" style="border-radius: 0;">
                <i class="fas fa-history"></i>
                <span class="menuLabel">
                    <?php echo __("Videos History"); ?>
                </span>
            </button>
        </div>
    </li>
<?php
}
?>