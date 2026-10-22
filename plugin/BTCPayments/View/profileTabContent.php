<div id="<?php echo $tabId ?? 'btcPaymentsHistory'; ?>" class="tab-pane fade in btcPayments-history">
    <div class="panel panel-default">
        <div class="panel-heading"><i class="fab fa-bitcoin" aria-hidden="true"></i> <?php echo __('Bitcoin payments'); ?></div>
        <div class="panel-body">
            <table id="Btc_invoicesTable" class="table table-striped table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th><?php echo __('Date'); ?></th>
                        <th class="hidden-xs"><?php echo __('Description'); ?></th>
                        <th><?php echo __('Amount'); ?></th>
                        <th class="hidden-xs hidden-sm"><?php echo __('Bitcoin'); ?></th>
                        <th><?php echo __('Status'); ?></th>
                        <th></th>
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
        var btcTexts = <?php echo json_encode([
            'waiting' => __('Waiting for payment'),
            'partial' => __('Partially paid'),
            'processing' => __('Confirming'),
            'paid' => __('Paid'),
            'rented' => __('Rental ready'),
            'live_ready' => __('Event access ready'),
            'benefit_failed' => __('Paid — purchase not completed'),
            'expired' => __('Expired'),
            'late' => __('Paid late — contact support'),
            'incomplete' => __('Incomplete — contact support'),
            'invalid' => __('Invalid'),
            'pay_now' => __('Pay now'),
            'details' => __('Details'),
        ]); ?>;

        function escapeText(text) {
            return $('<div>').text(text === null || text === undefined ? '' : String(text)).html();
        }

        function statusLabel(s) {
            if (!s) {
                return '';
            }
            var label = ['default', btcTexts.waiting];
            if (s.credited) {
                label = s.fulfillment === 'benefit_failed' ? ['warning', btcTexts.benefit_failed]
                    : ['success', btcTexts[s.fulfillment] || btcTexts.paid];
            } else if (s.status === 'new') {
                label = s.detail === 'PaidPartial' ? ['warning', btcTexts.partial] : ['info', btcTexts.waiting];
            } else if (s.status === 'processing' || s.status === 'settled') {
                label = ['primary', btcTexts.processing];
            } else if (s.status === 'expired') {
                label = s.detail === 'PaidLate' ? ['warning', btcTexts.late]
                    : (s.detail === 'PaidPartial' ? ['warning', btcTexts.incomplete] : ['default', btcTexts.expired]);
            } else if (s.status === 'invalid') {
                label = ['danger', btcTexts.invalid];
            }
            return '<span class="label label-' + label[0] + '">' + escapeText(label[1]) + '</span>';
        }

        Btc_invoicestableVar = $('#Btc_invoicesTable').DataTable({
            serverSide: true,
            "ajax": webSiteRootURL + "plugin/BTCPayments/View/Btc_invoices/list.json.php",
            "order": [
                [0, "desc"]
            ],
            "columns": [{
                    "data": "id",
                    "render": function(data, type, row) {
                        var created = row.public && row.public.created ? row.public.created : row.created_php_time;
                        return created ? escapeText(new Date(created * 1000).toLocaleString()) : '';
                    }
                },
                {
                    "data": null,
                    "orderable": false,
                    "className": "hidden-xs",
                    "render": function(data, type, row) {
                        return row.public ? escapeText(row.public.description) : '';
                    }
                },
                {
                    "data": null,
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.public ? escapeText(row.public.amount) : '';
                    }
                },
                {
                    "data": null,
                    "orderable": false,
                    "className": "hidden-xs hidden-sm",
                    "render": function(data, type, row) {
                        return row.public && row.public.btc_paid ? escapeText(row.public.btc_paid + ' BTC') : '-';
                    }
                },
                {
                    "data": null,
                    "orderable": false,
                    "render": function(data, type, row) {
                        return statusLabel(row.public);
                    }
                },
                {
                    "data": null,
                    "orderable": false,
                    "className": "text-right",
                    "render": function(data, type, row) {
                        if (!row.public) {
                            return '';
                        }
                        var waiting = row.public.status === 'new' && !row.public.final;
                        return '<a class="btn btn-xs ' + (waiting ? 'btn-primary' : 'btn-default') + '" target="_top" href="'
                            + escapeText(row.public.checkout_page) + '">'
                            + (waiting ? '<i class="fab fa-bitcoin" aria-hidden="true"></i> ' + escapeText(btcTexts.pay_now) : escapeText(btcTexts.details))
                            + '</a>';
                    }
                }
            ]
        });
    });
</script>
