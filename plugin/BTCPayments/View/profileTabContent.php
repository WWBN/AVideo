<div id="<?php echo $tabId; ?>" class="tab-pane fade in" style="padding: 10px 0;">
    <div class="panel panel-default">
        <div class="panel-heading"><i class="fas fa-file-invoice-dollar"></i> <?php echo __("BTC Invoices"); ?></div>
        <div class="panel-body">
            <table id="Btc_invoicesTable" class="table table-bordered table-striped table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th><i class="fas fa-info-circle"></i> Description</th>
                        <th><i class="fab fa-bitcoin"></i> BTC Amount</th>
                        <th><i class="fas fa-dollar-sign"></i> Amount</th>
                        <th><i class="fas fa-calendar-alt"></i> Created Date</th>
                        <th><i class="fas fa-calendar-check"></i> Paid Date</th>
                        <th><i class="fas fa-receipt"></i> Status</th>
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
    $(document).ready(function() {
        var Btc_invoicestableVar = $('#Btc_invoicesTable').DataTable({
            serverSide: true,
            "ajax": webSiteRootURL + "plugin/BTCPayments/View/Btc_invoices/list.json.php",
            "order": [[0, "desc"]],
            "columns": [
                { "data": "id" },
                { "data": "json_object.id", "defaultContent": "-" },
                { "data": "json_object.metadata.description", "defaultContent": "-" },
                { "data": "amount_btc", "render": function(data) { return data + ' BTC'; } },
                { "data": "amount_currency", "render": function(data, type, row) {
                        var currency = row.currency || 'USD';
                        return currency + ' ' + parseFloat(data).toFixed(2);
                    }
                },
                { "data": "created_php_time", "render": function(data) {
                        return new Date(data * 1000).toLocaleString();
                    }
                },
                { "data": "modified_php_time", "render": function(data, type, row) {
                        return row.json_object.status === "Settled" ? new Date(data * 1000).toLocaleString() : '-';
                    }
                },
                { "data": "json_object.status", "render": function(data) {
                        return data === "Settled"
                            ? '<span class="label label-success"><i class="fas fa-check-circle"></i> Paid</span>'
                            : '<span class="label label-warning"><i class="fas fa-clock"></i> Pending</span>';
                    }
                },
                { "data": "json_object.checkoutLink", "render": function(data, type, row) {
                        var status = row.json_object.status;
                        var buttonText = status === "Settled" ? '<i class="fas fa-receipt"></i> View Receipt' : '<i class="fas fa-credit-card"></i> Pay Now';
                        return '<a href="' + data + '" class="btn ' + (status === "Settled" ? 'btn-success' : 'btn-primary') + ' btn-sm" target="_blank">' + buttonText + '</a>';
                    }
                }
            ],
            select: true,
        });
    });
</script>
