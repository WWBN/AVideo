<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

$plugin = YouPHPTubePlugin::loadPluginIfEnabled('CloneSite');

if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: VAST</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.css"/>
    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fas fa-clone"></i> <?php echo __("Manage Clones"); ?>
                </div>
                <div class="panel-body">
                    <table id="campaignTable" class="display" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th><?php echo __("URL"); ?></th>
                                <th><?php echo __("Key"); ?></th>
                                <th><?php echo __("Last Clone"); ?></th>
                                <th><?php echo __("Status"); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th><?php echo __("URL"); ?></th>
                                <th><?php echo __("Key"); ?></th>
                                <th><?php echo __("Last Clone"); ?></th>
                                <th><?php echo __("Status"); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div id="btnModelLinks" style="display: none;">
            <div class="btn-group pull-right">
                <button href="" class="editor_status btn {status-class} btn-xs">
                    <i class="{status-icon}"></i> {status-text}
                </button>
                <button href="" class="editor_delete_link btn btn-danger btn-xs">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.js"></script>

        <script type="text/javascript">

            $(document).ready(function () {

                var tableLinks = $('#campaignTable').DataTable({
                    "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/CloneSite/clones.json.php",
                    "columns": [
                        {"data": "url"},
                        {"data": "key"},
                        {"data": "last_clone_request"},
                        {
                            data: 'status',
                            "render": function (data, type, full, meta) {
                                var content = "<span class='label label-success'>Active</span>";
                                if(full.status === 'i'){
                                    content = "<span class='label label-danger'>Inactive</span>";
                                }
                                return content;
                            }
                        },
                        {
                            data: 'status',
                            "render": function (data, type, full, meta) {
                                var content = $('#btnModelLinks').html();
                                if(full.status === 'i'){
                                    content = content.replace("{status-class}", "btn-success");
                                    content = content.replace("{status-icon}", "fas fa-check-square");
                                    content = content.replace("{status-text}", "Activate");
                                }else{
                                    content = content.replace("{status-class}", "btn-default");
                                    content = content.replace("{status-icon}", "fas fa-ban");
                                    content = content.replace("{status-text}", "Deactivate");
                                }
                                return content;
                            }
                        }
                    ],
                    select: true,
                });
                $('#campaignTable').on('click', 'button.editor_status', function (e) {
                    e.preventDefault();
                    var tr = $(this).closest('tr')[0];
                    var data = tableLinks.row(tr).data();
                    modal.showPleaseWait();
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $global['webSiteRootURL']; ?>plugin/CloneSite/changeStatus.json.php",
                        data: data

                    }).done(function (resposta) {
                        if (resposta.error) {
                            swal("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                        }
                        tableLinks.ajax.reload();
                        modal.hidePleaseWait();
                    });
                });
                $('#campaignTable').on('click', 'button.editor_delete_link', function (e) {
                    e.preventDefault();
                    var tr = $(this).closest('tr')[0];
                    var data = tableLinks.row(tr).data();
                    swal({
                        title: "<?php echo __("Are you sure?"); ?>",
                        text: "<?php echo __("You will not be able to recover this action!"); ?>",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "<?php echo __("Yes, delete it!"); ?>",
                        closeOnConfirm: true
                    },
                            function () {
                                modal.showPleaseWait();
                                $.ajax({
                                    type: "POST",
                                    url: "<?php echo $global['webSiteRootURL']; ?>plugin/CloneSite/delete.json.php",
                                    data: data

                                }).done(function (resposta) {
                                    if (resposta.error) {
                                        swal("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                                    }
                                    tableLinks.ajax.reload();
                                    modal.hidePleaseWait();
                                });
                            });
                });


            });
        </script>
    </body>
</html>
