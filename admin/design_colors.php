<div class="panel panel-default">
    <div class="panel-heading"><?php echo __('Customize Your site colors'); ?> <div class="pull-right"><?php echo getPluginSwitch('Customize'); ?></div></div>
    <div class="panel-body" style="padding: 15px 30px;">
        <?php
        if (!AVideoPlugin::exists('Customize')) {
            ?>
            <center>
                <img src="<?php echo $global['webSiteRootURL'], "view/css/custom/customize.png"; ?>" class="img-responsive">
            </center>
            <div class="alert alert-info">
                <?php echo __('Truly customize your AVideo and create a more professional video sharing site experience for your visitors by removing or replacing the footer, about page and Meta Description with your own.'); ?>
                <a class="btn btn-info btn-sm btn-xs" href="https://youphp.tube/plugins/"><?php echo __('Buy the Customize plugin now'); ?></a>
            </div>
            <?php
        } else {
            include '../plugin/Customize/page/colors.php';
        }
        ?>
    </div>
</div>
