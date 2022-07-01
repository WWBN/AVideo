<?php
$obj = AVideoPlugin::getObjectDataIfEnabled('Subscription');
?>
<li>
    <hr>
    <strong class="text-danger">
        <i class="fas fa-filter"></i> <?php echo __("R Rating"); ?>
    </strong>
    <ul style="margin: 0; padding-left: 15px; list-style-type: none;">
        <?php
        foreach (Video::$rratingOptions as $value) {
            if (empty($value)) {
                $label = __("Not Rated");
                $value = 0;
            } else {
                $label = strtoupper($value);
            }
            ?>
            <li>
                <a href="<?php echo $global['webSiteRootURL']; ?>?rrating=<?php echo $value; ?>">
                    <?php echo $label; ?>
                </a>  
            </li>  
            <?php
        }
        ?>
    </ul>
</li>     



