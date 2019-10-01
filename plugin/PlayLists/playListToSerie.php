<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
if (!User::isLogged()) {
    die('{"error":"' . __("Permission denied") . '"}');
}
$obj = YouPHPTubePlugin::getObjectDataIfEnabled('PlayLists');

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
if(!empty($_GET['action'])){
    switch ($_GET['action']) {
        case 'delete':
            $id = PlayLists::removeSerie($serie_playlists_id);
            if(empty($id)){
                $msg = "Serie NOT deleted";
            }
            break;
        case 'create':
            $id = PlayLists::saveSerie($serie_playlists_id);
            if(empty($id)){
                $msg = "Serie NOT saved";
            }
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-3.3.1.min.js" type="text/javascript"></script>

        <?php
        echo YouPHPTubePlugin::getHeadCode();
        ?>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="view/img/favicon.ico">
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/fontawesome-free-5.5.0-web/css/all.min.css" rel="stylesheet" type="text/css"/>
        <style>


        </style>
    </head>

    <body style="background-color: transparent;">
        <div class="container-fluid">
            <h1><?php echo $pl->getName(); ?></h1>
            <?php
            $videoPL = PlayLists::isPlayListASerie($serie_playlists_id);
            if (!empty($videoPL)) {
                ?>
                <a class="btn btn-primary btn-block" href="<?php echo $global['webSiteRootURL']; ?>mvideos?iframe=1&video_id=<?php echo $videoPL['id']; ?>"><?php echo __('Edit'); ?></a>    
                <a class="btn btn-danger btn-block" href="<?php echo $global['webSiteRootURL']; ?>plugin/PlayLists/playListToSerie.php?playlist_id=<?php echo $serie_playlists_id; ?>&action=delete"><?php echo __('Delete'); ?></a> 
                <div class="alert alert-danger"> 
                    <p>Deleting this series will remove only the video linked to it. All items in your playlist will remain unchanged.</p> 
                </div>
                <?php
            } else {
                ?>
                <a class="btn btn-success btn-block" href="<?php echo $global['webSiteRootURL']; ?>plugin/PlayLists/playListToSerie.php?playlist_id=<?php echo $serie_playlists_id; ?>&action=create"><?php echo __('Create'); ?></a>    
                <div class="alert alert-info"> 
                    <p>In order to create series and make it easier to play videos in sequence, on this page we will create a video linked to this playlist.</p>
                    <p>This video can be set up just like any other video by adding posters and viewing permissions</p> 
                </div>
                <?php
            }
            if(!empty($msg)){
                ?>
                <div class="alert alert-danger"><?php echo $msg; ?></div>    
                <?php
            }
            ?>
        </div>
        <?php
        $jsFiles = array();
        $jsFiles[] = "view/js/script.js";
        $jsFiles[] = "view/js/js-cookie/js.cookie.js";
        $jsURL = combineFiles($jsFiles, "js");
        ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="<?php echo $jsURL; ?>" type="text/javascript"></script>
</html>