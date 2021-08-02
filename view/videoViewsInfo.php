<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$videos_id = intval(@$_REQUEST['videos_id']);

if (empty($videos_id)) {
    forbiddenPage("Videos IF is required");
}

if (!Video::canEdit($videos_id)) {
    forbiddenPage("You cannot see this info");
}

$_REQUEST['rowCount'] = 20;
$_POST['sort']['created'] = 'DESC';
        
$statistics = VideoStatistic::getAllFromVideos_id($videos_id);
$total = VideoStatistic::getTotalFromVideos_id($videos_id);
$totalPages = ceil($total/$_REQUEST['rowCount']);

$v = new Video('', '', $videos_id);

//var_dump($total);exit;
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo $titleTag . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <h1>
                <?php
                echo $v->getTitle();
                ?>
            </h1>
            <h3>
                <?php
                echo number_format_short($v->getViews_count()); 
                ?>
                Views and watched 
                <?php
                echo seconds2human($v->getTotal_seconds_watching()); 
                ?>
            </h3>
            <?php
            $pag = getPagination($totalPages);
            echo $pag;
            ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>When</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($statistics as $value) {
                        ?>
                        <tr>
                            <td>
                                <?php
                                echo User::getNameIdentificationById($value['users_id']);
                                ?>
                            </td>
                            <td>
                                <?php
                                echo humanTimingAgo($value['when']);
                                ?>
                            </td>
                            <td>
                                <?php
                                echo seconds2human($value['seconds_watching_video']);
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
                <tfoot>
                <th>User</th>
                <th>When</th>
                <th>Time</th>
                </tfoot>
            </table>
            <?php
            echo $pag;
            ?>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
