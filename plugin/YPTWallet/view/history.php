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
?>
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
        var grid = $("#grid").bootgrid({
            labels: {
                noResults: "<?php echo __("No results found!"); ?>",
                all: "<?php echo __("All"); ?>",
                infos: "<?php echo __("Showing {{ctx.start}} to {{ctx.end}} of {{ctx.total}} entries"); ?>",
                loading: "<?php echo __("Loading..."); ?>",
                refresh: "<?php echo __("Refresh"); ?>",
                search: "<?php echo __("Search"); ?>",
            },
            ajax: true,
            url: "<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/log.json.php?users_id=<?php echo $users_id; ?>",
            formatters: {
                "description": function(column, row) {
                    return row.information || row.description;
                },
                "status": function(column, row) {
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
                "balance": function(column, row) {
                    return row.balance_formated;
                },
                "previousBalance": function(column, row) {
                    return row.previous_wallet_balance_formated;
                }
            }
        }).on("loaded.rs.jquery.bootgrid", function() {
            <?php if (User::isAdmin()) { ?>
            grid.find(".command-status-success").on("click", function() {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                setStatus("success", row.id);
            });

            grid.find(".command-status-pending").on("click", function() {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                setStatus("pending", row.id);
            });

            grid.find(".command-status-canceled").on("click", function() {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                setStatus("canceled", row.id);
            });
            <?php } ?>
        });
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
                    $("#grid").bootgrid("reload");
                }
            }
        });
    }
    <?php } ?>
</script>
<?php
$_page->print();
?>
