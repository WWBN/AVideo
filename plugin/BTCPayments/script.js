// Socket notification sent when a Bitcoin invoice of this user changes (BTCPayments::processVerifiedInvoice()).
document.addEventListener('BTCPayments', function (event) {
    var invoice = event.detail || {};
    var texts = typeof BTCPaymentsTexts === 'object' ? BTCPaymentsTexts : {};
    // The Bitcoin payment page of this invoice shows the status itself.
    if (typeof window.btcCheckoutInvoice === 'undefined' || window.btcCheckoutInvoice !== invoice.id) {
        switch (invoice.status) {
            case "Processing":
                if (texts.processing) {
                    avideoToastInfo(texts.processing);
                }
                break;
            case "Settled":
                if (texts.settled) {
                    avideoToastSuccess(texts.settled);
                }
                if (typeof updateYPTWallet === 'function') {
                    updateYPTWallet();
                }
                break;
        }
    }
    if (typeof Btc_invoicestableVar !== 'undefined' && Btc_invoicestableVar) {
        Btc_invoicestableVar.ajax.reload(null, false); // false keeps pagination
    }
});
