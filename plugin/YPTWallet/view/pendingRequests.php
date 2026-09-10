<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

if (!User::isAdmin()) {
    forbiddenPage("You can not do this");
    exit;
}

$_page = new Page(array('Pending Requests'));
$_page->loadBasicCSSAndJS();
?>
<style>
.bootgrid-table td{
    white-space: unset;
}
</style>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php
            echo __("Pending Requests");
            ?>
        </div>
        <div class="panel-body">
            <div class="row bgWhite list-group-item">
                <table id="grid" class="table table-condensed table-hover table-striped">
                    <thead>
                        <tr>
                            <th data-column-id="user" data-width="150px"><?php echo __("User"); ?></th>
                            <th data-column-id="valueText" data-width="150px"><?php echo __("Value"); ?></th>
                            <th data-column-id="description" data-formatter="description"><?php echo __("Description"); ?></th>
                            <th data-column-id="status" data-formatter="status" data-width="150px"><?php echo __("Status"); ?></th>
                            <th data-column-id="created" data-order="desc" data-formatter="created" data-width="150px"><?php echo __("Date"); ?></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {

        var pendingRequestsFormatters = {
            "status": function(row) {
                var status = "";
                status = "<div class=\"btn-group\"><button class='btn btn-success btn-xs command-status-success'>Confirm</button>";
                status += "<button class='btn btn-danger btn-xs command-status-canceled'>Cancel</button><div>";
                return status;
            },
            "description": function(row) {
                if (row.information) {
                    return row.information;
                } else {
                    return row.description;
                }
            },
            "created": function(row) {
                return '<span class="pendingTimers">' + row.created + '</span>';
            }
        };

        var dt = avideoDataTable("#grid", {
            avideoControls: true,
            serverSide: true,
            order: [[4, 'desc']],
            language: {
                zeroRecords: "<?php echo __("No results found!"); ?>",
                loadingRecords: "<?php echo __("Loading..."); ?>",
                search: "<?php echo __("Search"); ?>",
            },
            columns: [
                { data: 'user', width: '150px', orderable: false },
                { data: 'value', width: '150px', render: function(data, type, row) { return type === 'display' ? row.valueText : data; } },
                { data: 'description', render: function(data, type, row) { return type === 'display' ? pendingRequestsFormatters.description(row) : data; } },
                { data: 'status', width: '150px', render: function(data, type, row) { return type === 'display' ? pendingRequestsFormatters.status(row) : data; } },
                { data: 'created', width: '150px', render: function(data, type, row) { return type === 'display' ? pendingRequestsFormatters.created(row) : data; } }
            ],
            ajax: avideoDataTableAjax({ url: "<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/pendingRequests.json.php" })
        }).on('draw.dt', function() {
            createTimer('.pendingTimers');
        });

        var grid = $("#grid");
        grid.off('click.pendingRequests', '.command-status-success').on('click.pendingRequests', '.command-status-success', function(e) {
            var row = dt.row($(this).closest('tr')).data();
            setStatus("success", row.id);
        });

        grid.off('click.pendingRequests', '.command-status-canceled').on('click.pendingRequests', '.command-status-canceled', function(e) {
            var row = dt.row($(this).closest('tr')).data();
            setStatus("canceled", row.id);
        });
    });

    function setStatus(status, wallet_log_id) {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/YPTWallet/view/changeLogStatus.json.php',
            type: "POST",
            data: {
                status: status,
                wallet_log_id: wallet_log_id
            },
            success: function(response) {
                $(".walletBalance").text(response.walletBalance);
                modal.hidePleaseWait();
                if (response.error) {
                    setTimeout(function() {
                        avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    }, 500);
                } else {
                    $("#grid").DataTable().ajax.reload(null, false);
                }
            }
        });
    }
</script>
<?php
$_page->print();
?>
