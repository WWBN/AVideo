<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugin Audit"));
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Audit</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.css"/>
        <style>
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">

            <div class="panel panel-default">
                <div class="panel-body">
                    <table id="auditTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Method</th>
                                <th>Statement</th>
                                <th>Format</th>
                                <th>Values</th>
                                <th>Created</th>
                                <th>User</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Class</th>
                                <th>Method</th>
                                <th>Statement</th>
                                <th>Format</th>
                                <th>Values</th>
                                <th>Created</th>
                                <th>User</th>
                                <th>IP</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.js"></script>
        <script>
            $(document).ready(function () {
                var auditTable = $('#auditTable').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "ajax": {
                        "url": "<?php echo $global['webSiteRootURL']; ?>plugin/Audit/page/audits.json.php",
                    },
                    "columns": [
                        {"data": "class"},
                        {"data": "method"},
                        {"data": "statement"},
                        {"data": "formats"},
                        {"data": "values"},
                        {"data": "created"},
                        {"data": "user"},
                        {"data": "ip"},
                    ],
                    select: true,
                    "order": [[5, "desc"]]
                });
            });
        </script>
    </body>
</html>
