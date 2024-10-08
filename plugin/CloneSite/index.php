<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

$plugin = AVideoPlugin::loadPluginIfEnabled('CloneSite');

if (!User::isAdmin()) {
    forbiddenPage("You can not do this");
    exit;
}
$_page = new Page(array('Clone Site'));
$_page->setExtraScripts(
    array(
        'view/css/DataTables/datatables.min.js',
        'view/js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js'
    )
);
$_page->setExtraStyles(
    array(
        'view/css/DataTables/datatables.min.css',
        'view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'
    )
);
?><div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fas fa-clone"></i> <?php echo __("Manage Clones"); ?>
        </div>
        <div class="panel-body">
            <table id="campaignTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
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
<script type="text/javascript">
    $(document).ready(function() {

        var tableLinks = $('#campaignTable').DataTable({
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/CloneSite/clones.json.php",
            "columns": [{
                    "data": "url"
                },
                {
                    "data": "key"
                },
                {
                    "data": "last_clone_request"
                },
                {
                    data: 'status',
                    "render": function(data, type, full, meta) {
                        var content = "<span class='label label-success'>Active</span>";
                        if (full.status === 'i') {
                            content = "<span class='label label-danger'>Inactive</span>";
                        }
                        return content;
                    }
                },
                {
                    data: 'status',
                    "render": function(data, type, full, meta) {
                        var content = $('#btnModelLinks').html();
                        if (full.status === 'i') {
                            content = content.replace("{status-class}", "btn-success");
                            content = content.replace("{status-icon}", "fas fa-check-square");
                            content = content.replace("{status-text}", "Activate");
                        } else {
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
        $('#campaignTable').on('click', 'button.editor_status', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = tableLinks.row(tr).data();
            modal.showPleaseWait();
            $.ajax({
                type: "POST",
                url: "<?php echo $global['webSiteRootURL']; ?>plugin/CloneSite/changeStatus.json.php",
                data: data

            }).done(function(resposta) {
                if (resposta.error) {
                    avideoAlert("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                }
                tableLinks.ajax.reload();
                modal.hidePleaseWait();
            });
        });
        $('#campaignTable').on('click', 'button.editor_delete_link', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = tableLinks.row(tr).data();

            swal({
                    title: "<?php echo __("Are you sure?"); ?>",
                    text: "<?php echo __("You will not be able to recover this action!"); ?>",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then(function(willDelete) {
                    if (willDelete) {
                        modal.showPleaseWait();
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $global['webSiteRootURL']; ?>plugin/CloneSite/delete.json.php",
                            data: data

                        }).done(function(resposta) {
                            if (resposta.error) {
                                avideoAlert("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                            }
                            tableLinks.ajax.reload();
                            modal.hidePleaseWait();
                        });
                    }
                });
        });
    });
</script>
<?php
$_page->print();
?>