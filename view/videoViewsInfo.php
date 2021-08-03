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
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <div class="panel panel-default">
                <div class="panel-heading">

                    <h1>
                        <?php
                        echo $v->getTitle();
                        ?>
                    </h1>
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
                    serverSide: true,
                    "ajax": "<?php echo $global['webSiteRootURL']; ?>view/videoViewsInfo.json.php?videos_id=<?php echo $videos_id; ?>",
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
