<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

if (!User::isLogged()) {
    forbiddenPage("You can not do this");
    exit;
}

$users_id = !empty($_GET['users_id']) ? $_GET['users_id'] : User::getId();
$_page = new Page(array('History'));
$_page->loadBasicCSSAndJS();
?>
<style>
.bootgrid-table td{
    white-space: unset;
}
</style>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading" style="height: 70px;">
            <img src="<?php echo User::getPhoto($users_id); ?>" class="img img-responsive img-circle pull-left" style="height: 50px; margin-right: 10px;" alt="User Photo">
            <h1><?php echo User::getNameIdentificationById($users_id); ?></h1>
        </div>
        <div class="panel-body">
            <div class="row bgWhite list-group-item">
                <table id="grid" class="table table-condensed table-hover table-striped">
                    <thead>
                        <tr>
                            <th data-column-id="created" data-order="desc" data-width="150px"><?php echo __("Date"); ?></th>
                            <th data-column-id="valueText" data-width="120px"><?php echo __("Amount"); ?></th>
                            <th data-column-id="previousBalance" data-formatter="previousBalance" data-width="120px"><?php echo __("Previous Balance"); ?></th>
                            <th data-column-id="balance" data-formatter="balance" data-width="120px"><?php echo __("Balance"); ?></th>
                            <th data-column-id="type" data-width="150px"><?php echo __("Type"); ?></th>
                            <th data-column-id="description" data-formatter="description"><?php echo __("Description"); ?></th>
                            <th data-column-id="status" data-formatter="status" data-width="100px"><?php echo __("Status"); ?></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
if (AVideoPlugin::isEnabledByName('MonetizeUsers')) {
    include $global['systemRootPath'] . 'plugin/MonetizeUsers/View/report.php';
}
?>
<style>
    .bootgrid-table th,
    .bootgrid-table td {
        vertical-align: middle !important;
        font-size: 14px;
    }
    .bootgrid-table td .label {
        font-size: 13px;
    }
</style>
<script>
    $(document).ready(function() {
        var walletHistoryFormatters = {
            "description": function(row) {
                return row.information || row.description;
            },
            "status": function(row) {
                let label = {
                    success: 'label-success',
                    pending: 'label-warning',
                    canceled: 'label-danger'
                };
                let statusLabel = `<span class='label ${label[row.status] || "label-default"}'>${row.status}</span>`;
                <?php if (User::isAdmin()) { ?>
                if (row.type === "<?php echo YPTWallet::MANUAL_ADD; ?>" || row.type === "<?php echo YPTWallet::MANUAL_WITHDRAW; ?>") {
                    statusLabel += `<br><br>
                    <div class="btn-group">
                        <button class='btn btn-default btn-xs command-status-success'>Success</button>
                        <button class='btn btn-default btn-xs command-status-pending'>Pending</button>
                        <button class='btn btn-default btn-xs command-status-canceled'>Canceled</button>
                    </div>`;
                }
                <?php } ?>
                return statusLabel;
            },
            "balance": function(row) {
                return row.balance_formated;
            },
            "previousBalance": function(row) {
                return row.previous_wallet_balance_formated;
            }
        };

        var dt = avideoDataTable("#grid", {
            avideoControls: true,
            serverSide: true,
            order: [[0, 'desc']],
            language: {
                zeroRecords: "<?php echo __("No results found!"); ?>",
                loadingRecords: "<?php echo __("Loading..."); ?>",
                search: "<?php echo __("Search"); ?>",
            },
            columns: [
                { data: 'created', width: '150px' },
                { data: 'value', width: '120px', render: function(data, type, row) { return type === 'display' ? row.valueText : data; } },
                { data: 'previous_wallet_balance', width: '120px', render: function(data, type, row) { return type === 'display' ? walletHistoryFormatters.previousBalance(row) : data; } },
                { data: 'balance', width: '120px', orderable: false, render: function(data, type, row) { return type === 'display' ? walletHistoryFormatters.balance(row) : data; } },
                { data: 'type', width: '150px' },
                { data: 'description', render: function(data, type, row) { return type === 'display' ? walletHistoryFormatters.description(row) : data; } },
                { data: 'status', width: '100px', render: function(data, type, row) { return type === 'display' ? walletHistoryFormatters.status(row) : data; } }
            ],
            ajax: avideoDataTableAjax({ url: "<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/log.json.php?users_id=<?php echo $users_id; ?>" })
        });

        <?php if (User::isAdmin()) { ?>
        var grid = $("#grid");
        grid.off('click.walletHistory', '.command-status-success').on('click.walletHistory', '.command-status-success', function() {
            var row = dt.row($(this).closest('tr')).data();
            setStatus("success", row.id);
        });

        grid.off('click.walletHistory', '.command-status-pending').on('click.walletHistory', '.command-status-pending', function() {
            var row = dt.row($(this).closest('tr')).data();
            setStatus("pending", row.id);
        });

        grid.off('click.walletHistory', '.command-status-canceled').on('click.walletHistory', '.command-status-canceled', function() {
            var row = dt.row($(this).closest('tr')).data();
            setStatus("canceled", row.id);
        });
        <?php } ?>
    });

    <?php if (User::isAdmin()) { ?>
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
    <?php } ?>
</script>
<?php
$_page->print();
?>
