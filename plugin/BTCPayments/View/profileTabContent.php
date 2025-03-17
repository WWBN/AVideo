<div id="<?php echo $tabId; ?>" class="tab-pane fade in" style="padding: 10px 0;">
    <div class="panel panel-default">
        <div class="panel-heading"><i class="fas fa-file-invoice-dollar"></i> <?php echo __("BTC Invoices"); ?></div>
        <div class="panel-body">
            <table id="Btc_invoicesTable" class="table table-bordered table-striped table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="hidden-sm hidden-xs">#</th>
                        <th class="hidden-sm hidden-xs">Invoice</th>
                        <th class="hidden-xs"><i class="fas fa-info-circle"></i> Description</th>
                        <th><i class="fab fa-bitcoin"></i> BTC Amount</th>
                        <th><i class="fas fa-dollar-sign"></i> Amount</th>
                        <th><i class="fas fa-calendar-alt"></i> Created Date</th>
                        <th class="hidden-sm hidden-xs"><i class="fas fa-calendar-check"></i> Paid Date</th>
                        <th class="hidden-sm hidden-xs"><i class="fas fa-receipt"></i> Status</th>
                        <th><i class="fas fa-link"></i> Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    var Btc_invoicestableVar;
    $(document).ready(function() {
        Btc_invoicestableVar = $('#Btc_invoicesTable').DataTable({
            serverSide: true,
            "ajax": webSiteRootURL + "plugin/BTCPayments/View/Btc_invoices/list.json.php",
            "order": [
                [0, "desc"]
            ],
            "columns": [{
                    "data": "id",
                    "className": "hidden-sm hidden-xs",
                },
                {
                    "data": "json_object.id",
                    "defaultContent": "-",
                    "className": "hidden-sm hidden-xs",
                },
                {
                    "data": "json_object.metadata.description",
                    "defaultContent": "-",
                    "className": "hidden-xs",
                },
                {
                    "data": "amount_btc",
                    "render": function(data) {
                        return data + ' BTC';
                    }
                },
                {
                    "data": "amount_currency",
                    "render": function(data, type, row) {
                        var currency = row.currency || 'USD';
                        return currency + ' ' + parseFloat(data).toFixed(2);
                    }
                },
                {
                    "data": "created_php_time",
                    "render": function(data) {
                        return new Date(data * 1000).toLocaleString();
                    }
                },
                {
                    "data": "modified_php_time",
                    "render": function(data, type, row) {
                        return row.json_object.status === "Settled" ? new Date(data * 1000).toLocaleString() : '-';
                    },
                    "className": "hidden-sm hidden-xs",
                },
                {
                    "data": "json_object.status",
                    "render": function(data) {
                        return data === "Settled" ?
                            '<span class="label label-success"><i class="fas fa-check-circle"></i> Paid</span>' :
                            '<span class="label label-warning"><i class="fas fa-clock"></i> Pending</span>';
                    },
                    "className": "hidden-sm hidden-xs",
                },
                {
                    "data": "json_object.checkoutLink",
                    "render": function(data, type, row) {
                        var status = row.json_object.status;
                        var buttonText = status === "Settled" ? '<i class="fas fa-receipt"></i> View Receipt' : '<i class="fas fa-credit-card"></i> Pay Now';
                        return '<button onclick="avideoModalIframeSmall(\'' + data + '\');" class="btn ' + (status === "Settled" ? 'btn-success' : 'btn-primary') + ' btn-sm" target="_blank">' + buttonText + '</button>';
                    }
                }
            ],
            select: true,
            "drawCallback": function(settings) {
                var api = this.api();
                var hasPending = false;
                var pendingPayment = null;

                // Check for pending payments
                api.rows().every(function() {
                    var rowData = this.data();
                    if (rowData.json_object && (rowData.json_object.status !== "Settled")) {
                        hasPending = true;
                        pendingPayment = rowData;
                        return false; // Exit loop early if a pending payment is found
                    }
                });

                if (hasPending) {
                    let message =
                        "⚠️ You have a pending payment of <strong>" + pendingPayment.amount_btc +
                        " BTC</strong>.<br>" +
                        "If you want to make a new payment, you can proceed, but please ensure it will not be a duplicate transaction.";

                    console.warn(message);
                    avideoAlertInfo(message);
                } else {
                    handleNoPendingInvoices(); // Call function if no pending invoices exist
                }
            }


        });
    });
    // Function to call when no pending invoices exist
    function handleNoPendingInvoices() {
        console.log("No pending BTC invoices found.");
        // You can replace this with another action, like displaying a message or redirecting.
    }
</script>
