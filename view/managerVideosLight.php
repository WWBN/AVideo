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
    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php
                    if(empty($_REQUEST['image'])){
                        include $global['systemRootPath'] . 'view/managerVideosLight_meta.php';
                    }else{
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
