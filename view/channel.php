<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';

if (empty($_GET['user_id'])) {
    if(User::isLogged()){
        $_GET['user_id'] = User::getId();
    }else{
        return false;
    }
}
$user_id = $_GET['user_id'];

$user = new User($user_id);
$uploadedVideos = Video::getAllVideos("viewable", $user_id);
$publicOnly = true;
if (User::isLogged() && $user_id == User::getId()) {
    $publicOnly = false;
}
$playlists = PlayList::getAllFromUser($user_id, $publicOnly);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Channel"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container">
            <div class="bgWhite list-group-item gallery" >
                <div class="row bg-info profileBg" style="background-image: url('<?php echo $global['webSiteRootURL'], $user->getBackgroundURL(); ?>')">
                    <img src="<?php echo User::getPhoto($user_id); ?>" alt="<?php echo $user->_getName(); ?>" class="img img-responsive img-thumbnail" style="max-width: 100px;"/>
                </div>            
                <div class="col-md-12">
                    <h1 class="pull-left"><?php echo $user->_getName(); ?></h1> 
                    <span class="pull-right">
                    <?php
                    echo Subscribe::getButton($user_id);
                    ?>
                    </span>
                </div>
                <div class="col-md-12">
                    <?php
                    foreach ($playlists as $playlist) {
                        $videosArrayId = PlayList::getVideosIdFromPlaylist($playlist['id']);
                        if (empty($videosArrayId)) {
                            continue;
                        }
                        $videos = Video::getAllVideos("viewable", false, false, $videosArrayId);
                        ?>           
                            <h1><?php echo $playlist['name']; ?></h1>
                            <?php
                            foreach ($videos as $value) {
                                $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
                                ?>
                                <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6 galleryVideo ">
                                    <a href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>" >
                                        <?php
                                        if ($value['type'] !== "audio") {
                                            $poster = "{$global['webSiteRootURL']}videos/{$value['filename']}.jpg";
                                        } else {
                                            $poster = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
                                        }
                                        ?>
                                        <img src="<?php echo $poster; ?>" alt="<?php echo $value['title']; ?>" class="img img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" />
                                        <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                                    </a>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>">
                                        <h2><?php echo $value['title']; ?></h2>
                                    </a>
                                    <span class="watch-view-count col-lg-6" itemprop="interactionCount"><?php echo number_format($value['views_count'], 0); ?> <?php echo __("Views"); ?></span>
                                    <?php
                                    $value['tags'] = Video::getTags($value['id']);
                                    foreach ($value['tags'] as $value2) {
                                        if ($value2->label === __("Group")) {
                                            ?>
                                            <span class="label label-<?php echo $value2->type; ?> col-lg-6 group"><?php echo $value2->text; ?></span>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                                <?php
                            }
                            ?>  
                        <?php
                    }
                    ?>
                </div>            
                <div class="col-md-12">
                    <h1>Uploads</h1>
                    <?php
                    foreach ($uploadedVideos as $value) {
                        $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
                        ?>
                        <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6 galleryVideo ">
                            <a href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>" >
                                <?php
                                if ($value['type'] !== "audio") {
                                    $poster = "{$global['webSiteRootURL']}videos/{$value['filename']}.jpg";
                                } else {
                                    $poster = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
                                }
                                ?>
                                <img src="<?php echo $poster; ?>" alt="<?php echo $value['title']; ?>" class="img img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" />
                                <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                            </a>
                            <a href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>">
                                <h2><?php echo $value['title']; ?></h2>
                            </a>
                            <span class="watch-view-count col-lg-6" itemprop="interactionCount"><?php echo number_format($value['views_count'], 0); ?> <?php echo __("Views"); ?></span>
                            <?php
                            $value['tags'] = Video::getTags($value['id']);
                            foreach ($value['tags'] as $value2) {
                                if ($value2->label === __("Group")) {
                                    ?>
                                    <span class="label label-<?php echo $value2->type; ?> col-lg-6 group"><?php echo $value2->text; ?></span>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?> 
                </div>
            </div>
        </div>

        <?php
        include 'include/footer.php';
        ?>

    </body>
</html>



