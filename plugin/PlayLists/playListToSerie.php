<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
if (!User::isLogged()) {
    die('{"error":"' . __("Permission denied") . '"}');
}
$obj = AVideoPlugin::getObjectDataIfEnabled('PlayLists');

if (empty($obj)) {
    echo "Not enabled";
    exit;
}

$serie_playlists_id = intval(@$_GET['playlist_id']);
if (empty($serie_playlists_id)) {
    echo "Playlist ID Error";
    exit;
}

$pl = new PlayList($serie_playlists_id);
if (User::getId() != $pl->getUsers_id()) {
    die('{"error":"' . __("Permission denied") . '"}');
}
$msg = "";
if (!empty($_GET['action'])) {
    switch ($_GET['action']) {
        case 'delete':
            $id = PlayLists::removeSerie($serie_playlists_id);
            if (empty($id)) {
                $msg = "Serie NOT deleted";
            }
            break;
        case 'create':
            $id = PlayLists::saveSerie($serie_playlists_id);
            if (empty($id)) {
                $msg = "Serie NOT saved";
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <script src="<?php echo getCDN(); ?>view/js/jquery-3.5.1.min.js" type="text/javascript"></script>

        <?php
        echo AVideoPlugin::getHeadCode();
        ?>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="view/img/favicon.ico">
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <link href="<?php echo getCDN(); ?>view/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getCDN(); ?>view/css/fontawesome-free-5.5.0-web/css/all.min.css" rel="stylesheet" type="text/css"/>
        <style>


        </style>
    </head>

    <body style="background-color: transparent;">
        <div class="container-fluid">

            <div class="panel panel-info">
                <div class="panel-heading">
                    <h1><?php echo $pl->getName(); ?></h1>
                </div>
                <div class="panel-body">
                    <?php
                    $videoPL = PlayLists::isPlayListASerie($serie_playlists_id);
                    if (!empty($videoPL)) {
                        ?>
                        <div class="alert alert-danger"> 
                            <p>Deleting this series will remove only the video linked to it. All items in your playlist will remain unchanged.</p> 
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="alert alert-info"> 
                            <p>In order to create series and make it easier to play videos in sequence, on this page we will create a video linked to this playlist.</p>
                            <p>This video can be set up just like any other video by adding posters and viewing permissions</p> 

                            Programs can be expanded to Series, when a program becomes a series, a new "video" is created. In this video, you can choose the title, thumbnail images, visibility, etc. in other words all the characteristics that a video can have you also can have for your playlist.

                            The benefit to this is that you can add all needed metadata to your program, for example, create a cover and a specific name for your program, and manage it all in the video management menu.
                        </div>
                        <?php
                    }
                    if (!empty($msg)) {
                        ?>
                        <div class="alert alert-danger"><?php echo $msg; ?></div>    
                        <?php
                    }
                    ?>
                </div>
                <div class="panel-footer text-right">
                    <?php
                    if (!empty($videoPL)) {
                        ?>
                        <a class="btn btn-primary" href="<?php echo $global['webSiteRootURL']; ?>mvideos?iframe=1&video_id=<?php echo $videoPL['id']; ?>">
                            <i class="fas fa-edit"></i> <?php echo __('Edit'); ?>
                        </a>    
                        <a class="btn btn-danger" href="<?php echo $global['webSiteRootURL']; ?>plugin/PlayLists/playListToSerie.php?playlist_id=<?php echo $serie_playlists_id; ?>&action=delete">
                            <i class="fas fa-trash"></i> <?php echo __('Delete'); ?>
                        </a> 
                        <?php
                    } else {
                        ?>
                        <a class="btn btn-success" href="<?php echo $global['webSiteRootURL']; ?>plugin/PlayLists/playListToSerie.php?playlist_id=<?php echo $serie_playlists_id; ?>&action=create">
                            <i class="fas fa-film"></i> <?php echo __('Create'); ?>
                        </a>    
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        $jsFiles = array();
        $jsFiles[] = "view/js/script.js";
        $jsFiles[] = "view/js/js-cookie/js.cookie.js";
        $jsURL = combineFiles($jsFiles, "js");
        ?>
        <script src="<?php echo getCDN(); ?>view/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="<?php echo $jsURL; ?>" type="text/javascript"></script>
</html>