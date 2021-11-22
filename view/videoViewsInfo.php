<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

if (!empty($_REQUEST['hash'])) {
    $string = decryptString($_REQUEST['hash']);
    $obj = json_decode($string);
    $videos_id = intval($obj->videos_id);
} else {
    $videos_id = intval(@$_REQUEST['videos_id']);
    if (!Video::canEdit($videos_id)) {
        forbiddenPage("You cannot see this info");
    }
}
if (empty($videos_id)) {
    forbiddenPage("Videos ID is required");
}
$v = new Video('', '', $videos_id);
//var_dump($total);exit;
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title>Videos View info</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.css"/>
        <style>
            #viewInfoTitle .thumbsImage{
                width: 150px;
            }
            #viewInfoTitle{
                position: relative;
            }
            #buttonsGroup{
                position: absolute;
                right:10px;
                top: 10px;
            }
        </style>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <br>
            <div class="panel panel-default">
                <div class="panel-heading clearfix" id="viewInfoTitle">
                    <?php
                    echo Video::getVideosListItem($videos_id);
                    //echo $v->getTitle();
                    $obj = new stdClass();
                    $obj->videos_id = $videos_id;
                    $hash = encryptString($obj);
                    ?>
                    <div class="btn-group" role="group" aria-label="Basic example" id="buttonsGroup">
                        <button type="button" class="btn btn-default" onclick="copyToClipboard(webSiteRootURL + 'view/videoViewsInfo.php?hash=<?php echo $hash; ?>');"><i class="fas fa-copy"></i> <?php echo __('Share link'); ?></button>
                        <a href="<?php echo $global['webSiteRootURL']; ?>view/videoViewsInfo.csv.php?videos_id=<?php echo $videos_id; ?>&rowCount=9999&hash=<?php echo $hash; ?>" class="btn btn-primary" >
                            <i class="fas fa-file-csv"></i> <?php echo __('CSV File'); ?>
                        </a>
                    </div>
                </div>
                <div class="panel-body">
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
                    <table class="table table-hover" id="VideoViewsInfo">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>When</th>
                                <th>Time</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>User</th>
                                <th>When</th>
                                <th>Time</th>
                                <th>Location</th>
                            </tr>
                        </tfoot>
                    </table>
                    <?php
                    echo $pag;
                    ?>
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.js"></script>
        <script type="text/javascript">
                            $(document).ready(function () {
                                var VideoViewsInfo = $('#VideoViewsInfo').DataTable({
                                    "order": [[1, "desc"]],
                                    serverSide: true,
                                    "ajax": "<?php echo $global['webSiteRootURL']; ?>view/videoViewsInfo.json.php?videos_id=<?php echo $videos_id; ?>&hash=<?php echo @$_REQUEST['hash']; ?>",
                                                "columns": [
                                                    {data: 'users_id', render: function (data, type, row) {
                                                            return row.users
                                                        }},
                                                    {data: 'when', render: function (data, type, row) {
                                                            return row.when_human
                                                        }},
                                                    {data: 'seconds_watching_video', render: function (data, type, row) {
                                                            return row.seconds_watching_video_human
                                                        }},
                                                    {orderable: false, render: function (data, type, row) {
                                                            return row.location_name
                                                        }}
                                                ],
                                                select: true,
                                            });
                                        });
        </script>
    </body>
</html>
