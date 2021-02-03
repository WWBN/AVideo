<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/Channel.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';

if (isset($_SESSION['channelName'])) {
    _session_start();
    unset($_SESSION['channelName']);
}

$totalChannels = Channel::getTotalChannels();

if (!empty($_GET['page'])) {
    $_POST['current'] = intval($_GET['page']);
} else {
    $_POST['current'] = 1;
}

$users_id_array = VideoStatistic::getUsersIDFromChannelsWithMoreViews();

$current = $_POST['current'];
$_REQUEST['rowCount'] = 10;
$channels = Channel::getChannels(true, "u.id, '" . implode(",", $users_id_array) . "'");

$totalPages = ceil($totalChannels / $_REQUEST['rowCount']);
$metaDescription = __("Channels");
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Channels") . getSEOComplement() . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            #custom-search-input{
                padding: 3px;
                border: solid 1px #E4E4E4;
                border-radius: 6px;
                background-color: #fff;
            }

            #custom-search-input input{
                border: 0;
                box-shadow: none;
            }

            #custom-search-input button{
                margin: 2px 0 0 0;
                background: none;
                box-shadow: none;
                border: 0;
                color: #666666;
                padding: 0 8px 0 10px;
                border-left: solid 1px #ccc;
            }

            #custom-search-input button:hover{
                border: 0;
                box-shadow: none;
                border-left: solid 1px #ccc;
            }

            #custom-search-input .glyphicon-search{
                font-size: 23px;
            }
        </style>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container-fluid">
            <div class="panel panel-default" >
                <div class="panel-heading">
                    <form id="search-form" name="search-form" action="<?php echo $global['webSiteRootURL']; ?>channels" method="get">
                        <div id="custom-search-input">
                            <div class="input-group col-md-12">
                                <input type="search" name="searchPhrase" class="form-control input-lg" placeholder="<?php echo __("Search Channels"); ?>" value="<?php
                                echo htmlentities(@$_GET['searchPhrase']);
                                unsetSearch();
                                ?>" />
                                <span class="input-group-btn">
                                    <button class="btn btn-info btn-lg" type="submit">
                                        <i class="glyphicon glyphicon-search"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="panel-body" >
                    <ul class="pages">
                    </ul>
                    <?php
                    foreach ($channels as $value) {
                        $get = array('channelName' => $value['channelName']);
                        ?>
                        <div class="panel panel-default">
                            <div class="panel-heading" style="position: relative;">
                                <img src="<?php echo User::getPhoto($value['id']); ?>"
                                     class="img img-thumbnail img-responsive pull-left" style="max-height: 100px; margin: 0 10px;" alt="User Photo" />
                                <a href="<?php echo User::getChannelLink($value['id']); ?>" class="btn btn-default">
                                    <i class="fas fa-play-circle"></i>
                                    <?php
                                    echo User::getNameIdentificationById($value['id']);
                                    ?>
                                </a>
                                <div style="position: absolute; right: 10px; top: 10px;">
                                    <?php
                                    echo User::getBlockUserButton($value['id']);
                                    ?>
    <?php echo Subscribe::getButton($value['id']); ?>
                                </div>
                            </div>
                            <div class="panel-body gallery ">
                                <div>
    <?php echo stripslashes(str_replace('\\\\\\\n', '<br/>', $value['about'])); ?>
                                </div>
                                
                                <div class="clearfix" style="margin-bottom: 10px;"></div>
                                <div class="row clear clearfix galeryRowElement">
                                    <?php
                                    $_POST['current'] = 1;
                                    $_REQUEST['rowCount'] = 6;
                                    $_POST['sort']['created'] = "DESC";
                                    $uploadedVideos = Video::getAllVideosAsync("viewable", $value['id']);
                                    
                                    createGallerySection($uploadedVideos, dechex(crc32($value['channelName'])));
                                    ?>
                                </div>
                            </div>
                            <div class="panel-footer " style="font-size: 0.8em">
                                <div class=" text-muted align-right">
    <?php echo VideoStatistic::getChannelsTotalViews($value['id']), " ", __("Views in the last 30 days"); ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }

                    echo getPagination($totalPages, $current, "{$global['webSiteRootURL']}channels?page={page}");
                    ?>
                </div>
            </div>
        </div>

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
