<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
$metaDescription = "About Page";

$page = new Page('About', 'about');
?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-body">
            <?php
            $custom = '';
            if (AVideoPlugin::isEnabledByName("Customize")) {
                require_once $global['systemRootPath'] . 'plugin/Customize/Objects/ExtraConfig.php';
                $ec = new ExtraConfig();
                $custom = $ec->getAbout();
            }
            if (empty($custom)) {
            ?>
                <h1><?php echo __("I would humbly like to thank God for giving me the necessary knowledge, motivation, resources and idea to be able to execute this project. Without God's permission this would never be possible."); ?></h1>
                <blockquote class="blockquote">
                    <h1><?php echo __("For of Him, and through Him, and to Him, are all things: to whom be glory for ever. Amen."); ?></h1>
                    <footer class="blockquote-footer"><?php echo __("Apostle Paul in"); ?> <cite title="Source Title"><?php echo __("Romans 11:36"); ?></cite></footer>
                </blockquote>
                <div class="clearfix"></div>
                <span class="label label-success"><?php printf(__("You are running AVideo version %s!"), $config->getVersion()); ?></span>

                <span class="label label-success">
                    <?php printf(__("You can upload max of %s!"), get_max_file_size()); ?>
                </span>
                <span class="label label-success">
                    <?php printf(__("You have %s minutes of videos!"), number_format(getSecondsTotalVideosLength() / 6, 2)); ?>
                </span>
                <div class="clearfix"></div>
                <span class="label label-info">
                    <?php echo __("You are using"); ?>: <?php echo getUserAgentInfo(); ?> (<?php echo isMobile() ? __("Mobile") : __("PC"); ?>)
                </span>
            <?php
            } else {
                echo $custom;
            }
            ?>
        </div>
    </div>

</div>
<?php
$page->print();
?>