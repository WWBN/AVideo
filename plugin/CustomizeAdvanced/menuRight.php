<li class="dropdown">
    <a href="#" class=" btn btn-default navbar-btn" data-toggle="dropdown">
        <?php echo __("R Rating"); ?>
        <b class="caret"></b>
    </a>
    <ul class="dropdown-menu dropdown-menu-right">
        <li class=""  style="margin-right: 0;">
            <?php
            foreach (Video::$rratingOptions as $value) {
                if (empty($value)) {
                    $label = __("Not Rated");
                } else {
                    $label = strtoupper($value);
                }
                ?>
                <a href="<?php echo $global['webSiteRootURL']; ?>?rrating=<?php echo $value; ?>">
                    <?php echo "Label"; ?>
                </a>    
                <?php
            }
            ?>

        </li>
    </ul>
</li>
