<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$videos_id = @$_REQUEST['videos_id'];

if (empty($videos_id)) {
    forbiddenPage('Videos ID empty');
}

if (!Video::canEdit($videos_id)) {
    forbiddenPage('You cannot edit this video');
}

$video = new Video('', '', $videos_id);
$title = $video->getTitle();
$description = $video->getDescription();
$categories_id = $video->getCategories_id();
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo __("Edit Video"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            <?php
            if (!empty($advancedCustom->hideEditAdvancedFromVideosManager)) {
                ?>
                    .command-edit{
                        display: none !important;
                    }
                <?php
            }
            ?>
        </style>
    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <div class="panel panel-default ">
                <div class="panel-heading clearfix ">
                    <h1 class="pull-left" >
                        <?php
                        echo $title;
                        ?>
                    </h1>
                    <div class="btn-group pull-right">
                        <a href="<?php echo $global['webSiteRootURL']; ?>view/managerVideosLight.php?image=<?php echo empty($_REQUEST['image']) ? 1 : 0; ?>&avideoIframe=1&videos_id=<?php echo $videos_id; ?>" class="btn btn-default">
                            <?php
                            if (empty($_REQUEST['image'])) {
                                echo "<i class=\"far fa-image\"></i> " . __('Thumbnail');
                            } else {
                                echo "<i class=\"far fa-edit\"></i> " . __('Edit');
                            }
                            ?>
                        </a>
                        <a href="<?php echo $global['webSiteRootURL']; ?>mvideos?avideoIframe=1&video_id=<?php echo $videos_id; ?>" class="btn btn-primary command-edit">
                            <i class="far fa-edit"></i> <?php echo __('Advanced'); ?>
                        </a>
                    </div>
                </div>
                <div class="panel-body">
                    <?php
                    if (empty($_REQUEST['image'])) {
                        include $global['systemRootPath'] . 'view/managerVideosLight_meta.php';
                    } else {
                        include $global['systemRootPath'] . 'view/managerVideosLight_image.php';
                    }
                    ?>
                </div>
            </div>
        </div><!--/.container-->
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
