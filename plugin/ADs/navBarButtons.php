<?php
if (ADs::canHaveCustomAds()) {
    ?>
    <li>
        <div>
            <a href="#"  
               class="btn btn-default btn-block"   
               style="border-radius: 0;"
               onclick="avideoModalIframeFull(webSiteRootURL+'plugin/ADs/editor.php?customAds=1');return false;">
                <i class="fas fa-image"></i>
                <span class="menuLabel">
                <?php echo __('Custom ADs'); ?>
                </span>
            </a>
        </div>
    </li>      
    <?php
}