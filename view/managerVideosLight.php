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
<html lang="<?php echo getLanguage(); ?>">

<head>
    <title><?php echo __("Edit Video"); ?></title>
    <?php
    include $global['systemRootPath'] . 'view/include/head.php';
    ?>
    <style>
        <?php
        if (!empty($advancedCustom->hideEditAdvancedFromVideosManager)) {
        ?>.command-edit {
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
                <h1 class="pull-left">
                    <?php
                    echo $title;
                    ?>
                </h1>
                <div class="btn-group pull-right">
                    <?php
                    $url = "{$global['webSiteRootURL']}view/managerVideosLight.php";
                    $url = addQueryStringParameter($url, 'avideoIframe', 1);
                    $url = addQueryStringParameter($url, 'videos_id', $videos_id);

                    $assets = array();
                    $assets['image'] = array(
                        'button' => array(
                            'url' => addQueryStringParameter($url, 'image', 1),
                            'label' => "<i class=\"far fa-image\"></i> " . __('Thumbnail')
                        ),
                        'include' => $global['systemRootPath'] . 'view/managerVideosLight_image.php'
                    );
                    $assets['imageTime'] = array(
                        'button' =>array(
                            'url' => addQueryStringParameter($url, 'imageTime', 1),
                            'label' => "<i class=\"far fa-image\"></i> " . __('Thumbnail in video time'),
                        ),
                        'include' => $global['systemRootPath'] . 'view/managerVideosLight_imageTime.php'
                    );
                    $assets['meta'] = array(
                        'button' =>array(
                            'url' => addQueryStringParameter($url, 'image', 0),
                            'label' => "<i class=\"far fa-edit\"></i> " . __('Edit')
                        ),
                        'include' => $global['systemRootPath'] . 'view/managerVideosLight_meta.php'
                    );

                    $buttons = array(
                        'image' => array(
                            $assets['imageTime']['button'],
                            $assets['meta']['button'],
                        ),
                        'imageTime' => array(
                            $assets['image']['button'],
                            $assets['meta']['button'],
                        ),
                        'meta' => array(
                            $assets['imageTime']['button'],
                            $assets['image']['button'],
                        ),
                    );
                    
                    $index = '';
                    if (!empty($_REQUEST['image'])) {
                        $index = 'image';
                    } else if (!empty($_REQUEST['imageTime'])) {                        
                        $index = 'imageTime';
                    } else {
                        $index = 'meta';
                    }

                    foreach ($buttons[$index] as $key => $value) {
                        ?>
                        <a href="<?php echo $value['url']; ?>" class="btn btn-default">
                            <?php
                            echo $value['label']
                            ?>
                        </a>
                        <?php
                    }

                    ?>
                    <a href="<?php echo $global['webSiteRootURL']; ?>mvideos?avideoIframe=1&video_id=<?php echo $videos_id; ?>" class="btn btn-primary command-edit">
                        <i class="far fa-edit"></i> <?php echo __('Advanced'); ?>
                    </a>
                </div>
            </div>
            <div class="panel-body">
                <?php
                include $assets[$index]['include'];
                ?>
            </div>
        </div>
    </div><!--/.container-->
    <?php
    include $global['systemRootPath'] . 'view/include/footer.php';
    ?>
</body>

</html>