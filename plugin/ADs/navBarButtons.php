<?php
if (VideoOffline::canDownloadVideo()) {
    ?>
    <li>
        <div>
            <a href="#"  
               class="btn btn-warning btn-block"   
               style="border-radius: 0;"
               onclick="avideoModalIframeFull(webSiteRootURL+'offline');return false;">
                <i class="fas fa-play-circle"></i> <?php echo __('Offline Videos'); ?>
            </a>
        </div>
    </li>      
    <?php
}