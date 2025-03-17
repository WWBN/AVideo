document.addEventListener('BTCPayments', function (event) {
    console.log('BTCPayments', event.detail);

    switch (event.detail.status) {
        case "Processing":
            avideoToast('Your payment of ' + event.detail.totalBTCPaid + ' BTC is currently being processed. Please wait while we confirm the transaction.');
            break;
        case "Settled":
            avideoToastSuccess('Your payment of ' + event.detail.totalBTCPaid + ' BTC has been successfully completed. Thank you for your transaction!');
            break;
        default:
            console.log('No action required for status: ' + event.detail.status);
            break;
    }
    if (typeof Btc_invoicestableVar !== 'undefined') {
        Btc_invoicestableVar.ajax.reload(null, false); // false keeps pagination
    }
});
