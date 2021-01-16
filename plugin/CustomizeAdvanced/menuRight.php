<li class="dropdown"  data-toggle="tooltip" title="<?php echo __("R Rating"); ?>" data-placement="left">
    <a href="#" class=" btn btn-default dropdown-toggle   navbar-btn" data-toggle="dropdown">
        <i class="fas fa-filter"></i>
        <b class="caret"></b>
    </a>
    <ul class="dropdown-menu">
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