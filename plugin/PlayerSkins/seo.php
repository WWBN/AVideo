<?php
global $advancedCustom, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
$videos_id = getVideos_id();
if (empty($videos_id)) {
    return '';
}
$video = new Video('', '', $videos_id);
$keywords = strip_tags($advancedCustom->keywords);
$relatedVideos = Video::getRelatedMovies($videos_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="device_id" content="<?php echo getDeviceID(); ?>">
    <meta name="keywords" content=<?php printJSString($keywords); ?>>
    <link rel="manifest" href="<?php echo $global['webSiteRootURL']; ?>manifest.json">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $config->getFavicon(true); ?>">
    <link rel="icon" type="image/png" href="<?php echo $config->getFavicon(true); ?>">
    <link rel="shortcut icon" href="<?php echo $config->getFavicon(); ?>" sizes="16x16,24x24,32x32,48x48,144x144">
    <meta name="msapplication-TileImage" content="<?php echo $config->getFavicon(true); ?>">
    <meta name="robots" content="index, follow" />
    <meta name="description" content="<?php echo getSEODescription($video->getTitle()); ?>">
    <?php
    getOpenGraph($videos_id);
    ?>
    <title><?php echo htmlentities(getSEOTitle($video->getTitle())); ?></title>
</head>

<body>
    <section>
        <h1><?php echo $video->getTitle(); ?></h1>
        <video controls poster="<?php echo Video::getPoster($video->getId()); ?>" style="width: 100%;">
            <?php
            echo getSources($video->getFilename());
            ?>
            Your browser does not support the video tag.
        </video>
        <p><?php echo $video->getDescription(); ?></p>
        <?php
        getLdJson($videos_id);
        getItemprop($videos_id);
        ?>
    </section>
    <section>
        <h2><?php echo __('Related Videos'); ?></h2>
        <?php
        foreach ($relatedVideos as $key => $value) {
        ?>
            <article>
                <h3>
                    <a href="<?php echo Video::getURL($value['id']); ?>" title="<?php echo $value['title']; ?>">
                        <?php echo $value['title']; ?>
                    </a>
                </h3>
                <?php
                getLdJson($value['id']);
                getItemprop($value['id']);
                ?>
            </article>
        <?php
        }
        ?>
    </section>
</body>

</html>
<?php
exit;
?>